<template>
    <div class="project-members-vue">
        <!-- Shared project shell: identical hierarchy to Board and Timeline. -->
        <div class="pd-header mb-3">
            <div class="pd-header-left">
                <div class="pd-breadcrumb mb-1">
                    <a href="/projects">Dự án</a><span>/</span><span>{{ project.name }}</span><span>/</span><span class="text-dark font-weight-bold">Thành viên</span>
                </div>
                <div class="pd-title-row">
                    <h1 class="pd-title">{{ project.name }}</h1>
                    <span class="pd-project-key">{{ project.project_code || 'PROJ' }}</span>
                    <span class="pd-project-status">{{ project.status || 'active' }}</span>
                </div>
            </div>
            <div class="pm-project-actions">
                <button type="button" class="tp-btn tp-btn-light" @click="openImportDrawer"><i class="fa fa-file-excel-o"></i><span>Nhập Excel</span></button>
                <button type="button" class="tp-btn tp-btn-primary" @click="openInviteDrawer"><i class="fa fa-user-plus"></i><span>Mời thành viên</span></button>
            </div>
        </div>

        <div class="pd-project-nav mb-4">
            <a :href="`/projects/${project.id}`" class="pd-nav-item">Tổng quan</a>
            <a :href="`/projects/${project.id}/board`" class="pd-nav-item">Bảng</a>
            <a :href="`/projects/${project.id}/members`" class="pd-nav-item active">Thành viên</a>
            <a :href="`/projects/${project.id}/roles`" class="pd-nav-item">Vai trò &amp; Quyền</a>
            <a :href="`/projects/${project.id}/sprints`" class="pd-nav-item">Backlog / Sprint</a>
            <a :href="`/projects/${project.id}/timeline`" class="pd-nav-item">Timeline</a>
            <a :href="`/projects/${project.id}/roadmap`" class="pd-nav-item">Roadmap</a>
            <a :href="`/projects/${project.id}/reports`" class="pd-nav-item">Báo cáo</a>
        </div>

        <!-- ALERTS -->
        <div v-if="successMessage" class="pm-alert pm-alert-success">
            <i class="fa fa-check-circle"></i>
            <span>{{ successMessage }}</span>
        </div>
        <div v-if="errorMessage" class="pm-alert pm-alert-error">
            <i class="fa fa-exclamation-circle"></i>
            <span>{{ errorMessage }}</span>
        </div>

        <!-- STATS -->
        <div class="pm-stats">
            <div class="pm-stat-card">
                <div class="pm-stat-icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="pm-stat-info">
                    <span class="pm-stat-value">{{ members.length }}</span>
                    <span class="pm-stat-label">Thành viên</span>
                </div>
            </div>
            <div class="pm-stat-card">
                <div class="pm-stat-icon">
                    <i class="fa fa-shield"></i>
                </div>
                <div class="pm-stat-info">
                    <span class="pm-stat-value">{{ roles.length }}</span>
                    <span class="pm-stat-label">Vai trò</span>
                </div>
            </div>
            <div class="pm-stat-card">
                <div class="pm-stat-icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <div class="pm-stat-info">
                    <span class="pm-stat-value">{{ inviteCount }}</span>
                    <span class="pm-stat-label">Đã mời</span>
                </div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="pm-toolbar">
            <div class="pm-toolbar-left">
                <div class="pm-search">
                    <i class="fa fa-search"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Tìm thành viên..."
                        autocomplete="off"
                    >
                </div>
                <div class="pm-filter-chips">
                    <button
                        type="button"
                        class="pm-chip"
                        :class="{ active: selectedRoleFilter === 'all' && !inactiveFilter }"
                        @click="selectedRoleFilter = 'all'; inactiveFilter = false"
                    >
                        Tất cả
                    </button>
                    <button
                        v-for="role in roles"
                        :key="role.id"
                        type="button"
                        class="pm-chip"
                        :class="{ active: selectedRoleFilter === role.name && !inactiveFilter }"
                        @click="selectedRoleFilter = role.name; inactiveFilter = false"
                    >
                        {{ role.name }}
                    </button>
                    <button
                        type="button"
                        class="pm-chip"
                        :class="{ active: inactiveFilter }"
                        @click="inactiveFilter = !inactiveFilter"
                    >
                        Chưa hoạt động
                    </button>
                </div>
            </div>
            <button
                type="button"
                class="pm-btn pm-btn-primary"
                @click="openInviteDrawer"
            >
                <i class="fa fa-plus"></i>
                <span>Mời thành viên</span>
            </button>
            <button
                type="button"
                class="pm-btn pm-btn-default"
                @click="openImportDrawer"
            >
                <i class="fa fa-file-excel-o"></i>
                <span>Nhập Excel</span>
            </button>
        </div>

        <!-- MEMBERS LIST -->
        <div class="pm-list">
            <div
                v-for="member in filteredMembers"
                :key="member.id"
                class="pm-card"
            >
                <div class="pm-card-left">
                    <div class="pm-avatar">
                        <img
                            v-if="member.avatar && !brokenAvatars[member.id]"
                            :src="member.avatar"
                            :alt="member.name"
                            @error="handleAvatarError(member.id)"
                            onerror="this.nextElementSibling.style.display='flex'; this.style.display='none';"
                        >
                        <span v-if="!member.avatar || brokenAvatars[member.id]" class="pm-avatar-fallback">
                            {{ getInitials(member) }}
                        </span>
                    </div>
                    <div class="pm-member-info">
                        <strong class="pm-member-name">
                            {{ member.name || member.email }}
                        </strong>
                        <span class="pm-member-email">{{ member.email }}</span>
                        <div class="pm-member-meta">
                            <span v-if="member.job_title" class="pm-member-job">
                                {{ member.job_title }}
                            </span>
                            <span v-if="member.last_login_at" class="pm-member-active">
                                <i class="fa fa-clock-o"></i>
                                {{ formatLastActive(member.last_login_at) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="pm-card-right">
                    <div class="pm-card-actions">
                        <select
                            v-model="memberRoles[member.id]"
                            class="pm-role-select"
                            :disabled="member.pivot?.role === 'owner'"
                        >
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <button
                            v-if="member.pivot?.role !== 'owner' && memberRoles[member.id] !== member.pivot?.role"
                            class="pm-btn-update"
                            @click="updateRole(member)"
                            title="Cập nhật vai trò"
                        >
                            <i class="fa fa-check"></i>
                        </button>
                        <button
                            class="pm-btn-icon pm-btn-remove"
                            @click="removeMember(member)"
                            v-if="member.pivot?.role !== 'owner'"
                            title="Xóa thành viên"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-if="!filteredMembers.length" class="pm-empty">
                <div class="pm-empty-icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <h3>Chưa có thành viên</h3>
                <p>Mời thành viên tham gia dự án để bắt đầu cộng tác.</p>
                <button
                    type="button"
                    class="pm-btn pm-btn-primary"
                    @click="openInviteDrawer"
                >
                    <i class="fa fa-plus"></i>
                    <span>Mời thành viên đầu tiên</span>
                </button>
            </div>
        </div>

        <!-- INVITE DRAWER -->
        <div
            v-if="inviteDrawerOpen"
            class="pm-drawer-overlay"
            @click.self="closeInviteDrawer"
        >
            <div class="pm-drawer">
                <div class="pm-drawer-header">
                    <div>
                        <span class="pm-drawer-eyebrow">THÀNH VIÊN MỚI</span>
                        <h2>Mời thành viên</h2>
                    </div>
                    <button
                        type="button"
                        class="pm-drawer-close"
                        @click="closeInviteDrawer"
                    >
                        &times;
                    </button>
                </div>
                <div class="pm-drawer-body">
                    <div class="pm-drawer-field">
                        <label for="invite-email">Email thành viên</label>
                        <input
                            id="invite-email"
                            v-model="inviteForm.email"
                            type="email"
                            class="pm-form-control"
                            placeholder="example@company.com"
                            autocomplete="off"
                        >
                        <div v-if="inviteErrors.email" class="pm-field-error">
                            {{ inviteErrors.email[0] }}
                        </div>
                    </div>
                    <div class="pm-drawer-field">
                        <label for="invite-role">Vai trò</label>
                        <select
                            id="invite-role"
                            v-model="inviteForm.role"
                            class="pm-form-control"
                        >
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="pm-drawer-footer">
                    <button
                        type="button"
                        class="pm-btn pm-btn-default"
                        :disabled="inviting"
                        @click="closeInviteDrawer"
                    >
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="pm-btn pm-btn-primary"
                        :disabled="inviting || !inviteForm.email.trim()"
                        @click="inviteMember"
                    >
                        <i v-if="inviting" class="fa fa-spinner fa-spin"></i>
                        <i v-else class="fa fa-paper-plane"></i>
                        <span>{{ inviting ? 'Đang mời...' : 'Mời ngay' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- IMPORT DRAWER -->
    <div
        v-if="importDrawerOpen"
        class="pm-drawer-overlay"
        @click.self="closeImportDrawer"
    >
        <div class="pm-drawer">
            <div class="pm-drawer-header">
                <div>
                    <span class="pm-drawer-eyebrow">NHẬP THÀNH VIÊN</span>
                    <h2>Nhập từ Excel / CSV</h2>
                </div>
                <button
                    type="button"
                    class="pm-drawer-close"
                    @click="closeImportDrawer"
                >
                    &times;
                </button>
            </div>
            <div class="pm-drawer-body">
                <div class="pm-drawer-field">
                    <button
                        type="button"
                        class="pm-btn pm-btn-default"
                        @click="downloadTemplate"
                        style="margin-bottom: 10px;"
                    >
                        <i class="fa fa-download"></i>
                        <span>Tải template Excel</span>
                    </button>
                </div>

                <div class="pm-drawer-field">
                    <label>Chọn file Excel hoặc CSV</label>
                    <input
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="pm-form-control"
                        @change="handleImportFile"
                    >
                    <div class="pm-field-hint">
                        File cần có cột <strong>email</strong>. Cột <strong>role</strong> là tùy chọn.
                    </div>
                </div>

                <div class="pm-drawer-field" v-if="importPreview.length">
                    <label>Vai trò mặc định</label>
                    <select
                        v-model="importDefaultRole"
                        class="pm-form-control"
                    >
                        <option
                            v-for="role in roles"
                            :key="role.id"
                            :value="role.name"
                        >
                            {{ role.name }}
                        </option>
                    </select>
                </div>

                <div class="pm-import-preview" v-if="importPreview.length">
                    <table class="pm-import-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in importPreview" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>{{ row.email }}</td>
                                <td>{{ row.role || importDefaultRole }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pm-field-error" v-if="importErrors.length">
                    <div v-for="(err, index) in importErrors" :key="index">{{ err }}</div>
                </div>
            </div>
            <div class="pm-drawer-footer">
                <button
                    type="button"
                    class="pm-btn pm-btn-default"
                    :disabled="importing"
                    @click="closeImportDrawer"
                >
                    Hủy
                </button>
                <button
                    type="button"
                    class="pm-btn pm-btn-primary"
                    :disabled="importing || !importPreview.length"
                    @click="confirmImport"
                >
                    <i v-if="importing" class="fa fa-spinner fa-spin"></i>
                    <i v-else class="fa fa-upload"></i>
                    <span>{{ importing ? 'Đang nhập...' : 'Nhập thành viên' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { read, utils, write } from 'xlsx';

const props = defineProps({
    project: { type: Object, required: true },
    members: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    csrfToken: { type: String, default: '' },
});

const members = ref([...props.members]);
const roles = ref([...props.roles]);
const memberRoles = reactive({});
const searchQuery = ref('');
const selectedRoleFilter = ref('all');
const inactiveFilter = ref(false);
const brokenAvatars = reactive({});
const successMessage = ref('');
const errorMessage = ref('');
const inviteDrawerOpen = ref(false);
const inviting = ref(false);
const inviteCount = ref(0);
const inviteErrors = ref({});

const inviteForm = reactive({
    email: '',
    role: props.roles[0]?.name || 'member',
});

const importDrawerOpen = ref(false);
const importFile = ref(null);
const importPreview = ref([]);
const importDefaultRole = ref(props.roles[0]?.name || 'member');
const importing = ref(false);
const importErrors = ref([]);

props.members.forEach(m => {
    memberRoles[m.id] = m.pivot?.role || 'Thành viên';
});

const filteredMembers = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    let result = members.value;

    if (query) {
        result = result.filter(m => {
            const name = (m.name || m.email || '').toLowerCase();
            const email = (m.email || '').toLowerCase();
            const job = (m.job_title || '').toLowerCase();
            return name.includes(query) || email.includes(query) || job.includes(query);
        });
    }

    if (selectedRoleFilter.value !== 'all') {
        result = result.filter(m => m.pivot?.role === selectedRoleFilter.value);
    }

    if (inactiveFilter.value) {
        result = result.filter(m => isInactive(m));
    }

    return result;
});

function getInitials(member) {
    const name = member.name || member.email || '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

function handleAvatarError(memberId) {
    brokenAvatars[memberId] = true;
}

function isInactive(member) {
    if (!member.last_login_at) return true;
    const lastLogin = new Date(member.last_login_at);
    const now = new Date();
    const diffDays = (now - lastLogin) / (1000 * 60 * 60 * 24);
    return diffDays > 7;
}

function getRoleBadgeClass(role) {
    const map = {
        'owner': 'pm-badge-owner',
        'manager': 'pm-badge-manager',
        'member': 'pm-badge-member',
        'viewer': 'pm-badge-viewer',
    };
    return map[role] || 'pm-badge-member';
}

function formatLastActive(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Vừa hoạt động';
    if (minutes < 60) return `${minutes} phút trước`;
    if (hours < 24) return `${hours} giờ trước`;
    if (days < 7) return `${days} ngày trước`;
    return date.toLocaleDateString('vi-VN');
}

function updateRole(member) {
    const form = new FormData();
    form.append('_token', props.csrfToken);
    form.append('user_id', member.id);
    form.append('role', memberRoles[member.id]);

    fetch(`/projects/${props.project.id}/members/role`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        body: form,
    })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(data => {
        if (!data.success) {
            errorMessage.value = data.error || 'Không thể cập nhật vai trò.';
            setTimeout(() => { errorMessage.value = ''; }, 3000);
            return;
        }

        member.pivot.role = memberRoles[member.id];
        successMessage.value = data.message || 'Đã cập nhật vai trò.';
        setTimeout(() => { successMessage.value = ''; }, 3000);
    })
    .catch(() => {
        errorMessage.value = 'Lỗi kết nối. Vui lòng thử lại.';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    });
}

function removeMember(member) {
    if (!confirm(`Xóa thành viên "${member.name || member.email}" khỏi dự án?`)) return;
    const form = new FormData();
    form.append('_token', props.csrfToken);
    form.append('user_id', member.id);

    fetch(`/projects/${props.project.id}/members`, {
        method: 'POST',
        headers: {
            'X-HTTP-Method-Override': 'DELETE',
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        body: form,
    })
    .then(r => {
        if (!r.ok) throw new Error();
        return r.json();
    })
    .then(data => {
        if (data.success) {
            members.value = members.value.filter(m => m.id !== member.id);
            successMessage.value = 'Đã xóa thành viên khỏi dự án.';
            setTimeout(() => { successMessage.value = ''; }, 3000);
        } else {
            errorMessage.value = data.error || 'Không thể xóa thành viên.';
            setTimeout(() => { errorMessage.value = ''; }, 3000);
        }
    })
    .catch(() => {
        errorMessage.value = 'Lỗi kết nối. Vui lòng thử lại.';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    });
}

function openInviteDrawer() {
    inviteForm.email = '';
    inviteForm.role = props.roles[0]?.name || 'member';
    inviteErrors.value = {};
    closeMessages();
    inviteDrawerOpen.value = true;
}

function closeInviteDrawer() {
    inviteDrawerOpen.value = false;
    inviteForm.email = '';
    inviteErrors.value = {};
}

function inviteMember() {
    if (!inviteForm.email.trim()) return;

    inviting.value = true;
    inviteErrors.value = {};
    closeMessages();

    const form = new FormData();
    form.append('_token', props.csrfToken);
    form.append('email', inviteForm.email.trim());
    form.append('role', inviteForm.role);

    fetch(`/projects/${props.project.id}/members/invite`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        body: form,
    })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(data => {
        if (!data.success) {
            if (data.errors) {
                inviteErrors.value = data.errors;
            } else {
                errorMessage.value = data.error || 'Không thể mời thành viên.';
                setTimeout(() => { errorMessage.value = ''; }, 3000);
            }
            return;
        }

        successMessage.value = data.message || 'Mời thành viên thành công.';
        inviteCount.value++;
        inviteDrawerOpen.value = false;
        setTimeout(() => { successMessage.value = ''; }, 3000);
    })
    .catch(() => {
        errorMessage.value = 'Lỗi kết nối. Vui lòng thử lại.';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    })
    .finally(() => {
        inviting.value = false;
    });
}

function downloadTemplate() {
    const sampleRows = [
        { email: 'nguyenvanA@example.com', role: 'member' },
        { email: 'tranvanB@example.com', role: 'manager' },
    ];

    const worksheet = utils.json_to_sheet(sampleRows);
    const workbook = { Sheets: { 'Members': worksheet }, SheetNames: ['Members'] };

    const buffer = write(workbook, { bookType: 'xlsx', type: 'array' });
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = 'members-import-template.xlsx';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function openImportDrawer() {
    importFile.value = null;
    importPreview.value = [];
    importDefaultRole.value = props.roles[0]?.name || 'member';
    importErrors.value = [];
    closeMessages();
    importDrawerOpen.value = true;
}

function closeImportDrawer() {
    importDrawerOpen.value = false;
    importFile.value = null;
    importPreview.value = [];
    importErrors.value = [];
}

function handleImportFile(event) {
    const file = event.target.files[0];
    if (!file) return;

    importFile.value = file;
    importErrors.value = [];

    const reader = new FileReader();
    reader.onload = function (e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = utils.sheet_to_json(firstSheet, { defval: '' });

            const preview = [];
            for (const row of jsonData) {
                const email = (row.email || row.Email || row.EMAIL || '').toString().trim();
                if (!email) continue;

                preview.push({
                    email: email,
                    role: (row.role || row. Role || row.ROLE || '').toString().trim(),
                });
            }

            importPreview.value = preview;
        } catch (error) {
            console.error('Failed to parse file:', error);
            importErrors.value = ['Không thể đọc file. Vui lòng kiểm tra định dạng file.'];
            importPreview.value = [];
        }
    };
    reader.readAsArrayBuffer(file);
}

async function confirmImport() {
    if (!importPreview.value.length) return;

    importing.value = true;
    importErrors.value = [];
    closeMessages();

    const payload = importPreview.value.map(row => ({
        email: row.email,
        role: row.role || importDefaultRole.value,
    }));

    try {
        const response = await fetch(`/projects/${props.project.id}/members/import`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: JSON.stringify({ members: payload }),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const message = data.message || 'Không thể nhập thành viên.';
            const errors = data.errors || [message];
            importErrors.value = Array.isArray(errors) ? errors : [errors];
            errorMessage.value = message;
            setTimeout(() => { errorMessage.value = ''; }, 3000);
            return;
        }

        successMessage.value = `Nhập ${data.count} thành viên thành công.`;
        inviteCount.value += data.count;
        importDrawerOpen.value = false;
        setTimeout(() => { successMessage.value = ''; }, 3000);
    } catch (error) {
        console.error('Import error:', error);
        errorMessage.value = 'Lỗi kết nối. Vui lòng thử lại.';
        setTimeout(() => { errorMessage.value = ''; }, 3000);
    } finally {
        importing.value = false;
    }
}

function closeMessages() {
    successMessage.value = '';
    errorMessage.value = '';
}
</script>
<style scoped>
.pm-project-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 640px) {
    .pm-project-actions {
        flex-direction: column;
        align-items: stretch;
    }
}

/* HEADER */
.pm-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.pm-eyebrow {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.pm-title {
    margin: 0;
    color: #172033;
    font-size: 28px;
    line-height: 1.25;
    font-weight: 700;
}

.pm-subtitle {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
}

.pm-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 0 0 auto;
}

/* BUTTONS */
.pm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 38px;
    padding: 8px 14px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    box-sizing: border-box;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}

.pm-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pm-btn-default {
    background: #fff;
    border-color: #cbd5e1;
    color: #334155;
}

.pm-btn-default:hover:not(:disabled) {
    background: #f8fafc;
    border-color: #94a3b8;
}

.pm-btn-primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.pm-btn-primary:hover:not(:disabled) {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

.pm-btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 6px;
    border: 1px solid transparent;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}

.pm-btn-remove:hover {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
}

/* ALERTS */
.pm-alert {
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 42px;
    margin-bottom: 16px;
    padding: 10px 13px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 500;
    box-sizing: border-box;
}

.pm-alert-success {
    border: 1px solid #bbf7d0;
    background: #f0fdf4;
    color: #166534;
}

.pm-alert-error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

/* STATS */
.pm-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.pm-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.pm-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: #2563eb;
    font-size: 18px;
    flex: 0 0 44px;
}

.pm-stat-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.pm-stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #172033;
    line-height: 1.2;
}

.pm-stat-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

/* TOOLBAR */
.pm-toolbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}

.pm-toolbar-left {
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.pm-search {
    position: relative;
    min-width: 280px;
}

.pm-search i {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
    font-size: 14px;
}

.pm-search input {
    width: 100%;
    height: 40px;
    padding: 8px 12px 8px 36px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #fff;
    color: #172033;
    font-size: 13px;
    outline: none;
    box-sizing: border-box;
    transition: all 0.15s ease;
}

.pm-search input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pm-filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.pm-chip {
    padding: 5px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #fff;
    color: #475569;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.pm-chip:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.pm-chip.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

/* LIST */
.pm-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* CARD */
.pm-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    transition: all 0.15s ease;
}

.pm-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.pm-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}

.pm-avatar {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    overflow: hidden;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 44px;
    font-size: 14px;
    font-weight: 700;
}

.pm-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pm-avatar-fallback {
    color: #fff;
    background: #2563eb;
    font-size: 14px;
    font-weight: 700;
}

.pm-member-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.pm-member-name {
    display: block;
    color: #172033;
    font-size: 14px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pm-member-email {
    display: block;
    color: #64748b;
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pm-member-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 2px;
}

.pm-member-job {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

.pm-member-active {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #94a3b8;
}

.pm-member-active i {
    font-size: 10px;
}

.pm-card-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 0 0 auto;
}

/* ROLE BADGE */
.pm-role-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    background: #f1f5f9;
    color: #475569;
    white-space: nowrap;
}

.pm-badge-owner {
    background: #fef3c7;
    color: #92400e;
}

.pm-badge-manager {
    background: #dbeafe;
    color: #1e40af;
}

.pm-badge-member {
    background: #f1f5f9;
    color: #475569;
}

.pm-badge-viewer {
    background: #f3e8ff;
    color: #6b21a8;
}

/* ROLE SELECT */
.pm-role-select {
    height: 34px;
    padding: 0 28px 0 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #334155;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    outline: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    transition: all 0.15s ease;
}

.pm-role-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pm-role-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f8fafc;
}

/* UPDATE BUTTON */
.pm-btn-update {
    height: 34px;
    min-width: 34px;
    padding: 0 10px;
    border: 1px solid #2563eb;
    border-radius: 6px;
    background: #2563eb;
    color: #fff;
    font-size: 12px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-left: 8px;
    transition: all 0.15s ease;
}

.pm-btn-update:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

/* IMPORT PREVIEW */
.pm-import-preview {
    margin-top: 12px;
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.pm-import-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.pm-import-table th,
.pm-import-table td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.pm-import-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
    position: sticky;
    top: 0;
}

.pm-import-table tr:last-child td {
    border-bottom: none;
}

.pm-field-hint {
    margin-top: 6px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.5;
}

/* EMPTY STATE */
.pm-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.pm-empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #f1f5f9;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
}

.pm-empty h3 {
    margin: 0 0 6px;
    color: #172033;
    font-size: 16px;
    font-weight: 600;
}

.pm-empty p {
    margin: 0 0 20px;
    color: #64748b;
    font-size: 13px;
}

/* DRAWER */
.pm-drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    justify-content: flex-end;
    background: rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(1px);
}

.pm-drawer {
    width: min(480px, 100vw);
    height: 100%;
    background: #fff;
    box-shadow: -8px 0 30px rgba(15, 23, 42, 0.12);
    display: flex;
    flex-direction: column;
    animation: pmSlideIn 0.2s ease-out;
}

@keyframes pmSlideIn {
    from { transform: translateX(30px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.pm-drawer-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.pm-drawer-eyebrow {
    display: block;
    margin-bottom: 4px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.pm-drawer-header h2 {
    margin: 0;
    color: #172033;
    font-size: 18px;
    font-weight: 700;
}

.pm-drawer-close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #64748b;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.pm-drawer-close:hover {
    background: #f1f5f9;
    color: #172033;
}

.pm-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.pm-drawer-field {
    margin-bottom: 18px;
}

.pm-drawer-field label {
    display: block;
    margin-bottom: 6px;
    color: #172033;
    font-size: 13px;
    font-weight: 600;
}

.pm-form-control {
    width: 100%;
    min-height: 40px;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #fff;
    color: #172033;
    font-size: 13px;
    outline: none;
    box-sizing: border-box;
    transition: all 0.15s ease;
}

.pm-form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.pm-field-error {
    margin-top: 6px;
    color: #dc2626;
    font-size: 12px;
}

.pm-drawer-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .pm-header {
        flex-direction: column;
    }

    .pm-stats {
        grid-template-columns: 1fr;
    }

    .pm-toolbar {
        flex-direction: column;
    }

    .pm-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .pm-card-right {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
