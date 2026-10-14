@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/notification-review.css') }}?v={{ filemtime(public_path('css/notification-review.css')) }}" rel="stylesheet">
<link href="{{ asset('css/notification-polish.css') }}?v={{ filemtime(public_path('css/notification-polish.css')) }}" rel="stylesheet">
@endpush

@section('content')
<section class="tp-page notification-page"><div class="tp-page-shell">
<section class="tp-page-head">
    <div>
        <span class="tp-eyebrow">THÔNG BÁO</span>
        <h1>Trung tâm thông báo</h1>
        <p>Tất cả thông báo và hoạt động gần đây trong dự án của bạn.</p>
    </div>
    <div class="tp-actions">
        <form id="mark-all-read-form" method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary" id="mark-all-read-btn">
                <i class="fa fa-check-square-o"></i> Đánh dấu tất cả là đã đọc
            </button>
        </form>
    </div>
</section>

<section class="tp-surface">
    <div class="tp-surface-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-bell-o me-2"></i> Tất cả thông báo</span>
        <ul class="nav nav-pills nav-pills-sm" id="notification-filter">
            <li class="nav-item">
                <a class="nav-link active" href="?filter=all" data-filter="all">Tất cả</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?filter=unread" data-filter="unread">Chưa đọc</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?filter=read" data-filter="read">Đã đọc</a>
            </li>
        </ul>
    </div>

    <div class="tp-surface-body">
        @if($notifications->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa fa-bell-slash-o" style="font-size: 48px; opacity: 0.3;"></i>
                <p class="mt-3">Bạn chưa có thông báo nào.</p>
                <p class="text-small">Các thông báo về hoạt động của dự án sẽ hiển thị ở đây.</p>
            </div>
        @else
            <div class="tp-notification-list">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $eventType = $data['event_type'] ?? '';
                        $title = $data['title'] ?? 'Thông báo';
                        $message = $data['message'] ?? '';
                        $url = $data['url'] ?? null;
                        $actorName = $data['actor_name'] ?? 'Hệ thống';
                        $isUnread = is_null($notification->read_at);
                    @endphp
                    <article class="tp-notification-item {{ $isUnread ? 'tp-unread' : '' }}" data-id="{{ $notification->id }}">
                        <span class="tp-notification-icon">
                            <i class="fa fa-bell"></i>
                        </span>
                        <div class="tp-notification-content">
                            <div class="tp-notification-title-row">
                                <h2>{{ $title }}</h2>
                                <time class="tp-notification-time">{{ $notification->created_at->diffForHumans() }}</time>
                            </div>
                            <p>{{ $message }}</p>
                            @if($actorName && $actorName !== 'Hệ thống')
                                <div class="tp-notification-actor">
                                    <i class="fa fa-user-o"></i> Bởi {{ $actorName }}
                                </div>
                            @endif
                        </div>
                        <div class="tp-notification-actions">
                            @if($isUnread)
                                <button type="button" class="btn btn-sm btn-link tp-mark-read" title="Đánh dấu đã đọc">
                                    <i class="fa fa-check"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-link tp-delete-notification text-danger" title="Xóa">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="p-3 border-top">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</section>

</div></section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var baseUrl = '/notifications';

    function markRead(id) {
        fetch(baseUrl + '/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        }).then(response => response.json()).then(function(data) {
            if (data.success) {
                var item = document.querySelector('[data-id="' + id + '"]');
                if (item) {
                    item.classList.remove('tp-unread');
                }
            }
        });
    }

    function deleteNotification(id) {
        fetch(baseUrl + '/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        }).then(function() {
            var item = document.querySelector('[data-id="' + id + '"]');
            if (item) {
                item.remove();
            }
        });
    }

    document.querySelectorAll('.tp-mark-read').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.closest('.tp-notification-item').dataset.id;
            markRead(id);
        });
    });

    document.querySelectorAll('.tp-delete-notification').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.closest('.tp-notification-item').dataset.id;
            if (confirm('Bạn có chắc muốn xóa thông báo này?')) {
                deleteNotification(id);
            }
        });
    });

    document.getElementById('mark-all-read-form').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(baseUrl + '/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        }).then(response => response.json()).then(function(data) {
            if (data.success) {
                document.querySelectorAll('.tp-notification-item').forEach(function(item) {
                    item.classList.remove('tp-unread');
                });
            }
        });
    });
});
</script>
@endsection
