<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Báo cáo {{ $project->name }}</title>
    <style>
        @page { margin: 24px; }
        body { color: #172b4d; font-family: "DejaVu Sans", sans-serif; font-size: 12px; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        .meta { margin: 0 0 18px; color: #42526e; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #dfe1e6; text-align: left; }
        th { color: #172b4d; background: #eef2ff; }
        tbody tr:nth-child(even) { background: #f8f9fb; }
        .empty { color: #6b778c; text-align: center; }
    </style>
</head>
<body>
    <h1>Báo cáo tiến độ — {{ $project->name }}</h1>
    <p class="meta">Tiến độ dự án: {{ $summary['progress'] }}% · Hoàn thành: {{ $summary['done'] }}/{{ $summary['total'] }} task · Worklog: {{ number_format($summary['hours'], 2) }} giờ</p>
    <table>
        <thead><tr><th>Sprint</th><th>Trạng thái</th><th>Tiến độ</th><th>Task</th><th>Worklog</th></tr></thead>
        <tbody>
            @forelse($sprintRows as $row)
                <tr><td>{{ $row['name'] }}</td><td>{{ $row['status'] }}</td><td>{{ $row['progress'] }}%</td><td>{{ $row['done'] }}/{{ $row['total'] }}</td><td>{{ number_format($row['hours'], 2) }}h</td></tr>
            @empty
                <tr><td class="empty" colspan="5">Chưa có Sprint phù hợp.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
