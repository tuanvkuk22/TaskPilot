<template>
  <div class="project-list-vue">
    <!-- HEADER -->
    <div class="pl-header">
      <div class="pl-header-left">
        <h1 class="pl-title">Dự án</h1>
        <p class="pl-subtitle">Quản lý và theo dõi tất cả dự án của bạn</p>
      </div>
      <a href="/projects/create" class="pl-create-btn">
        <i class="fa fa-plus"></i> Tạo dự án mới
      </a>
    </div>

    <!-- STATS -->
    <div class="pl-stats">
      <div class="pl-stat-card">
        <div class="pl-stat-icon blue"><i class="fa fa-folder"></i></div>
        <div class="pl-stat-info">
          <span class="pl-stat-number">{{ stats.total }}</span>
          <span class="pl-stat-label">Tất cả</span>
        </div>
      </div>
      <div class="pl-stat-card">
        <div class="pl-stat-icon green"><i class="fa fa-play-circle"></i></div>
        <div class="pl-stat-info">
          <span class="pl-stat-number">{{ stats.active }}</span>
          <span class="pl-stat-label">Đang hoạt động</span>
        </div>
      </div>
      <div class="pl-stat-card">
        <div class="pl-stat-icon orange"><i class="fa fa-check-circle"></i></div>
        <div class="pl-stat-info">
          <span class="pl-stat-number">{{ stats.completed }}</span>
          <span class="pl-stat-label">Đã hoàn thành</span>
        </div>
      </div>
      <div class="pl-stat-card">
        <div class="pl-stat-icon grey"><i class="fa fa-archive"></i></div>
        <div class="pl-stat-info">
          <span class="pl-stat-number">{{ stats.archived }}</span>
          <span class="pl-stat-label">Đã lưu trữ</span>
        </div>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="pl-filters">
      <div class="pl-search-box">
        <i class="fa fa-search"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm kiếm dự án..."
          class="pl-search-input"
        />
      </div>
      <div class="pl-filter-pills">
        <button
          v-for="f in filters"
          :key="f.value"
          :class="['pl-pill', { active: activeFilter === f.value }]"
          @click="activeFilter = f.value"
        >
          <i :class="f.icon"></i>
          {{ f.label }}
        </button>
      </div>
    </div>

    <!-- TABLE -->
    <div class="pl-table-wrap">
      <table class="pl-table">
        <thead>
          <tr>
            <th>Dự án</th>
            <th>Khách hàng / Đội</th>
            <th>Trạng thái</th>
            <th>Công việc</th>
            <th style="text-align: right;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in filteredProjects" :key="project.id">
            <td>
              <div class="pl-project-name">
                <a :href="`/projects/${project.id}`" class="pl-project-link">{{ project.name }}</a>
                <span class="pl-project-meta">{{ project.project_code || 'Chưa có mã' }}</span>
              </div>
            </td>
            <td>
              <div class="pl-project-name">
                <span>{{ project.company?.name || 'Nội bộ' }}</span>
                <span class="pl-project-meta">{{ project.team?.name || project.team_name || 'Chưa có đội' }}</span>
              </div>
            </td>
            <td>
              <span :class="['pl-status-badge', statusClass(project.status)]">
                {{ statusLabel(project.status) }}
              </span>
            </td>
            <td>
              <span class="pl-task-count">
                <i class="fa fa-tasks"></i> {{ project.tasks_count || 0 }}
              </span>
            </td>
            <td>
              <div class="pl-actions">
                <a :href="`/projects/${project.id}/board`" class="pl-btn pl-btn-ghost">
                  <i class="fa fa-columns"></i> Board
                </a>
                <a :href="`/projects/${project.id}`" class="pl-btn pl-btn-primary">
                  <i class="fa fa-eye"></i> Chi tiết
                </a>
                <button
                  v-if="isProjectAdmin(project)"
                  class="pl-btn pl-btn-danger"
                  @click="openDeleteModal(project)"
                  title="Xóa dự án"
                >
                  <i class="fa fa-trash"></i> Xóa
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!filteredProjects.length">
            <td colspan="5">
              <div class="pl-empty">
                <div class="pl-empty-icon"><i class="fa fa-folder-open-o"></i></div>
                <h3>Chưa có dự án nào</h3>
                <p>Bắt đầu bằng cách tạo dự án đầu tiên để quản lý công việc và theo dõi tiến độ.</p>
                <a href="/projects/create" class="pl-create-btn">
                  <i class="fa fa-plus"></i> Tạo dự án đầu tiên
                </a>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- DELETE MODAL -->
    <div v-if="deleteModal.open" class="pl-modal-overlay" @click.self="closeDeleteModal">
      <div class="pl-modal">
        <div class="pl-modal-header">
          <h3>Xóa dự án</h3>
          <button class="pl-modal-close" @click="closeDeleteModal">&times;</button>
        </div>
        <div class="pl-modal-body">
          <p>Bạn có chắc chắn muốn xóa dự án <strong>{{ deleteModal.project?.name }}</strong>?</p>
          <p class="pl-modal-warning">Hành động này không thể hoàn tác. Tất cả dữ liệu liên quan sẽ bị xóa vĩnh viễn.</p>
        </div>
        <div class="pl-modal-footer">
          <button class="pl-btn pl-btn-ghost" @click="closeDeleteModal">Hủy</button>
          <button class="pl-btn pl-btn-danger" @click="confirmDelete" :disabled="deleting">
            <i class="fa fa-trash"></i> {{ deleting ? 'Đang xóa...' : 'Xóa dự án' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  projects: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ total: 0, active: 0, completed: 0, archived: 0 }) },
});

const searchQuery = ref('');
const activeFilter = ref('all');
const deleting = ref(false);

const deleteModal = ref({
  open: false,
  project: null,
});

const filters = [
  { value: 'all', label: 'Tất cả', icon: 'fa fa-th-large' },
  { value: 'active', label: 'Đang hoạt động', icon: 'fa fa-play-circle' },
  { value: 'archived', label: 'Đã lưu trữ', icon: 'fa fa-archive' },
  { value: 'completed', label: 'Đã hoàn thành', icon: 'fa fa-check-circle' },
];

const filteredProjects = computed(() => {
  let result = props.projects || [];

  if (activeFilter.value !== 'all') {
    result = result.filter(p => p.status === activeFilter.value);
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(p => {
      return (
        (p.name || '').toLowerCase().includes(q) ||
        (p.project_code || '').toLowerCase().includes(q) ||
        (p.company?.name || '').toLowerCase().includes(q) ||
        (p.team?.name || '').toLowerCase().includes(q)
      );
    });
  }

  return result;
});

function isProjectAdmin(project) {
  const role = project.user_role;
  return role === 'owner' || role === 'admin';
}

function openDeleteModal(project) {
  deleteModal.value = {
    open: true,
    project,
  };
}

function closeDeleteModal() {
  deleteModal.value = {
    open: false,
    project: null,
  };
}

async function confirmDelete() {
  const project = deleteModal.value.project;
  if (!project) return;

  deleting.value = true;

  try {
    const form = new FormData();
    form.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    form.append('_method', 'DELETE');

    const response = await fetch(`/projects/${project.id}`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      body: form,
    });

    if (!response.ok) throw new Error('Delete failed');

    const data = await response.json();
    if (data.success) {
      const index = props.projects.findIndex(p => p.id === project.id);
      if (index > -1) {
        props.projects.splice(index, 1);
      }
      closeDeleteModal();
      alert('Đã xóa dự án thành công.');
    } else {
      alert(data.error || 'Không thể xóa dự án.');
    }
  } catch (error) {
    alert('Lỗi kết nối. Vui lòng thử lại.');
  } finally {
    deleting.value = false;
  }
}

function truncate(text, length) {
  if (!text) return '';
  return text.length > length ? text.slice(0, length) + '...' : text;
}

function statusClass(status) {
  const map = {
    active: 'pl-status-active',
    archived: 'pl-status-archived',
    completed: 'pl-status-completed',
  };
  return map[status] || 'pl-status-archived';
}

function statusLabel(status) {
  const map = {
    active: 'Đang hoạt động',
    archived: 'Đã lưu trữ',
    completed: 'Đã hoàn thành',
  };
  return map[status] || status;
}
</script>

<style scoped>
.project-list-vue {
  padding: 28px 0 40px;
}

.pl-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.pl-title {
  font-size: 26px;
  font-weight: 700;
  color: #172b4d;
  margin: 0 0 6px;
}

.pl-subtitle {
  color: #6b778c;
  margin: 0;
  font-size: 13.5px;
}

.pl-create-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #0c66e4;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.15s ease;
  box-shadow: 0 2px 8px rgba(12,102,228,.25);
}

.pl-create-btn:hover {
  background: #0052cc;
  color: #fff;
  box-shadow: 0 4px 16px rgba(12,102,228,.35);
  transform: translateY(-1px);
}

.pl-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 28px;
}

.pl-stat-card {
  background: #fff;
  border: 1px solid #dfe1e6;
  border-radius: 10px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.15s ease;
}

.pl-stat-card:hover {
  box-shadow: 0 4px 8px rgba(23,43,77,.12);
  transform: translateY(-2px);
}

.pl-stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.pl-stat-icon.blue { background: #deebff; color: #0c66e4; }
.pl-stat-icon.green { background: #e3fcef; color: #00875a; }
.pl-stat-icon.orange { background: #fffae6; color: #ff991f; }
.pl-stat-icon.grey { background: #f8f9fa; color: #6b778c; }

.pl-stat-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.pl-stat-number {
  font-size: 22px;
  font-weight: 700;
  color: #172b4d;
  line-height: 1.1;
}

.pl-stat-label {
  font-size: 12px;
  font-weight: 500;
  color: #6b778c;
  text-transform: uppercase;
  letter-spacing: .4px;
}

.pl-filters {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.pl-search-box {
  position: relative;
  flex: 1;
  min-width: 240px;
  max-width: 360px;
}

.pl-search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b778c;
  font-size: 13px;
}

.pl-search-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border: 1px solid #dfe1e6;
  border-radius: 8px;
  font-size: 13.5px;
  color: #172b4d;
  background: #fff;
  outline: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.pl-search-input:focus {
  border-color: #0c66e4;
  box-shadow: 0 0 0 2px rgba(12,102,228,.25);
}

.pl-filter-pills {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.pl-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 9999px;
  font-size: 13px;
  font-weight: 500;
  color: #6b778c;
  background: #fff;
  border: 1px solid #dfe1e6;
  cursor: pointer;
  transition: all 0.15s ease;
}

.pl-pill:hover {
  color: #172b4d;
  border-color: #0c66e4;
  background: #deebff;
}

.pl-pill.active {
  background: #0c66e4;
  color: #fff;
  border-color: #0c66e4;
  box-shadow: 0 2px 6px rgba(12,102,228,.25);
}

.pl-pill i { font-size: 12px; }

.pl-table-wrap {
  background: #fff;
  border: 1px solid #dfe1e6;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(23,43,77,.08);
}

.pl-table {
  width: 100%;
  border-collapse: collapse;
}

.pl-table thead {
  background: #f4f5f7;
  border-bottom: 1px solid #dfe1e6;
}

.pl-table th {
  padding: 12px 20px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: #6b778c;
}

.pl-table tbody tr {
  border-bottom: 1px solid #dfe1e6;
  transition: background 0.15s ease;
}

.pl-table tbody tr:last-child {
  border-bottom: none;
}

.pl-table tbody tr:hover {
  background: #f8f9fa;
}

.pl-table td {
  padding: 16px 20px;
  font-size: 13.5px;
  color: #172b4d;
  vertical-align: middle;
}

.pl-project-name {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pl-project-link {
  font-size: 14px;
  font-weight: 600;
  color: #0c66e4;
  text-decoration: none;
  transition: color 0.15s ease;
}

.pl-project-link:hover {
  color: #0052cc;
  text-decoration: underline;
}

.pl-project-meta {
  font-size: 12px;
  color: #6b778c;
}

.pl-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 9999px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .4px;
}

.pl-status-badge::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.pl-status-active {
  background: #e3fcef;
  color: #00875a;
}
.pl-status-active::before { background: #00875a; }

.pl-status-archived {
  background: #f8f9fa;
  color: #6b778c;
}
.pl-status-archived::before { background: #6b778c; }

.pl-status-completed {
  background: #deebff;
  color: #0065ff;
}
.pl-status-completed::before { background: #0065ff; }

.pl-task-count {
  font-size: 13px;
  color: #6b778c;
}

.pl-task-count i {
  width: 14px;
  color: #91a0b6;
}

.pl-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: flex-end;
}

.pl-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12.5px;
  font-weight: 500;
  text-decoration: none;
  border: 1px solid transparent;
  transition: all 0.15s ease;
  cursor: pointer;
}

.pl-btn-ghost {
  color: #6b778c;
  border-color: #dfe1e6;
  background: #fff;
}

.pl-btn-ghost:hover {
  color: #172b4d;
  border-color: #0c66e4;
  background: #deebff;
}

.pl-btn-primary {
  color: #fff;
  background: #0c66e4;
  border-color: #0c66e4;
}

.pl-btn-primary:hover {
  background: #0052cc;
  border-color: #0052cc;
  box-shadow: 0 2px 6px rgba(12,102,228,.25);
}

.pl-btn-danger {
  color: #fff;
  background: #dc2626;
  border-color: #dc2626;
}

.pl-btn-danger:hover {
  background: #b91c1c;
  border-color: #b91c1c;
  box-shadow: 0 2px 6px rgba(220,38,38,.25);
}

.pl-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.pl-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  text-align: center;
}

.pl-empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #f4f5f7;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.pl-empty-icon i {
  font-size: 36px;
  color: #91a0b6;
}

.pl-empty h3 {
  font-size: 18px;
  font-weight: 600;
  color: #172b4d;
  margin: 0 0 8px;
}

.pl-empty p {
  font-size: 13.5px;
  color: #6b778c;
  max-width: 400px;
  margin: 0 0 24px;
  line-height: 1.6;
}

.pl-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(15, 23, 42, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.pl-modal {
  background: #fff;
  border-radius: 12px;
  width: min(460px, 100%);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.pl-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #dfe1e6;
}

.pl-modal-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #172b4d;
}

.pl-modal-close {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  color: #6b778c;
  font-size: 22px;
  cursor: pointer;
  border-radius: 6px;
}

.pl-modal-close:hover {
  background: #f4f5f7;
  color: #172b4d;
}

.pl-modal-body {
  padding: 20px;
}

.pl-modal-body p {
  margin: 0 0 12px;
  font-size: 14px;
  color: #334155;
  line-height: 1.5;
}

.pl-modal-warning {
  color: #dc2626 !important;
  font-size: 13px !important;
}

.pl-modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid #dfe1e6;
  background: #fafbfc;
}

@media (max-width: 768px) {
  .pl-stats {
    grid-template-columns: repeat(2, 1fr);
  }
  .pl-header {
    flex-direction: column;
  }
  .pl-filters {
    flex-direction: column;
    align-items: stretch;
  }
  .pl-search-box {
    max-width: none;
  }
  .pl-table-wrap {
    overflow-x: auto;
  }
  .pl-actions {
    flex-wrap: wrap;
  }
}
</style>
