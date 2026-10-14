<template>
    <div class="project-create-vue">
        <section class="tp-page-head">
            <div>
                <span class="tp-eyebrow">PROJECTS</span>
                <h1>Tạo dự án mới</h1>
                <p>Một không gian để tập hợp thành viên, backlog và các mốc quan trọng.</p>
            </div>
            <a :href="projectsIndexUrl" class="btn btn-default">Quay lại</a>
        </section>

        <form method="POST" :action="storeUrl" enctype="multipart/form-data" class="project-create-form">
            <input type="hidden" name="_token" :value="csrfToken">

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
                                    :value="projectCodePreview"
                                    readonly
                                    placeholder="Mã dự án tự động tạo"
                                >
                                <div class="edit-hint">Mã dự án được tạo tự động khi lưu.</div>
                            </div>

                            <div class="edit-field">
                                <label for="image">Ảnh dự án</label>
                                <div class="edit-image-upload">
                                    <div v-if="!imagePreview" class="edit-image-placeholder">
                                        <i class="fa fa-image"></i>
                                        <span>Chưa có ảnh</span>
                                    </div>
                                    <div v-else class="edit-image-preview">
                                        <img :src="imagePreview" alt="Preview" class="edit-image-thumb">
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

                    <div class="edit-card">
                        <div class="edit-card-header">
                            <h2 class="edit-card-title">
                                <i class="fa fa-user-plus"></i>
                                Mời thành viên
                            </h2>
                            <p class="edit-card-desc">Thêm thành viên vào dự án ngay trong bước tạo</p>
                        </div>
                        <div class="edit-card-body">
                            <div class="edit-field">
                                <label>Thành viên</label>
                                <div class="edit-member-input" @click="focusMemberInput">
                                    <span
                                        v-for="(member, index) in members"
                                        :key="index"
                                        class="edit-member-tag"
                                    >
                                        <i class="fa fa-user-circle"></i>
                                        <span>{{ member }}</span>
                                        <i class="fa fa-times" @click.stop="removeMember(index)"></i>
                                    </span>
                                    <input
                                        ref="memberInputEl"
                                        v-model="memberInput"
                                        type="text"
                                        class="edit-member-text"
                                        placeholder="Nhập email hoặc username, nhấn Enter..."
                                        @keydown="handleMemberKeydown"
                                        @blur="addMemberFromInput"
                                    >
                                </div>
                                <input type="hidden" name="members" :value="members.join(', ')">
                                <div class="edit-hint">Nhập email hoặc username, nhấn Enter để thêm thành viên.</div>
                            </div>
                        </div>
                    </div>

                    <div class="edit-card edit-card-actions">
                        <div class="edit-card-body">
                            <button type="submit" class="edit-btn edit-btn-primary">
                                <i class="fa fa-check"></i>
                                Tạo dự án
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
                        v-model="companyName"
                        type="text"
                        class="edit-input"
                        placeholder="Ví dụ: Công ty ABC"
                        @keydown.enter="createCompany"
                    >

                    <label>Mô tả</label>
                    <textarea
                        v-model="companyDescription"
                        class="edit-textarea"
                        rows="3"
                        placeholder="Mô tả ngắn về khách hàng..."
                    ></textarea>

                    <label>Email liên hệ</label>
                    <input
                        v-model="companyEmail"
                        type="email"
                        class="edit-input"
                        placeholder="contact@company.com"
                    >

                    <label>Số điện thoại</label>
                    <input
                        v-model="companyPhone"
                        type="tel"
                        class="edit-input"
                        placeholder="0123 456 789"
                    >

                    <label>Địa chỉ</label>
                    <input
                        v-model="companyAddress"
                        type="text"
                        class="edit-input"
                        placeholder="Địa chỉ công ty..."
                    >

                    <label>Ghi chú</label>
                    <textarea
                        v-model="companyNote"
                        class="edit-textarea"
                        rows="3"
                        placeholder="Ghi chú thêm..."
                    ></textarea>

                    <div v-if="companyError" class="edit-error">{{ companyError }}</div>
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
    companies: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    teams: { type: Array, default: () => [] },
    projectCodePreview: { type: String, default: '' },
    companyId: { type: [Number, String], default: null },
    errors: { type: Object, default: () => ({}) },
    old: { type: Object, default: () => ({}) },
    categoryStoreUrl: { type: String, default: '' },
    companyStoreUrl: { type: String, default: '' },
    csrfToken: { type: String, default: '' },
    storeUrl: { type: String, default: '' },
    projectsIndexUrl: { type: String, default: '' },
});

const form = reactive({
    name: props.old.name || '',
    objective: props.old.objective || '',
    description: props.old.description || '',
    company_id: props.companyId || (props.old.company_id || ''),
    category_id: props.old.category_id || '',
    team_id: props.old.team_id || '',
    status: props.old.status || 'active',
    priority: props.old.priority || 'medium',
    start_date: props.old.start_date || '',
    end_date: props.old.end_date || '',
    budget: props.old.budget || '',
});

const members = ref([]);
const memberInput = ref('');
const memberInputEl = ref(null);
const removeImage = ref(false);
const imagePreview = ref('');
let objectiveQuill = null;
let descriptionQuill = null;
const showCategoryModal = ref(false);
const categoryName = ref('');
const categoryError = ref('');

const showCompanyModal = ref(false);
const companyName = ref('');
const companyDescription = ref('');
const companyEmail = ref('');
const companyPhone = ref('');
const companyAddress = ref('');
const companyNote = ref('');
const companyError = ref('');

const quillToolbar = [
    ['bold', 'italic', 'underline'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['blockquote'],
    ['link'],
    ['clean']
];

onMounted(() => {
    const initialMembers = props.old?.members || '';
    if (initialMembers) {
        members.value = initialMembers.split(/[\r\n,;]+/).map(m => m.trim()).filter(Boolean);
    }

    nextTick(() => {
        objectiveQuill = initQuill('objective', form.objective, function(html) {
            form.objective = html;
        });

        descriptionQuill = initQuill('description', form.description, function(html) {
            form.description = html;
        });

        const formEl = document.querySelector('form');
        if (formEl && !formEl.hasAttribute('data-quill-sync-bound')) {
            formEl.setAttribute('data-quill-sync-bound', '1');
            formEl.addEventListener('submit', function() {
                if (objectiveQuill) {
                    var ta = document.getElementById('objective');
                    if (ta) ta.value = objectiveQuill.root.innerHTML;
                }
                if (descriptionQuill) {
                    var ta = document.getElementById('description');
                    if (ta) ta.value = descriptionQuill.root.innerHTML;
                }
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

function onImageChange(e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        const file = files[0];
        const reader = new FileReader();
        reader.onload = function(evt) {
            imagePreview.value = evt.target.result;
        };
        reader.readAsDataURL(file);
        removeImage.value = false;
    }
}

function clearImage() {
    imagePreview.value = '';
    removeImage.value = true;
    const input = document.getElementById('image-input');
    if (input) {
        input.value = '';
    }
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

function focusMemberInput() {
    memberInputEl.value?.focus();
}

function handleMemberKeydown(e) {
    if (e.key === 'Enter' || e.key === ',' || e.key === 'Tab') {
        e.preventDefault();
        addMemberFromInput();
    } else if (e.key === 'Backspace' && !memberInput.value && members.value.length > 0) {
        members.value.pop();
    }
}

function addMemberFromInput() {
    const val = memberInput.value.trim().replace(/^[,;\s]+|[,;\s]+$/g, '');
    if (val && !members.value.includes(val)) {
        members.value.push(val);
    }
    memberInput.value = '';
}

function removeMember(index) {
    members.value.splice(index, 1);
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
        console.error('createCategory error:', e);
        categoryError.value = 'Lỗi kết nối. Vui lòng thử lại.';
    }
}

function openCompanyModal() {
    companyName.value = '';
    companyDescription.value = '';
    companyEmail.value = '';
    companyPhone.value = '';
    companyAddress.value = '';
    companyNote.value = '';
    companyError.value = '';
    showCompanyModal.value = true;
}

function closeCompanyModal() {
    showCompanyModal.value = false;
}

async function createCompany() {
    companyError.value = '';
    if (!companyName.value.trim()) {
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
                name: companyName.value.trim(),
                description: companyDescription.value.trim() || null,
                contact_email: companyEmail.value.trim() || null,
                contact_phone: companyPhone.value.trim() || null,
                contact_address: companyAddress.value.trim() || null,
                contact_note: companyNote.value.trim() || null,
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
        console.error('createCompany error:', e);
        companyError.value = 'Lỗi kết nối. Vui lòng thử lại.';
    }
}
</script>
