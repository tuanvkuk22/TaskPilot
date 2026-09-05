@if(auth()->check())
<li class="dropdown tp-notification-bell">
    <a href="#" class="tp-notification-toggle" role="button" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bell-o"></i>
        <span class="badge bg-danger rounded-circle tp-unread-count" id="notification-unread-count" style="display: none; position: absolute; top: 6px; right: 6px; padding: 2px 6px; font-size: 10px;">0</span>
    </a>
    <div class="dropdown-menu tp-notification-dropdown" style="display: none; width: 380px; max-height: 500px; overflow: hidden; flex-direction: column;">
        <div class="tp-dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <span class="fw-bold">Thông báo</span>
            <a href="/notifications" class="text-small text-primary text-decoration-none tp-view-all">Xem tất cả</a>
        </div>
        <div class="tp-dropdown-body" id="notification-list-container">
            <div class="text-center py-4 text-muted">
                <i class="fa fa-circle-o-notch fa-pulse"></i> Đang tải...
            </div>
        </div>
        <div class="tp-dropdown-footer px-3 py-2 border-top d-none" id="notification-mark-all-container">
            <a href="#" class="text-small text-decoration-none p-0 tp-mark-all-read" style="color: #0052CC;">
                <i class="fa fa-check"></i> Đánh dấu tất cả là đã đọc
            </a>
        </div>
    </div>
</li>

<script>
(function() {
    var baseUrl = '/notifications';
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var bell = document.querySelector('.tp-notification-bell');
    if (!bell) return;

    var toggleLink = bell.querySelector('.tp-notification-toggle');
    var dropdownMenu = bell.querySelector('.dropdown-menu');

    function toggleDropdown(e) {
        e.preventDefault();
        e.stopPropagation();
        var isShown = dropdownMenu.style.display === 'block';
        dropdownMenu.style.display = isShown ? 'none' : 'block';
        if (!isShown) {
            loadRecentNotifications();
        }
    }

    function closeDropdown(e) {
        if (dropdownMenu && !bell.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    }

    if (toggleLink) {
        toggleLink.addEventListener('click', toggleDropdown);
    }

    document.addEventListener('click', closeDropdown);

    function loadRecentNotifications() {
        var container = document.getElementById('notification-list-container');
        var countBadge = document.getElementById('notification-unread-count');

        if (!container || !countBadge) return;

        fetch(baseUrl + '/recent', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            var notifications = data.notifications || [];
            var unreadCount = data.unread_count || 0;

            countBadge.textContent = unreadCount;
            countBadge.style.display = unreadCount > 0 ? 'inline-block' : 'none';

            if (notifications.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted"><i class="fa fa-bell-slash-o"></i> Bạn chưa có thông báo nào.</div>';
                var markAllContainer = document.getElementById('notification-mark-all-container');
                if (markAllContainer) markAllContainer.classList.add('d-none');
                return;
            }

            var html = '';
            notifications.forEach(function(notification) {
                var ndata = notification.data || {};
                var title = ndata.title || 'Thông báo';
                var message = ndata.message || '';
                var nurl = ndata.url || null;
                var actorName = ndata.actor_name || 'Hệ thống';
                var isUnread = !notification.read_at;
                var time = notification.created_at_human || '';

                var icon = 'bell-o';
                if (ndata.event_type && ndata.event_type.indexOf('task') !== -1) icon = 'tasks';
                else if (ndata.event_type && ndata.event_type.indexOf('comment') !== -1) icon = 'comment';
                else if (ndata.event_type && ndata.event_type.indexOf('project') !== -1) icon = 'folder';
                else if (ndata.event_type && ndata.event_type.indexOf('sprint') !== -1) icon = 'flag';
                else if (ndata.event_type && ndata.event_type.indexOf('worklog') !== -1) icon = 'clock-o';
                else if (ndata.event_type && ndata.event_type.indexOf('team') !== -1) icon = 'users';

                var linkUrl = nurl ? nurl : 'javascript:void(0)';

                html += '<a href="' + linkUrl + '" class="dropdown-item d-flex align-items-start py-2 px-3 ' + (isUnread ? 'tp-unread' : '') + '" data-id="' + notification.id + '" style="white-space: normal; text-decoration: none;">';
                html += '<span class="badge rounded-circle bg-primary bg-opacity-10 text-primary p-1 flex-shrink-0 mt-1 me-2"><i class="fa fa-' + icon + '"></i></span>';
                html += '<div class="flex-grow-1">';
                html += '<div class="fw-semibold small text-dark">' + title + '</div>';
                html += '<div class="text-small text-muted">' + message + '</div>';
                html += '<div class="text-small text-secondary"><i class="fa fa-user-o me-1"></i> ' + actorName + ' - ' + time + '</div>';
                html += '</div>';
                if (isUnread) {
                    html += '<button type="button" class="btn btn-sm btn-link tp-mark-read ms-1 p-0" title="Đánh dấu đã đọc"><i class="fa fa-check"></i></button>';
                }
                html += '</a>';
            });

            container.innerHTML = html;

            var markAllContainer = document.getElementById('notification-mark-all-container');
            if (markAllContainer) markAllContainer.classList.toggle('d-none', unreadCount === 0);

            container.querySelectorAll('.dropdown-item[data-id]').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    var id = this.dataset.id;
                    var itemUrl = this.getAttribute('href');
                    e.preventDefault();
                    markNotificationRead(id, itemUrl);
                });
            });

            container.querySelectorAll('.tp-mark-read').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var itemId = this.closest('[data-id]').dataset.id;
                    markNotificationRead(itemId, null);
                });
            });
        })
        .catch(function(err) {
            container.innerHTML = '<div class="text-center py-3 text-danger">Lỗi tải thông báo: ' + err.message + '</div>';
        });
    }

    function markNotificationRead(id, redirectUrl) {
        fetch(baseUrl + '/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
        }).then(function(r) { return r.json(); }).then(function(data) {
            var badge = document.getElementById('notification-unread-count');
            var count = parseInt(badge.textContent || '0', 10) - 1;
            badge.textContent = count > 0 ? count : 0;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
            if (redirectUrl && redirectUrl !== 'javascript:void(0)') {
                window.location.href = redirectUrl;
            } else {
                loadRecentNotifications();
            }
        });
    }

    var markAllContainer = document.getElementById('notification-mark-all-container');
    if (markAllContainer) {
        var markAllBtn = markAllContainer.querySelector('.tp-mark-all-read');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                fetch(baseUrl + '/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                }).then(function(r) { return r.json(); }).then(function() {
                    var badge = document.getElementById('notification-unread-count');
                    badge.textContent = '0';
                    badge.style.display = 'none';
                    loadRecentNotifications();
                });
            });
        }
    }

    loadRecentNotifications();
    setInterval(loadRecentNotifications, 30000);
})();
</script>
@endif
