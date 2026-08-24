<template>
    <div class="project-edit-vue">
        <section class="tp-page-head">
            <div>
                <span class="tp-eyebrow">PROJECTS</span>
                <h1>Chỉnh sửa dự án</h1>
                <p>Cập nhật thông tin và cài đặt cho dự án <strong>{{ project.name }}</strong></p>
            </div>
            <a :href="projectsIndexUrl" class="btn btn-default">Quay lại danh sách</a>
        </section>

        <form method="POST" :action="updateUrl" enctype="multipart/form-data" class="project-edit-form">
            <input type="hidden" name="_token" :value="csrfToken">
            <input type="hidden" name="_method" value="PUT">

            <div class="edit-grid">
                <div class="edit-col">
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-info-circle"></i>
                                Thông tin cơ bản
                            </h2>
                            <p class="edit-card-desc">Tên, mã, ảnh, mục tiêu và mô tả dự án</p>
                        </div>
                        <div class="edit-card-body">
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
                                <label>Mã dự án</label>
                                <input
                                    type="text"
                                    class="edit-input"
                                    :value="project.project_code || ''"
                                    readonly
                                    placeholder="Mã dự án tự động tạo"
                                >
                                <div class="edit-hint">Mã dự án được tạo tự động, không thể thay đổi.</div>
                            </div>

                            <div class="edit-field">
                                <label for="image">Ảnh dự án</label>
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
                                <label for="objective">Mục tiêu</label>
                                <textarea
                                    id="objective"
                                    name="objective"
                                    class="edit-textarea quill-target"
                                    rows="3"
                                    v-once
                                ></textarea>
                            </div>

                            <div class="edit-field">
                                <label for="description">Mô tả</label>
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
                </div>

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
                                <label>Trạng thái <span class="required">*</span></label>
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
                                <label>Ưu tiên <span class="required">*</span></label>
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
                                        step="100000"
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
                                Phân loại
                            </h2>
                            <p class="edit-card-desc">Khách hàng, danh mục và đội nhóm</p>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label>Khách hàng</label>
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
                                <label>Đội nhóm</label>
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

                    <div class="edit-card edit-card-actions">
                        <div class="edit-card-body">
                            <button type="submit" class="edit-btn edit-btn-primary">
                                <i class="fa fa-check"></i>
                                Lưu thay đổi
                            </button>
                            <a :href="projectsIndexUrl" class="edit-btn edit-btn-secondary">
                                <i class="fa fa-times"></i>
                                Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div v-if="showCategoryModal" class="edit-modal-overlay" @click.self="closeCategoryModal">
            <div class="edit-modal">
                <div class="edit-modal-header">
                    <h3>Tạo danh mục mới</h3>
                    <button type="button" class="edit-modal-close" @click="closeCategoryModal">&times;</button>
                </div>
                <div class="edit-modal-body">
                    <label>Tên danh mục</label>
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

                    <label style="margin-top: 14px;">Địa chỉ</label>
                    <input
                        v-model="companyForm.address"
                        type="text"
                        class="edit-input"
                        placeholder="Địa chỉ công ty"
                    >

                    <label style="margin-top: 14px;">Ghi chú</label>
                    <textarea
                        v-model="companyForm.note"
                        class="edit-input edit-textarea"
                        rows="2"
                        placeholder="Ghi chú nội bộ"
                    ></textarea>
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
import { ref, reactive, onMounted, nextTick } from 'vue';

const props = defineProps({
    project: { type: Object, required: true },
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

const form = reactive({
    name: props.old.name || props.project.name || '',
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

const companyForm = reactive({
    name: '',
    description: '',
    email: '',
    phone: '',
    address: '',
    note: '',
});

const showCategoryModal = ref(false);
const categoryName = ref('');
const categoryError = ref('');

const showCompanyModal = ref(false);
const companyName = ref('');
const companyError = ref('');
const imagePreview = ref('');
const removeImage = ref(false);

let objectiveQuill = null;
let descriptionQuill = null;

const quillToolbar = [
    ['bold', 'italic', 'underline'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['blockquote'],
    ['link'],
    ['clean']
];

onMounted(() => {
    nextTick(() => {
        objectiveQuill = initQuill('objective', form.objective, function(html) {
            form.objective = html;
        });
        descriptionQuill = initQuill('description', form.description, function(html) {
            form.description = html;
        });

        const formEl = document.querySelector('.project-edit-form');
        if (formEl) {
            formEl.addEventListener('submit', function() {
                syncQuill('objective', objectiveQuill);
                syncQuill('description', descriptionQuill);
            });
        }
    });
});

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
        categoryError.value = 'Lỗi kết nối. Vui lòng thử lại.';
    }
}

function openCompanyModal() {
    companyForm.name = '';
    companyForm.description = '';
    companyForm.email = '';
    companyForm.phone = '';
    companyForm.address = '';
    companyForm.note = '';
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
                contact_phone: companyForm.phone.trim(),
                contact_email: companyForm.email.trim(),
                contact_address: companyForm.address.trim(),
                contact_note: companyForm.note.trim(),
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
        companyError.value = 'Lỗi kết nối. Vui lòng thử lại.';
    }
}
</script>
