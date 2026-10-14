@if(auth()->check())
<div class="tp-notification-menu" id="tp-notification-menu">
    <button type="button" class="tp-notification-btn" id="tp-notification-btn" aria-label="Thông báo" aria-expanded="false" title="Thông báo">
        <i class="fa fa-bell-o tp-bell-icon"></i>
        <span class="tp-notification-badge" id="notification-unread-count" style="display: none;">0</span>
    </button>

    <div class="tp-notification-dropdown" id="tp-notification-dropdown">
        <div class="tp-notif-header">
            <div class="tp-notif-title-wrap">
                <span class="tp-notif-title"><i class="fa fa-bell me-2"></i>Thông báo</span>
                <span class="tp-notif-count-pill" id="notification-unread-pill" style="display: none;">0 mới</span>
            </div>
            <a href="/notifications" class="tp-notif-view-all">Xem tất cả <i class="fa fa-angle-right ms-1"></i></a>
        </div>

        <div class="tp-notif-body" id="notification-list-container">
            <div class="tp-notif-loading">
                <i class="fa fa-circle-o-notch fa-spin"></i>
                <span>Đang tải thông báo...</span>
            </div>
        </div>

        <div class="tp-notif-footer" id="notification-mark-all-container" style="display: none;">
            <button type="button" class="tp-notif-mark-all-btn" id="tp-mark-all-btn">
                <i class="fa fa-check-square-o me-1"></i> Đánh dấu tất cả là đã đọc
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var baseUrl = '/notifications';
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var menuWrap = document.getElementById('tp-notification-menu');
    if (!menuWrap) return;

    var btn = document.getElementById('tp-notification-btn');
    var dropdown = document.getElementById('tp-notification-dropdown');
    var container = document.getElementById('notification-list-container');
    var badge = document.getElementById('notification-unread-count');
    var pill = document.getElementById('notification-unread-pill');
    var markAllWrap = document.getElementById('notification-mark-all-container');
    var markAllBtn = document.getElementById('tp-mark-all-btn');

    function toggleDropdown(e) {
        e.preventDefault();
        e.stopPropagation();
        var isOpen = dropdown.classList.contains('show');
        if (isOpen) {
            closeDropdown();
        } else {
            // Close any other open topbar dropdowns (e.g. user menu)
            var userMenu = document.getElementById('tp-user-dropdown-menu');
            if (userMenu) userMenu.classList.remove('show');

            dropdown.classList.add('show');
            btn.classList.add('active');
            btn.setAttribute('aria-expanded', 'true');
            loadRecentNotifications();
        }
    }

    function closeDropdown() {
        if (dropdown) {
            dropdown.classList.remove('show');
            btn.classList.remove('active');
            btn.setAttribute('aria-expanded', 'false');
        }
    }

    btn.addEventListener('click', toggleDropdown);

    document.addEventListener('click', function(e) {
        if (!menuWrap.contains(e.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    function getEventMeta(eventType) {
        var type = (eventType || '').toLowerCase();
        if (type.indexOf('task') !== -1) {
            return { icon: 'tasks', colorClass: 'icon-task' };
        } else if (type.indexOf('comment') !== -1) {
            return { icon: 'comment', colorClass: 'icon-comment' };
        } else if (type.indexOf('project') !== -1) {
            return { icon: 'folder', colorClass: 'icon-project' };
        } else if (type.indexOf('sprint') !== -1) {
            return { icon: 'flag', colorClass: 'icon-sprint' };
        } else if (type.indexOf('worklog') !== -1) {
            return { icon: 'clock-o', colorClass: 'icon-worklog' };
        } else if (type.indexOf('team') !== -1) {
            return { icon: 'users', colorClass: 'icon-team' };
        }
        return { icon: 'bell', colorClass: 'icon-default' };
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function updateBadge(count) {
        var num = parseInt(count, 10) || 0;
        if (num > 0) {
            badge.textContent = num > 99 ? '99+' : num;
            badge.style.display = 'inline-flex';
            if (pill) {
                pill.textContent = num + ' mới';
                pill.style.display = 'inline-block';
            }
        } else {
            badge.style.display = 'none';
            if (pill) {
                pill.style.display = 'none';
            }
        }
    }

    function loadRecentNotifications() {
        if (!container) return;

        fetch(baseUrl + '/recent', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(data) {
            var notifications = data.notifications || [];
            var unreadCount = data.unread_count || 0;

            updateBadge(unreadCount);

            if (notifications.length === 0) {
                container.innerHTML = '<div class="tp-notif-empty">' +
                    '<div class="tp-notif-empty-icon"><i class="fa fa-bell-slash-o"></i></div>' +
                    '<div class="tp-notif-empty-title">Bạn chưa có thông báo nào</div>' +
                    '<div class="tp-notif-empty-sub">Các cập nhật công việc và dự án sẽ xuất hiện tại đây.</div>' +
                    '</div>';
                if (markAllWrap) markAllWrap.style.display = 'none';
                return;
            }

            var html = '';
            notifications.forEach(function(n) {
                var ndata = n.data || {};
                var title = escapeHtml(ndata.title || 'Thông báo');
                var message = escapeHtml(ndata.message || '');
                var nurl = ndata.url || '';
                var actorName = escapeHtml(ndata.actor_name || 'Hệ thống');
                var isUnread = !n.read_at;
                var time = escapeHtml(n.created_at_human || '');
                var meta = getEventMeta(ndata.event_type);

                var linkUrl = nurl ? escapeHtml(nurl) : 'javascript:void(0)';

                html += '<div class="tp-notif-item ' + (isUnread ? 'is-unread' : '') + '" data-id="' + n.id + '">';
                html += '  <div class="tp-notif-icon ' + meta.colorClass + '"><i class="fa fa-' + meta.icon + '"></i></div>';
                html += '  <a href="' + linkUrl + '" class="tp-notif-content-link">';
                html += '    <div class="tp-notif-item-title">' + title + '</div>';
                if (message) {
                    html += '    <div class="tp-notif-item-msg">' + message + '</div>';
                }
                html += '    <div class="tp-notif-item-meta">';
                html += '      <span><i class="fa fa-user-circle-o me-1"></i>' + actorName + '</span>';
                html += '      <span>•</span>';
                html += '      <span><i class="fa fa-clock-o me-1"></i>' + time + '</span>';
                html += '    </div>';
                html += '  </a>';
                if (isUnread) {
                    html += '  <button type="button" class="tp-notif-mark-one-btn" title="Đánh dấu đã đọc" data-action="read">';
                    html += '    <i class="fa fa-check"></i>';
                    html += '  </button>';
                }
                html += '</div>';
            });

            container.innerHTML = html;

            if (markAllWrap) {
                markAllWrap.style.display = unreadCount > 0 ? 'block' : 'none';
            }

            // Click item handler
            container.querySelectorAll('.tp-notif-content-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var item = this.closest('.tp-notif-item');
                    var id = item ? item.dataset.id : null;
                    var href = this.getAttribute('href');
                    if (id && item.classList.contains('is-unread')) {
                        markNotificationRead(id, href);
                        if (href && href !== 'javascript:void(0)') {
                            e.preventDefault();
                        }
                    }
                });
            });

            // Mark single read
            container.querySelectorAll('.tp-notif-mark-one-btn').forEach(function(readBtn) {
                readBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var item = this.closest('.tp-notif-item');
                    var id = item ? item.dataset.id : null;
                    if (id) {
                        markNotificationRead(id, null);
                    }
                });
            });
        })
        .catch(function(err) {
            container.innerHTML = '<div class="tp-notif-empty text-danger">' +
                '<i class="fa fa-exclamation-triangle mb-2" style="font-size: 24px;"></i>' +
                '<div>Lỗi tải thông báo</div>' +
                '</div>';
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
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (redirectUrl && redirectUrl !== 'javascript:void(0)') {
                window.location.href = redirectUrl;
            } else {
                loadRecentNotifications();
            }
        });
    }

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
            })
            .then(function(r) { return r.json(); })
            .then(function() {
                updateBadge(0);
                loadRecentNotifications();
            });
        });
    }

    // Initial check on page load to display unread badge
    loadRecentNotifications();
    setInterval(loadRecentNotifications, 30000);
})();
</script>
@endif
