<template>
  <div class="home-dashboard-container">
    <!-- Hero Header -->
    <div class="hero-card">
      <div class="hero-content">
        <div class="welcome-badge">
          <i class="fa fa-sparkles text-amber"></i> {{ greetingTime }}
        </div>
        <h1 class="hero-title">Chào mừng trở lại, {{ userName }}! 👋</h1>
        <p class="hero-subtitle">
          Tổng quan không gian làm việc, tiến độ dự án và danh sách công việc được tối ưu hóa bởi AI.
        </p>
      </div>

      <div class="hero-actions">
        <a :href="routes.tasksCreate || '/tasks/create'" class="btn-hero btn-hero-primary">
          <i class="fa fa-plus-circle"></i> Tạo công việc
        </a>
        <a :href="routes.projectsCreate || '/projects/create'" class="btn-hero btn-hero-secondary">
          <i class="fa fa-folder-plus"></i> Dự án mới
        </a>
        <a :href="routes.skillsIndex || '/skills'" class="btn-hero btn-hero-ai">
          <i class="fa fa-magic"></i> Hồ sơ kỹ năng AI
        </a>
      </div>
    </div>

    <!-- Stat Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon icon-projects">
          <i class="fa fa-folder-open"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ totalProjects }}</span>
          <span class="stat-label">Dự án hoạt động</span>
        </div>
        <a :href="routes.projectsIndex || '/projects'" class="stat-link" title="Xem dự án">
          <i class="fa fa-arrow-right"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-icon icon-tasks">
          <i class="fa fa-tasks"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ totalTasks }}</span>
          <span class="stat-label">Công việc trên hệ thống</span>
        </div>
        <a :href="routes.tasksIndex || '/tasks'" class="stat-link" title="Xem công việc">
          <i class="fa fa-arrow-right"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-icon icon-user-tasks">
          <i class="fa fa-user-check"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ userTasksCount }}</span>
          <span class="stat-label">Giao cho bạn</span>
        </div>
        <a :href="routes.tasksIndex || '/tasks'" class="stat-link" title="Công việc của tôi">
          <i class="fa fa-arrow-right"></i>
        </a>
      </div>

      <div class="stat-card">
        <div class="stat-icon icon-companies">
          <i class="fa fa-building"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ totalCompanies }}</span>
          <span class="stat-label">Khách hàng / Đối tác</span>
        </div>
        <a :href="routes.companiesIndex || '/companies'" class="stat-link" title="Xem khách hàng">
          <i class="fa fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Main Content Area: Split 2 Columns -->
    <div class="dashboard-body">
      <!-- Left Column: Quick Launch & Workflow Features -->
      <div class="dashboard-main">
        <!-- Quick Launch Cards -->
        <div class="content-card">
          <div class="card-header">
            <h3><i class="fa fa-bolt text-indigo"></i> Khởi động nhanh</h3>
            <span class="badge-tag">Lối tắt chính</span>
          </div>

          <div class="quick-shortcuts-grid">
            <a :href="routes.projectsIndex || '/projects'" class="shortcut-item">
              <div class="shortcut-icon bg-indigo-subtle text-indigo">
                <i class="fa fa-cubes"></i>
              </div>
              <div class="shortcut-text">
                <strong>Danh sách Dự án</strong>
                <p>Quản lý và điều phối các dự án</p>
              </div>
            </a>

            <a :href="routes.tasksIndex || '/tasks'" class="shortcut-item">
              <div class="shortcut-icon bg-blue-subtle text-blue">
                <i class="fa fa-th-list"></i>
              </div>
              <div class="shortcut-text">
                <strong>Bảng Kanban / Tasks</strong>
                <p>Theo dõi luồng xử lý công việc</p>
              </div>
            </a>

            <a :href="routes.teamsIndex || '/teams'" class="shortcut-item">
              <div class="shortcut-icon bg-emerald-subtle text-emerald">
                <i class="fa fa-users"></i>
              </div>
              <div class="shortcut-text">
                <strong>Đội nhóm & Thành viên</strong>
                <p>Phân quyền và kết nối nhân sự</p>
              </div>
            </a>

            <a :href="routes.skillsIndex || '/skills'" class="shortcut-item">
              <div class="shortcut-icon bg-purple-subtle text-purple">
                <i class="fa fa-sliders"></i>
              </div>
              <div class="shortcut-text">
                <strong>Hồ sơ Kỹ năng AI</strong>
                <p>Cập nhật kỹ năng để AI gợi ý bài toán chuẩn xác</p>
              </div>
            </a>

            <a :href="routes.companiesIndex || '/companies'" class="shortcut-item">
              <div class="shortcut-icon bg-amber-subtle text-amber-icon">
                <i class="fa fa-building"></i>
              </div>
              <div class="shortcut-text">
                <strong>Khách hàng & Đối tác</strong>
                <p>Quản lý thông tin doanh nghiệp hợp tác</p>
              </div>
            </a>

            <a :href="routes.guide || '/huong-dan'" class="shortcut-item">
              <div class="shortcut-icon bg-rose-subtle text-rose">
                <i class="fa fa-book"></i>
              </div>
              <div class="shortcut-text">
                <strong>Hướng dẫn & Quy trình</strong>
                <p>Tra cứu hướng dẫn sử dụng hệ thống</p>
              </div>
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column: AI Assistant Widget -->
      <div class="dashboard-sidebar">
        <div class="content-card ai-card">
          <div class="card-header border-bottom-0 pb-0">
            <div class="ai-title-wrap">
              <span class="ai-sparkle-badge"><i class="fa fa-magic"></i></span>
              <div>
                <h3 class="m-0">AI Gợi Ý Hôm Nay</h3>
                <p class="text-muted text-xs mb-0">Hệ thống khớp nối công việc thông minh</p>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div v-if="tasksList.length === 0" class="empty-ai-state">
              <div class="empty-icon"><i class="fa fa-check-circle text-emerald"></i></div>
              <h4>Tất cả công việc đã có người đảm nhận!</h4>
              <p class="text-muted">
                Không còn công việc tồn đọng cần phân công. Hãy tiếp tục duy trì tiến độ mượt mà.
              </p>
              <a :href="routes.projectsIndex || '/projects'" class="btn-sm-link">
                Xem danh sách dự án <i class="fa fa-chevron-right"></i>
              </a>
            </div>

            <div v-else class="ai-tasks-list">
              <p class="ai-intro-text">
                Các công việc chưa được giao. Bấm nút AI bên dưới để nhận đề xuất nhân sự phù hợp dựa trên <strong>Hồ sơ Kỹ năng</strong>.
              </p>

              <div v-for="task in tasksList" :key="task.id" class="ai-task-item">
                <div class="ai-task-info">
                  <a :href="`/tasks/${task.id}`" class="ai-task-title">
                    {{ task.name }}
                  </a>
                  <div class="ai-task-meta">
                    <span class="project-tag">{{ task.project ? task.project.name : 'Dự án' }}</span>
                    <span class="priority-badge" :class="`priority-${(task.priority || 'medium').toLowerCase()}`">
                      {{ (task.priority || 'medium').toUpperCase() }}
                    </span>
                  </div>
                </div>

                <button 
                  type="button" 
                  class="btn-ai-recommend" 
                  :data-ai-recommend-task-id="task.id"
                  @click="triggerAiModal(task.id)"
                >
                  <i class="fa fa-magic"></i> Đề xuất AI
                </button>
              </div>
            </div>
          </div>

          <div class="card-footer bg-purple-subtle-light">
            <div class="skills-prompt">
              <i class="fa fa-lightbulb text-purple"></i>
              <span>Cập nhật <strong>Hồ sơ Kỹ năng</strong> để AI gợi ý chính xác hơn cho bạn!</span>
            </div>
            <a :href="routes.skillsIndex || '/skills'" class="btn-skills-prompt">
              Cập nhật ngay
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  userName: { type: String, default: 'Thành viên' },
  totalProjects: { type: Number, default: 0 },
  totalTasks: { type: Number, default: 0 },
  totalCompanies: { type: Number, default: 0 },
  userTasksCount: { type: Number, default: 0 },
  unassignedTasks: { type: Array, default: () => [] },
  routes: { type: Object, default: () => ({}) }
});

const tasksList = computed(() => (props.unassignedTasks || []).slice(0, 3));

const greetingTime = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Buổi sáng năng động';
  if (hour < 18) return 'Buổi chiều hiệu quả';
  return 'Buổi tối tốt lành';
});

function triggerAiModal(taskId) {
  // If globally defined ai-assignee widget exists, invoke it
  if (window.TaskPilotAI && typeof window.TaskPilotAI.openModal === 'function') {
    window.TaskPilotAI.openModal(taskId);
  } else {
    // Dispatch custom event for vanilla JS handler if present
    const btn = document.querySelector(`[data-ai-recommend-task-id="${taskId}"]`);
    if (btn) btn.click();
  }
}
</script>
