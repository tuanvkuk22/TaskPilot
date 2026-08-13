<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Lời mời tham gia dự án</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F4F5F7; color: #172B4D; margin: 0; padding: 30px 15px; }
        .email-card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(9, 30, 66, 0.08); }
        .brand-header { color: #0052CC; font-size: 22px; font-weight: 700; margin-bottom: 24px; text-align: center; }
        h1 { font-size: 20px; font-weight: 600; color: #172B4D; margin-bottom: 16px; }
        p { font-size: 15px; line-height: 1.6; color: #5E6C84; margin-bottom: 24px; }
        .btn-action { display: inline-block; background-color: #0052CC; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 15px; }
        .footer { margin-top: 36px; padding-top: 20px; border-top: 1px solid #EBECF0; font-size: 12px; color: #97A0AF; text-align: center; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="brand-header">TaskPilot</div>
        <h1>Lời mời tham gia dự án</h1>
        <p>Xin chào,</p>
        <p>Bạn đã được thêm vào làm thành viên dự án <strong>{{ $project->name }}</strong> (Mã dự án: <code>{{ $project->project_code ?: 'PRJ' }}</code>) trên TaskPilot bởi {{ $inviter->name ?? 'Quản lý dự án' }}.</p>
        <p>{{ $project->description ?: 'Truy cập dự án ngay để cập nhật các công việc được giao.' }}</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/projects/' . $project->id) }}" class="btn-action">Truy cập dự án</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} TaskPilot Project Management Platform. All rights reserved.
        </div>
    </div>
</body>
</html>
