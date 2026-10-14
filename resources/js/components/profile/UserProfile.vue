<template>
  <div class="user-profile-container">
    <!-- Header Hero Card -->
    <div class="profile-hero-card">
      <div class="hero-left">
        <div class="user-avatar-large">
          <span>{{ userInitials }}</span>
        </div>
        <div class="user-info-hero">
          <div class="user-badge-row">
            <span class="badge-role"><i class="fa fa-shield me-1"></i> Thành viên Workspace</span>
            <span class="badge-status"><i class="fa fa-check-circle me-1"></i> Đang hoạt động</span>
          </div>
          <h1 class="user-fullname">{{ user.name }}</h1>
          <p class="user-email-text"><i class="fa fa-envelope-o me-1"></i> {{ user.email }}</p>
        </div>
      </div>
      <div class="hero-stats-grid">
        <div class="hero-stat-card">
          <div class="stat-num text-primary">{{ stats.assigned_tasks || 0 }}</div>
          <div class="stat-lbl">Task được giao</div>
        </div>
        <div class="hero-stat-card">
          <div class="stat-num text-success">{{ stats.completed_tasks || 0 }}</div>
          <div class="stat-lbl">Đã hoàn thành</div>
        </div>
        <div class="hero-stat-card">
          <div class="stat-num text-warning">{{ stats.in_progress_tasks || 0 }}</div>
          <div class="stat-lbl">Đang thực hiện</div>
        </div>
        <div class="hero-stat-card">
          <div class="stat-num text-purple">{{ userSkills.length }}</div>
          <div class="stat-lbl">Kỹ năng AI</div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="profile-tabs-bar">
      <button
        type="button"
        class="tab-btn"
        :class="{ active: activeTab === 'info' }"
        @click="activeTab = 'info'"
      >
        <i class="fa fa-user me-2"></i> Thông tin cá nhân
      </button>
      <button
        type="button"
        class="tab-btn"
        :class="{ active: activeTab === 'skills' }"
        @click="activeTab = 'skills'"
      >
        <i class="fa fa-magic me-2"></i> Hồ sơ kỹ năng AI
        <span class="badge-skill-count">{{ userSkills.length }}</span>
      </button>
    </div>

    <!-- TAB 1: PERSONAL INFORMATION -->
    <div v-if="activeTab === 'info'" class="tab-content-panel fade-in">
      <div class="info-grid">
        <!-- Personal Details Card -->
        <div class="card-surface info-card">
          <div class="surface-header">
            <h3><i class="fa fa-id-card text-primary me-2"></i> Chi tiết tài khoản</h3>
          </div>
          <div class="surface-body">
            <div class="info-row">
              <div class="info-label"><i class="fa fa-user-o me-2 text-muted"></i> Họ và tên:</div>
              <div class="info-value font-weight-bold">{{ user.name }}</div>
            </div>
            <div class="info-row">
              <div class="info-label"><i class="fa fa-envelope-o me-2 text-muted"></i> Địa chỉ Email:</div>
              <div class="info-value">{{ user.email }}</div>
            </div>
            <div class="info-row">
              <div class="info-label"><i class="fa fa-calendar me-2 text-muted"></i> Ngày khởi tạo:</div>
              <div class="info-value">{{ formatDate(user.created_at) }}</div>
            </div>
            <div class="info-row">
              <div class="info-label"><i class="fa fa-lock me-2 text-muted"></i> Trạng thái xác thực:</div>
              <div class="info-value text-success font-weight-bold">
                <i class="fa fa-check-circle me-1"></i> Đã xác thực bảo mật
              </div>
            </div>
          </div>
        </div>

        <!-- Activity Overview Card -->
        <div class="card-surface info-card">
          <div class="surface-header">
            <h3><i class="fa fa-line-chart text-indigo me-2"></i> Tổng quan đóng góp</h3>
          </div>
          <div class="surface-body">
            <div class="activity-metric-item">
              <div class="d-flex justify-content-between mb-1">
                <span class="font-size-13 font-weight-bold text-dark">Tỷ lệ hoàn thành công việc</span>
                <span class="font-size-13 font-weight-bold text-success">{{ completionRate }}%</span>
              </div>
              <div class="metric-progress-bar">
                <div class="metric-fill bg-success" :style="{ width: completionRate + '%' }"></div>
              </div>
            </div>

            <div class="metric-summary-list mt-4">
              <div class="metric-row">
                <span><i class="fa fa-tasks me-2 text-primary"></i> Tổng số Task được giao</span>
                <strong>{{ stats.assigned_tasks || 0 }} tasks</strong>
              </div>
              <div class="metric-row">
                <span><i class="fa fa-check-circle me-2 text-success"></i> Số Task hoàn thành</span>
                <strong>{{ stats.completed_tasks || 0 }} tasks</strong>
              </div>
              <div class="metric-row">
                <span><i class="fa fa-spinner me-2 text-warning"></i> Task đang xử lý</span>
                <strong>{{ stats.in_progress_tasks || 0 }} tasks</strong>
              </div>
              <div class="metric-row">
                <span><i class="fa fa-sliders me-2 text-purple"></i> Hồ sơ kỹ năng AI</span>
                <strong>{{ userSkills.length }} kỹ năng đăng ký</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: SKILL PROFILE -->
    <div v-else-if="activeTab === 'skills'" class="tab-content-panel fade-in">
      <div class="profile-layout">
        <!-- Left Column: Add & Manage Skills -->
        <div class="skills-main">
          <!-- Add Skill Form Card -->
          <div class="card-surface">
            <div class="surface-header">
              <h3><i class="fa fa-plus-circle text-indigo"></i> Cập nhật Kỹ năng mới</h3>
            </div>
            
            <div class="surface-body">
              <!-- Preset Badges -->
              <div class="preset-section">
                <label class="section-label">Gợi ý kỹ năng phổ biến (Click để thêm nhanh):</label>
                <div class="preset-tags">
                  <button
                    v-for="preset in presetSkills"
                    :key="preset"
                    type="button"
                    class="preset-tag"
                    :class="{ active: newSkill.name.toLowerCase() === preset.toLowerCase() }"
                    @click="selectPreset(preset)"
                  >
                    <i class="fa fa-plus text-xs"></i> {{ preset }}
                  </button>
                </div>
              </div>

              <!-- Form inputs -->
              <form @submit.prevent="saveSkill" class="skill-form">
                <div class="form-grid">
                  <div class="form-group flex-2">
                    <label class="form-label">Tên kỹ năng / Công nghệ <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      v-model="newSkill.name"
                      class="form-control"
                      placeholder="VD: PHP, Vue.js, Python, UI/UX, Docker..."
                      required
                    />
                  </div>

                  <div class="form-group flex-1">
                    <label class="form-label">Mức độ thành thạo</label>
                    <select v-model.number="newSkill.level" class="form-control">
                      <option :value="1">1 ★ - Sơ cấp (Beginner)</option>
                      <option :value="2">2 ★★ - Cơ bản (Elementary)</option>
                      <option :value="3">3 ★★★ - Khá (Intermediate)</option>
                      <option :value="4">4 ★★★★ - Giỏi (Advanced)</option>
                      <option :value="5">5 ★★★★★ - Chuyên gia (Expert)</option>
                    </select>
                  </div>

                  <div class="form-group flex-1">
                    <label class="form-label">Số năm kinh nghiệm</label>
                    <input
                      type="number"
                      v-model.number="newSkill.years"
                      class="form-control"
                      min="0"
                      max="50"
                      placeholder="VD: 3 năm"
                    />
                  </div>
                </div>

                <div class="form-actions">
                  <button type="submit" class="btn-save" :disabled="saving">
                    <i v-if="saving" class="fa fa-spinner fa-spin"></i>
                    <i v-else class="fa fa-check"></i>
                    {{ editingId ? 'Cập nhật kỹ năng' : 'Lưu kỹ năng vào Hồ sơ AI' }}
                  </button>
                  <button v-if="editingId" type="button" class="btn-cancel" @click="resetForm">
                    Hủy
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Skills List Card -->
          <div class="card-surface mt-4">
            <div class="surface-header flex-between">
              <h3><i class="fa fa-list-ul text-blue"></i> Danh sách Kỹ năng đã đăng ký ({{ userSkills.length }})</h3>
              <span v-if="userSkills.length > 0" class="badge-count">AI đã sẵn sàng phân tích</span>
            </div>

            <div class="surface-body p-0">
              <div v-if="loading" class="text-center py-5">
                <i class="fa fa-circle-o-notch fa-spin text-purple fa-2x"></i>
                <p class="text-muted mt-2">Đang tải hồ sơ kỹ năng...</p>
              </div>

              <div v-else-if="userSkills.length === 0" class="empty-state">
                <div class="empty-icon"><i class="fa fa-sliders text-muted"></i></div>
                <h4>Chưa có kỹ năng nào được đăng ký</h4>
                <p class="text-muted">
                  Hãy chọn hoặc nhập các kỹ năng của bạn ở trên để AI có thể gợi ý các bài toán phù hợp nhất!
                </p>
              </div>

              <div v-else class="skills-table-wrapper">
                <table class="skills-table">
                  <thead>
                    <tr>
                      <th>Tên Kỹ năng</th>
                      <th>Mức độ thành thạo</th>
                      <th>Kinh nghiệm</th>
                      <th>Tương thích AI</th>
                      <th class="text-right">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="skill in userSkills" :key="skill.id">
                      <td>
                        <strong class="skill-name-text">{{ skill.skill_name }}</strong>
                      </td>
                      <td>
                        <div class="star-rating">
                          <span 
                            v-for="star in 5" 
                            :key="star" 
                            class="star-icon"
                            :class="{ filled: star <= skill.proficiency_level }"
                          >★</span>
                          <span class="level-label">({{ levelText(skill.proficiency_level) }})</span>
                        </div>
                      </td>
                      <td>
                        <span class="exp-badge">
                          <i class="fa fa-clock-o"></i> {{ skill.years_experience || 1 }} năm
                        </span>
                      </td>
                      <td>
                        <span class="ai-status-tag">
                          <i class="fa fa-check-circle text-emerald"></i> Active
                        </span>
                      </td>
                      <td class="text-right">
                        <button 
                          type="button" 
                          class="btn-action edit" 
                          @click="editSkill(skill)"
                          title="Chỉnh sửa"
                        >
                          <i class="fa fa-pencil"></i>
                        </button>
                        <button 
                          type="button" 
                          class="btn-action delete" 
                          @click="deleteSkill(skill.id)"
                          title="Xóa"
                        >
                          <i class="fa fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: AI Insights Preview -->
        <div class="skills-sidebar">
          <div class="card-surface ai-preview-card">
            <div class="surface-header">
              <h3><i class="fa fa-microchip text-purple"></i> Xem Trước Đánh Giá AI</h3>
            </div>
            
            <div class="surface-body">
              <div class="ai-explain-box">
                <i class="fa fa-info-circle text-indigo"></i>
                <span>
                  Khi quản lý tạo công việc mới, AI sẽ quét tiêu đề, mô tả và thành phần dự án để so khớp với <strong>Kỹ năng</strong> của bạn.
                </span>
              </div>

              <h4 class="sidebar-subheading">Công việc chưa giao trong dự án:</h4>

              <div v-if="matchingTasks.length === 0" class="empty-tasks">
                <p class="text-muted">Không có công việc nào đang chờ phân công.</p>
              </div>

              <div v-else class="preview-tasks-list">
                <div v-for="task in matchingTasks" :key="task.id" class="preview-task-item">
                  <div class="task-info">
                    <a :href="`/tasks/${task.id}`" class="task-title">{{ task.name }}</a>
                    <span class="task-project">{{ task.project ? task.project.name : 'Dự án' }}</span>
                  </div>
                  <div class="task-match-score">
                    <span class="score-badge" :class="scoreClass(task)">
                      <i class="fa fa-bolt"></i> {{ calculateScore(task) }}%
                    </span>
                  </div>
                </div>
              </div>

              <div class="surface-footer">
                <div class="ai-footer-tip">
                  💡 <em>Kỹ năng được cập nhật ngay lập tức vào mô hình Machine Learning / Heuristics Recommender.</em>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  user: { type: Object, required: true },
  stats: { type: Object, default: () => ({}) },
  initialSkills: { type: Array, default: () => [] },
  matchingTasks: { type: Array, default: () => [] },
});

const activeTab = ref(window.location.hash === '#skills' ? 'skills' : 'info');

const userSkills = ref([...props.initialSkills]);
const matchingTasks = ref([...props.matchingTasks]);

const presetSkills = ref([
  'PHP', 'Laravel', 'Vue.js', 'JavaScript', 'TypeScript', 'HTML/CSS',
  'React', 'Node.js', 'Python', 'MySQL', 'PostgreSQL', 'Docker',
  'REST API', 'DevOps', 'UI/UX Design', 'QA Testing', 'System Architecture',
  'Agile/Scrum', 'Git', 'CI/CD', 'Security', 'Data Analysis'
]);

const newSkill = ref({
  name: '',
  level: 3,
  years: 1,
});

const editingId = ref(null);
const loading = ref(false);
const saving = ref(false);

const userInitials = computed(() => {
  if (!props.user || !props.user.name) return 'U';
  const parts = props.user.name.trim().split(' ');
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
});

const completionRate = computed(() => {
  const total = props.stats.assigned_tasks || 0;
  if (!total) return 0;
  const completed = props.stats.completed_tasks || 0;
  return Math.round((completed / total) * 100);
});

const levelNames = {
  1: 'Sơ cấp',
  2: 'Cơ bản',
  3: 'Khá',
  4: 'Giỏi',
  5: 'Chuyên gia',
};

function levelText(level) {
  return levelNames[level] || 'Khá';
}

function formatDate(dateString) {
  if (!dateString) return 'Chưa rõ';
  const d = new Date(dateString);
  if (isNaN(d.getTime())) return dateString;
  return d.toLocaleDateString('vi-VN');
}

function selectPreset(preset) {
  newSkill.value.name = preset;
}

function resetForm() {
  newSkill.value = { name: '', level: 3, years: 1 };
  editingId.value = null;
}

function editSkill(skill) {
  editingId.value = skill.id;
  newSkill.value = {
    name: skill.skill_name,
    level: skill.proficiency_level || 3,
    years: skill.years_experience || 1,
  };
}

async function fetchSkills() {
  try {
    const res = await fetch('/api/skills');
    const data = await res.json();
    if (data.success) {
      userSkills.value = data.skills || [];
      if (data.presetSkills) {
        presetSkills.value = data.presetSkills;
      }
    }
  } catch (err) {
    console.error('Error fetching skills:', err);
  }
}

async function saveSkill() {
  if (!newSkill.value.name.trim()) return;

  saving.value = true;
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch('/api/skills', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        skill_name: newSkill.value.name.trim(),
        proficiency_level: newSkill.value.level,
        years_experience: newSkill.value.years,
      }),
    });

    const data = await res.json();
    if (data.success) {
      resetForm();
      await fetchSkills();
    } else {
      alert(data.message || 'Có lỗi xảy ra khi lưu kỹ năng.');
    }
  } catch (err) {
    console.error('Error saving skill:', err);
  } finally {
    saving.value = false;
  }
}

async function deleteSkill(id) {
  if (!confirm('Bạn có chắc chắn muốn xóa kỹ năng này khỏi hồ sơ AI?')) return;

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch(`/api/skills/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      },
    });
    const data = await res.json();
    if (data.success) {
      await fetchSkills();
    }
  } catch (err) {
    console.error('Error deleting skill:', err);
  }
}

function calculateScore(task) {
  const userSkillNames = userSkills.value.map(s => s.skill_name.toLowerCase());
  if (userSkillNames.length === 0) return 45;

  const taskText = (task.name + ' ' + (task.description || '')).toLowerCase();
  let matches = 0;
  userSkillNames.forEach(s => {
    if (taskText.includes(s)) matches += 1;
  });

  const bonus = matches * 15 + Math.min(userSkillNames.length * 4, 20);
  return Math.min(65 + bonus, 98);
}

function scoreClass(task) {
  const score = calculateScore(task);
  if (score >= 85) return 'score-high';
  if (score >= 70) return 'score-mid';
  return 'score-normal';
}

onMounted(() => {
  if (!userSkills.value.length) {
    fetchSkills();
  }
});
</script>

<style scoped>
.user-profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px 40px;
}

.profile-hero-card {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  border-radius: 16px;
  padding: 32px 28px;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.25);
  margin-bottom: 24px;
}

.hero-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-avatar-large {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: 800;
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(79, 70, 229, 0.4);
  border: 3px solid rgba(255, 255, 255, 0.2);
}

.user-badge-row {
  display: flex;
  gap: 8px;
  margin-bottom: 6px;
}

.badge-role {
  background: rgba(99, 102, 241, 0.25);
  color: #a5b4fc;
  border: 1px solid rgba(99, 102, 241, 0.4);
  font-size: 11px;
  font-weight: 600;
  padding: 2px 10px;
  border-radius: 20px;
}

.badge-status {
  background: rgba(16, 185, 129, 0.2);
  color: #6ee7b7;
  border: 1px solid rgba(16, 185, 129, 0.4);
  font-size: 11px;
  font-weight: 600;
  padding: 2px 10px;
  border-radius: 20px;
}

.user-fullname {
  font-size: 26px;
  font-weight: 800;
  margin: 0 0 4px 0;
  letter-spacing: -0.3px;
}

.user-email-text {
  color: #94a3b8;
  font-size: 13.5px;
  margin: 0;
}

.hero-stats-grid {
  display: flex;
  gap: 16px;
}

.hero-stat-card {
  background: rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 14px 20px;
  text-align: center;
  min-width: 110px;
}

.stat-num {
  font-size: 22px;
  font-weight: 800;
  line-height: 1.2;
}

.stat-lbl {
  font-size: 11px;
  color: #cbd5e1;
  margin-top: 2px;
}

.profile-tabs-bar {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 24px;
}

.tab-btn {
  background: transparent;
  border: none;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
}

.tab-btn:hover {
  color: #1e293b;
}

.tab-btn.active {
  color: #4f46e5;
  border-bottom-color: #4f46e5;
}

.badge-skill-count {
  background: #e0e7ff;
  color: #4338ca;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  margin-left: 8px;
}

/* Info Tab */
.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.info-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px dashed #f1f5f9;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: #64748b;
  font-size: 13.5px;
}

.info-value {
  color: #0f172a;
  font-size: 14px;
}

.metric-progress-bar {
  height: 10px;
  background: #f1f5f9;
  border-radius: 6px;
  overflow: hidden;
}

.metric-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.4s ease;
}

.metric-summary-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.metric-row {
  display: flex;
  justify-content: space-between;
  font-size: 13.5px;
  color: #334155;
}

/* Skills Tab Layout */
.profile-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 24px;
}

.card-surface {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.surface-header {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  background: #f8fafc;
}

.surface-header h3 {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.surface-body {
  padding: 20px;
}

.preset-section {
  margin-bottom: 20px;
}

.section-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 8px;
  display: block;
}

.preset-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.preset-tag {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #475569;
  border-radius: 16px;
  padding: 4px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.15s;
}

.preset-tag:hover, .preset-tag.active {
  background: #4f46e5;
  border-color: #4f46e5;
  color: #ffffff;
}

.form-grid {
  display: flex;
  gap: 12px;
}

.flex-2 { flex: 2; }
.flex-1 { flex: 1; }

.form-actions {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.btn-save {
  background: #4f46e5;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  transition: background 0.15s;
}

.btn-save:hover { background: #4338ca; }

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
}

.skills-table {
  width: 100%;
  border-collapse: collapse;
}

.skills-table th {
  background: #f8fafc;
  padding: 12px 16px;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.skills-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13.5px;
}

.star-rating {
  color: #f59e0b;
  font-size: 14px;
}

.star-icon {
  color: #cbd5e1;
}

.star-icon.filled {
  color: #f59e0b;
}

.level-label {
  color: #64748b;
  font-size: 12px;
  margin-left: 6px;
}

.exp-badge {
  background: #f1f5f9;
  color: #475569;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 12px;
}

.ai-status-tag {
  color: #10b981;
  font-weight: 600;
  font-size: 12px;
}

.btn-action {
  background: transparent;
  border: none;
  padding: 4px 8px;
  cursor: pointer;
  color: #94a3b8;
  transition: color 0.15s;
}

.btn-action.edit:hover { color: #3b82f6; }
.btn-action.delete:hover { color: #ef4444; }

/* AI Preview Sidebar */
.ai-explain-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 12px;
  font-size: 12.5px;
  color: #1e40af;
  margin-bottom: 16px;
}

.sidebar-subheading {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 12px;
}

.preview-tasks-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.preview-task-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 12px;
}

.task-title {
  font-weight: 600;
  color: #0f172a;
  font-size: 13px;
  display: block;
  text-decoration: none;
}

.task-project {
  font-size: 11px;
  color: #64748b;
}

.score-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 12px;
}

.score-high { background: #dcfce7; color: #15803d; }
.score-mid { background: #dbeafe; color: #1d4ed8; }
.score-normal { background: #f1f5f9; color: #475569; }

@media (max-width: 900px) {
  .profile-hero-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .hero-stats-grid {
    width: 100%;
    justify-content: space-between;
  }
  .profile-layout, .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>
