import '../css/ai-assignee.css';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function displayName(candidate) {
    return candidate?.name || candidate?.display_name || candidate?.assignee || 'Thành viên';
}

function confidence(candidate) {
    if (candidate?.confidence_percent != null && candidate.confidence_percent !== '') {
        return candidate.confidence_percent;
    }
    const score = Number(candidate?.score);
    return Number.isFinite(score) ? Math.round(score * 100) : 0;
}

function ensureModal() {
    let overlay = document.getElementById('tp-ai-assignee-modal');
    if (overlay) {
        return overlay;
    }

    overlay = document.createElement('div');
    overlay.id = 'tp-ai-assignee-modal';
    overlay.className = 'ai-modal-overlay';
    overlay.style.display = 'none';
    overlay.innerHTML = `
        <div class="ai-modal-card">
            <div class="ai-modal-header">
                <div class="ai-modal-title">
                    <i class="fa fa-magic" style="color: #8b5cf6; margin-right: 6px;"></i>
                    <span>AI Đề Xuất Người Xử Lý Phù Hợp</span>
                </div>
                <button type="button" class="ai-modal-close" data-ai-close>&times;</button>
            </div>
            <div class="ai-modal-body">
                <p class="ai-modal-subtitle">
                    Mô hình Deep Learning phân tích yêu cầu công việc, chuyên môn và tải công việc hiện tại.
                </p>
                <div data-ai-body></div>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay || event.target.closest('[data-ai-close]')) {
            overlay.style.display = 'none';
        }
    });

    return overlay;
}

function renderCandidates(list, selectedUserId) {
    if (!list.length) {
        return '<div class="ai-empty-state"><i class="fa fa-info-circle"></i> Chưa tìm thấy ứng viên phù hợp.</div>';
    }

    return `<div class="ai-candidate-list">${list.map((candidate) => {
        const selected = String(selectedUserId || '') === String(candidate.user_id) ? ' selected' : '';
        const reasons = Array.isArray(candidate.match_reasons)
            ? candidate.match_reasons.map((reason) => `
                <div class="ai-reason-item">
                    <i class="fa fa-check-circle" style="color: #10b981; margin-right: 4px;"></i> ${escapeHtml(reason)}
                </div>
            `).join('')
            : '';

        return `
            <div class="ai-candidate-card${selected}">
                <div class="ai-candidate-header">
                    <div class="ai-candidate-info">
                        <div class="ai-candidate-avatar">${escapeHtml(displayName(candidate).charAt(0).toUpperCase())}</div>
                        <div>
                            <div class="ai-candidate-name">${escapeHtml(displayName(candidate))}</div>
                            <div class="ai-candidate-meta">
                                <span>${escapeHtml(candidate.job_title || 'Thành viên dự án')}</span>
                                <span class="ai-dot">•</span>
                                <span>${candidate.active_tasks_count ?? 0} task đang làm</span>
                            </div>
                        </div>
                    </div>
                    <div class="ai-candidate-score-badge">${confidence(candidate)}% Phù hợp</div>
                </div>
                ${reasons ? `<div class="ai-candidate-reasons">${reasons}</div>` : ''}
                <div class="ai-candidate-action">
                    <button type="button" class="ai-select-btn" data-ai-pick="${candidate.user_id}">
                        <i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này
                    </button>
                </div>
            </div>
        `;
    }).join('')}</div>`;
}

async function requestRecommendations(context) {
    const response = await fetch('/tasks/recommend-assignee', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            name: context.name || '',
            summary: context.name || '',
            description: context.description || '',
            project_id: context.project_id || null,
            issue_type: context.issue_type || 'task',
            priority: context.priority || 'medium',
            task_id: context.task_id || null,
        }),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success || !Array.isArray(data.recommendations)) {
        throw new Error(
            data.message === 'This action is unauthorized.'
                ? 'Bạn không có quyền dùng AI gợi ý cho công việc này.'
                : (data.message || 'Không thể tải gợi ý AI vào lúc này.')
        );
    }

    return data.recommendations;
}

async function saveAssignee(taskId, userId) {
    const response = await fetch(`/tasks/${taskId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ _method: 'PUT', assignee_id: userId }),
    });

    return response.json();
}

async function sendFeedback(recommendationId) {
    if (!recommendationId) {
        return;
    }

    try {
        await fetch('/tasks/ai-feedback', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                recommendation_id: recommendationId,
                status: 'accepted',
            }),
        });
    } catch (err) {
        console.warn('AI feedback error:', err);
    }
}

function contextFromButton(button) {
    const formRoot = button.closest('form');
    const read = (id) => document.getElementById(id)?.value || '';

    return {
        name: button.dataset.name || read('name') || read('input-task-name'),
        description: button.dataset.description || read('description') || '',
        project_id: button.dataset.projectId || read('project_id') || null,
        issue_type: button.dataset.issueType || read('issue_type') || 'task',
        priority: button.dataset.priority || read('priority') || 'medium',
        task_id: button.dataset.taskId || null,
        mode: button.dataset.aiMode || 'select',
        select: button.dataset.aiSelect || '#assignee_id',
        form: formRoot,
    };
}

async function openFromButton(button) {
    const context = contextFromButton(button);
    if (!context.name && !context.description) {
        alert('Hãy nhập tên hoặc mô tả công việc trước khi dùng AI đề xuất.');
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> AI';

    try {
        const recommendations = await requestRecommendations(context);
        const overlay = ensureModal();
        const body = overlay.querySelector('[data-ai-body]');
        const selectedId = context.form?.querySelector(context.select)?.value || '';
        body.innerHTML = renderCandidates(recommendations, selectedId);
        overlay.style.display = 'flex';

        body.querySelectorAll('[data-ai-pick]').forEach((pickBtn) => {
            pickBtn.addEventListener('click', async () => {
                const userId = pickBtn.getAttribute('data-ai-pick');
                const candidate = recommendations.find((item) => String(item.user_id) === String(userId));
                if (context.mode === 'save' && context.task_id) {
                    pickBtn.disabled = true;
                    pickBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';
                    try {
                        const result = await saveAssignee(context.task_id, userId);
                        if (!result.success) {
                            alert('Không thể lưu người thực hiện.');
                            pickBtn.disabled = false;
                            pickBtn.innerHTML = '<i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này';
                            return;
                        }
                        await sendFeedback(candidate?.recommendation_id);
                        window.location.reload();
                        return;
                    } catch (err) {
                        console.error(err);
                        alert('Không thể lưu người thực hiện.');
                        pickBtn.disabled = false;
                        pickBtn.innerHTML = '<i class="fa fa-check" style="margin-right: 4px;"></i> Chọn người này';
                        return;
                    }
                }

                const select = document.querySelector(context.select);
                if (select) {
                    if (![...select.options].some((opt) => String(opt.value) === String(userId))) {
                        const option = document.createElement('option');
                        option.value = userId;
                        option.textContent = `👤 ${displayName(candidate)}`;
                        select.appendChild(option);
                    }
                    select.value = userId;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                overlay.style.display = 'none';
            });
        });
    } catch (err) {
        console.error(err);
        alert(err.message || 'Lỗi kết nối khi gọi dịch vụ AI.');
    } finally {
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-ai-assignee]');
    if (!button) {
        return;
    }
    event.preventDefault();
    event.stopPropagation();
    openFromButton(button);
});
