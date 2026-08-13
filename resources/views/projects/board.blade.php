@extends('layouts.app')

@section('content')
<div class="jira-workspace">

    <div class="jira-workspace-heading">
        <div>
            <span class="jira-eyebrow">{{ $project->project_code }}</span>
            <h1>{{ $project->name }} / Board</h1>
            <p>Kanban workflow theo phong cách Jira.</p>
        </div>

        <div class="jira-view-links">
            <a class="active" href="{{ route('projects.board', $project) }}">Board</a>
            <a href="{{ route('sprints.index', $project) }}">Backlog / Sprint</a>
            <a href="{{ route('projects.timeline', $project) }}">Timeline</a>
            <a href="{{ route('projects.roadmap', $project) }}">Roadmap</a>
            <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}">+ Issue</a>
        </div>
    </div>

    <div class="jira-board">

        @foreach([
            'todo' => ['To do', 'jira-column-todo'],
            'in_progress' => ['In progress', 'jira-column-progress'],
            'done' => ['Done', 'jira-column-done']
        ] as $status => $column)

            <section
                class="jira-column {{ $column[1] }}"
                data-status="{{ $status }}"
            >
                <header>
                    <h2>{{ $column[0] }}</h2>

                    <span>
                        {{ $project->tasks->where('status', $status)->count() }}
                    </span>
                </header>

                <div class="jira-column-body">

                    @forelse($project->tasks->where('status', $status) as $task)

                        <article
                            class="jira-issue-card"
                            draggable="true"
                            data-task-id="{{ $task->id }}"
                        >
                            <div class="jira-issue-top">

                                <span class="jira-issue-key">
                                    {{ $task->issue_key ?? 'TP-' . str_pad($task->id, 5, '0', STR_PAD_LEFT) }}
                                </span>

                                <span class="jira-issue-type jira-type-{{ $task->issue_type ?? 'task' }}">
                                    {{ strtoupper($task->issue_type ?? 'task') }}
                                </span>

                                <span class="jira-priority jira-priority-{{ $task->priority ?? 'low' }}">
                                    {{ ucfirst($task->priority ?? 'low') }}
                                </span>

                            </div>

                            <a href="{{ route('tasks.show', $task) }}">
                                {{ $task->name }}
                            </a>

                            <div class="jira-issue-meta">
                                <span>
                                    {{ $task->labels ?: 'No labels' }}
                                </span>

                                <span>
                                    {{ $task->assignee->name ?? 'Unassigned' }}
                                </span>
                            </div>
                        </article>

                    @empty

                        <div class="jira-empty-column">
                            Không có issue
                        </div>

                    @endforelse

                </div>
            </section>

        @endforeach

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let draggedCard = null;
    let sourceColumn = null;
    let sourceNextSibling = null;
    let placeholder = null;

    const columns = document.querySelectorAll('.jira-column-body');

    /*
     * Tạo placeholder có kích thước giống card.
     * Card thật không bị di chuyển liên tục khi dragover
     * nên không còn hiện tượng giật / nhảy hàng.
     */
    function createPlaceholder(card) {
        const rect = card.getBoundingClientRect();

        placeholder = document.createElement('div');

        placeholder.className = 'jira-drag-placeholder';

        placeholder.style.height = rect.height + 'px';
        placeholder.style.width = '100%';
    }

    /*
     * Xác định card nào nằm ngay phía sau vị trí chuột.
     */
    function getDragAfterElement(container, mouseY) {

        const cards = [
            ...container.querySelectorAll(
                '.jira-issue-card:not(.is-dragging)'
            )
        ];

        let closest = {
            offset: Number.NEGATIVE_INFINITY,
            element: null
        };

        cards.forEach(function (card) {

            const box = card.getBoundingClientRect();

            const offset =
                mouseY -
                box.top -
                (box.height / 2);

            if (
                offset < 0 &&
                offset > closest.offset
            ) {
                closest = {
                    offset: offset,
                    element: card
                };
            }
        });

        return closest.element;
    }

    /*
     * Đặt placeholder đúng vị trí chuột.
     */
    function movePlaceholder(container, mouseY) {

        const afterElement =
            getDragAfterElement(container, mouseY);

        if (afterElement === null) {
            container.appendChild(placeholder);
        } else {
            container.insertBefore(
                placeholder,
                afterElement
            );
        }
    }

    /*
     * Bắt đầu kéo.
     */
    document
        .querySelectorAll('.jira-issue-card[draggable="true"]')
        .forEach(function (card) {

            card.addEventListener('dragstart', function (event) {

                draggedCard = card;

                sourceColumn = card.parentElement;

                sourceNextSibling = card.nextElementSibling;

                createPlaceholder(card);

                /*
                 * Card thật được ẩn khỏi layout.
                 * Placeholder giữ nguyên vị trí ban đầu.
                 */
                card.classList.add('is-dragging');

                event.dataTransfer.effectAllowed = 'move';

                event.dataTransfer.setData(
                    'text/plain',
                    card.dataset.taskId
                );

                /*
                 * Delay nhỏ để browser tạo drag image
                 * rồi mới đưa card ra khỏi layout.
                 */
                setTimeout(function () {

                    if (draggedCard) {
                        draggedCard.style.display = 'none';
                    }

                }, 0);
            });

            card.addEventListener('dragend', function () {

                if (!draggedCard) {
                    return;
                }

                /*
                 * Nếu drop không thành công,
                 * đưa card về vị trí cũ.
                 */
                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.replaceChild(
                        draggedCard,
                        placeholder
                    );
                }

                draggedCard.style.display = '';
                draggedCard.classList.remove('is-dragging');

                document
                    .querySelectorAll('.jira-drop-target')
                    .forEach(function (column) {
                        column.classList.remove('jira-drop-target');
                    });

                placeholder = null;
                draggedCard = null;
                sourceColumn = null;
                sourceNextSibling = null;
            });
        });

    /*
     * Xử lý từng cột.
     */
    columns.forEach(function (column) {

        column.addEventListener('dragover', function (event) {

            event.preventDefault();

            if (!draggedCard || !placeholder) {
                return;
            }

            event.dataTransfer.dropEffect = 'move';

            column
                .closest('.jira-column')
                .classList.add('jira-drop-target');

            /*
             * Nếu đang kéo sang cột khác,
             * placeholder sẽ được chuyển sang cột đó.
             */
            movePlaceholder(
                column,
                event.clientY
            );
        });

        column.addEventListener('dragleave', function (event) {

            const parentColumn =
                column.closest('.jira-column');

            if (
                !parentColumn.contains(event.relatedTarget)
            ) {
                parentColumn.classList.remove(
                    'jira-drop-target'
                );
            }
        });

        column.addEventListener('drop', async function (event) {

            event.preventDefault();

            if (!draggedCard || !placeholder) {
                return;
            }

            const targetColumn =
                column.closest('.jira-column');

            const status =
                targetColumn.dataset.status;

            const card = draggedCard;
            const oldColumn = sourceColumn;

            const oldNextSibling = sourceNextSibling;

            /*
             * Đưa card vào đúng vị trí placeholder.
             */
            placeholder.replaceWith(card);

            card.style.display = '';
            card.classList.remove('is-dragging');

            targetColumn.classList.remove(
                'jira-drop-target'
            );

            /*
             * Cập nhật số lượng task ngay trên UI.
             */
            updateColumnCount();

            /*
             * Lưu trạng thái lên server.
             * Không reload trang.
             */
            try {

                const response = await fetch(
                    '{{ url('tasks') }}/' +
                    card.dataset.taskId +
                    '/move',
                    {
                        method: 'PATCH',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },

                        body: JSON.stringify({
                            status: status
                        })
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'Không thể cập nhật trạng thái task'
                    );
                }

            } catch (error) {

                console.error(error);

                /*
                 * API lỗi -> trả card về vị trí cũ.
                 */
                if (oldNextSibling &&
                    oldNextSibling.parentNode === oldColumn) {

                    oldColumn.insertBefore(
                        card,
                        oldNextSibling
                    );

                } else {

                    oldColumn.appendChild(card);
                }

                updateColumnCount();
            }

            placeholder = null;
            draggedCard = null;
            sourceColumn = null;
            sourceNextSibling = null;
        });
    });

    /*
     * Cập nhật counter trên đầu mỗi cột.
     */
    function updateColumnCount() {

        document
            .querySelectorAll('.jira-column')
            .forEach(function (column) {

                const body =
                    column.querySelector('.jira-column-body');

                const counter =
                    column.querySelector('header span');

                if (!body || !counter) {
                    return;
                }

                counter.textContent =
                    body.querySelectorAll(
                        '.jira-issue-card'
                    ).length;
            });
    }
});
</script>
@endsection