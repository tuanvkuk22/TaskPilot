<?php

namespace ProjectsManager\Console\Commands;

use Illuminate\Console\Command;
use ProjectsManager\Sprint;

class CloseExpiredSprintsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sprints:close-expired
        {--project= : ID của dự án cụ thể cần đóng sprint quá hạn}
        {--dry-run : Chỉ hiển thị các sprint quá hạn mà không thực hiện đóng}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động đóng các sprint đang active đã quá hạn kết thúc (ends_at < today)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $projectId = $this->option('project') ? (int) $this->option('project') : null;
        $isDryRun = (bool) $this->option('dry-run');

        $query = Sprint::where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', now()->toDateString());

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $expiredSprints = $query->with('project')->get();

        if ($expiredSprints->isEmpty()) {
            $this->info('Không có Sprint nào đang active bị quá hạn kết thúc.');
            return 0;
        }

        $this->warn("Tìm thấy {$expiredSprints->count()} Sprint quá hạn cần đóng:");

        $headers = ['ID', 'Tên Sprint', 'Dự án (ID)', 'Ngày kết thúc (ends_at)', 'Trạng thái'];
        $rows = [];

        foreach ($expiredSprints as $sprint) {
            $projectName = $sprint->project ? "{$sprint->project->name} (#{$sprint->project_id})" : "N/A (#{$sprint->project_id})";
            $endsAt = $sprint->ends_at ? $sprint->ends_at->format('Y-m-d') : 'N/A';

            if (! $isDryRun) {
                $sprint->update(['status' => 'closed']);
                $newStatus = 'closed (Đã đóng)';
            } else {
                $newStatus = 'active (Chế độ xem trước)';
            }

            $rows[] = [$sprint->id, $sprint->name, $projectName, $endsAt, $newStatus];
        }

        $this->table($headers, $rows);

        if ($isDryRun) {
            $this->info('[DRY-RUN] Không có thay đổi nào được lưu vào cơ sở dữ liệu.');
        } else {
            $this->info("Thành công: Đã đóng {$expiredSprints->count()} Sprint quá hạn.");
        }

        return 0;
    }
}
