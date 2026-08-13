/**
 * Jira-style Tag / Pill Input Component for Member Invitations
 */
function initJiraTagInput(containerId, hiddenInputId) {
    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(hiddenInputId);

    if (!container || !hiddenInput) return;

    let tags = [];

    // Initialize existing values if any
    if (hiddenInput.value.trim()) {
        const initialItems = hiddenInput.value.split(/[\r\n,;]+/);
        initialItems.forEach(item => {
            const val = item.trim();
            if (val && !tags.includes(val)) {
                tags.push(val);
            }
        });
    }

    container.className = 'jira-tag-input-container project-member-field';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'border-0 outline-0 flex-grow-1 shadow-none bg-transparent ms-1';
    input.placeholder = 'Nhập email hoặc username rồi nhấn Enter...';
    input.style.outline = 'none';
    input.style.minWidth = '200px';

    container.addEventListener('click', function (e) {
        if (e.target === container) {
            input.focus();
        }
    });

    function updateHiddenInput() {
        hiddenInput.value = tags.join(', ');
    }

    function renderTags() {
        // Remove existing badge pills
        const existingBadges = container.querySelectorAll('.jira-tag-pill');
        existingBadges.forEach(badge => badge.remove());

        tags.forEach((tag, index) => {
            const badge = document.createElement('span');
            badge.className = 'jira-tag-pill badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 d-inline-flex align-items-center me-1 mb-1 fs-6 fw-normal';
            badge.innerHTML = `
                <i class="fa fa-user-circle me-2 text-primary opacity-75"></i>
                <span>${tag}</span>
                <i class="fa fa-times ms-2 text-primary text-opacity-75 remove-btn" style="cursor:pointer;" data-index="${index}"></i>
            `;

            const removeBtn = badge.querySelector('.remove-btn');
            removeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                tags.splice(index, 1);
                renderTags();
                updateHiddenInput();
            });

            container.insertBefore(badge, input);
        });

        updateHiddenInput();
    }

    function addTag(val) {
        const cleaned = val.trim().replace(/^[,;\s]+|[,;\s]+$/g, '');
        if (cleaned && !tags.includes(cleaned)) {
            tags.push(cleaned);
            return true;
        }
        return false;
    }

    function processInput() {
        const raw = input.value;
        if (!raw) return;

        const items = raw.split(/[\r\n,;]+/);
        let added = false;

        items.forEach(item => {
            if (addTag(item)) {
                added = true;
            }
        });

        if (added) {
            input.value = '';
            renderTags();
        }
    }

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',' || e.key === ' ' || e.key === 'Tab') {
            e.preventDefault();
            processInput();
        } else if (e.key === 'Backspace' && input.value === '' && tags.length > 0) {
            tags.pop();
            renderTags();
        }
    });

    input.addEventListener('blur', function () {
        processInput();
    });

    input.addEventListener('paste', function (e) {
        setTimeout(() => {
            processInput();
        }, 10);
    });

    container.appendChild(input);
    renderTags();
}
