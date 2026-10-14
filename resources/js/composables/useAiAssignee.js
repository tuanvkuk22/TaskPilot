function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export function candidateDisplayName(candidate) {
    return candidate?.name || candidate?.display_name || candidate?.assignee || 'Thành viên';
}

export function candidateInitial(candidate) {
    return candidateDisplayName(candidate).charAt(0).toUpperCase();
}

export function candidateConfidence(candidate) {
    if (candidate?.confidence_percent != null && candidate.confidence_percent !== '') {
        return candidate.confidence_percent;
    }
    const score = Number(candidate?.score);
    return Number.isFinite(score) ? Math.round(score * 100) : 0;
}

export function assigneeFromCandidate(candidate) {
    return {
        id: candidate.user_id,
        name: candidateDisplayName(candidate),
        full_name: candidateDisplayName(candidate),
        email: candidate.email || null,
        avatar: candidate.avatar || null,
        job_title: candidate.job_title || '',
    };
}

export async function fetchAiRecommendations(context, recommendUrl = '/tasks/recommend-assignee') {
    const response = await fetch(recommendUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            name: context.name || '',
            summary: context.name || context.summary || '',
            description: context.description || '',
            project_id: context.project_id || context.projectId || null,
            issue_type: context.issue_type || context.issueType || 'task',
            priority: context.priority || 'medium',
            task_id: context.task_id || context.taskId || null,
        }),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success || !Array.isArray(data.recommendations)) {
        const message = data.message === 'This action is unauthorized.'
            ? 'Bạn không có quyền dùng AI gợi ý cho công việc này.'
            : (data.message || 'Không thể tải gợi ý AI vào lúc này.');
        throw new Error(message);
    }

    return data.recommendations;
}

export async function saveTaskAssignee(taskId, userId) {
    const response = await fetch(`/tasks/${taskId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            _method: 'PUT',
            assignee_id: userId || null,
        }),
    });

    return response.json();
}

export async function sendAiFeedback(recommendationId, status = 'accepted', feedbackUrl = '/tasks/ai-feedback') {
    if (!recommendationId) {
        return;
    }

    try {
        await fetch(feedbackUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                recommendation_id: recommendationId,
                status,
            }),
        });
    } catch (err) {
        console.warn('AI feedback error:', err);
    }
}

const BATCH_CHUNK = 25;

async function postJson(url, body) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });
    return response.json();
}

export async function fetchAiBatchRecommendations(taskIds, url = '/tasks/recommend-assignee-batch') {
    const items = [];
    for (let index = 0; index < taskIds.length; index += BATCH_CHUNK) {
        const chunk = taskIds.slice(index, index + BATCH_CHUNK);
        const data = await postJson(url, { task_ids: chunk });
        if (!data.success || !Array.isArray(data.items)) {
            throw new Error(data.message || 'Không thể tải gợi ý AI hàng loạt.');
        }
        items.push(...data.items);
    }
    return items;
}

export async function applyAiBatchAssignments(assignments, url = '/tasks/ai-assign-batch') {
    const tasks = [];
    for (let index = 0; index < assignments.length; index += BATCH_CHUNK) {
        const chunk = assignments.slice(index, index + BATCH_CHUNK);
        const data = await postJson(url, { assignments: chunk });
        if (!data.success) {
            throw new Error(data.message || 'Không thể lưu phân công hàng loạt.');
        }
        tasks.push(...(data.tasks || []));
    }
    return tasks;
}

export async function resetAiTestAssignments({ projectId = null, taskIds = [] } = {}, url = '/tasks/ai-test-reset') {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            project_id: projectId || null,
            task_ids: taskIds,
        }),
    });

    const data = await response.json();
    if (!data.success) {
        throw new Error(data.message || 'Không thể reset dữ liệu test AI.');
    }

    return data;
}
