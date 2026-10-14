<?php

namespace ProjectsManager\Modules\Meetings\Controllers;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\RoomServiceClient;
use Agence104\LiveKit\VideoGrant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livekit\TrackSource;
use ProjectsManager\Meeting;
use ProjectsManager\MeetingBreakoutRoom;
use ProjectsManager\MeetingDocument;
use ProjectsManager\MeetingMinute;
use ProjectsManager\MeetingParticipantLog;
use ProjectsManager\MeetingRecording;
use ProjectsManager\Modules\Meetings\Handlers\CreateMeetingHandler;
use ProjectsManager\Modules\Meetings\Handlers\SendMeetingMessageHandler;
use ProjectsManager\Modules\Meetings\Repositories\MeetingRepository;
use ProjectsManager\Modules\Meetings\Services\MeetingAccessService;
use ProjectsManager\Modules\Tasks\Handlers\StoreTaskHandler;
use ProjectsManager\Project;
use ProjectsManager\User;
use ProjectsManager\Http\Controllers\Controller;

class MeetingsController extends Controller
{
    public function __construct(private readonly MeetingRepository $repository, private readonly MeetingAccessService $access) {}

    public function index(Project $project): View
    {
        abort_unless($this->access->can(request()->user(), $project, MeetingAccessService::VIEW), 403);
        return view('meetings.index', ['project' => $project, 'meetings' => $this->repository->accessibleFor(request()->user(), $project)]);
    }

    public function store(Request $request, Project $project, CreateMeetingHandler $handler): JsonResponse
    {
        abort_unless($this->access->can($request->user(), $project, MeetingAccessService::MANAGE), 403);
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'starts_at' => ['nullable', 'date']]);
        return response()->json(['meeting' => $handler->handle($request->user(), $project, $data)], 201);
    }

    public function show(Meeting $meeting): View
    {
        abort_unless($this->access->canAccess(request()->user(), $meeting), 403);
        return view('meetings.show', ['meeting' => $meeting, 'canManage' => $this->access->canAccess(request()->user(), $meeting, MeetingAccessService::MANAGE), 'canRecord' => $this->access->canAccess(request()->user(), $meeting, MeetingAccessService::RECORD)]);
    }

    private function roomService(): RoomServiceClient
    {
        return new RoomServiceClient(config('services.livekit.url'), config('services.livekit.key'), config('services.livekit.secret'));
    }

    public function presence(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $data = $request->validate(['event' => ['required', 'in:joined,left']]);
        if ($meeting->status === 'scheduled' && $data['event'] === 'joined') $meeting->update(['status' => 'live']);
        $log = MeetingParticipantLog::create(['meeting_id' => $meeting->id, 'user_id' => $request->user()->id, 'event' => $data['event']]);
        return response()->json(['log' => $log], 201);
    }

    public function presenceLogs(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        return response()->json(['logs' => $meeting->participantLogs()->with('user:id,name')->latest()->limit(200)->get()]);
    }

    public function livePresence(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        try {
            $response = $this->roomService()->listParticipants($meeting->room_name);
            $participants = collect($response->getParticipants())->map(fn ($participant) => [
                'identity' => $participant->getIdentity(),
                'name' => $participant->getName(),
            ])->values();
        } catch (\Throwable $e) {
            $participants = collect();
        }
        return response()->json(['participants' => $participants]);
    }

    public function moderate(Request $request, Meeting $meeting, User $user): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting, MeetingAccessService::MANAGE), 403);
        abort_unless($meeting->members()->whereKey($user->id)->exists(), 404);
        $data = $request->validate(['action' => ['required', 'in:mute_microphone,mute_camera']]);
        $source = $data['action'] === 'mute_microphone' ? TrackSource::MICROPHONE : TrackSource::CAMERA;
        $service = $this->roomService();
        try {
            $participant = $service->getParticipant($meeting->room_name, (string) $user->id);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Người này hiện không có trong phòng.'], 404);
        }
        $muted = false;
        foreach ($participant->getTracks() as $track) {
            if ($track->getSource() === $source) {
                $service->mutePublishedTrack($meeting->room_name, (string) $user->id, $track->getSid(), true);
                $muted = true;
            }
        }
        return response()->json(['muted' => $muted]);
    }

    public function documents(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        return response()->json(['documents' => $meeting->documents()->with('user:id,name')->latest()->get()]);
    }

    public function storeDocument(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $data = $request->validate(['file' => ['nullable', 'file', 'max:51200'], 'url' => ['nullable', 'url', 'max:2048'], 'name' => ['nullable', 'string', 'max:255']]);
        abort_if(!$request->hasFile('file') && empty($data['url']), 422, 'Cần chọn tệp hoặc nhập liên kết.');
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $document = $meeting->documents()->create(['user_id' => $request->user()->id, 'type' => MeetingDocument::TYPE_FILE, 'name' => $data['name'] ?? $file->getClientOriginalName(), 'path' => $file->store('meetings/' . $meeting->id . '/documents', 'public'), 'mime' => $file->getMimeType(), 'size' => $file->getSize()]);
        } else {
            $document = $meeting->documents()->create(['user_id' => $request->user()->id, 'type' => MeetingDocument::TYPE_LINK, 'name' => $data['name'] ?: $data['url'], 'url' => $data['url']]);
        }
        return response()->json(['document' => $document->load('user:id,name')], 201);
    }

    public function breakoutRooms(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        return response()->json(['rooms' => $meeting->breakoutRooms()->with(['task:id,name', 'members:id,name'])->whereNull('closed_at')->get()]);
    }

    public function storeBreakoutRooms(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting, MeetingAccessService::MANAGE), 403);
        $data = $request->validate(['rooms' => ['required', 'array', 'min:1'], 'rooms.*.name' => ['required', 'string', 'max:255'], 'rooms.*.task_id' => ['nullable', 'integer', 'exists:tasks,id'], 'rooms.*.member_ids' => ['array'], 'rooms.*.member_ids.*' => ['integer', 'exists:users,id']]);
        $memberIds = $meeting->members->modelKeys();
        $rooms = collect($data['rooms'])->map(function ($room) use ($meeting, $request, $memberIds) {
            $breakout = $meeting->breakoutRooms()->create(['task_id' => $room['task_id'] ?? null, 'created_by' => $request->user()->id, 'name' => $room['name'], 'room_name' => $meeting->room_name . '-breakout-' . Str::lower(Str::random(10))]);
            $breakout->members()->sync(collect($room['member_ids'] ?? [])->intersect($memberIds)->values()->all());
            return $breakout->load(['task:id,name', 'members:id,name']);
        });
        return response()->json(['rooms' => $rooms], 201);
    }

    public function breakoutToken(Request $request, Meeting $meeting, MeetingBreakoutRoom $room): JsonResponse
    {
        abort_unless($room->meeting_id === $meeting->id, 404);
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $isManager = $this->access->canAccess($request->user(), $meeting, MeetingAccessService::MANAGE);
        abort_unless($isManager || $room->members()->whereKey($request->user()->id)->exists(), 403);
        $options = (new AccessTokenOptions())->setIdentity((string) $request->user()->id)->setName($request->user()->name)->setTtl(3600);
        $grant = (new VideoGrant())->setRoomJoin()->setRoomName($room->room_name)->setCanPublish(true)->setCanSubscribe(true)->setCanPublishData(true);
        $token = (new AccessToken(config('services.livekit.key'), config('services.livekit.secret')))->init($options)->setGrant($grant)->toJwt();
        return response()->json(['token' => $token, 'url' => config('services.livekit.url'), 'room' => $room->room_name]);
    }

    public function closeBreakoutRoom(Request $request, Meeting $meeting, MeetingBreakoutRoom $room): JsonResponse
    {
        abort_unless($room->meeting_id === $meeting->id && $this->access->canAccess($request->user(), $meeting, MeetingAccessService::MANAGE), 403);
        $room->update(['closed_at' => now()]);
        try {
            $this->roomService()->deleteRoom($room->room_name);
        } catch (\Throwable $e) {
        }
        return response()->json(['room' => $room]);
    }

    public function token(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $options = (new AccessTokenOptions())->setIdentity((string) $request->user()->id)->setName($request->user()->name)->setTtl(3600);
        $grant = (new VideoGrant())->setRoomJoin()->setRoomName($meeting->room_name)->setCanPublish(true)->setCanSubscribe(true)->setCanPublishData(true);
        $token = (new AccessToken(config('services.livekit.key'), config('services.livekit.secret')))->init($options)->setGrant($grant)->toJwt();
        return response()->json(['token' => $token, 'url' => config('services.livekit.url'), 'room' => $meeting->room_name]);
    }

    public function messages(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $recipientId = $request->integer('recipient_id') ?: null;
        abort_if($recipientId && !$meeting->members()->whereKey($recipientId)->exists(), 403);
        return response()->json(['messages' => $this->repository->messages($meeting, $request->user(), $recipientId)]);
    }

    public function tasks(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        return response()->json(['tasks' => $this->repository->tasksForMeeting($meeting)]);
    }

    public function message(Request $request, Meeting $meeting, SendMeetingMessageHandler $handler): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $data = $request->validate(['body' => ['nullable', 'string', 'max:10000'], 'recipient_id' => ['nullable', 'integer', 'exists:users,id'], 'mention_ids' => ['array'], 'mention_ids.*' => ['integer', 'exists:users,id'], 'files' => ['nullable', 'array', 'max:10'], 'files.*' => ['file', 'max:51200', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,mp3,wav,m4a,webm,mp4,mov']]);
        abort_if($request->filled('recipient_id') && !$meeting->members()->whereKey($request->integer('recipient_id'))->exists(), 403);
        abort_if (!$request->filled('body') && !$request->hasFile('files'), 422, 'Tin nhắn cần nội dung hoặc tệp đính kèm.');
        $data['attachments'] = collect($request->file('files', []))->map(fn ($file) => ['path' => $file->store('meetings/' . $meeting->id, 'public'), 'name' => $file->getClientOriginalName(), 'mime' => $file->getMimeType(), 'size' => $file->getSize()])->all();
        $message = $handler->handle($request->user(), $meeting, $data, collect($data['mention_ids'] ?? [])->intersect($meeting->members->modelKeys())->values()->all());
        return response()->json(['message' => $message], 201);
    }

    public function updateMessage(Request $request, Meeting $meeting, \ProjectsManager\MeetingMessage $message): JsonResponse
    {
        abort_unless($message->meeting_id === $meeting->id && $this->canManageMessage($request->user(), $meeting, $message), 403);
        $data = $request->validate(['body' => ['required', 'string', 'max:10000']]);
        $message->update(['body' => $data['body'], 'edited_at' => now()]);
        return response()->json(['message' => $message->fresh(['user', 'mentions', 'sourceTask'])]);
    }

    public function deleteMessage(Request $request, Meeting $meeting, \ProjectsManager\MeetingMessage $message): JsonResponse
    {
        abort_unless($message->meeting_id === $meeting->id && $this->canManageMessage($request->user(), $meeting, $message), 403);
        $message->update(['body' => null, 'attachment_path' => null, 'attachment_name' => null, 'deleted_at' => now()]);
        return response()->json(['message' => $message->fresh(['user', 'mentions', 'sourceTask'])]);
    }

    private function canManageMessage(User $user, Meeting $meeting, \ProjectsManager\MeetingMessage $message): bool
    {
        return $message->user_id === $user->id || $this->access->canAccess($user, $meeting, MeetingAccessService::MANAGE);
    }

    public function taskFromMessage(Request $request, Meeting $meeting, \ProjectsManager\MeetingMessage $message, StoreTaskHandler $taskHandler): JsonResponse
    {
        abort_unless($message->meeting_id === $meeting->id && $this->access->canAccess($request->user(), $meeting), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'assignee_id' => ['nullable', 'integer', 'exists:users,id'], 'due_date' => ['nullable', 'date']]);
        $task = $taskHandler->handle($request->user()->id, ['name' => $data['name'], 'description' => $message->body, 'project_id' => $meeting->project_id, 'assignee_id' => $data['assignee_id'] ?? $request->user()->id, 'due_date' => $data['due_date'] ?? null, 'status' => 'todo', 'priority' => 'medium', 'issue_type' => 'task']);
        $message->update(['source_task_id' => $task->id]);
        return response()->json(['task' => $task->fresh(), 'message' => $message->fresh('sourceTask')], 201);
    }

    public function note(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        $data = $request->validate(['body' => ['required', 'string', 'max:50000']]);
        $note = $meeting->note()->updateOrCreate(['user_id' => $request->user()->id], ['body' => $data['body']]);
        return response()->json(['note' => $note]);
    }

    public function minutes(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting, MeetingAccessService::MANAGE), 403);
        $data = $request->validate(['content' => ['nullable', 'string'], 'decisions' => ['nullable', 'string'], 'actions' => ['array'], 'actions.*.title' => ['required', 'string', 'max:255'], 'actions.*.assignee_id' => ['nullable', 'integer', 'exists:users,id'], 'actions.*.due_date' => ['nullable', 'date']]);
        $minute = $meeting->minutes()->updateOrCreate(['meeting_id' => $meeting->id], ['created_by' => $request->user()->id, 'content' => $data['content'] ?? null, 'decisions' => $data['decisions'] ?? null]);
        $minute->actions()->delete();
        foreach ($data['actions'] ?? [] as $action) $minute->actions()->create($action);
        return response()->json(['minutes' => $minute->load('actions.assignee')]);
    }

    public function recording(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting, MeetingAccessService::RECORD), 403);
        $data = $request->validate(['recording' => ['required', 'file', 'max:512000', 'mimes:mp4,webm,mov,mkv']]);
        $file = $data['recording'];
        $recording = MeetingRecording::create(['meeting_id' => $meeting->id, 'project_id' => $meeting->project_id, 'uploaded_by' => $request->user()->id, 'path' => $file->store('meetings/' . $meeting->id . '/recordings', 'public'), 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
        return response()->json(['recording' => $recording], 201);
    }

    public function file(Request $request, Meeting $meeting, string $path)
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        abort_unless(Str::startsWith($path, 'meetings/' . $meeting->id . '/'), 404);
        return Storage::disk('public')->download($path);
    }

    public function archive(Request $request, Meeting $meeting): View
    {
        abort_unless($this->access->canAccess($request->user(), $meeting), 403);
        return view('meetings.archive', ['meeting' => $meeting->load(['messages.user', 'minutes.actions.assignee', 'recordings', 'participantLogs.user']), 'canRejoin' => $meeting->status !== 'ended']);
    }

    public function end(Request $request, Meeting $meeting): JsonResponse
    {
        abort_unless($this->access->canAccess($request->user(), $meeting, MeetingAccessService::MANAGE), 403);
        $meeting->update(['status' => 'ended', 'ended_at' => now()]);
        try {
            $this->roomService()->deleteRoom($meeting->room_name);
        } catch (\Throwable $e) {
        }
        return response()->json(['meeting' => $meeting->fresh()]);
    }
}
