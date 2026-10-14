<?php

namespace ProjectsManager;

class ProjectRoleConstants
{
    public const PERMISSIONS = [
        // Project
        'project.view',
        'project.edit',
        'project.manage',
        'project.archive',
        'project.delete',
        'project.settings',

        // Task
        'task.view',
        'task.create',
        'task.edit',
        'task.delete',
        'task.assign',
        'task.status',
        'task.priority',
        'task.label',
        'task.manage',

        // Member
        'member.view',
        'member.invite',
        'member.edit',
        'member.remove',
        'member.role',

        // Document
        'document.view',
        'document.create',
        'document.edit',
        'document.delete',
        'document.manage',

        // Meeting
        'meeting.view',
        'meeting.create',
        'meeting.edit',
        'meeting.delete',
        'meeting.manage',

        // Comment
        'comment.view',
        'comment.create',
        'comment.edit',
        'comment.delete',

        // File
        'file.upload',
        'file.download',
        'file.delete',

        // Report
        'report.view',
        'report.export',
    ];

    public const PERMISSION_GROUPS = [
        [
            'key' => 'project',
            'label' => 'Dự án',
            'icon' => 'fa fa-briefcase',
            'permissions' => [
                'project.view',
                'project.edit',
                'project.manage',
                'project.archive',
                'project.delete',
                'project.settings',
            ],
        ],

        [
            'key' => 'task',
            'label' => 'Công việc',
            'icon' => 'fa fa-check-circle',
            'permissions' => [
                'task.view',
                'task.create',
                'task.edit',
                'task.delete',
                'task.assign',
                'task.status',
                'task.priority',
                'task.label',
                'task.manage',
            ],
        ],

        [
            'key' => 'member',
            'label' => 'Thành viên',
            'icon' => 'fa fa-users',
            'permissions' => [
                'member.view',
                'member.invite',
                'member.edit',
                'member.remove',
                'member.role',
            ],
        ],

        [
            'key' => 'document',
            'label' => 'Tài liệu',
            'icon' => 'fa fa-file-text-o',
            'permissions' => [
                'document.view',
                'document.create',
                'document.edit',
                'document.delete',
                'document.manage',
            ],
        ],

        [
            'key' => 'meeting',
            'label' => 'Cuộc họp',
            'icon' => 'fa fa-video-camera',
            'permissions' => [
                'meeting.view',
                'meeting.create',
                'meeting.edit',
                'meeting.delete',
                'meeting.manage',
            ],
        ],

        [
            'key' => 'comment',
            'label' => 'Bình luận',
            'icon' => 'fa fa-comment',
            'permissions' => [
                'comment.view',
                'comment.create',
                'comment.edit',
                'comment.delete',
            ],
        ],

        [
            'key' => 'file',
            'label' => 'Tệp',
            'icon' => 'fa fa-paperclip',
            'permissions' => [
                'file.upload',
                'file.download',
                'file.delete',
            ],
        ],

        [
            'key' => 'report',
            'label' => 'Báo cáo',
            'icon' => 'fa fa-bar-chart',
            'permissions' => [
                'report.view',
                'report.export',
            ],
        ],
    ];

    public const PERMISSION_LABELS = [
        'project.view' => 'Xem dự án',
        'project.edit' => 'Chỉnh sửa thông tin dự án',
        'project.manage' => 'Quản lý dự án',
        'project.archive' => 'Lưu trữ dự án',
        'project.delete' => 'Xóa dự án',
        'project.settings' => 'Quản lý cài đặt dự án',

        'task.view' => 'Xem công việc',
        'task.create' => 'Tạo công việc',
        'task.edit' => 'Chỉnh sửa công việc',
        'task.delete' => 'Xóa công việc',
        'task.assign' => 'Phân công công việc',
        'task.status' => 'Thay đổi trạng thái',
        'task.priority' => 'Thay đổi độ ưu tiên',
        'task.label' => 'Quản lý nhãn công việc',
        'task.manage' => 'Quản lý toàn bộ công việc',

        'member.view' => 'Xem thành viên',
        'member.invite' => 'Mời thành viên',
        'member.edit' => 'Chỉnh sửa thành viên',
        'member.remove' => 'Xóa thành viên',
        'member.role' => 'Quản lý vai trò thành viên',

        'document.view' => 'Xem tài liệu',
        'document.create' => 'Tạo tài liệu',
        'document.edit' => 'Chỉnh sửa tài liệu',
        'document.delete' => 'Xóa tài liệu',
        'document.manage' => 'Quản lý toàn bộ tài liệu',

        'meeting.view' => 'Xem cuộc họp',
        'meeting.create' => 'Tạo cuộc họp',
        'meeting.edit' => 'Chỉnh sửa cuộc họp',
        'meeting.delete' => 'Xóa cuộc họp',
        'meeting.manage' => 'Quản lý toàn bộ cuộc họp',

        'comment.view' => 'Xem bình luận',
        'comment.create' => 'Tạo bình luận',
        'comment.edit' => 'Chỉnh sửa bình luận',
        'comment.delete' => 'Xóa bình luận',

        'file.upload' => 'Tải tệp lên',
        'file.download' => 'Tải tệp xuống',
        'file.delete' => 'Xóa tệp',

        'report.view' => 'Xem báo cáo',
        'report.export' => 'Xuất báo cáo',
    ];
}
