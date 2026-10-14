<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Project;
use ProjectsManager\Worklog;

class ProjectReportsController extends Controller
{
    public function show(Request $request, Project $project): View
    {
        return view('projects.reports', $this->data($project, $request->integer('sprint_id') ?: null));
    }

    public function excel(Request $request, Project $project): Response
    {
        $data = $this->data($project, $request->integer('sprint_id') ?: null);
        $rows = [['Sprint', 'Trạng thái', 'Tổng task', 'Hoàn thành', 'Tiến độ', 'Worklog (giờ)']];
        foreach ($data['sprintRows'] as $row) {
            $rows[] = [$row['name'], $row['status'], $row['total'], $row['done'], $row['progress'].'%', $row['hours']];
        }
        $rows[] = ['Tổng dự án', '', $data['summary']['total'], $data['summary']['done'], $data['summary']['progress'].'%', $data['summary']['hours']];
        $xml = '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Báo cáo"><Table>';
        foreach ($rows as $row) { $xml .= '<Row>'; foreach ($row as $cell) { $xml .= '<Cell><Data ss:Type="String">'.e((string) $cell).'</Data></Cell>'; } $xml .= '</Row>'; }
        return response($xml.'</Table></Worksheet></Workbook>', 200, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="bao-cao-'.$project->id.'.xls"']);
    }

    public function pdf(Request $request, Project $project): Response
    {
        $data = $this->data($project, $request->integer('sprint_id') ?: null);

        return Pdf::setOption(['defaultFont' => 'DejaVu Sans'])
            ->loadView('projects.report-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('bao-cao-'.$project->id.'.pdf');
    }

    private function data(Project $project, ?int $selectedSprint): array
    {
        $project->load(['sprints.tasks', 'tasks']);
        $logs = Worklog::query()->whereHas('task', fn ($q) => $q->where('project_id', $project->id))->get();
        $doneStatuses = ['done', 'completed', 'closed', 'resolved'];
        $sprintRows = $project->sprints->map(function ($sprint) use ($logs, $doneStatuses) {
            $tasks = $sprint->tasks; $total = $tasks->count(); $done = $tasks->filter(fn ($task) => in_array($task->status, $doneStatuses, true))->count();
            return ['id'=>$sprint->id,'name'=>$sprint->name,'status'=>$sprint->status,'total'=>$total,'done'=>$done,'progress'=>$total ? (int) round($done / $total * 100) : 0,'hours'=>(float) $logs->whereIn('task_id',$tasks->pluck('id'))->sum('hours')];
        })->when($selectedSprint, fn ($rows) => $rows->where('id', $selectedSprint))->values();
        $total = $project->tasks->count(); $done = $project->tasks->filter(fn ($task) => in_array($task->status, $doneStatuses, true))->count();
        return compact('project','sprintRows','selectedSprint') + ['summary'=>['total'=>$total,'done'=>$done,'progress'=>$total ? (int) round($done / $total * 100) : 0,'hours'=>(float) $logs->sum('hours')]];
    }
}
