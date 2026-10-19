<template>
    <div class="company-form-container">
        <!-- Top Breadcrumb & Page Head -->
        <header class="tp-crm-head">
            <div class="tp-crm-breadcrumb">
                <a :href="backUrl" class="tp-crumb-link">
                    <i class="fa fa-home me-1"></i> Trang chủ
                </a>
                <span class="tp-crumb-sep">/</span>
                <a :href="backUrl" class="tp-crumb-link">
                    <i class="fa fa-building-o me-1"></i> Đối tác &amp; Khách hàng
                </a>
                <span class="tp-crumb-sep">/</span>
                <span class="tp-crumb-current">{{ isEdit ? 'Chỉnh sửa hồ sơ' : 'Thêm đối tác mới' }}</span>
            </div>

            <div class="tp-crm-title-row">
                <div class="tp-crm-title-left">
                    <div class="tp-crm-badge">
                        <i class="fa fa-id-badge"></i>
                        <span>TASKPILOT CRM &bull; ENTERPRISE DIRECTORY</span>
                    </div>
                    <h1 class="tp-crm-heading">
                        {{ isEdit ? (form.name ? `Hồ sơ: ${form.name}` : 'Chỉnh sửa khách hàng') : 'Thêm khách hàng / Đối tác mới' }}
                    </h1>
                    <p class="tp-crm-subheading">
                        {{ isEdit
                            ? 'Cập nhật pháp nhân, đại diện liên hệ và thông tin đối tác liên kết với các dự án TaskPilot.'
                            : 'Khởi tạo hồ sơ khách hàng mới để gán vào quản lý dự án, hợp đồng và phân loại công việc.'
                        }}
                    </p>
                </div>

                <div class="tp-crm-title-actions">
                    <a :href="backUrl" class="tp-btn-crm tp-btn-secondary" title="Quay lại danh sách">
                        <i class="fa fa-arrow-left"></i>
                        <span>Quay lại</span>
                    </a>
                    <button type="button" class="tp-btn-crm tp-btn-primary" @click="submitForm">
                        <i class="fa fa-check"></i>
                        <span>{{ isEdit ? 'Lưu thay đổi' : 'Tạo đối tác' }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Form -->
        <form ref="mainForm" method="POST" :action="actionUrl" class="tp-crm-form" @submit="onFormSubmit">
            <input type="hidden" name="_token" :value="csrfToken">
            <input v-if="isEdit" type="hidden" name="_method" value="PUT">

            <div class="tp-crm-layout">
                <!-- LEFT COLUMN: Main Form Inputs (63%) -->
                <div class="tp-crm-main-col">
                    <!-- CARD 1: Thông tin tổ chức & Nhận diện thương hiệu -->
                    <div class="tp-card">
                        <div class="tp-card-header">
                            <div class="tp-card-icon tp-card-icon-blue">
                                <i class="fa fa-building"></i>
                            </div>
                            <div class="tp-card-header-text">
                                <h2 class="tp-card-title">Thông tin doanh nghiệp &amp; Nhận diện</h2>
                                <p class="tp-card-subtitle">Tên giao dịch chính thức, phân khúc đối tác và lĩnh vực hoạt động</p>
                            </div>
                        </div>

                        <div class="tp-card-body">
                            <!-- Company Name -->
                            <div class="tp-field">
                                <div class="tp-field-label-row">
                                    <label for="company-name" class="tp-label">
                                        Tên khách hàng / Tổ chức <span class="tp-required">*</span>
                                    </label>
                                    <span class="tp-counter">{{ form.name.length }}/255</span>
                                </div>
                                <div class="tp-input-with-icon">
                                    <i class="fa fa-briefcase tp-field-icon"></i>
                                    <input
                                        id="company-name"
                                        v-model="form.name"
                                        name="name"
                                        type="text"
                                        class="tp-input"
                                        :class="{ 'is-invalid': errors.name }"
                                        placeholder="Ví dụ: Tập đoàn Viettel, FPT Software, Công ty ABC..."
                                        required
                                        maxlength="255"
                                        autofocus
                                    >
                                </div>
                                <div v-if="errors.name" class="tp-error-text">
                                    <i class="fa fa-exclamation-circle me-1"></i>{{ errors.name[0] }}
                                </div>
                            </div>

                            <!-- Customer Tier Selection (Quick Tag) -->
                            <div class="tp-field mt-3">
                                <label class="tp-label">Phân khúc đối tác (Tier / Segment)</label>
                                <div class="tp-tier-selector">
                                    <button
                                        type="button"
                                        v-for="tier in tiers"
                                        :key="tier.id"
                                        class="tp-tier-chip"
                                        :class="{ active: selectedTier === tier.id }"
                                        @click="selectTier(tier)"
                                    >
                                        <i :class="`fa ${tier.icon} me-1`"></i>
                                        <span>{{ tier.name }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Industry Selector -->
                            <div class="tp-field mt-3">
                                <div class="tp-field-label-row">
                                    <label class="tp-label">Lĩnh vực hoạt động nhanh</label>
                                    <span class="tp-hint-label">Bấm để thêm vào mô tả</span>
                                </div>
                                <div class="tp-industry-tags">
                                    <button
                                        type="button"
                                        v-for="industry in industries"
                                        :key="industry"
                                        class="tp-industry-tag"
                                        @click="appendIndustry(industry)"
                                    >
                                        + {{ industry }}
                                    </button>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="tp-field mt-3">
                                <div class="tp-field-label-row">
                                    <label for="company-desc" class="tp-label">Mô tả hoặc Giới thiệu doanh nghiệp</label>
                                    <span class="tp-counter">{{ form.description.length }}/1000</span>
                                </div>
                                <textarea
                                    id="company-desc"
                                    v-model="form.description"
                                    name="description"
                                    class="tp-textarea"
                                    :class="{ 'is-invalid': errors.description }"
                                    rows="4"
                                    placeholder="Thông tin giới thiệu về khách hàng, thế mạnh ngành nghề hoặc phạm vi dự án hợp tác..."
                                    maxlength="1000"
                                ></textarea>
                                <div v-if="errors.description" class="tp-error-text">
                                    <i class="fa fa-exclamation-circle me-1"></i>{{ errors.description[0] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: Thông tin liên hệ & Địa chỉ -->
                    <div class="tp-card mt-4">
                        <div class="tp-card-header">
                            <div class="tp-card-icon tp-card-icon-emerald">
                                <i class="fa fa-address-book-o"></i>
                            </div>
                            <div class="tp-card-header-text">
                                <h2 class="tp-card-title">Đầu mối liên hệ &amp; Địa chỉ giao dịch</h2>
                                <p class="tp-card-subtitle">Kênh liên lạc chính thức để kết nối quản lý dự án với đối tác</p>
                            </div>
                        </div>

                        <div class="tp-card-body">
                            <!-- 2-column: Email & Phone -->
                            <div class="tp-grid-2">
                                <div class="tp-field">
                                    <label for="company-email" class="tp-label">Email liên hệ chính</label>
                                    <div class="tp-input-with-icon">
                                        <i class="fa fa-envelope-o tp-field-icon"></i>
                                        <input
                                            id="company-email"
                                            v-model="form.contact_email"
                                            name="contact_email"
                                            type="email"
                                            class="tp-input"
                                            :class="{ 'is-invalid': errors.contact_email }"
                                            placeholder="contact@company.com"
                                            maxlength="255"
                                        >
                                    </div>
                                    <div v-if="errors.contact_email" class="tp-error-text">
                                        <i class="fa fa-exclamation-circle me-1"></i>{{ errors.contact_email[0] }}
                                    </div>
                                </div>

                                <div class="tp-field">
                                    <label for="company-phone" class="tp-label">Số điện thoại liên hệ</label>
                                    <div class="tp-input-with-icon">
                                        <i class="fa fa-phone tp-field-icon"></i>
                                        <input
                                            id="company-phone"
                                            v-model="form.contact_phone"
                                            name="contact_phone"
                                            type="tel"
                                            class="tp-input"
                                            :class="{ 'is-invalid': errors.contact_phone }"
                                            placeholder="024 3999 8888 hoặc 0912 345 678"
                                            maxlength="50"
                                        >
                                    </div>
                                    <div v-if="errors.contact_phone" class="tp-error-text">
                                        <i class="fa fa-exclamation-circle me-1"></i>{{ errors.contact_phone[0] }}
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="tp-field mt-3">
                                <div class="tp-field-label-row">
                                    <label for="company-address" class="tp-label">Địa chỉ trụ sở / Văn phòng đại diện</label>
                                    <a
                                        v-if="form.contact_address && form.contact_address.trim()"
                                        :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(form.contact_address)}`"
                                        target="_blank"
                                        rel="noopener"
                                        class="tp-sub-action-link"
                                    >
                                        <i class="fa fa-external-link me-1"></i> Mở Google Maps
                                    </a>
                                </div>
                                <div class="tp-input-with-icon">
                                    <i class="fa fa-map-marker tp-field-icon text-danger"></i>
                                    <input
                                        id="company-address"
                                        v-model="form.contact_address"
                                        name="contact_address"
                                        type="text"
                                        class="tp-input"
                                        :class="{ 'is-invalid': errors.contact_address }"
                                        placeholder="Tòa nhà Keangnam, Phạm Hùng, Cầu Giấy, Hà Nội..."
                                        maxlength="500"
                                    >
                                </div>
                                <div v-if="errors.contact_address" class="tp-error-text">
                                    <i class="fa fa-exclamation-circle me-1"></i>{{ errors.contact_address[0] }}
                                </div>
                            </div>

                            <!-- Internal Note -->
                            <div class="tp-field mt-3">
                                <div class="tp-field-label-row">
                                    <label for="company-note" class="tp-label">
                                        <i class="fa fa-lock text-warning me-1"></i>
                                        Ghi chú nội bộ (Internal Notes)
                                    </label>
                                    <span class="tp-hint-label">Chỉ nội bộ quản lý dự án nhìn thấy</span>
                                </div>
                                <textarea
                                    id="company-note"
                                    v-model="form.contact_note"
                                    name="contact_note"
                                    class="tp-textarea tp-note-textarea"
                                    :class="{ 'is-invalid': errors.contact_note }"
                                    rows="3"
                                    placeholder="Ghi chú về người quyết định chính, điều khoản hợp đồng đặc biệt, quy trình thanh toán hoặc lưu ý triển khai..."
                                    maxlength="1000"
                                ></textarea>
                                <div v-if="errors.contact_note" class="tp-error-text">
                                    <i class="fa fa-exclamation-circle me-1"></i>{{ errors.contact_note[0] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Live Interactive Business Card Preview & Ecosystem (37%) -->
                <div class="tp-crm-side-col">
                    <!-- LIVE BUSINESS CARD -->
                    <div class="tp-card tp-client-card-preview">
                        <div class="tp-preview-header">
                            <div class="tp-preview-header-overlay"></div>
                            <div class="tp-preview-tier-badge">
                                <i :class="`fa ${currentTierObj.icon} me-1`"></i>
                                {{ currentTierObj.name }}
                            </div>
                        </div>

                        <div class="tp-preview-content">
                            <!-- Avatar with Gradient Ring -->
                            <div class="tp-preview-avatar-wrap">
                                <div class="tp-preview-avatar" :style="{ background: avatarGradient }">
                                    {{ companyInitials }}
                                </div>
                            </div>

                            <h3 class="tp-preview-name">
                                {{ form.name.trim() || 'Tên doanh nghiệp / Đối tác' }}
                            </h3>

                            <div class="tp-preview-status">
                                <span class="tp-status-dot"></span>
                                <span>Khách hàng đối tác TaskPilot</span>
                            </div>

                            <!-- Interactive Contact Action Items -->
                            <div class="tp-preview-contact-box">
                                <!-- Email item -->
                                <div class="tp-preview-item">
                                    <div class="tp-preview-item-icon tp-icon-mail">
                                        <i class="fa fa-envelope-o"></i>
                                    </div>
                                    <div class="tp-preview-item-info">
                                        <span class="tp-preview-item-label">Email</span>
                                        <a
                                            v-if="form.contact_email"
                                            :href="`mailto:${form.contact_email}`"
                                            class="tp-preview-item-value tp-link"
                                        >
                                            {{ form.contact_email }}
                                        </a>
                                        <span v-else class="tp-preview-item-value text-muted">Chưa cập nhật email</span>
                                    </div>
                                    <button
                                        v-if="form.contact_email"
                                        type="button"
                                        class="tp-mini-copy-btn"
                                        @click="copyText(form.contact_email, 'Email')"
                                        title="Sao chép email"
                                    >
                                        <i class="fa fa-clone"></i>
                                    </button>
                                </div>

                                <!-- Phone item -->
                                <div class="tp-preview-item">
                                    <div class="tp-preview-item-icon tp-icon-phone">
                                        <i class="fa fa-phone"></i>
                                    </div>
                                    <div class="tp-preview-item-info">
                                        <span class="tp-preview-item-label">Điện thoại</span>
                                        <a
                                            v-if="form.contact_phone"
                                            :href="`tel:${form.contact_phone}`"
                                            class="tp-preview-item-value tp-link"
                                        >
                                            {{ form.contact_phone }}
                                        </a>
                                        <span v-else class="tp-preview-item-value text-muted">Chưa có số điện thoại</span>
                                    </div>
                                    <button
                                        v-if="form.contact_phone"
                                        type="button"
                                        class="tp-mini-copy-btn"
                                        @click="copyText(form.contact_phone, 'Số điện thoại')"
                                        title="Sao chép số"
                                    >
                                        <i class="fa fa-clone"></i>
                                    </button>
                                </div>

                                <!-- Address item -->
                                <div class="tp-preview-item">
                                    <div class="tp-preview-item-icon tp-icon-pin">
                                        <i class="fa fa-map-marker"></i>
                                    </div>
                                    <div class="tp-preview-item-info">
                                        <span class="tp-preview-item-label">Địa chỉ trụ sở</span>
                                        <span class="tp-preview-item-value">
                                            {{ form.contact_address || 'Chưa cung cấp địa chỉ' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Snippet in Card -->
                            <div v-if="form.description" class="tp-preview-desc">
                                <div class="tp-preview-desc-title">Mô tả doanh nghiệp:</div>
                                <p class="tp-preview-desc-text">{{ form.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- ECOSYSTEM INTEGRATION TIP BOX -->
                    <div class="tp-card tp-ecosystem-card">
                        <div class="tp-eco-body">
                            <div class="tp-eco-header">
                                <div class="tp-eco-icon">
                                    <i class="fa fa-sitemap"></i>
                                </div>
                                <h4 class="tp-eco-title">Hệ sinh thái dự án TaskPilot</h4>
                            </div>
                            <ul class="tp-eco-list">
                                <li>
                                    <i class="fa fa-check-circle text-primary"></i>
                                    <span>Tự động xuất hiện trong dropdown chọn khách hàng của các dự án mới.</span>
                                </li>
                                <li>
                                    <i class="fa fa-check-circle text-primary"></i>
                                    <span>Hỗ trợ phân nhóm và thống kê công việc theo từng hợp đồng khách hàng.</span>
                                </li>
                                <li>
                                    <i class="fa fa-check-circle text-primary"></i>
                                    <span>Dữ liệu liên hệ được bảo mật nội bộ và hỗ trợ truy xuất nhanh.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- FORM SUBMIT & ACTIONS CARD -->
                    <div class="tp-card tp-actions-card">
                        <div class="tp-card-body p-3">
                            <button
                                type="submit"
                                class="tp-btn-crm tp-btn-primary tp-btn-large w-100"
                                :disabled="isSubmitting"
                            >
                                <i :class="`fa ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'} me-1`"></i>
                                <span>{{ isSubmitting ? 'Đang lưu dữ liệu...' : (isEdit ? 'Lưu thay đổi khách hàng' : 'Tạo mới đối tác ngay') }}</span>
                            </button>

                            <a :href="backUrl" class="tp-btn-crm tp-btn-secondary tp-btn-large w-100 mt-2">
                                <i class="fa fa-times me-1"></i>
                                <span>Hủy bỏ &amp; Quay lại</span>
                            </a>

                            <div class="tp-action-footer-hint">
                                <i class="fa fa-keyboard-o me-1"></i> Mẹo: Bấm <strong>Ctrl + Enter</strong> để lưu nhanh
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Toast Notification -->
        <transition name="tp-toast-fade">
            <div v-if="toastMsg" class="tp-crm-toast">
                <i class="fa fa-check-circle me-2"></i>
                <span>{{ toastMsg }}</span>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    company: { type: Object, default: () => null },
    isEdit: { type: Boolean, default: false },
    actionUrl: { type: String, required: true },
    backUrl: { type: String, required: true },
    csrfToken: { type: String, default: '' },
    errors: { type: Object, default: () => ({}) },
    old: { type: Object, default: () => ({}) },
});

const form = reactive({
    name: props.old.name || props.company?.name || '',
    description: props.old.description || props.company?.description || '',
    contact_email: props.old.contact_email || props.company?.contact_email || '',
    contact_phone: props.old.contact_phone || props.company?.contact_phone || '',
    contact_address: props.old.contact_address || props.company?.contact_address || '',
    contact_note: props.old.contact_note || props.company?.contact_note || '',
});

const isSubmitting = ref(false);
const mainForm = ref(null);
const toastMsg = ref('');

// Tiers
const tiers = [
    { id: 'enterprise', name: 'Enterprise', icon: 'fa-shield', gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)' },
    { id: 'strategic', name: 'Đối tác chiến lược', icon: 'fa-star', gradient: 'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)' },
    { id: 'sme', name: 'SME / Vừa & Nhỏ', icon: 'fa-cubes', gradient: 'linear-gradient(135deg, #059669 0%, #10b981 100%)' },
    { id: 'startup', name: 'Startup / Cá nhân', icon: 'fa-rocket', gradient: 'linear-gradient(135deg, #ea580c 0%, #f97316 100%)' },
];

const selectedTier = ref('enterprise');

// Check if note or description mentions tier
if (form.contact_note.includes('[Tier: Strategic]')) selectedTier.value = 'strategic';
else if (form.contact_note.includes('[Tier: SME]')) selectedTier.value = 'sme';
else if (form.contact_note.includes('[Tier: Startup]')) selectedTier.value = 'startup';

function selectTier(tier) {
    selectedTier.value = tier.id;
}

const currentTierObj = computed(() => {
    return tiers.find(t => t.id === selectedTier.value) || tiers[0];
});

// Industries list
const industries = [
    'Phần mềm & CNTT',
    'Tài chính & Ngân hàng',
    'Viễn thông',
    'Bán lẻ & E-Commerce',
    'Bất động sản',
    'Sản xuất & Vận tải',
    'Giáo dục & Đào tạo',
    'Y tế & Dược phẩm',
];

function appendIndustry(industry) {
    if (!form.description) {
        form.description = `Lĩnh vực: ${industry}.`;
    } else if (!form.description.includes(industry)) {
        form.description += ` | ${industry}`;
    }
    showToast(`Đã thêm lĩnh vực "${industry}" vào mô tả.`);
}

// Compute initials for avatar
const companyInitials = computed(() => {
    if (!form.name || !form.name.trim()) return 'TP';
    const words = form.name.trim().split(/\s+/);
    if (words.length === 1) return words[0].substring(0, 2).toUpperCase();
    return (words[0][0] + words[words.length - 1][0]).toUpperCase();
});

// Dynamic avatar gradient depending on initials
const avatarGradient = computed(() => {
    return currentTierObj.value.gradient;
});

function copyText(val, label) {
    if (!val) return;
    navigator.clipboard.writeText(val);
    showToast(`Đã sao chép ${label}: ${val}`);
}

function showToast(msg) {
    toastMsg.value = msg;
    setTimeout(() => {
        if (toastMsg.value === msg) toastMsg.value = '';
    }, 2800);
}

function submitForm() {
    if (mainForm.value) {
        mainForm.value.requestSubmit();
    }
}

function onFormSubmit() {
    isSubmitting.value = true;
}

// Keyboard shortcut: Ctrl + Enter to submit
function handleKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        submitForm();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
/* CONTAINER & RESET */
.company-form-container {
    width: 100%;
    max-width: 1240px;
    margin: 0 auto;
    padding: 16px 20px 60px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    box-sizing: border-box;
}

/* HEADER SECTION */
.tp-crm-head {
    margin-bottom: 24px;
}

.tp-crm-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 12px;
}

.tp-crumb-link {
    color: #64748b;
    text-decoration: none;
    transition: color 0.15s ease;
}

.tp-crumb-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.tp-crumb-sep {
    color: #cbd5e1;
}

.tp-crumb-current {
    color: #0f172a;
    font-weight: 600;
}

.tp-crm-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.tp-crm-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 9999px;
    padding: 3px 12px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.tp-crm-heading {
    margin: 0 0 6px;
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    line-height: 1.25;
}

.tp-crm-subheading {
    margin: 0;
    font-size: 13.5px;
    color: #64748b;
    max-width: 720px;
    line-height: 1.5;
}

.tp-crm-title-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* BUTTONS */
.tp-btn-crm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.tp-btn-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff !important;
    border-color: #1d4ed8;
}

.tp-btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transform: translateY(-1px);
}

.tp-btn-secondary {
    background: #ffffff;
    color: #334155 !important;
    border-color: #cbd5e1;
}

.tp-btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a !important;
}

.tp-btn-large {
    padding: 12px 20px;
    font-size: 14px;
}

/* MAIN LAYOUT GRID */
.tp-crm-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
}

.tp-crm-main-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tp-crm-side-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
    position: sticky;
    top: 20px;
}

/* CARDS */
.tp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
    overflow: hidden;
    transition: border-color 0.2s;
}

.tp-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.tp-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.tp-card-icon-blue {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.tp-card-icon-emerald {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.tp-card-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}

.tp-card-subtitle {
    margin: 2px 0 0;
    font-size: 12px;
    color: #64748b;
}

.tp-card-body {
    padding: 24px;
}

/* FORM FIELDS */
.tp-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tp-field-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.tp-label {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin: 0;
}

.tp-required {
    color: #ef4444;
    margin-left: 2px;
}

.tp-counter {
    font-size: 11px;
    color: #94a3b8;
}

.tp-hint-label {
    font-size: 11.5px;
    color: #64748b;
}

.tp-sub-action-link {
    font-size: 11.5px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.tp-sub-action-link:hover {
    text-decoration: underline;
}

.tp-input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.tp-field-icon {
    position: absolute;
    left: 14px;
    font-size: 14px;
    color: #94a3b8;
    pointer-events: none;
}

.tp-input {
    width: 100%;
    height: 44px;
    padding: 0 14px 0 40px;
    font-size: 13.5px;
    color: #0f172a;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
    outline: none;
    transition: all 0.15s ease;
}

.tp-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.tp-input.is-invalid {
    border-color: #ef4444;
    background: #fef2f2;
}

.tp-textarea {
    width: 100%;
    padding: 12px 14px;
    font-size: 13.5px;
    color: #0f172a;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
    outline: none;
    resize: vertical;
    line-height: 1.5;
    transition: all 0.15s ease;
}

.tp-textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.tp-note-textarea {
    background: #fffdfa;
    border-color: #fed7aa;
}

.tp-note-textarea:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}

.tp-error-text {
    font-size: 12px;
    color: #dc2626;
    margin-top: 3px;
    display: flex;
    align-items: center;
}

.tp-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* TIER SELECTOR CHIPS */
.tp-tier-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tp-tier-chip {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.tp-tier-chip:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #1e293b;
}

.tp-tier-chip.active {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
}

/* INDUSTRY QUICK TAGS */
.tp-industry-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.tp-industry-tag {
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 500;
    color: #475569;
    background: #f1f5f9;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.tp-industry-tag:hover {
    background: #e2e8f0;
    color: #0f172a;
    border-style: solid;
}

/* CLIENT PREVIEW CARD (SIDEBAR) */
.tp-client-card-preview {
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
}

.tp-preview-header {
    height: 72px;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    position: relative;
    padding: 12px 16px;
    display: flex;
    justify-content: flex-end;
}

.tp-preview-tier-badge {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 9999px;
    height: fit-content;
}

.tp-preview-content {
    padding: 0 20px 20px;
    position: relative;
    text-align: center;
}

.tp-preview-avatar-wrap {
    margin-top: -38px;
    margin-bottom: 12px;
    display: flex;
    justify-content: center;
}

.tp-preview-avatar {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    color: #ffffff;
    font-size: 24px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    letter-spacing: -0.5px;
}

.tp-preview-name {
    margin: 0 0 4px;
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    word-break: break-word;
}

.tp-preview-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: #059669;
    font-weight: 600;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    padding: 2px 10px;
    border-radius: 9999px;
    margin-bottom: 16px;
}

.tp-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.tp-preview-contact-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
    text-align: left;
}

.tp-preview-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}

.tp-preview-item:last-child {
    border-bottom: none;
}

.tp-preview-item-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.tp-icon-mail {
    background: #eff6ff;
    color: #2563eb;
}

.tp-icon-phone {
    background: #ecfdf5;
    color: #059669;
}

.tp-icon-pin {
    background: #fef2f2;
    color: #dc2626;
}

.tp-preview-item-info {
    flex: 1;
    min-width: 0;
}

.tp-preview-item-label {
    display: block;
    font-size: 10.5px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.tp-preview-item-value {
    font-size: 12.5px;
    color: #1e293b;
    font-weight: 500;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tp-preview-item-value.tp-link {
    color: #2563eb;
    text-decoration: none;
}

.tp-preview-item-value.tp-link:hover {
    text-decoration: underline;
}

.tp-mini-copy-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 12px;
    padding: 4px 6px;
    border-radius: 4px;
    transition: all 0.15s;
}

.tp-mini-copy-btn:hover {
    color: #2563eb;
    background: #eff6ff;
}

.tp-preview-desc {
    margin-top: 14px;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: left;
}

.tp-preview-desc-title {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.tp-preview-desc-text {
    margin: 0;
    font-size: 12px;
    color: #334155;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ECOSYSTEM CARD */
.tp-ecosystem-card {
    background: #faf5ff;
    border-color: #e9d5ff;
}

.tp-eco-body {
    padding: 18px 20px;
}

.tp-eco-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.tp-eco-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #f3e8ff;
    color: #9333ea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.tp-eco-title {
    margin: 0;
    font-size: 13.5px;
    font-weight: 700;
    color: #6b21a8;
}

.tp-eco-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tp-eco-list li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 12px;
    color: #4c1d95;
    line-height: 1.4;
}

.tp-eco-list li i {
    margin-top: 2px;
    font-size: 12px;
}

/* ACTIONS CARD */
.tp-actions-card {
    border-color: #cbd5e1;
}

.tp-action-footer-hint {
    margin-top: 10px;
    text-align: center;
    font-size: 11.5px;
    color: #64748b;
}

/* TOAST */
.tp-crm-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #0f172a;
    color: #ffffff;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
}

.tp-toast-fade-enter-active,
.tp-toast-fade-leave-active {
    transition: all 0.25s ease;
}

.tp-toast-fade-enter-from,
.tp-toast-fade-leave-to {
    opacity: 0;
    transform: translateY(12px);
}

/* DARK MODE SUPPORT */
:global(.tp-theme-dark) .company-form-container {
    color: #f1f5f9;
}

:global(.tp-theme-dark) .tp-crm-heading {
    color: #f8fafc;
}

:global(.tp-theme-dark) .tp-crm-subheading {
    color: #94a3b8;
}

:global(.tp-theme-dark) .tp-card {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

:global(.tp-theme-dark) .tp-card-header {
    background: #0f172a;
    border-color: #334155;
}

:global(.tp-theme-dark) .tp-card-title {
    color: #f8fafc;
}

:global(.tp-theme-dark) .tp-label {
    color: #cbd5e1;
}

:global(.tp-theme-dark) .tp-input,
:global(.tp-theme-dark) .tp-textarea {
    background: #0f172a;
    border-color: #334155;
    color: #f8fafc;
}

:global(.tp-theme-dark) .tp-input:focus,
:global(.tp-theme-dark) .tp-textarea:focus {
    border-color: #3b82f6;
    background: #1e293b;
}

:global(.tp-theme-dark) .tp-tier-chip {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}

:global(.tp-theme-dark) .tp-tier-chip.active {
    background: #1e3a8a;
    border-color: #3b82f6;
    color: #93c5fd;
}

:global(.tp-theme-dark) .tp-industry-tag {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}

:global(.tp-theme-dark) .tp-preview-name {
    color: #f8fafc;
}

:global(.tp-theme-dark) .tp-preview-contact-box {
    background: #0f172a;
    border-color: #334155;
}

:global(.tp-theme-dark) .tp-preview-item {
    border-color: #1e293b;
}

:global(.tp-theme-dark) .tp-preview-item-value {
    color: #f1f5f9;
}

:global(.tp-theme-dark) .tp-preview-desc {
    background: #0f172a;
}

:global(.tp-theme-dark) .tp-preview-desc-text {
    color: #cbd5e1;
}

:global(.tp-theme-dark) .tp-ecosystem-card {
    background: #1e1b4b;
    border-color: #3730a3;
}

:global(.tp-theme-dark) .tp-eco-title {
    color: #c084fc;
}

:global(.tp-theme-dark) .tp-eco-list li {
    color: #e9d5ff;
}

:global(.tp-theme-dark) .tp-btn-secondary {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0 !important;
}

:global(.tp-theme-dark) .tp-btn-secondary:hover {
    background: #334155;
    color: #ffffff !important;
}

/* RESPONSIVE */
@media (max-width: 992px) {
    .tp-crm-layout {
        grid-template-columns: 1fr;
    }
    .tp-crm-side-col {
        position: static;
    }
}

@media (max-width: 640px) {
    .tp-crm-heading {
        font-size: 20px;
    }
    .tp-grid-2 {
        grid-template-columns: 1fr;
    }
    .tp-card-header {
        padding: 14px 16px;
    }
    .tp-card-body {
        padding: 16px;
    }
}
</style>
