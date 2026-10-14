<template>
    <div class="project-roles-vue">

        <!-- =========================================
             PAGE HEADER
        ========================================== -->

        <div class="roles-page-header">
            <div class="roles-page-heading">
                <h1>Vai trò & quyền truy cập</h1>

                <p>
                    Quản lý quyền của thành viên trong
                    <strong>{{ project.name }}</strong>
                </p>
            </div>

            <div class="roles-header-actions">
                <a
                    :href="`/projects/${project.id}`"
                    class="btn btn-default"
                >
                    <i class="fa fa-arrow-left"></i>

                    <span>Quay lại</span>
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    @click="openCreateDrawer"
                >
                    <i class="fa fa-plus"></i>

                    <span>Tạo vai trò</span>
                </button>
            </div>
        </div>


        <!-- =========================================
             ALERT
        ========================================== -->

        <div
            v-if="successMessage"
            class="roles-alert roles-alert-success"
        >
            <i class="fa fa-check-circle"></i>

            <span>
                {{ successMessage }}
            </span>
        </div>

        <div
            v-if="errorMessage"
            class="roles-alert roles-alert-danger"
        >
            <i class="fa fa-exclamation-circle"></i>

            <span>
                {{ errorMessage }}
            </span>
        </div>


        <!-- =========================================
             WORKSPACE
        ========================================== -->

        <section class="roles-workspace">

            <!-- =====================================
                 SIDEBAR
            ====================================== -->

            <aside class="roles-sidebar">

                <div class="roles-sidebar-header">
                    <div class="roles-sidebar-title">
                        <i class="fa fa-shield"></i>

                        <strong>Vai trò</strong>

                        <span class="roles-count">
                            {{ roles.length }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="roles-add-small"
                        title="Tạo vai trò"
                        @click="openCreateDrawer"
                    >
                        <i class="fa fa-plus"></i>
                    </button>
                </div>


                <!-- ROLE LIST -->

                <div
                    v-if="roles.length"
                    class="roles-list"
                >
                    <button
                        v-for="role in roles"
                        :key="role.id"
                        type="button"
                        class="role-item"
                        :class="{
                            active: selectedRole?.id === role.id
                        }"
                        @click="selectRole(role)"
                    >
                        <span class="role-icon">
                            <i class="fa fa-shield"></i>
                        </span>

                        <span class="role-item-content">
                            <strong>
                                {{ role.name }}
                            </strong>

                            <small>
                                {{
                                    (role.permissions || []).length
                                }}
                                quyền được cấp
                            </small>
                        </span>

                        <i
                            class="fa fa-chevron-right role-arrow"
                        ></i>
                    </button>
                </div>


                <!-- EMPTY SIDEBAR -->

                <div
                    v-else
                    class="roles-empty-sidebar"
                >
                    <div class="roles-empty-icon">
                        <i class="fa fa-shield"></i>
                    </div>

                    <strong>
                        Chưa có vai trò
                    </strong>

                    <span>
                        Tạo vai trò đầu tiên cho dự án để bắt đầu phân quyền.
                    </span>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        @click="openCreateDrawer"
                    >
                        <i class="fa fa-plus"></i>

                        <span>Tạo vai trò</span>
                    </button>
                </div>

            </aside>


            <!-- =====================================
                 MAIN CONTENT
            ====================================== -->

            <main class="roles-content">

                <!-- ROLE SELECTED -->

                <template v-if="selectedRole">

                    <!-- ROLE HEADER -->

                    <div class="role-detail-header">

                        <div class="role-detail-title">

                            <div class="role-detail-icon">
                                <i class="fa fa-shield"></i>
                            </div>

                            <div>
                                <span class="role-detail-eyebrow">
                                    PROJECT ROLE
                                </span>

                                <h2>
                                    {{ selectedRole.name }}
                                </h2>

                                <p>
                                    {{
                                        selectedRole.permissions?.length || 0
                                    }}
                                    quyền được cấp
                                </p>
                            </div>

                        </div>


                        <div class="role-detail-actions">

                            <button
                                type="button"
                                class="btn btn-default"
                                @click="openEditDrawer(selectedRole)"
                            >
                                <i class="fa fa-pencil"></i>

                                <span>Sửa</span>
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-danger"
                                @click="deleteRole(selectedRole)"
                            >
                                <i class="fa fa-trash"></i>

                                <span>Xóa</span>
                            </button>

                        </div>

                    </div>


                    <!-- PERMISSION TOOLBAR -->

                    <div class="permission-toolbar">

                        <div>
                            <strong>
                                Quyền truy cập
                            </strong>

                            <span>
                                Các quyền mà vai trò này có thể thực hiện
                            </span>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-default"
                            @click="openEditDrawer(selectedRole)"
                        >
                            <i class="fa fa-pencil"></i>

                            <span>Chỉnh sửa quyền</span>
                        </button>

                    </div>


                    <!-- PERMISSION MATRIX -->

                    <div class="permission-matrix">

                        <div
                            v-for="group in permissionGroups"
                            :key="group.key"
                            class="permission-section"
                        >

                            <!-- GROUP HEADER -->

                            <div class="permission-section-header">

                                <div class="permission-group-icon">
                                    <i :class="group.icon"></i>
                                </div>

                                <div class="permission-section-info">

                                    <strong>
                                        {{ group.label }}
                                    </strong>

                                    <span>
                                        {{
                                            countGroupPermissions(group)
                                        }}
                                        / {{ group.permissions.length }}
                                        quyền
                                    </span>

                            </div>

                        </div>


                        <!-- PERMISSION ROWS -->

                        <div
                            v-for="permission in group.permissions"
                            :key="permission"
                            class="permission-row"
                        >
                                <span class="permission-name">
                                    {{
                                        permissionLabels[permission] ||
                                        permission
                                    }}
                                </span>

                                <label class="permission-checkbox permission-row-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="hasPermission(permission)"
                                        @change="toggleRolePermission(group, permission)"
                                        class="permission-native-input"
                                    >

                                    <span
                                        class="permission-box"
                                        aria-hidden="true"
                                    ></span>
                                </label>
                            </div>

                        </div>

                    </div>

                </template>


                <!-- NO ROLE SELECTED -->

                <div
                    v-else
                    class="roles-content-empty"
                >
                    <div class="roles-content-empty-inner">

                        <div class="roles-content-empty-icon">
                            <i class="fa fa-shield"></i>
                        </div>

                        <h2>
                            Chọn một vai trò
                        </h2>

                        <p>
                            Chọn vai trò bên trái để xem quyền truy cập.
                        </p>

                    </div>
                </div>

            </main>

        </section>


        <!-- =========================================
             DRAWER
        ========================================== -->

        <div
            v-if="drawerOpen"
            class="drawer-overlay"
            @click.self="closeDrawer"
        >

            <aside class="role-drawer">

                <!-- DRAWER HEADER -->

                <div class="drawer-header">

                    <div>
                        <span class="drawer-eyebrow">
                            {{
                                editingRole
                                    ? 'EDIT ROLE'
                                    : 'NEW ROLE'
                            }}
                        </span>

                        <h2>
                            {{
                                editingRole
                                    ? 'Sửa vai trò'
                                    : 'Tạo vai trò'
                            }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="drawer-close"
                        aria-label="Đóng"
                        @click="closeDrawer"
                    >
                        &times;
                    </button>

                </div>


                <!-- DRAWER BODY -->

                <div class="drawer-body">

                    <!-- ROLE NAME -->

                    <div class="drawer-field">

                        <label for="role-name">
                            Tên vai trò
                        </label>

                        <input
                            id="role-name"
                            v-model="roleForm.name"
                            type="text"
                            class="form-control"
                            :class="{
                                'is-invalid': formErrors.name
                            }"
                            placeholder="Ví dụ: Developer, QA, Designer..."
                            autocomplete="off"
                        >

                        <div
                            v-if="formErrors.name"
                            class="field-error"
                        >
                            {{ formErrors.name[0] }}
                        </div>

                    </div>


                    <!-- PERMISSIONS -->

                    <div class="drawer-field permissions-field">

                        <!-- PERMISSION HEADING -->

                        <div class="permission-heading">

                            <div>
                                <label>
                                    Quyền truy cập
                                </label>

                                <small>
                                    Chọn những gì vai trò được phép thực hiện.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="permission-select-all"
                                @click="toggleAllPermissions"
                            >
                                {{
                                    allPermissionsSelected
                                        ? 'Bỏ chọn tất cả'
                                        : 'Chọn tất cả'
                                }}
                            </button>

                        </div>


                        <!-- SEARCH -->

                        <div class="drawer-permission-search">

                            <i class="fa fa-search"></i>

                            <input
                                v-model="permissionSearch"
                                type="text"
                                placeholder="Tìm quyền..."
                                autocomplete="off"
                            >

                        </div>


                        <!-- GROUPS -->

                        <div class="drawer-permission-groups">

                            <div
                                v-for="group in filteredGroups"
                                :key="group.key"
                                class="drawer-permission-group"
                            >

                                <!-- GROUP HEADER -->

                                <div class="drawer-group-header">

                                    <div class="drawer-group-title">

                                        <span class="drawer-group-icon">
                                            <i :class="group.icon"></i>
                                        </span>

                                        <strong>
                                            {{ group.label }}
                                        </strong>

                                        <span class="drawer-group-count">
                                            {{
                                                countSelectedInGroup(group)
                                            }}/{{ group.permissions.length }}
                                        </span>

                                    </div>

                                    <button
                                        type="button"
                                        class="group-toggle"
                                        @click="toggleGroup(group)"
                                    >
                                        {{
                                            isGroupSelected(group)
                                                ? 'Bỏ chọn'
                                                : 'Chọn nhóm'
                                        }}
                                    </button>

                                </div>


                                <!-- PERMISSION CHECKBOX -->

                                <label
                                    v-for="permission in group.permissions"
                                    :key="permission"
                                    class="permission-checkbox"
                                >

                                    <!--
                                        INPUT NATIVE:
                                        Chỉ dùng cho v-model.
                                        Được ẩn hoàn toàn bằng CSS.
                                    -->

                                    <input
                                        v-model="roleForm.permissions"
                                        type="checkbox"
                                        :value="permission"
                                        class="permission-native-input"
                                        style="
                                            position: absolute;
                                            width: 1px;
                                            height: 1px;
                                            margin: -1px;
                                            padding: 0;
                                            overflow: hidden;
                                            clip: rect(0, 0, 0, 0);
                                            white-space: nowrap;
                                            border: 0;
                                            opacity: 0;
                                        "
                                    >

                                    <!--
                                        CHECKBOX HIỂN THỊ:
                                        Đây là ô duy nhất người dùng nhìn thấy.
                                    -->

                                    <span
                                        class="permission-box"
                                        aria-hidden="true"
                                    ></span>

                                    <span class="permission-checkbox-label">
                                        {{
                                            permissionLabels[permission] ||
                                            permission
                                        }}
                                    </span>

                                </label>

                            </div>


                            <!-- NO RESULT -->

                            <div
                                v-if="!filteredGroups.length"
                                class="permissions-not-found"
                            >
                                <i class="fa fa-search"></i>

                                <span>
                                    Không tìm thấy quyền phù hợp.
                                </span>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- DRAWER FOOTER -->

                <div class="drawer-footer">

                    <button
                        type="button"
                        class="btn btn-default"
                        :disabled="saving"
                        @click="closeDrawer"
                    >
                        Hủy
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary"
                        :disabled="
                            saving ||
                            !roleForm.name.trim()
                        "
                        @click="saveRole"
                    >

                        <i
                            v-if="saving"
                            class="fa fa-spinner fa-spin"
                        ></i>

                        <i
                            v-else
                            class="fa fa-check"
                        ></i>

                        {{
                            saving
                                ? 'Đang lưu...'
                                : editingRole
                                    ? 'Lưu thay đổi'
                                    : 'Tạo vai trò'
                        }}

                    </button>

                </div>

            </aside>

        </div>

    </div>
</template>
<script setup>
import {
    computed,
    onMounted,
    reactive,
    ref,
} from 'vue';


/* =========================================
   PROPS
========================================= */

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },

    roles: {
        type: Array,
        default: () => [],
    },

    csrfToken: {
        type: String,
        default: '',
    },
});


/* =========================================
   STATE
========================================= */

const roles = ref([
    ...props.roles,
]);

const permissionGroups = ref([]);

const permissionLabels = ref({});

const selectedRole = ref(null);

const drawerOpen = ref(false);

const editingRole = ref(null);

const saving = ref(false);

const successMessage = ref('');

const errorMessage = ref('');

const formErrors = ref({});

const permissionSearch = ref('');


/* =========================================
   FORM
========================================= */

const roleForm = reactive({
    name: '',
    permissions: [],
});


/* =========================================
   FALLBACK PERMISSION GROUPS
========================================= */

const FALLBACK_GROUPS = [
    {
        key: 'project',
        label: 'Dự án',
        icon: 'fa fa-briefcase',
        permissions: [
            'project.view',
            'project.edit',
            'project.manage',
            'project.archive',
            'project.delete',
            'project.settings',
        ],
    },

    {
        key: 'task',
        label: 'Công việc',
        icon: 'fa fa-check-circle',
        permissions: [
            'task.view',
            'task.create',
            'task.edit',
            'task.delete',
            'task.assign',
            'task.status',
            'task.priority',
            'task.label',
            'task.manage',
        ],
    },

    {
        key: 'member',
        label: 'Thành viên',
        icon: 'fa fa-users',
        permissions: [
            'member.view',
            'member.invite',
            'member.edit',
            'member.remove',
            'member.role',
        ],
    },

    {
        key: 'document',
        label: 'Tài liệu',
        icon: 'fa fa-file-alt',
        permissions: [
            'document.view',
            'document.create',
            'document.edit',
            'document.delete',
            'document.manage',
        ],
    },

    {
        key: 'meeting',
        label: 'Cuộc họp',
        icon: 'fa fa-video',
        permissions: [
            'meeting.view',
            'meeting.create',
            'meeting.edit',
            'meeting.delete',
            'meeting.manage',
        ],
    },

    {
        key: 'comment',
        label: 'Bình luận',
        icon: 'fa fa-comment',
        permissions: [
            'comment.view',
            'comment.create',
            'comment.edit',
            'comment.delete',
        ],
    },

    {
        key: 'file',
        label: 'Tệp',
        icon: 'fa fa-paperclip',
        permissions: [
            'file.upload',
            'file.download',
            'file.delete',
        ],
    },

    {
        key: 'report',
        label: 'Báo cáo',
        icon: 'fa fa-chart-bar',
        permissions: [
            'report.view',
            'report.export',
        ],
    },
];


/* =========================================
   FALLBACK LABELS
========================================= */

const FALLBACK_LABELS = {
    'project.view': 'Xem dự án',
    'project.edit': 'Chỉnh sửa thông tin dự án',
    'project.manage': 'Quản lý dự án',
    'project.archive': 'Lưu trữ dự án',
    'project.delete': 'Xóa dự án',
    'project.settings': 'Quản lý cài đặt dự án',

    'task.view': 'Xem công việc',
    'task.create': 'Tạo công việc',
    'task.edit': 'Chỉnh sửa công việc',
    'task.delete': 'Xóa công việc',
    'task.assign': 'Phân công công việc',
    'task.status': 'Thay đổi trạng thái',
    'task.priority': 'Thay đổi độ ưu tiên',
    'task.label': 'Quản lý nhãn công việc',
    'task.manage': 'Quản lý toàn bộ công việc',

    'member.view': 'Xem thành viên',
    'member.invite': 'Mời thành viên',
    'member.edit': 'Chỉnh sửa thành viên',
    'member.remove': 'Xóa thành viên',
    'member.role': 'Quản lý vai trò thành viên',

    'document.view': 'Xem tài liệu',
    'document.create': 'Tạo tài liệu',
    'document.edit': 'Chỉnh sửa tài liệu',
    'document.delete': 'Xóa tài liệu',
    'document.manage': 'Quản lý toàn bộ tài liệu',

    'meeting.view': 'Xem cuộc họp',
    'meeting.create': 'Tạo cuộc họp',
    'meeting.edit': 'Chỉnh sửa cuộc họp',
    'meeting.delete': 'Xóa cuộc họp',
    'meeting.manage': 'Quản lý toàn bộ cuộc họp',

    'comment.view': 'Xem bình luận',
    'comment.create': 'Tạo bình luận',
    'comment.edit': 'Chỉnh sửa bình luận',
    'comment.delete': 'Xóa bình luận',

    'file.upload': 'Tải tệp lên',
    'file.download': 'Tải tệp xuống',
    'file.delete': 'Xóa tệp',

    'report.view': 'Xem báo cáo',
    'report.export': 'Xuất báo cáo',
};


/* =========================================
   COMPUTED
========================================= */

const allPermissions = computed(() => {
    return permissionGroups.value.flatMap(
        group => group.permissions
    );
});


const allPermissionsSelected = computed(() => {
    if (!allPermissions.value.length) {
        return false;
    }

    return allPermissions.value.every(
        permission =>
            roleForm.permissions.includes(permission)
    );
});


const filteredGroups = computed(() => {
    const query = permissionSearch.value
        .trim()
        .toLowerCase();

    if (!query) {
        return permissionGroups.value;
    }

    return permissionGroups.value
        .map(group => {
            const permissions =
                group.permissions.filter(permission => {
                    const label =
                        permissionLabels.value[permission] ||
                        permission;

                    return (
                        label.toLowerCase().includes(query) ||
                        permission.toLowerCase().includes(query)
                    );
                });

            return {
                ...group,
                permissions,
            };
        })
        .filter(
            group =>
                group.permissions.length > 0
        );
});


/* =========================================
   INIT
========================================= */

onMounted(async () => {
    await loadPermissions();

    if (roles.value.length) {
        selectedRole.value = roles.value[0];
    }
});


/* =========================================
   LOAD PERMISSIONS
========================================= */

async function loadPermissions() {
    try {
        const response = await fetch(
            '/projects/role-permissions',
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            }
        );

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status}`
            );
        }

        const data =
            await response.json();

        permissionGroups.value =
            data.groups || FALLBACK_GROUPS;

        permissionLabels.value =
            data.labels || FALLBACK_LABELS;

    } catch (error) {
        console.error(
            'Failed to load role permissions:',
            error
        );

        permissionGroups.value =
            FALLBACK_GROUPS;

        permissionLabels.value =
            FALLBACK_LABELS;

        errorMessage.value =
            'Không thể tải danh sách quyền từ máy chủ. Đang dùng danh sách mặc định.';
    }
}


/* =========================================
   ROLE SELECTION
========================================= */

function selectRole(role) {
    selectedRole.value = role;

    clearMessages();
}


/* =========================================
   CREATE DRAWER
========================================= */

function openCreateDrawer() {
    editingRole.value = null;

    roleForm.name = '';

    roleForm.permissions = [];

    permissionSearch.value = '';

    formErrors.value = {};

    clearMessages();

    drawerOpen.value = true;
}


/* =========================================
   EDIT DRAWER
========================================= */

function openEditDrawer(role) {
    editingRole.value = role;

    roleForm.name =
        role.name || '';

    roleForm.permissions = [
        ...(role.permissions || []),
    ];

    permissionSearch.value = '';

    formErrors.value = {};

    clearMessages();

    drawerOpen.value = true;
}


/* =========================================
   CLOSE DRAWER
========================================= */

function closeDrawer() {
    if (saving.value) {
        return;
    }

    drawerOpen.value = false;

    editingRole.value = null;

    roleForm.name = '';

    roleForm.permissions = [];

    permissionSearch.value = '';

    formErrors.value = {};
}


/* =========================================
   PERMISSION HELPERS
========================================= */

function toggleRolePermission(group, permission) {
    if (!selectedRole.value) {
        return;
    }

    const currentPermissions =
        selectedRole.value.permissions || [];

    const index =
        currentPermissions.indexOf(permission);

    let newPermissions;

    if (index === -1) {
        newPermissions = [...currentPermissions, permission];
    } else {
        newPermissions = currentPermissions.filter(
            p => p !== permission
        );
    }

    selectedRole.value.permissions = newPermissions;

    saveRolePermissions(newPermissions);
}


async function saveRolePermissions(permissions) {
    if (!selectedRole.value) {
        return;
    }

    const form = new FormData();

    form.append(
        '_token',
        props.csrfToken
    );

    form.append(
        'permissions[]',
        ...permissions
    );


    form.append(
        '_method',
        'PUT'
    );

    try {
        const response = await fetch(
            `/projects/${props.project.id}/roles/${selectedRole.value.id}`,
            {
                method: 'POST',

                headers: {
                    Accept: 'application/json',

                    'X-Requested-With':
                        'XMLHttpRequest',
                },

                body: form,
            }
        );

        if (!response.ok) {
            const data = await response.json();

            errorMessage.value =
                data.message ||
                'Không thể cập nhật quyền.';

            return;
        }

        successMessage.value = 'Đã cập nhật quyền.';

        setTimeout(() => {
            successMessage.value = '';
        }, 3000);
    } catch (error) {
        console.error(
            'saveRolePermissions error:',
            error
        );

        errorMessage.value =
            'Lỗi kết nối. Vui lòng thử lại.';
    }
}


function hasPermission(permission) {
    return (
        selectedRole.value?.permissions || []
    ).includes(permission);
}


function countGroupPermissions(group) {
    return group.permissions.filter(
        permission =>
            hasPermission(permission)
    ).length;
}


function countSelectedInGroup(group) {
    return group.permissions.filter(
        permission =>
            roleForm.permissions.includes(permission)
    ).length;
}


/* =========================================
   GROUP SELECTION
========================================= */

function isGroupSelected(group) {
    if (!group.permissions.length) {
        return false;
    }

    return group.permissions.every(
        permission =>
            roleForm.permissions.includes(permission)
    );
}


function toggleGroup(group) {
    const selected =
        isGroupSelected(group);

    if (selected) {
        roleForm.permissions =
            roleForm.permissions.filter(
                permission =>
                    !group.permissions.includes(permission)
            );

        return;
    }

    const permissions =
        new Set(
            roleForm.permissions
        );

    group.permissions.forEach(
        permission => {
            permissions.add(permission);
        }
    );

    roleForm.permissions = [
        ...permissions,
    ];
}


/* =========================================
   ALL PERMISSIONS
========================================= */

function toggleAllPermissions() {
    if (allPermissionsSelected.value) {
        roleForm.permissions = [];

        return;
    }

    roleForm.permissions = [
        ...allPermissions.value,
    ];
}


/* =========================================
   SAVE ROLE
========================================= */

async function saveRole() {
    clearMessages();

    formErrors.value = {};

    const roleName =
        roleForm.name.trim();

    if (!roleName) {
        formErrors.value = {
            name: [
                'Vui lòng nhập tên vai trò.',
            ],
        };

        return;
    }

    saving.value = true;

    try {
        const form =
            new FormData();

        form.append(
            '_token',
            props.csrfToken
        );

        form.append(
            'name',
            roleName
        );

        roleForm.permissions.forEach(
            permission => {
                form.append(
                    'permissions[]',
                    permission
                );
            }
        );


        let url =
            `/projects/${props.project.id}/roles`;


        if (editingRole.value) {
            url +=
                `/${editingRole.value.id}`;

            form.append(
                '_method',
                'PUT'
            );
        }


        const response =
            await fetch(
                url,
                {
                    method: 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    body: form,
                }
            );


        const data =
            await response.json();


        if (!response.ok) {
            formErrors.value =
                data.errors || {};

            errorMessage.value =
                data.message ||
                'Không thể lưu vai trò.';

            return;
        }


        if (editingRole.value) {
            const index =
                roles.value.findIndex(
                    role =>
                        role.id ===
                        editingRole.value.id
                );


            if (index !== -1) {
                roles.value[index] =
                    data.role;
            }


            selectedRole.value =
                data.role;

            successMessage.value =
                'Đã cập nhật vai trò.';

        } else {
            roles.value.push(
                data.role
            );

            selectedRole.value =
                data.role;

            successMessage.value =
                'Đã tạo vai trò mới.';
        }


        closeDrawer();


        setTimeout(() => {
            successMessage.value = '';
        }, 3000);

    } catch (error) {
        console.error(
            'saveRole error:',
            error
        );

        errorMessage.value =
            'Lỗi kết nối. Vui lòng thử lại.';

    } finally {
        saving.value = false;
    }
}


/* =========================================
   DELETE ROLE
========================================= */

async function deleteRole(role) {
    const confirmed =
        confirm(
            `Bạn có chắc muốn xóa vai trò "${role.name}"?`
        );

    if (!confirmed) {
        return;
    }

    clearMessages();

    try {
        const form =
            new FormData();

        form.append(
            '_token',
            props.csrfToken
        );

        form.append(
            '_method',
            'DELETE'
        );


        const response =
            await fetch(
                `/projects/${props.project.id}/roles/${role.id}`,
                {
                    method: 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    body: form,
                }
            );


        const data =
            await response.json();


        if (!response.ok) {
            errorMessage.value =
                data.message ||
                'Không thể xóa vai trò.';

            return;
        }


        roles.value =
            roles.value.filter(
                item =>
                    item.id !== role.id
            );


        if (
            selectedRole.value?.id ===
            role.id
        ) {
            selectedRole.value =
                roles.value[0] || null;
        }


        successMessage.value =
            'Đã xóa vai trò.';


        setTimeout(() => {
            successMessage.value = '';
        }, 3000);

    } catch (error) {
        console.error(
            'deleteRole error:',
            error
        );

        errorMessage.value =
            'Lỗi kết nối. Vui lòng thử lại.';
    }
}


/* =========================================
   MESSAGES
========================================= */

function clearMessages() {
    successMessage.value = '';

    errorMessage.value = '';
}

function hasFormPermission(permission) {
    return roleForm.permissions.includes(permission);
}

function togglePermission(permission) {
    if (hasFormPermission(permission)) {
        roleForm.permissions = roleForm.permissions.filter(
            item => item !== permission
        );
    } else {
        roleForm.permissions.push(permission);
    }
}

</script>
<style scoped>

/* =====================================================
   PAGE
===================================================== */

.project-roles-vue {
    width: 100%;
    min-width: 0;
    color: #172033;
}


/* =====================================================
   HEADER
===================================================== */

.roles-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.roles-page-heading {
    min-width: 0;
}

.roles-page-header h1 {
    margin: 0;
    color: #172033;
    font-size: 28px;
    line-height: 1.25;
    font-weight: 700;
}

.roles-page-header p {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.5;
}

.roles-page-header p strong {
    color: #334155;
}

.roles-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 0 0 auto;
}


/* =====================================================
   BUTTON
===================================================== */

.btn {
    min-height: 38px;
    padding: 8px 14px;

    border-radius: 7px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    font-size: 13px;
    font-weight: 600;

    text-decoration: none;

    cursor: pointer;

    box-sizing: border-box;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-sm {
    min-height: 34px;
    padding: 6px 10px;
    font-size: 12px;
}


/* =====================================================
   ALERT
===================================================== */

.roles-alert {
    min-height: 42px;

    margin-bottom: 16px;
    padding: 10px 13px;

    border-radius: 7px;

    display: flex;
    align-items: center;
    gap: 9px;

    box-sizing: border-box;

    font-size: 13px;
    font-weight: 500;
}

.roles-alert-success {
    border: 1px solid #bbf7d0;
    background: #f0fdf4;
    color: #166534;
}

.roles-alert-danger {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}


/* =====================================================
   WORKSPACE
===================================================== */

.roles-workspace {
    min-height: 600px;

    display: grid;
    grid-template-columns: 270px minmax(0, 1fr);

    border: 1px solid #dbe2ea;
    border-radius: 10px;

    overflow: hidden;

    background: #fff;
}


/* =====================================================
   SIDEBAR
===================================================== */

.roles-sidebar {
    min-width: 0;

    border-right: 1px solid #dbe2ea;

    background: #f8fafc;
}

.roles-sidebar-header {
    height: 62px;

    padding: 0 14px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-bottom: 1px solid #dbe2ea;

    box-sizing: border-box;
}

.roles-sidebar-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.roles-sidebar-title i {
    color: #2563eb;

    font-size: 14px;
}

.roles-sidebar-title strong {
    color: #172033;
    font-size: 14px;
}

.roles-count {
    min-width: 22px;
    height: 22px;

    padding: 0 6px;

    border-radius: 11px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: #e2e8f0;
    color: #475569;

    font-size: 11px;
    font-weight: 700;
}

.roles-add-small {
    width: 30px;
    height: 30px;

    padding: 0;

    border: 1px solid #cbd5e1;
    border-radius: 6px;

    background: #fff;
    color: #475569;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
}

.roles-add-small:hover {
    background: #f1f5f9;
    color: #2563eb;
}


/* =====================================================
   ROLE LIST
===================================================== */

.roles-list {
    padding: 8px;
}

.role-item {
    width: 100%;

    min-height: 56px;

    margin-bottom: 4px;
    padding: 10px 12px;

    border: 1px solid transparent;
    border-left: 3px solid transparent;
    border-radius: 8px;

    background: transparent;

    display: flex;
    align-items: center;

    gap: 10px;

    text-align: left;

    cursor: pointer;

    transition:
        background-color 0.15s ease,
        border-color 0.15s ease;
}

.role-item:hover {
    background: #f1f5f9;
}

.role-item.active {
    border-left-color: #2563eb;
    background: #eff6ff;
}

.role-icon {
    width: 34px;
    height: 34px;

    min-width: 34px;

    border-radius: 7px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #e2e8f0;
    color: #64748b;
}

.role-item.active .role-icon {
    background: #dbeafe;
    color: #2563eb;
}

.role-item-content {
    min-width: 0;

    flex: 1;

    display: flex;
    flex-direction: column;

    gap: 3px;
}

.role-item-content strong {
    overflow: hidden;

    color: #334155;

    font-size: 13px;
    font-weight: 600;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.role-item-content small {
    color: #94a3b8;
    font-size: 11px;
}

.role-arrow {
    color: #94a3b8;
    font-size: 10px;
}


/* =====================================================
   EMPTY SIDEBAR
===================================================== */

.roles-empty-sidebar {
    padding: 40px 20px;

    display: flex;
    flex-direction: column;
    align-items: center;

    text-align: center;
}

.roles-empty-icon {
    width: 48px;
    height: 48px;

    margin-bottom: 14px;

    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #e2e8f0;
    color: #64748b;

    font-size: 20px;
}

.roles-empty-sidebar strong {
    margin-bottom: 6px;

    color: #334155;
    font-size: 14px;
    font-weight: 600;
}

.roles-empty-sidebar span {
    margin-bottom: 18px;

    color: #94a3b8;

    font-size: 12px;
    line-height: 1.5;
}


/* =====================================================
   CONTENT
===================================================== */

.roles-content {
    min-width: 0;

    background: #fff;
}


/* =====================================================
    ROLE DETAIL HEADER
===================================================== */

.role-detail-header {
    min-height: 100px;

    padding: 20px 22px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    box-sizing: border-box;

    background: #fff;
}

.role-detail-title {
    min-width: 0;

    display: flex;
    align-items: center;

    gap: 14px;
}

.role-detail-icon {
    width: 44px;
    height: 44px;

    min-width: 44px;

    border-radius: 9px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #eff6ff;
    color: #2563eb;

    font-size: 18px;
}

.role-detail-eyebrow {
    display: block;

    margin-bottom: 3px;

    color: #94a3b8;

    font-size: 10px;
    font-weight: 700;

    letter-spacing: 0.08em;
}

.role-detail-title h2 {
    margin: 0;

    color: #172033;

    font-size: 19px;
    line-height: 1.3;
}

.role-detail-title p {
    margin: 4px 0 0;

    color: #64748b;

    font-size: 12px;
}

.role-detail-actions {
    display: flex;
    align-items: center;

    gap: 8px;

    flex: 0 0 auto;
}


/* =====================================================
    PERMISSION TOOLBAR
===================================================== */

.permission-toolbar {
    min-height: 60px;

    padding: 12px 22px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 16px;

    box-sizing: border-box;

    background: #fafbfc;
}

.permission-toolbar > div {
    min-width: 0;

    display: flex;
    flex-direction: column;

    gap: 2px;
}

.permission-toolbar strong {
    color: #334155;

    font-size: 13px;
    font-weight: 600;
}

.permission-toolbar span {
    color: #94a3b8;

    font-size: 12px;
}


/* =====================================================
    PERMISSION MATRIX
===================================================== */

.permission-matrix {
    padding: 16px 22px 24px;
}

.permission-section {
    margin-bottom: 12px;

    border: 1px solid #e2e8f0;
    border-radius: 8px;

    overflow: hidden;

    background: #fff;
}

.permission-section:last-child {
    margin-bottom: 0;
}

.permission-section-header {
    min-height: 48px;

    padding: 8px 13px;

    background: #f8fafc;

    border-bottom: 1px solid #e2e8f0;

    display: flex;
    align-items: center;

    gap: 10px;

    box-sizing: border-box;
}

.permission-group-icon {
    width: 30px;
    height: 30px;

    min-width: 30px;

    border-radius: 6px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #eaf2ff;
    color: #2563eb;
}

.permission-section-info {
    display: flex;
    flex-direction: column;

    gap: 2px;
}

.permission-section-info strong {
    color: #334155;

    font-size: 13px;
    font-weight: 600;
}

.permission-section-info span {
    color: #94a3b8;

    font-size: 11px;
}

.permission-row {
    min-height: 44px;

    padding: 0 13px;

    border-bottom: 1px solid #f1f5f9;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;

    box-sizing: border-box;

    background: #fff;

    transition: background-color 0.15s ease;
}

.permission-row:last-child {
    border-bottom: 0;
}

.permission-row:hover {
    background: #f8fafc;
}

.permission-row-checkbox {
    cursor: pointer;
}

.permission-row-checkbox .permission-box {
    transition:
        background-color 0.15s ease,
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.permission-row-checkbox .permission-native-input:checked
+ .permission-box {
    background: #2563eb;

    border-color: #2563eb;
}

.permission-row-checkbox .permission-native-input:checked
+ .permission-box::after {
    content: "✓";

    color: #fff;

    font-size: 12px;
    font-weight: 700;

    line-height: 1;

    transform: translateY(-1px);
}

.permission-row-checkbox .permission-native-input:focus-visible
+ .permission-box {
    outline: 3px solid rgba(37, 99, 235, 0.15);

    outline-offset: 2px;
}

.permission-name {
    color: #475569;

    font-size: 13px;
}

.permission-state {
    min-width: 110px;

    display: inline-flex;
    align-items: center;
    justify-content: flex-end;

    gap: 6px;

    color: #94a3b8;

    font-size: 11px;
    font-weight: 600;
}

.permission-state.allowed {
    color: #16a34a;
}


/* =====================================================
    EMPTY CONTENT
===================================================== */

.roles-content-empty {
    min-height: 500px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #fff;

    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.roles-content-empty-inner {
    text-align: center;

    padding: 40px 20px;
}

.roles-content-empty-icon {
    width: 56px;
    height: 56px;

    margin: 0 auto 16px;

    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #f1f5f9;
    color: #94a3b8;

    font-size: 22px;
}

.roles-content-empty h2 {
    margin: 0 0 6px;

    color: #334155;

    font-size: 17px;
    font-weight: 600;
}

.roles-content-empty p {
    margin: 0;

    color: #94a3b8;

    font-size: 13px;

    line-height: 1.5;
}


/* =====================================================
   DRAWER OVERLAY
===================================================== */

.drawer-overlay {
    position: fixed;

    inset: 0;

    z-index: 9999;

    background: rgba(15, 23, 42, 0.35);

    display: flex;
    justify-content: flex-end;
}


/* =====================================================
   DRAWER
===================================================== */

.role-drawer {
    width: min(620px, 100vw);

    height: 100%;

    background: #fff;

    display: flex;
    flex-direction: column;

    box-shadow:
        -10px 0 30px rgba(15, 23, 42, 0.15);
}


/* =====================================================
   DRAWER HEADER
===================================================== */

.drawer-header {
    flex: 0 0 auto;

    padding: 20px 24px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 15px;
}

.drawer-eyebrow {
    display: block;

    margin-bottom: 5px;

    color: #64748b;

    font-size: 10px;
    font-weight: 700;

    letter-spacing: 0.08em;
}

.drawer-header h2 {
    margin: 0;

    color: #172033;

    font-size: 21px;
    line-height: 1.3;
}

.drawer-close {
    width: 34px;
    height: 34px;

    padding: 0;

    border: 0;
    border-radius: 7px;

    background: transparent;
    color: #64748b;

    font-size: 27px;
    line-height: 1;

    cursor: pointer;
}

.drawer-close:hover {
    background: #f1f5f9;
    color: #172033;
}


/* =====================================================
   DRAWER BODY
===================================================== */

.drawer-body {
    flex: 1;

    min-height: 0;

    overflow-y: auto;

    padding: 22px 24px;
}

.drawer-field {
    margin-bottom: 24px;
}

.drawer-field > label {
    display: block;

    margin-bottom: 7px;

    color: #172033;

    font-size: 13px;
    font-weight: 700;
}

.form-control {
    width: 100%;

    min-height: 42px;

    padding: 9px 12px;

    border: 1px solid #cbd5e1;
    border-radius: 7px;

    background: #fff;

    color: #172033;

    font-size: 14px;

    outline: none;

    box-sizing: border-box;
}

.form-control:focus {
    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.field-error {
    margin-top: 6px;

    color: #dc2626;

    font-size: 12px;
}


/* =====================================================
   PERMISSION HEADING
===================================================== */

.permission-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 15px;

    margin-bottom: 12px;
}

.permission-heading label {
    display: block;

    margin-bottom: 4px;
}

.permission-heading small {
    display: block;

    color: #64748b;

    font-size: 12px;
    font-weight: 400;
}

.permission-select-all {
    flex: 0 0 auto;

    min-height: 34px;

    padding: 6px 11px;

    border: 1px solid #cbd5e1;
    border-radius: 6px;

    background: #fff;

    color: #334155;

    font-size: 12px;

    cursor: pointer;
}

.permission-select-all:hover {
    background: #f8fafc;
}


/* =====================================================
   SEARCH
===================================================== */

.drawer-permission-search {
    position: relative;

    margin-bottom: 12px;
}

.drawer-permission-search i {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #94a3b8;

    pointer-events: none;
}

.drawer-permission-search input {
    width: 100%;

    height: 40px;

    padding: 8px 12px 8px 36px;

    border: 1px solid #cbd5e1;
    border-radius: 7px;

    background: #fff;

    color: #172033;

    box-sizing: border-box;

    outline: none;

    font-size: 13px;
}

.drawer-permission-search input:focus {
    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px rgba(59, 130, 246, 0.08);
}


/* =====================================================
   DRAWER GROUP
===================================================== */

.drawer-permission-group {
    margin-bottom: 12px;

    overflow: hidden;

    border: 1px solid #dbe2ea;
    border-radius: 8px;

    background: #fff;
}

.drawer-group-header {
    min-height: 50px;

    padding: 8px 12px;

    box-sizing: border-box;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;

    background: #f8fafc;

    border-bottom: 1px solid #dbe2ea;
}

.drawer-group-title {
    min-width: 0;

    display: flex;
    align-items: center;

    gap: 9px;
}

.drawer-group-title strong {
    color: #172033;

    font-size: 13px;
}

.drawer-group-icon {
    width: 28px;
    height: 28px;

    min-width: 28px;

    border-radius: 6px;

    background: #e8f0ff;
    color: #2563eb;

    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.drawer-group-count {
    padding: 2px 6px;

    border-radius: 10px;

    background: #eaf2ff;
    color: #2563eb;

    font-size: 10px;
    font-weight: 700;
}

.group-toggle {
    flex: 0 0 auto;

    padding: 6px 9px;

    border: 1px solid #cbd5e1;
    border-radius: 6px;

    background: #fff;

    color: #334155;

    font-size: 11px;

    cursor: pointer;
}

.group-toggle:hover {
    background: #f1f5f9;
}


/* =====================================================
   CUSTOM CHECKBOX
   QUAN TRỌNG:
   CHỈ .permission-box ĐƯỢC HIỂN THỊ
===================================================== */
.permission-checkbox {
    position: relative;

    min-height: 47px;

    padding: 0 14px;

    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;

    gap: 10px;

    box-sizing: border-box;

    border-bottom: 1px solid #edf0f4;

    background: #fff;

    cursor: pointer;

    user-select: none;
}

.permission-checkbox:last-child {
    border-bottom: 0;
}

.permission-checkbox:hover {
    background: #f8fafc;
}


/* =====================================================
   NATIVE INPUT
   ẨN HOÀN TOÀN
===================================================== */

.permission-native-input {
    position: absolute !important;

    width: 1px !important;
    height: 1px !important;

    margin: -1px !important;
    padding: 0 !important;

    overflow: hidden !important;

    clip: rect(0, 0, 0, 0) !important;
    clip-path: inset(50%) !important;

    white-space: nowrap !important;

    border: 0 !important;

    opacity: 0 !important;
}


/* =====================================================
   CUSTOM BOX
===================================================== */
.permission-box {
    position: relative;

    width: 18px;
    height: 18px;

    min-width: 18px;
    min-height: 18px;

    flex: 0 0 18px;
    align-self: center;

    box-sizing: border-box;

    border: 1px solid #94a3b8;
    border-radius: 4px;

    background: #fff;

    display: flex;

    align-items: center;

    justify-content: center;

    transition:
        background-color 0.15s ease,
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

/* =====================================================
   CHECKED
===================================================== */

.permission-native-input:checked
+ .permission-box {
    background: #2563eb;

    border-color: #2563eb;
}


/* =====================================================
   CHECK ICON
===================================================== */

.permission-native-input:checked
+ .permission-box::after {
    content: "✓";

    color: #fff;

    font-size: 12px;
    font-weight: 700;

    line-height: 1;

    transform: translateY(-1px);
}


/* =====================================================
   CHECKED LABEL
===================================================== */

.permission-native-input:checked
+ .permission-box
+ .permission-checkbox-label {
    color: #172033;

    font-weight: 600;
}


/* =====================================================
   LABEL
===================================================== */

.permission-checkbox-label {
    min-width: 0;

    color: #334155;

    font-size: 13px;
    font-weight: 500;

    line-height: 1.4;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


/* =====================================================
   FOCUS
===================================================== */

.permission-native-input:focus-visible
+ .permission-box {
    outline: 3px solid rgba(37, 99, 235, 0.15);

    outline-offset: 2px;
}


/* =====================================================
   NOT FOUND
===================================================== */

.permissions-not-found {
    min-height: 120px;

    display: flex;
    flex-direction: column;

    align-items: center;
    justify-content: center;

    gap: 8px;

    color: #94a3b8;

    text-align: center;

    font-size: 13px;
}


/* =====================================================
   DRAWER FOOTER
===================================================== */

.drawer-footer {
    flex: 0 0 auto;

    padding: 14px 24px;

    border-top: 1px solid #e5e7eb;

    background: #fff;

    display: flex;
    align-items: center;
    justify-content: flex-end;

    gap: 8px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 900px) {

    .roles-workspace {
        grid-template-columns: 230px minmax(0, 1fr);
    }

    .role-detail-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .role-detail-actions {
        width: 100%;
    }

}


@media (max-width: 700px) {

    .roles-page-header {
        flex-direction: column;
    }

    .roles-header-actions {
        width: 100%;
    }

    .roles-header-actions .btn {
        flex: 1;
    }

    .roles-workspace {
        grid-template-columns: 1fr;
    }

    .roles-sidebar {
        border-right: 0;

        border-bottom: 1px solid #dbe2ea;
    }

    .roles-list {
        max-height: 250px;

        overflow-y: auto;
    }

    .role-drawer {
        width: 100%;
    }

    .drawer-header,
    .drawer-body,
    .drawer-footer {
        padding-left: 16px;
        padding-right: 16px;
    }

    .permission-heading {
        flex-direction: column;
        align-items: stretch;
    }

    .permission-select-all {
        align-self: flex-start;
    }

    .permission-row {
        align-items: flex-start;
        flex-direction: column;

        padding-top: 10px;
        padding-bottom: 10px;

        gap: 6px;
    }

    .permission-state {
        justify-content: flex-start;
    }

}

</style>