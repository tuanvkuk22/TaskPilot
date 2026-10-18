/**
 * TaskPilot - Modern Vertical Sidebar & Global Topbar Search
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var appRoot = document.getElementById('tp-app-root');
        if (!appRoot) return;

        // 1. Sidebar Collapse (Desktop)
        var collapseBtn = document.getElementById('tp-sidebar-collapse');
        var savedState = localStorage.getItem('tp_sidebar_collapsed');
        if (savedState === '1') {
            appRoot.classList.add('tp-sidebar-collapsed');
        }

        if (collapseBtn) {
            collapseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                appRoot.classList.toggle('tp-sidebar-collapsed');
                var isCollapsed = appRoot.classList.contains('tp-sidebar-collapsed');
                localStorage.setItem('tp_sidebar_collapsed', isCollapsed ? '1' : '0');
            });
        }

        // 2. Mobile Drawer Toggle
        var mobileToggleBtn = document.getElementById('tp-mobile-toggle');
        var mobileCloseBtn = document.getElementById('tp-mobile-close');
        var backdrop = document.getElementById('tp-sidebar-backdrop');

        function openMobileSidebar() {
            appRoot.classList.add('tp-sidebar-mobile-open');
        }

        function closeMobileSidebar() {
            appRoot.classList.remove('tp-sidebar-mobile-open');
        }

        if (mobileToggleBtn) {
            mobileToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openMobileSidebar();
            });
        }

        if (mobileCloseBtn) {
            mobileCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeMobileSidebar();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeMobileSidebar);
        }

        // 3. User Profile Dropdown
        var userBtn = document.getElementById('tp-user-dropdown-btn');
        var userMenu = document.getElementById('tp-user-dropdown-menu');

        if (userBtn && userMenu) {
            userBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userMenu.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target) && !userBtn.contains(e.target)) {
                    userMenu.classList.remove('show');
                }
            });
        }

        // 4. Global Search (Ctrl + K & Live Search)
        var searchInput = document.getElementById('tp-global-search-input');
        var searchDropdown = document.getElementById('tp-search-dropdown');
        var searchContainer = document.getElementById('tp-search-container');
        var debounceTimer = null;

        // Ctrl + K shortcut
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                    triggerSearch(searchInput.value.trim());
                }
            } else if (e.key === 'Escape') {
                if (searchDropdown) {
                    searchDropdown.classList.remove('show');
                }
            }
        });

        if (searchInput && searchDropdown) {
            searchInput.addEventListener('focus', function() {
                triggerSearch(this.value.trim());
            });

            searchInput.addEventListener('input', function() {
                var query = this.value.trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    triggerSearch(query);
                }, 200);
            });

            document.addEventListener('click', function(e) {
                if (searchContainer && !searchContainer.contains(e.target)) {
                    searchDropdown.classList.remove('show');
                }
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    var firstItem = searchDropdown.querySelector('.tp-search-item');
                    if (firstItem) {
                        e.preventDefault();
                        window.location.href = firstItem.getAttribute('href');
                    }
                }
            });
        }

        function triggerSearch(query) {
            var searchUrl = '/api/quick-search?q=' + encodeURIComponent(query);

            fetch(searchUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Search failed');
                return res.json();
            })
            .then(function(data) {
                renderSearchResults(data, query);
            })
            .catch(function(err) {
                console.error('QuickSearch error:', err);
            });
        }

        function renderSearchResults(data, query) {
            var html = '';
            var results = data.results || {};
            var hasAny = false;

            // Quick links (when empty query)
            if (results.quick_links && results.quick_links.length > 0) {
                hasAny = true;
                html += '<div class="tp-search-section-title">Lối tắt nhanh</div>';
                results.quick_links.forEach(function(item) {
                    html += buildItemHtml(item);
                });
            }

            // Tasks
            if (results.tasks && results.tasks.length > 0) {
                hasAny = true;
                html += '<div class="tp-search-section-title">Công việc (' + results.tasks.length + ')</div>';
                results.tasks.forEach(function(item) {
                    html += buildItemHtml(item);
                });
            }

            // Projects
            if (results.projects && results.projects.length > 0) {
                hasAny = true;
                html += '<div class="tp-search-section-title">Dự án (' + results.projects.length + ')</div>';
                results.projects.forEach(function(item) {
                    html += buildItemHtml(item);
                });
            }

            // Teams
            if (results.teams && results.teams.length > 0) {
                hasAny = true;
                html += '<div class="tp-search-section-title">Đội nhóm (' + results.teams.length + ')</div>';
                results.teams.forEach(function(item) {
                    html += buildItemHtml(item);
                });
            }

            // Companies
            if (results.companies && results.companies.length > 0) {
                hasAny = true;
                html += '<div class="tp-search-section-title">Khách hàng (' + results.companies.length + ')</div>';
                results.companies.forEach(function(item) {
                    html += buildItemHtml(item);
                });
            }

            if (!hasAny) {
                html = '<div class="tp-search-empty"><i class="fa fa-search" style="font-size:22px; margin-bottom:8px; display:block; opacity:0.5;"></i>Không tìm thấy kết quả phù hợp cho "<strong>' + escapeHtml(query) + '</strong>"</div>';
            }

            searchDropdown.innerHTML = html;
            searchDropdown.classList.add('show');
        }

        function buildItemHtml(item) {
            var icon = item.icon || 'fa-file-o';
            var badgeHtml = item.badge ? '<span class="tp-search-item-badge">' + escapeHtml(item.badge) + '</span>' : '';
            return '<a href="' + item.url + '" class="tp-search-item">' +
                '<div class="tp-search-item-left">' +
                    '<div class="tp-search-item-icon"><i class="fa ' + icon + '"></i></div>' +
                    '<div class="tp-search-item-info">' +
                        '<div class="tp-search-item-title">' + escapeHtml(item.title) + '</div>' +
                        (item.subtitle ? '<div class="tp-search-item-sub">' + escapeHtml(item.subtitle) + '</div>' : '') +
                    '</div>' +
                '</div>' +
                badgeHtml +
            '</a>';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // 5. Horizontal Screen Scroll: Keep topbar, project header, tabs, and filter controls stationary on screen
        var scrollTicking = false;
        function updateHorizontalSticky() {
            var scrollX = window.scrollX || window.pageXOffset || 0;
            var panels = document.querySelectorAll('.tp-screen-sticky-panel, .tp-topbar');
            for (var i = 0; i < panels.length; i++) {
                panels[i].style.transform = scrollX > 0 ? 'translateX(' + scrollX + 'px)' : '';
            }
            scrollTicking = false;
        }

        window.addEventListener('scroll', function() {
            if (!scrollTicking) {
                window.requestAnimationFrame(updateHorizontalSticky);
                scrollTicking = true;
            }
        }, { passive: true });

        window.addEventListener('resize', function() {
            updateHorizontalSticky();
        }, { passive: true });

        // Initial check in case page loaded with non-zero scroll
        updateHorizontalSticky();
    });
})();
