<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $fillable = [
        'project_id', 'name', 'goal', 'status', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Tự động đóng các sprint đang active đã quá hạn kết thúc (ends_at < today).
     *
     * @param int|null $projectId
     * @return \Illuminate\Database\Eloquent\Collection<int, Sprint>
     */
    public static function closeExpiredSprints(?int $projectId = null)
    {
        $query = static::where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', now()->toDateString());

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $expiredSprints = $query->with('project')->get();

        foreach ($expiredSprints as $sprint) {
            $sprint->update(['status' => 'closed']);
        }

        return $expiredSprints;
    }
}