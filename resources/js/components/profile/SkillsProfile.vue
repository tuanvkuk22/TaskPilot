<template>
  <div class="skills-profile-container">
    <!-- Header Card -->
    <div class="header-card">
      <div class="header-content">
        <div class="ai-badge">
          <i class="fa fa-magic text-purple"></i> THUẬT TOÁN KHỚP NỐI AI (AI ASSIGNEE RECOMMENDER)
        </div>
        <h1>Hồ Sơ Kỹ Năng Cá Nhân</h1>
        <p>
          Mô hình AI của TaskPilot sử dụng danh sách kỹ năng, mức độ thành thạo và số năm kinh nghiệm của bạn để tự động gợi ý và đề xuất bạn vào các dự án/công việc phù hợp nhất.
        </p>
      </div>
      <div class="header-stat">
        <div class="stat-circle">
          <span class="circle-number">{{ userSkills.length }}</span>
          <span class="circle-label">Kỹ năng</span>
        </div>
      </div>
    </div>

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
                      <button type="button" class="btn-action edit" @click="editSkill(skill)" title="Chỉnh sửa">
                        <i class="fa fa-pencil"></i>
                      </button>
                      <button type="button" class="btn-action delete" @click="deleteSkill(skill.id)" title="Xóa">
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

      <!-- Right Column: AI Matching Preview Card -->
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

            <div v-if="tasks.length === 0" class="empty-tasks">
              <p class="text-muted">Không có công việc nào đang chờ phân công.</p>
            </div>

            <div v-else class="preview-tasks-list">
              <div v-for="task in tasks" :key="task.id" class="preview-task-item">
                <div class="task-info">
                  <a :href="`/tasks/${task.id}`" class="task-title">{{ task.name }}</a>
                  <span class="task-project">{{ task.project ? task.project.name : 'Dự án' }}</span>
                </div>
                <div class="task-match-score">
                  <span class="score-badge" :class="getScoreClass(task)">
                    <i class="fa fa-bolt"></i> {{ getMatchScore(task) }}%
                  </span>
                </div>
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
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  initialSkills: { type: Array, default: () => [] },
  matchingTasks: { type: Array, default: () => [] }
});

const userSkills = ref([...props.initialSkills]);
const tasks = ref([...props.matchingTasks]);
const presetSkills = ref([
  'PHP', 'Laravel', 'Vue.js', 'JavaScript', 'TypeScript', 'HTML/CSS',
  'React', 'Python', 'MySQL', 'Docker', 'REST API', 'DevOps', 'UI/UX Design', 'QA Testing'
]);

const newSkill = ref({
  name: '',
  level: 3,
  years: 1
});

const editingId = ref(null);
const loading = ref(false);
const saving = ref(false);

const levelTextMap = {
  1: 'Sơ cấp',
  2: 'Cơ bản',
  3: 'Khá',
  4: 'Giỏi',
  5: 'Chuyên gia'
};

function levelText(lvl) {
  return levelTextMap[lvl] || 'Khá';
}

function selectPreset(presetName) {
  newSkill.value.name = presetName;
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
    years: skill.years_experience || 1
  };
}

async function fetchSkills() {
  try {
    const res = await fetch('/api/skills');
    const data = await res.json();
    if (data.success) {
      userSkills.value = data.skills || [];
      if (data.presetSkills) presetSkills.value = data.presetSkills;
    }
  } catch (e) {
    console.error('Error fetching skills:', e);
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
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        skill_name: newSkill.value.name.trim(),
        proficiency_level: newSkill.value.level,
        years_experience: newSkill.value.years
      })
    });
    const data = await res.json();
    if (data.success) {
      resetForm();
      await fetchSkills();
    } else {
      alert(data.message || 'Có lỗi xảy ra khi lưu kỹ năng.');
    }
  } catch (e) {
    console.error('Error saving skill:', e);
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
        'Accept': 'application/json'
      }
    });
    const data = await res.json();
    if (data.success) {
      await fetchSkills();
    }
  } catch (e) {
    console.error('Error deleting skill:', e);
  }
}

function getMatchScore(task) {
  // Simulate AI match score based on current user skills count and keyword matching
  const userSkillNames = userSkills.value.map(s => s.skill_name.toLowerCase());
  if (userSkillNames.length === 0) return 45;

  const taskText = (task.name + ' ' + (task.description || '')).toLowerCase();
  let hits = 0;
  userSkillNames.forEach(skill => {
    if (taskText.includes(skill)) hits += 1;
  });

  const baseScore = 65;
  const bonus = hits * 15 + Math.min(userSkillNames.length * 4, 20);
  return Math.min(baseScore + bonus, 98);
}

function getScoreClass(task) {
  const score = getMatchScore(task);
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
