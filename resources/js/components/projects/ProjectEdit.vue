<template>
    <div class="project-edit-vue">
        <!-- Header Banner -->
        <div class="tp-header-banner mb-4">
            <div class="tp-header-content">
                <div class="tp-eyebrow">
                    <i class="fa fa-sliders"></i>
                    <span>CÀI ĐẶT DỰ ÁN &amp; QUY TRÌNH</span>
                </div>
                <h1 class="m-0" style="font-size: 22px; font-weight: 700; color: #ffffff;">
                    Chỉnh sửa dự án: {{ project.name }}
                </h1>
                <p class="m-0 text-muted small mt-1" style="color: #cbd5e1 !important;">
                    Cập nhật thông tin cơ bản, thành viên và phân loại dự án.
                </p>
            </div>
            <div class="tp-header-actions d-flex align-items-center gap-2">
                <a :href="`/projects/${project.id}`" class="tp-btn tp-btn-secondary">
                    <i class="fa fa-eye"></i>
                    <span>Xem chi tiết</span>
                </a>
                <a :href="`/projects/${project.id}/roadmap`" class="tp-btn tp-btn-light">
                    <i class="fa fa-calendar"></i>
                    <span>Roadmap</span>
                </a>
                <a :href="projectsIndexUrl" class="tp-btn tp-btn-light">
                    <i class="fa fa-arrow-left"></i>
                    <span>Quay lại</span>
                </a>
            </div>
        </div>

        <!-- Notification Toast -->
        <div v-if="toastMessage" class="tp-workflow-toast" :class="toastType">
            <i :class="toastType === 'success' ? 'fa fa-check-circle' : 'fa fa-exclamation-triangle'"></i>
            <span>{{ toastMessage }}</span>
            <button type="button" @click="toastMessage = ''">&times;</button>
        </div>

        <form method="POST" :action="updateUrl" enctype="multipart/form-data" class="project-edit-form" @submit="handleSubmit">
            <input type="hidden" name="_token" :value="csrfToken">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="project_type" :value="form.project_type">
            <input type="hidden" name="workflow_config" :value="workflowJson">

            <div class="edit-grid">
                <!-- Left Column (Main Details + Workflow Engine) -->
                <div class="edit-col">
                    <!-- CARD 1: Basic Information -->
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-info-circle"></i>
                                Thông tin cơ bản
                            </h2>
                            <p class="edit-card-desc">Tên, mã, ảnh, loại hình dự án và mô tả chi tiết</p>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                                <div class="edit-field">
                                    <label for="name">Tên dự án <span class="required">*</span></label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        name="name"
                                        class="edit-input"
                                        :class="{ 'is-invalid': errors.name }"
                                        placeholder="Nhập tên dự án"
                                        required
                                    >
                                    <div v-if="errors.name" class="edit-error">{{ errors.name[0] }}</div>
                                </div>

                                <div class="edit-field">
                                    <label>Mã dự án (Key)</label>
                                    <input
                                        type="text"
                                        class="edit-input"
                                        :value="project.project_code || 'PROJ'"
                                        readonly
                                        style="background: #f1f5f9; font-weight: 700; color: #334155;"
                                    >
                                    <div class="edit-hint">Mã tiền tố cho công việc (VD: {{ project.project_code || 'PROJ' }}-123)</div>
                                </div>
                            </div>

                            <!-- Project Type Selection (Software, Marketing, HR, Custom) -->
                            <div class="edit-field" style="margin-top: 6px;">
                                <label style="font-weight: 600; color: #0f172a; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Loại hình dự án (Project Type) <span class="required">*</span></span>
                                    <span class="text-muted" style="font-weight: 400; font-size: 11px;">Ảnh hưởng tới quy trình trạng thái (Workflow)</span>
                                </label>
                                <div class="tp-type-selector-grid">
                                    <div
                                        class="tp-type-card"
                                        :class="{ active: form.project_type === 'software' }"
                                        @click="selectProjectType('software')"
                                    >
                                        <div class="tp-type-icon text-primary"><i class="fa fa-code"></i></div>
                                        <div class="tp-type-info">
                                            <strong>Phát triển Phần mềm (Software)</strong>
                                            <p>Cần làm ➔ Đang thực hiện ➔ Hoàn thành</p>
                                        </div>
                                        <i v-if="form.project_type === 'software'" class="fa fa-check-circle tp-type-check"></i>
                                    </div>

                                    <div
                                        class="tp-type-card"
                                        :class="{ active: form.project_type === 'marketing' }"
                                        @click="selectProjectType('marketing')"
                                    >
                                        <div class="tp-type-icon text-success"><i class="fa fa-bullhorn"></i></div>
                                        <div class="tp-type-info">
                                            <strong>Chiến dịch Marketing (Marketing)</strong>
                                            <p>Lên ý tưởng ➔ Đang thực hiện ➔ Hoàn thành</p>
                                        </div>
                                        <i v-if="form.project_type === 'marketing'" class="fa fa-check-circle tp-type-check"></i>
                                    </div>

                                    <div
                                        class="tp-type-card"
                                        :class="{ active: form.project_type === 'hr' }"
                                        @click="selectProjectType('hr')"
                                    >
                                        <div class="tp-type-icon text-warning"><i class="fa fa-users"></i></div>
                                        <div class="tp-type-info">
                                            <strong>Quản trị Nhân sự (HR)</strong>
                                            <p>Tiếp nhận hồ sơ ➔ Đang xử lý ➔ Hoàn tất</p>
                                        </div>
                                        <i v-if="form.project_type === 'hr'" class="fa fa-check-circle tp-type-check"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="edit-field" style="margin-top: 14px;">
                                <label for="image">Ảnh đại diện dự án</label>
                                <div class="edit-image-upload">
                                    <div v-if="(!projectImageUrl || removeImage) && !imagePreview" class="edit-image-placeholder">
                                        <i class="fa fa-image"></i>
                                        <span>Chưa có ảnh</span>
                                    </div>
                                    <div v-else class="edit-image-preview">
                                        <img :src="imagePreview || projectImageUrl" alt="Project image" class="edit-image-thumb">
                                        <button type="button" class="edit-image-clear" @click="clearImage" title="Xóa ảnh">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <label class="edit-image-btn" for="image-input">
                                        <i class="fa fa-upload"></i> Chọn ảnh
                                    </label>
                                    <input
                                        type="file"
                                        id="image-input"
                                        name="image"
                                        accept="image/*"
                                        class="edit-image-input"
                                        style="display: none"
                                        @change="onImageChange"
                                    >
                                    <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">
                                </div>
                                <div v-if="errors.image" class="edit-error">{{ errors.image[0] }}</div>
                            </div>

                            <div class="edit-field">
                                <label for="objective">Mục tiêu dự án</label>
                                <textarea
                                    id="objective"
                                    name="objective"
                                    class="edit-textarea quill-target"
                                    rows="3"
                                    v-once
                                ></textarea>
                            </div>

                            <div class="edit-field">
                                <label for="description">Mô tả chi tiết</label>
                                <textarea
                                    id="description"
                                    name="description"
                                    class="edit-textarea quill-target"
                                    rows="5"
                                    v-once
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- WORKFLOW BANNER: Directed to Project Dashboard #workflow -->
                    <div class="edit-card p-4 mb-3" style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); border: 1px solid #bfdbfe; border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <div style="width: 44px; height: 44px; border-radius: 10px; background: #3b82f6; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.25);">
                                    <i class="fa fa-sitemap"></i>
                                </div>
                                <div>
                                    <h3 style="font-size: 16px; font-weight: 700; color: #1e3a8a; margin: 0 0 4px 0;">
                                        Quy trình làm việc (Workflow Engine)
                                    </h3>
                                    <p class="m-0 text-muted small" style="color: #475569 !important; line-height: 1.5;">
                                        Quy trình, danh sách trạng thái và ma trận chuyển tiếp được cấu hình và quản lý trực tiếp tại trang chi tiết dự án.
                                    </p>
                                </div>
                            </div>
                            <div>
                                <a :href="`/projects/${project.id}#workflow`" class="tp-btn tp-btn-primary btn-sm text-nowrap">
                                    <i class="fa fa-sliders me-1"></i> Quản lý Quy trình &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Progress, Dates, Classification, Actions) -->
                <div class="edit-col">
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-calendar"></i>
                                Tiến độ &amp; Kế hoạch
                            </h2>
                            <p class="edit-card-desc">Thời gian, ngân sách và ưu tiên</p>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label>Trạng thái dự án <span class="required">*</span></label>
                                <div class="edit-select-wrapper">
                                    <select
                                        v-model="form.status"
                                        name="status"
                                        class="edit-select"
                                        :class="{ 'is-invalid': errors.status }"
                                        required
                                    >
                                        <option value="planning">Lên kế hoạch</option>
                                        <option value="active">Đang hoạt động</option>
                                        <option value="on_hold">Tạm dừng</option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                            </div>

                            <div class="edit-field">
                                <label>Mức độ ưu tiên <span class="required">*</span></label>
                                <div class="edit-select-wrapper">
                                    <select
                                        v-model="form.priority"
                                        name="priority"
                                        class="edit-select"
                                        :class="{ 'is-invalid': errors.priority }"
                                        required
                                    >
                                        <option value="low">Thấp</option>
                                        <option value="medium">Trung bình</option>
                                        <option value="high">Cao</option>
                                        <option value="critical">Khẩn cấp</option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                            </div>

                            <div class="edit-field">
                                <label>Ngân sách dự kiến</label>
                                <div class="edit-input-group">
                                    <input
                                        type="number"
                                        v-model="form.budget"
                                        name="budget"
                                        class="edit-input"
                                        :class="{ 'is-invalid': errors.budget }"
                                        placeholder="0"
                                        min="0"
                                        step="any"
                                    >
                                    <span class="edit-input-addon">VND</span>
                                </div>
                                <div v-if="errors.budget" class="edit-error">{{ errors.budget[0] }}</div>
                            </div>

                            <div class="edit-row">
                                <div class="edit-field">
                                    <label>Ngày bắt đầu</label>
                                    <input
                                        type="date"
                                        v-model="form.start_date"
                                        name="start_date"
                                        class="edit-input"
                                    >
                                </div>
                                <div class="edit-field">
                                    <label>Hạn hoàn thành</label>
                                    <input
                                        type="date"
                                        v-model="form.end_date"
                                        name="end_date"
                                        class="edit-input"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-sitemap"></i>
                                Phân loại &amp; Đội ngũ
                            </h2>
                            <p class="edit-card-desc">Khách hàng, danh mục và đội nhóm</p>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label>Khách hàng của dự án</label>
                                <div class="edit-select-wrapper">
                                    <select
                                        v-model="form.company_id"
                                        name="company_id"
                                        class="edit-select"
                                        :class="{ 'is-invalid': errors.company_id }"
                                    >
                                        <option value="">Nội bộ</option>
                                        <option v-for="company in companies" :key="company.id" :value="company.id">
                                            {{ company.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                    <button type="button" class="edit-quick-add" @click="openCompanyModal" title="Tạo khách hàng nhanh">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <div class="edit-hint">Khách hàng / đối tác chủ quản liên kết với dự án.</div>
                            </div>

                            <div class="edit-field">
                                <label>Danh mục</label>
                                <div class="edit-select-wrapper">
                                    <select
                                        v-model="form.category_id"
                                        name="category_id"
                                        class="edit-select"
                                        :class="{ 'is-invalid': errors.category_id }"
                                    >
                                        <option value="">Chưa chọn</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                    <button type="button" class="edit-quick-add" @click="openCategoryModal" title="Tạo danh mục nhanh">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="edit-field">
                                <label>Đội nhóm phụ trách</label>
                                <div class="edit-select-wrapper">
                                    <select
                                        v-model="form.team_id"
                                        name="team_id"
                                        class="edit-select"
                                        :class="{ 'is-invalid': errors.team_id }"
                                    >
                                        <option value="">Chưa gán</option>
                                        <option v-for="team in teams" :key="team.id" :value="team.id">
                                            {{ team.name }}
                                        </option>
                                    </select>
                                    <i class="fa fa-chevron-down edit-select-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="edit-card edit-card-actions">
                        <div class="edit-card-body">
                            <button type="submit" class="edit-btn edit-btn-primary w-100 mb-2">
                                <i class="fa fa-check me-1"></i>
                                Lưu toàn bộ thay đổi
                            </button>
                            <a :href="projectsIndexUrl" class="edit-btn edit-btn-secondary w-100">
                                <i class="fa fa-times me-1"></i>
                                Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        <!-- Quick Category Modal -->
        <div v-if="showCategoryModal" class="edit-modal-overlay" @click.self="closeCategoryModal">
            <div class="edit-modal">
                <div class="edit-modal-header">
                    <h3>Tạo danh mục mới</h3>
                    <button type="button" class="edit-modal-close" @click="closeCategoryModal">&times;</button>
                </div>
                <div class="edit-modal-body">
                    <label>Tên danh mục <span class="required">*</span></label>
                    <input
                        v-model="categoryName"
                        type="text"
                        class="edit-input"
                        placeholder="Ví dụ: Nội bộ, Khách hàng..."
                        @keydown.enter="createCategory"
                    >
                    <div v-if="categoryError" class="edit-error">{{ categoryError }}</div>
                </div>
                <div class="edit-modal-footer">
                    <button type="button" class="edit-btn edit-btn-secondary" @click="closeCategoryModal">Hủy</button>
                    <button type="button" class="edit-btn edit-btn-primary" @click="createCategory">Tạo</button>
                </div>
            </div>
        </div>

        <!-- Quick Company Modal -->
        <div v-if="showCompanyModal" class="edit-modal-overlay" @click.self="closeCompanyModal">
            <div class="edit-modal">
                <div class="edit-modal-header">
                    <h3>Tạo khách hàng mới</h3>
                    <button type="button" class="edit-modal-close" @click="closeCompanyModal">&times;</button>
                </div>
                <div class="edit-modal-body">
                    <label>Tên khách hàng <span class="required">*</span></label>
                    <input
                        v-model="companyForm.name"
                        type="text"
                        class="edit-input"
                        placeholder="Ví dụ: Công ty ABC"
                    >
                    <div v-if="companyError" class="edit-error">{{ companyError }}</div>

                    <label style="margin-top: 14px;">Mô tả</label>
                    <textarea
                        v-model="companyForm.description"
                        class="edit-input edit-textarea"
                        rows="2"
                        placeholder="Mô tả ngắn về khách hàng"
                    ></textarea>

                    <label style="margin-top: 14px;">Email liên hệ</label>
                    <input
                        v-model="companyForm.email"
                        type="email"
                        class="edit-input"
                        placeholder="contact@example.com"
                    >

                    <label style="margin-top: 14px;">Số điện thoại</label>
                    <input
                        v-model="companyForm.phone"
                        type="tel"
                        class="edit-input"
                        placeholder="0123 456 789"
                    >
                </div>
                <div class="edit-modal-footer">
                    <button type="button" class="edit-btn edit-btn-secondary" @click="closeCompanyModal">Hủy</button>
                    <button type="button" class="edit-btn edit-btn-primary" @click="createCompany">Tạo</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';

const props = defineProps({
    project: { type: Object, required: true },
    effectiveWorkflow: { type: Object, default: () => null },
    defaultWorkflows: { type: Object, default: () => ({}) },
    companies: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    teams: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    old: { type: Object, default: () => ({}) },
    categoryStoreUrl: { type: String, default: '' },
    companyStoreUrl: { type: String, default: '' },
    csrfToken: { type: String, default: '' },
    updateUrl: { type: String, default: '' },
    projectsIndexUrl: { type: String, default: '' },
    projectImageUrl: { type: String, default: '' },
});

// Toast Notification
const toastMessage = ref('');
const toastType = ref('success');
function showToast(msg, type = 'success') {
    toastMessage.value = msg;
    toastType.value = type;
    setTimeout(() => {
        if (toastMessage.value === msg) {
            toastMessage.value = '';
        }
    }, 4500);
}

// Form State
const form = reactive({
    name: props.old.name || props.project.name || '',
    project_type: props.old.project_type || props.project.project_type || 'software',
    objective: props.old.objective || props.project.objective || '',
    description: props.old.description || props.project.description || '',
    company_id: props.old.company_id || props.project.company_id || '',
    category_id: props.old.category_id || props.project.category_id || '',
    team_id: props.old.team_id || props.project.team_id || '',
    status: props.old.status || props.project.status || 'active',
    priority: props.old.priority || props.project.priority || 'medium',
    start_date: props.old.start_date || props.project.start_date || '',
    end_date: props.old.end_date || props.project.end_date || '',
    budget: props.old.budget || props.project.budget || '',
});

// Preserve existing workflow configuration when submitting project details
const workflowJson = computed(() => {
    return JSON.stringify(props.effectiveWorkflow || props.project?.workflow_config || {});
});

// Switch project type
function selectProjectType(type) {
    form.project_type = type;
}

// Image handling
const imagePreview = ref('');
const removeImage = ref(false);

function onImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(evt) {
        imagePreview.value = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function clearImage() {
    imagePreview.value = '';
    removeImage.value = true;
    const input = document.getElementById('image-input');
    if (input) input.value = '';
}

// Modals for Category & Company
const showCategoryModal = ref(false);
const categoryName = ref('');
const categoryError = ref('');

function openCategoryModal() {
    categoryName.value = '';
    categoryError.value = '';
    showCategoryModal.value = true;
}

function closeCategoryModal() {
    showCategoryModal.value = false;
}

async function createCategory() {
    categoryError.value = '';
    if (!categoryName.value.trim()) {
        categoryError.value = 'Vui lòng nhập tên danh mục.';
        return;
    }

    try {
        const response = await fetch(props.categoryStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: JSON.stringify({ name: categoryName.value.trim() }),
        });

        const data = await response.json();
        if (response.ok) {
            props.categories.push({ id: data.id, name: data.name });
            form.category_id = data.id;
            closeCategoryModal();
        } else {
            categoryError.value = data.message || 'Không thể tạo danh mục.';
        }
    } catch (e) {
        categoryError.value = 'Lỗi kết nối khi tạo danh mục.';
    }
}

const showCompanyModal = ref(false);
const companyError = ref('');
const companyForm = reactive({
    name: '',
    description: '',
    email: '',
    phone: '',
});

function openCompanyModal() {
    companyForm.name = '';
    companyForm.description = '';
    companyForm.email = '';
    companyForm.phone = '';
    companyError.value = '';
    showCompanyModal.value = true;
}

function closeCompanyModal() {
    showCompanyModal.value = false;
}

async function createCompany() {
    companyError.value = '';
    if (!companyForm.name.trim()) {
        companyError.value = 'Vui lòng nhập tên khách hàng.';
        return;
    }

    try {
        const response = await fetch(props.companyStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: JSON.stringify({
                name: companyForm.name.trim(),
                description: companyForm.description.trim(),
                contact_email: companyForm.email.trim(),
                contact_phone: companyForm.phone.trim(),
            }),
        });

        const data = await response.json();
        if (response.ok) {
            props.companies.push({ id: data.id, name: data.name });
            form.company_id = data.id;
            closeCompanyModal();
        } else {
            companyError.value = data.message || 'Không thể tạo khách hàng.';
        }
    } catch (e) {
        companyError.value = 'Lỗi kết nối khi tạo khách hàng.';
    }
}

// Quill Rich Text Editor Initialization
let objectiveQuill = null;
let descriptionQuill = null;

const quillToolbar = [
    ['bold', 'italic', 'underline'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['blockquote'],
    ['link'],
    ['clean']
];

function initQuill(textareaId, initialContent, onChange) {
    const textarea = document.getElementById(textareaId);
    if (!textarea || typeof Quill === 'undefined') return null;

    textarea.style.display = 'none';
    const container = document.createElement('div');
    container.id = textareaId + '-editor';
    container.className = 'quill-editor';
    textarea.parentNode.insertBefore(container, textarea);

    textarea.value = initialContent || '';

    const quill = new Quill('#' + textareaId + '-editor', {
        theme: 'snow',
        placeholder: '',
        modules: { toolbar: quillToolbar }
    });

    let ignoreChange = true;
    quill.clipboard.dangerouslyPasteHTML(initialContent || '');
    ignoreChange = false;

    quill.on('text-change', function() {
        if (ignoreChange) return;
        const html = quill.root.innerHTML;
        textarea.value = html;
        onChange(html);
    });

    return quill;
}

function syncQuill(textareaId, quill) {
    if (quill) {
        const textarea = document.getElementById(textareaId);
        if (textarea) {
            textarea.value = quill.root.innerHTML;
            form[textareaId === 'objective' ? 'objective' : 'description'] = quill.root.innerHTML;
        }
    }
}

function handleSubmit() {
    syncQuill('objective', objectiveQuill);
    syncQuill('description', descriptionQuill);
}

onMounted(() => {
    nextTick(() => {
        objectiveQuill = initQuill('objective', form.objective, function(html) {
            form.objective = html;
        });
        descriptionQuill = initQuill('description', form.description, function(html) {
            form.description = html;
        });
    });
});
</script>

<style scoped>
.tp-workflow-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999;
    padding: 12px 18px;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    animation: fadeInSlide 0.25s ease-out;
}
.tp-workflow-toast.success { background: #059669; }
.tp-workflow-toast.error { background: #dc2626; }
.tp-workflow-toast button {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
}

@keyframes fadeInSlide {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}

.tp-type-selector-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 6px;
}
@media (max-width: 768px) {
    .tp-type-selector-grid { grid-template-columns: 1fr; }
}
.tp-type-card {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    cursor: pointer;
    background: #fff;
    position: relative;
    transition: all 0.2s ease;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.tp-type-card:hover {
    border-color: #93c5fd;
    background: #f8fafc;
}
.tp-type-card.active {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 1px #2563eb;
}
.tp-type-icon {
    font-size: 20px;
    margin-top: 2px;
}
.tp-type-info strong {
    display: block;
    font-size: 13px;
    color: #0f172a;
    margin-bottom: 2px;
}
.tp-type-info p {
    margin: 0;
    font-size: 11px;
    color: #64748b;
    line-height: 1.35;
}
.tp-type-check {
    position: absolute;
    top: 10px;
    right: 10px;
    color: #2563eb;
    font-size: 16px;
}

/* Visual Pipeline */
.tp-section-subtitle {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
}
.tp-pipeline-scroll-wrap {
    overflow-x: auto;
    padding: 10px 4px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.tp-pipeline-flow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 100%;
}
.tp-pipeline-step-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.tp-pipeline-node {
    width: 175px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-left-width: 4px;
    border-radius: 8px;
    padding: 10px 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}
.tp-node-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}
.tp-node-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.tp-node-num {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
}
.tp-node-cat {
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 1px 5px;
    border-radius: 4px;
    margin-left: auto;
    background: #f1f5f9;
    color: #475569;
}
.tp-node-cat.cat-done { background: #dcfce7; color: #166534; }
.tp-node-cat.cat-in_progress { background: #dbeafe; color: #1e40af; }
.tp-node-cat.cat-testing { background: #fef3c7; color: #92400e; }
.tp-node-cat.cat-review { background: #ede9fe; color: #5b21b6; }
.tp-node-title {
    font-size: 13px;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}
.tp-node-key code {
    font-size: 10px;
    color: #64748b;
    background: #f1f5f9;
    padding: 1px 4px;
    border-radius: 3px;
}
.tp-node-gates {
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.badge-gate {
    font-size: 9px;
    padding: 2px 5px;
    border-radius: 4px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-gate-lead { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.badge-gate-cust { background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.badge-gate-rule { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

.tp-pipeline-arrow {
    color: #94a3b8;
    font-size: 16px;
    display: flex;
    align-items: center;
}

/* Status Table */
.tp-status-table-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.tp-status-table {
    width: 100%;
    border-collapse: collapse;
}
.tp-status-table th {
    background: #f8fafc;
    padding: 10px 12px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e2e8f0;
}
.tp-status-table td {
    padding: 10px 12px;
    font-size: 13px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.tp-status-table tr:last-child td { border-bottom: none; }
.status-color-pill {
    width: 12px;
    height: 12px;
    border-radius: 3px;
    display: inline-block;
}
.tp-code-badge {
    font-size: 11px;
    color: #0369a1;
    background: #f0f9ff;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid #bae6fd;
}
.tp-compact-select {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}
.tp-color-input {
    width: 28px;
    height: 28px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 1px;
    cursor: pointer;
}
.tp-icon-btn {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 11px;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s;
}
.tp-icon-btn:hover:not(:disabled) {
    background: #e2e8f0;
    color: #0f172a;
}
.tp-icon-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Rule Boxes */
.tp-rule-box {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    height: 100%;
}
.tp-gate-box { border-left: 4px solid #2563eb; }
.tp-validation-box { border-left: 4px solid #16a34a; }
.tp-rule-box-header {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 14px;
}
.tp-rule-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.tp-rule-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}
.tp-rule-desc {
    margin: 2px 0 0;
    font-size: 11px;
    color: #64748b;
    line-height: 1.35;
}
.tp-toggle-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    cursor: pointer;
}
.tp-checkbox-switch {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #2563eb;
}
.tp-toggle-info strong {
    display: block;
    font-size: 12.5px;
    color: #1e293b;
}
.tp-toggle-info span {
    display: block;
    font-size: 11px;
    color: #64748b;
    margin-top: 1px;
}
.tp-rule-tip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 11px;
    color: #475569;
    display: flex;
    gap: 6px;
    align-items: flex-start;
    line-height: 1.4;
}
</style>
