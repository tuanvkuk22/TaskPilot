@extends('layouts.app')
@push('styles')
<style>
    /* =========================================================
   CREATE TEAM PAGE
   ========================================================= */

.team-create-page {
    max-width: 1180px;
    margin: 0 auto;
    padding: 28px 20px 60px;
}


/* =========================================================
   HEADER
   ========================================================= */

.team-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;

    gap: 30px;

    margin-bottom: 28px;
}

.team-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;

    margin-bottom: 10px;

    color: #6b778c;

    font-size: 12px;
}

.team-breadcrumb a {
    color: #42526e;
    text-decoration: none;
}

.team-breadcrumb a:hover {
    color: #0052cc;
}

.team-breadcrumb i {
    font-size: 8px;
    color: #97a0af;
}

.team-page-header h1 {
    margin: 0 0 7px;

    color: #172b4d;

    font-size: 26px;
    font-weight: 700;

    letter-spacing: -.4px;
}

.team-page-header p {
    max-width: 650px;

    margin: 0;

    color: #6b778c;

    font-size: 13px;
}

.team-back-btn {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    height: 36px;

    padding: 0 13px;

    color: #42526e;

    background: #fff;

    border: 1px solid #dfe1e6;
    border-radius: 5px;

    font-size: 13px;
    font-weight: 500;

    text-decoration: none;

    white-space: nowrap;
}

.team-back-btn:hover {
    background: #f4f5f7;
    color: #172b4d;
}


/* =========================================================
   ALERT
   ========================================================= */

.team-alert {
    display: flex;
    align-items: flex-start;

    gap: 12px;

    padding: 14px 16px;

    margin-bottom: 20px;

    background: #fff5f5;

    border: 1px solid #ffbdad;

    border-radius: 7px;

    color: #172b4d;
}

.team-alert-icon {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: #de350b;
    color: #fff;

    border-radius: 50%;

    font-size: 12px;
}

.team-alert strong {
    display: block;

    margin-bottom: 4px;

    font-size: 13px;
}

.team-alert ul {
    margin: 0;
    padding-left: 18px;

    color: #6b778c;

    font-size: 12px;
}


/* =========================================================
   LAYOUT
   ========================================================= */

.team-layout {
    display: grid;

    grid-template-columns: minmax(0, 1fr) 290px;

    gap: 20px;

    align-items: start;
}

.team-main-column {
    min-width: 0;
}


/* =========================================================
   SECTION
   ========================================================= */

.team-section {
    padding: 24px;

    margin-bottom: 16px;

    background: #fff;

    border: 1px solid #dfe1e6;

    border-radius: 8px;

    box-shadow: 0 1px 2px rgba(9, 30, 66, .04);
}

.team-section-header {
    display: flex;
    align-items: flex-start;

    gap: 12px;

    margin-bottom: 24px;
}

.team-section-number {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: #e9f2ff;
    color: #0052cc;

    border-radius: 6px;

    font-size: 11px;
    font-weight: 700;
}

.team-section-header h2 {
    margin: 2px 0 3px;

    color: #172b4d;

    font-size: 16px;
    font-weight: 650;
}

.team-section-header p {
    margin: 0;

    color: #6b778c;

    font-size: 12px;
}


/* =========================================================
   FORM
   ========================================================= */

.team-form-group {
    margin-bottom: 22px;
}

.team-form-group:last-child {
    margin-bottom: 0;
}

.team-form-group label {
    display: flex;
    align-items: center;

    gap: 4px;

    margin-bottom: 7px;

    color: #172b4d;

    font-size: 13px;
    font-weight: 600;
}

.team-form-group label span {
    color: #de350b;
}

.team-form-group label small {
    margin-left: 4px;

    color: #97a0af;

    font-size: 11px;
    font-weight: 400;
}

.team-input,
.team-textarea {
    width: 100%;

    background: #fff;

    border: 1px solid #dfe1e6;
    border-radius: 5px;

    color: #172b4d;

    font-size: 13px;

    outline: none;

    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}

.team-input {
    height: 42px;

    padding: 0 12px;
}

.team-textarea {
    min-height: 110px;

    padding: 10px 12px;

    resize: vertical;
}

.team-input::placeholder,
.team-textarea::placeholder {
    color: #97a0af;
}

.team-input:focus,
.team-textarea:focus {
    border-color: #4c9aff;

    box-shadow:
        0 0 0 2px rgba(0, 82, 204, .12);
}

.team-help {
    margin-top: 6px;

    color: #8993a4;

    font-size: 11px;
}

.team-error {
    margin-top: 5px;

    color: #de350b;

    font-size: 11px;
}


/* =========================================================
   TEAM TYPE
   ========================================================= */

.team-type-grid {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 10px;
}

.team-type-option {
    display: block;

    margin: 0;

    cursor: pointer;
}

.team-type-option input {
    position: absolute;

    opacity: 0;

    pointer-events: none;
}

.team-type-card {
    min-height: 145px;

    position: relative;

    padding: 16px;

    background: #fff;

    border: 1px solid #dfe1e6;
    border-radius: 7px;

    transition:
        border-color .15s ease,
        background .15s ease,
        box-shadow .15s ease;
}

.team-type-card:hover {
    border-color: #b3bac5;

    background: #fafbfc;
}

.team-type-option input:checked + .team-type-card {
    border-color: #0052cc;

    background: #f4f8ff;

    box-shadow:
        0 0 0 1px #0052cc;
}

.team-type-icon {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 12px;

    border-radius: 6px;

    font-size: 15px;
}

.team-type-icon.technical {
    background: #e9f2ff;
    color: #0052cc;
}

.team-type-icon.marketing {
    background: #e3fcef;
    color: #00875a;
}

.team-type-icon.business {
    background: #fff0b3;
    color: #974f0c;
}

.team-type-icon.other {
    background: #f1f5f9;
    color: #475569;
}

.custom-team-type-wrap {
    margin-top: 14px;
    padding: 14px;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #f8fbff;
}

.custom-team-type-wrap label {
    display: block;
    margin-bottom: 7px;
    color: #172b4d;
    font-size: 13px;
    font-weight: 600;
}

.custom-team-type-wrap label span { color: #de350b; }

.team-type-content strong {
    display: block;

    margin-bottom: 5px;

    color: #172b4d;

    font-size: 13px;
}

.team-type-content span {
    display: block;

    color: #6b778c;

    font-size: 11px;

    line-height: 1.5;
}

.team-type-check {
    width: 18px;
    height: 18px;

    position: absolute;

    top: 12px;
    right: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #fff;

    background: #0052cc;

    border-radius: 50%;

    opacity: 0;

    transform: scale(.7);

    transition: .15s ease;
}

.team-type-option input:checked + .team-type-card .team-type-check {
    opacity: 1;

    transform: scale(1);
}

.team-type-check i {
    font-size: 9px;
}


/* =========================================================
   INVITE
   ========================================================= */

.team-invite-box {
    padding: 16px;

    background: #f7f8fa;

    border: 1px solid #dfe1e6;

    border-radius: 7px;
}

.team-invite-header {
    display: flex;
    align-items: center;

    gap: 10px;

    margin-bottom: 14px;
}

.team-invite-icon {
    width: 34px;
    height: 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #e9f2ff;
    color: #0052cc;

    border-radius: 6px;
}

.team-invite-header strong {
    display: block;

    color: #172b4d;

    font-size: 13px;
}

.team-invite-header span {
    display: block;

    margin-top: 2px;

    color: #6b778c;

    font-size: 11px;
}

.team-invite-help {
    display: flex;
    align-items: flex-start;

    gap: 7px;

    margin-top: 10px;

    color: #6b778c;

    font-size: 11px;
}

.team-invite-help i {
    margin-top: 2px;

    color: #0052cc;
}


/* =========================================================
   SIDEBAR
   ========================================================= */

.team-sidebar {
    position: sticky;

    top: 78px;
}

.team-summary,
.team-tips {
    padding: 18px;

    margin-bottom: 12px;

    background: #fff;

    border: 1px solid #dfe1e6;

    border-radius: 8px;

    box-shadow: 0 1px 2px rgba(9, 30, 66, .04);
}

.team-tips {
    padding: 14px;
}

.team-summary-title,
.team-tips-title {
    display: flex;
    align-items: center;

    gap: 8px;

    margin-bottom: 15px;

    color: #172b4d;

    font-size: 13px;
    font-weight: 650;
}

.team-tips-title {
    margin-bottom: 10px;
}

.team-summary-title i {
    color: #0052cc;
}

.team-tips-title i {
    color: #ffab00;
}

.team-summary-item {
    padding: 11px 0;

    border-top: 1px solid #ebecf0;
}

.team-summary-item:first-of-type {
    border-top: 0;
}

.team-summary-item span {
    display: block;

    margin-bottom: 3px;

    color: #6b778c;

    font-size: 10px;

    text-transform: uppercase;
    letter-spacing: .3px;
}

.team-summary-item strong {
    display: block;

    overflow: hidden;

    color: #172b4d;

    font-size: 12px;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.team-tip {
    display: flex;
    align-items: flex-start;

    gap: 9px;
    padding: 8px 9px;
    margin-top: 5px;
    border: 1px solid #eef2f7;
    color: #64748b;
    font-size: 11px;
    line-height: 1.35;
}

.team-tip i {
    margin-top: 1px;

    color: #00875a;

    font-size: 13px;
}

.team-tip.tip-action {
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    cursor: pointer;
    border-radius: 5px;
    transition: background .15s ease;
}

.team-tip.tip-action:hover,
.team-tip.tip-action:focus-visible {
    border-color: #bfdbfe;
    background: #eff6ff;
    outline: none;
}

.team-tip.tip-action .fa-circle-o { color: #94a3b8; }


/* =========================================================
   ACTIONS
   ========================================================= */

.team-actions {
    display: flex;
    align-items: center;

    gap: 8px;
}

.team-cancel-btn,
.team-submit-btn {
    height: 40px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 5px;

    font-size: 12px;
    font-weight: 600;

    text-decoration: none;

    cursor: pointer;
}

.team-cancel-btn {
    flex: 1;

    color: #42526e;

    background: #fff;

    border: 1px solid #dfe1e6;
}

.team-cancel-btn:hover {
    background: #f4f5f7;

    color: #172b4d;
}

.team-submit-btn {
    flex: 1.5;

    gap: 7px;

    color: #fff;

    background: #0052cc;

    border: 1px solid #0052cc;
}

.team-submit-btn:hover {
    background: #0747a6;
    border-color: #0747a6;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 991px) {

    .team-layout {
        grid-template-columns: 1fr;
    }

    .team-sidebar {
        position: static;

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 12px;
    }

    .team-summary,
    .team-tips {
        margin-bottom: 0;
    }

    .team-actions {
        grid-column: 1 / -1;
    }

}


@media (max-width: 767px) {

    .team-create-page {
        padding: 20px 14px 40px;
    }

    .team-page-header {
        align-items: flex-start;

        flex-direction: column;

        gap: 14px;
    }

    .team-section {
        padding: 18px;
    }

    .team-type-grid {
        grid-template-columns: 1fr;
    }

    .team-type-card {
        min-height: auto;

        display: flex;
        align-items: center;

        gap: 12px;
    }

    .team-type-icon {
        margin-bottom: 0;
    }

    .team-sidebar {
        display: block;
    }

    .team-summary,
    .team-tips {
        margin-bottom: 12px;
    }

}


@media (max-width: 480px) {

    .team-page-header h1 {
        font-size: 22px;
    }

    .team-section-header {
        margin-bottom: 18px;
    }

    .team-actions {
        flex-direction: column-reverse;
    }

    .team-cancel-btn,
    .team-submit-btn {
        width: 100%;
    }

}
/* =========================================================
   INVITE MEMBERS
   ========================================================= */

.team-invite-box {
    padding: 18px;

    background: #fff;
    border: 1px solid #dfe1e6;
    border-radius: 8px;
}


/* Header */

.team-invite-header {
    display: flex;
    align-items: center;
    gap: 11px;

    margin-bottom: 14px;
}

.team-invite-icon {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: #e9f2ff;
    color: #0052cc;

    border-radius: 8px;
}

.team-invite-header strong {
    display: block;

    color: #172b4d;

    font-size: 13px;
    font-weight: 650;
}

.team-invite-header span {
    display: block;

    margin-top: 2px;

    color: #8993a4;

    font-size: 11px;
}


/* =========================================================
   TAG INPUT CONTAINER
   ========================================================= */

#jira-tag-container {
    min-height: 48px;

    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 6px;

    padding: 7px 9px;

    background: #fff;

    border: 1px solid #c1c7d0;
    border-radius: 6px;

    box-sizing: border-box;

    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}

#jira-tag-container:focus-within {
    border-color: #4c9aff;

    box-shadow:
        0 0 0 2px rgba(0, 82, 204, .12);
}


/* =========================================================
   TAG
   ========================================================= */

/*
 * Plugin có thể tạo tag với class khác nhau.
 * Áp dụng cho các phần tử con của container.
 */

#jira-tag-container .tag,
#jira-tag-container .jira-tag,
#jira-tag-container [class*="tag"] {
    display: inline-flex;
    align-items: center;

    height: 30px;

    padding: 0 8px;

    background: #e9f2ff !important;
    color: #0747a6 !important;

    border: 1px solid #b3d4ff !important;
    border-radius: 5px !important;

    font-size: 12px;
    font-weight: 500;

    line-height: 1;

    white-space: nowrap;
}


/* dấu X */

#jira-tag-container .tag button,
#jira-tag-container .jira-tag button,
#jira-tag-container [class*="tag"] button {
    width: 18px;
    height: 18px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin-left: 5px;
    padding: 0;

    background: transparent !important;
    border: 0 !important;

    color: #5e6c84 !important;

    font-size: 12px;

    cursor: pointer;
}

#jira-tag-container .tag button:hover,
#jira-tag-container .jira-tag button:hover,
#jira-tag-container [class*="tag"] button:hover {
    color: #de350b !important;
}


/* =========================================================
   INPUT
   ========================================================= */

#jira-tag-container input {
    flex: 1 1 180px;

    min-width: 180px;
    height: 32px;

    padding: 0 5px;

    background: transparent !important;

    border: 0 !important;
    outline: none !important;

    box-shadow: none !important;

    color: #172b4d;

    font-size: 13px;
}

#jira-tag-container input::placeholder {
    color: #8993a4;

    font-size: 13px;
}


/* =========================================================
   HELP
   ========================================================= */

.team-invite-help {
    display: flex;
    align-items: flex-start;

    gap: 7px;

    margin-top: 9px;

    color: #6b778c;

    font-size: 11px;
}

.team-invite-help i {
    margin-top: 2px;

    color: #0052cc;
}

.team-invite-help b {
    color: #42526e;

    font-weight: 600;
}

</style>

@endpush

@section('content')

<div class="team-create-page">

    {{-- Page Header --}}
    <div class="team-page-header">

        <div>
            <div class="team-breadcrumb">
                <a href="{{ route('teams.index') }}">
                    <i class="fa fa-users"></i>
                    Đội nhóm
                </a>

                <i class="fa fa-chevron-right"></i>

                <span>Tạo mới</span>
            </div>

            <h1>Tạo đội nhóm mới</h1>

            <p>
                Tạo một không gian cộng tác để quản lý thành viên,
                dự án và công việc hiệu quả hơn.
            </p>
        </div>

        <a
            href="{{ route('teams.index') }}"
            class="team-back-btn"
        >
            <i class="fa fa-arrow-left"></i>
            <span>Quay lại</span>
        </a>

    </div>


    {{-- Validation --}}
    @if(isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag && $errors->any())

        <div class="team-alert">
            <div class="team-alert-icon">
                <i class="fa fa-exclamation"></i>
            </div>

            <div>
                <strong>Không thể tạo đội nhóm</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

    @endif


    <form
        method="post"
        action="{{ route('teams.store') }}"
        id="create-team-form"
    >
        @csrf

        <div class="team-layout">

            {{-- =================================================
                 LEFT - MAIN FORM
                 ================================================= --}}
            <div class="team-main-column">

                {{-- Basic Information --}}
                <section class="team-section">

                    <div class="team-section-header">

                        <div class="team-section-number">
                            01
                        </div>

                        <div>
                            <h2>Thông tin đội nhóm</h2>
                            <p>
                                Đặt tên và mô tả để mọi người hiểu rõ mục đích của đội.
                            </p>
                        </div>

                    </div>


                    <div class="team-form-group">

                        <label for="team-name">
                            Tên đội nhóm
                            <span>*</span>
                        </label>

                        <input
                            id="team-name"
                            name="name"
                            maxlength="80"
                            type="text"
                            class="team-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Ví dụ: Đội Phát triển sản phẩm"
                            required
                            autocomplete="off"
                        >

                        @error('name')
                            <div class="team-error">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="team-help">
                            Chọn tên ngắn gọn, dễ nhận biết và phù hợp với mục tiêu của đội.
                        </div>

                    </div>


                    <div class="team-form-group">

                        <label for="team-description">
                            Mô tả
                            <small>Không bắt buộc</small>
                        </label>

                        <textarea
                            id="team-description"
                            name="description"
                            class="team-textarea @error('description') is-invalid @enderror"
                            rows="5"
                            placeholder="Đội này phụ trách những gì? Mục tiêu chính là gì?"
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <div class="team-error">
                                {{ $message }}
                            </div>
                        @enderror

                </section>


                {{-- Team Type --}}
                <section class="team-section">

                    <div class="team-section-header">

                        <div class="team-section-number">
                            02
                        </div>

                        <div>
                            <h2>Loại đội nhóm</h2>
                            <p>
                                Chọn loại phù hợp nhất với mục đích sử dụng.
                            </p>
                        </div>

                    </div>


                    <div class="team-type-grid">

                        <label class="team-type-option">

                            <input
                                type="radio"
                                name="team_type"
                                value="technical"
                                {{ old('team_type') === 'technical' ? 'checked' : '' }}
                            >

                            <div class="team-type-card">

                                <div class="team-type-icon technical">
                                    <i class="fa fa-code"></i>
                                </div>

                                <div class="team-type-content">
                                    <strong>Kỹ thuật & CNTT</strong>

                                    <span>
                                        Phát triển phần mềm, QA, DevOps và hạ tầng.
                                    </span>
                                </div>

                                <div class="team-type-check">
                                    <i class="fa fa-check"></i>
                                </div>

                            </div>

                        </label>


                        <label class="team-type-option">

                            <input
                                type="radio"
                                name="team_type"
                                value="marketing"
                                {{ old('team_type') === 'marketing' ? 'checked' : '' }}
                            >

                            <div class="team-type-card">

                                <div class="team-type-icon marketing">
                                    <i class="fa fa-bullhorn"></i>
                                </div>

                                <div class="team-type-content">
                                    <strong>Marketing & Kinh doanh</strong>

                                    <span>
                                        Marketing, bán hàng, tăng trưởng và truyền thông.
                                    </span>
                                </div>

                                <div class="team-type-check">
                                    <i class="fa fa-check"></i>
                                </div>

                            </div>

                        </label>


                        <label class="team-type-option">

                            <input
                                type="radio"
                                name="team_type"
                                value="business"
                                {{ old('team_type') === 'business' ? 'checked' : '' }}
                            >

                            <div class="team-type-card">

                                <div class="team-type-icon business">
                                    <i class="fa fa-briefcase"></i>
                                </div>

                                <div class="team-type-content">
                                    <strong>Vận hành & Doanh nghiệp</strong>

                                    <span>
                                        Nhân sự, tài chính và quản lý vận hành.
                                    </span>
                                </div>

                                <div class="team-type-check">
                                    <i class="fa fa-check"></i>
                                </div>

                            </div>

                        </label>

                        <label class="team-type-option">
                            <input type="radio" name="team_type" value="other" {{ old('team_type') === 'other' ? 'checked' : '' }}>
                            <div class="team-type-card">
                                <div class="team-type-icon other"><i class="fa fa-ellipsis-h"></i></div>
                                <div class="team-type-content"><strong>Khác</strong><span>Nhập loại đội nhóm phù hợp với cách bạn làm việc.</span></div>
                                <div class="team-type-check"><i class="fa fa-check"></i></div>
                            </div>
                        </label>

                    </div>

                    <div id="custom-team-type-wrap" class="custom-team-type-wrap" style="display: none;">
                        <label for="team_type_custom">Loại đội nhóm khác <span>*</span></label>
                        <input id="team_type_custom" name="team_type_custom" class="form-control" maxlength="100" value="{{ old('team_type_custom') }}" placeholder="Ví dụ: Thiết kế sản phẩm, Giáo dục, Cộng đồng...">
                    </div>

                </section>

                {{-- Members --}}
                <section class="team-section">

                    <div class="team-section-header">

                        <div class="team-section-number">
                            03
                        </div>

                        <div>
                            <h2>Mời thành viên</h2>
                            <p>
                                Thêm thành viên bằng email hoặc username.
                            </p>
                        </div>

                    </div>


                    <div class="team-invite-box">

                        <div class="team-invite-header">

                            <div class="team-invite-icon">
                                <i class="fa fa-user-plus"></i>
                            </div>

                            <div>
                                <strong>Mời thành viên vào đội</strong>

                                <span>
                                    Bạn có thể thêm nhiều người cùng lúc.
                                </span>
                            </div>

                        </div>


                        <div id="jira-tag-container"></div>

                        <input
                            type="hidden"
                            id="team-members"
                            name="members"
                            value="{{ old('members') }}"
                        >


                        <div class="team-invite-help">
                            <i class="fa fa-info-circle"></i>

                            <span>
                                Nhập email hoặc username rồi nhấn
                                <b>Enter</b>, dấu phẩy hoặc xuống dòng để thêm thành viên.
                            </span>
                        </div>

                    </div>

                </section>

            </div>


            {{-- =================================================
                 RIGHT - SIDEBAR
                 ================================================= --}}
            <aside class="team-sidebar">

                {{-- Summary --}}
                <div class="team-summary">

                    <div class="team-summary-title">
                        <i class="fa fa-layer-group"></i>
                        Tóm tắt
                    </div>

                    <div class="team-summary-item">
                        <span>Tên đội nhóm</span>
                        <strong id="summary-team-name">
                            Chưa đặt tên
                        </strong>
                    </div>

                    <div class="team-summary-item">
                        <span>Loại đội</span>
                        <strong id="summary-team-type">
                            Chưa chọn
                        </strong>
                    </div>

                    <div class="team-summary-item">
                        <span>Thành viên</span>
                        <strong id="summary-members">
                            1 người
                        </strong>
                    </div>

                </div>


                {{-- Tips --}}
                <div class="team-tips">

                    <div class="team-tips-title">
                        <i class="fa fa-lightbulb"></i>
                        Gợi ý
                    </div>

                    <button type="button" class="team-tip tip-action" data-tip-target="team-name">
                        <i id="tip-name-icon" class="fa fa-circle-o"></i>
                        <span id="tip-name-text">Đặt tên dễ nhận biết.</span>
                    </button>

                    <button type="button" class="team-tip tip-action" data-tip-target="team-description">
                        <i id="tip-description-icon" class="fa fa-circle-o"></i>
                        <span id="tip-description-text">Thêm mô tả mục tiêu.</span>
                    </button>

                    <button type="button" class="team-tip tip-action" data-tip-target="jira-tag-container">
                        <i id="tip-members-icon" class="fa fa-circle-o"></i>
                        <span id="tip-members-text">Mời thành viên (tùy chọn).</span>
                    </button>

                </div>


                {{-- Actions --}}
                <div class="team-actions">

                    <a
                        href="{{ route('teams.index') }}"
                        class="team-cancel-btn"
                    >
                        Hủy
                    </a>

                    <button
                        type="submit"
                        class="team-submit-btn"
                    >
                        <i class="fa fa-plus"></i>
                        Tạo đội nhóm
                    </button>

                </div>

            </aside>

        </div>

    </form>

</div>

@endsection


@section('jqueryScript')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * Jira tag input
     */
    const tagContainer = document.getElementById('jira-tag-container');
    const membersInput = document.getElementById('team-members');
    const summaryMembers = document.getElementById('summary-members');
    let memberIdentifiers = (membersInput.value || '').split(/[\r\n,;]+/).map(value => value.trim()).filter(Boolean);
    let tipsReady = false;

    function syncMembers() {
        membersInput.value = memberIdentifiers.join(', ');
        summaryMembers.textContent = `${memberIdentifiers.length + 1} người`;
        if (tipsReady) updateTips();
    }

    function addMemberIdentifiers(value) {
        value.split(/[\r\n,;]+/).map(item => item.trim()).filter(Boolean).forEach(function (identifier) {
            if (!memberIdentifiers.some(item => item.toLowerCase() === identifier.toLowerCase())) {
                memberIdentifiers.push(identifier);
            }
        });
        renderMemberTags();
    }

    function renderMemberTags() {
        tagContainer.innerHTML = '';

        memberIdentifiers.forEach(function (identifier, index) {
            const tag = document.createElement('span');
            tag.className = 'jira-tag';
            tag.textContent = identifier;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', `Xóa ${identifier}`);
            remove.innerHTML = '&times;';
            remove.addEventListener('click', function () {
                memberIdentifiers.splice(index, 1);
                renderMemberTags();
            });
            tag.appendChild(remove);
            tagContainer.appendChild(tag);
        });

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Nhập email hoặc username rồi nhấn Enter';
        input.autocomplete = 'off';
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ',' || event.key === ';') {
                event.preventDefault();
                addMemberIdentifiers(input.value);
                input.value = '';
            }
        });
        input.addEventListener('blur', function () {
            if (input.value.trim()) {
                addMemberIdentifiers(input.value);
                input.value = '';
            }
        });
        tagContainer.appendChild(input);
        syncMembers();
    }

    renderMemberTags();


    /*
     * Team name preview
     */
    const teamName = document.getElementById('team-name');
    const summaryName = document.getElementById('summary-team-name');

    if (teamName && summaryName) {

        teamName.addEventListener('input', function () {

            summaryName.textContent =
                this.value.trim() || 'Chưa đặt tên';

        });

    }

    const teamDescription = document.getElementById('team-description');

    function setTip(id, isComplete, text) {
        const icon = document.getElementById(`tip-${id}-icon`);
        const label = document.getElementById(`tip-${id}-text`);
        icon.className = `fa ${isComplete ? 'fa-check-circle' : 'fa-circle-o'}`;
        label.textContent = text;
    }

    function updateTips() {
        const name = teamName.value.trim();
        const description = teamDescription.value.trim();
        const memberCount = memberIdentifiers.length;
        setTip('name', name.length >= 3, name.length >= 3 ? 'Tên đội nhóm đã sẵn sàng.' : 'Đặt tên dễ nhận biết.');
        setTip('description', description.length >= 10, description.length >= 10 ? 'Mô tả mục tiêu đã được thêm.' : 'Thêm mô tả mục tiêu.');
        setTip('members', memberCount > 0, memberCount > 0 ? `Đã mời ${memberCount} thành viên.` : 'Mời thành viên khi cần thiết (không bắt buộc).');
    }

    teamName.addEventListener('input', updateTips);
    teamDescription.addEventListener('input', updateTips);
    document.querySelectorAll('.tip-action').forEach(function (tip) {
        tip.addEventListener('click', function () {
            const targetId = tip.dataset.tipTarget;
            const target = targetId === 'jira-tag-container'
                ? tagContainer.querySelector('input')
                : document.getElementById(targetId);
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.focus();
        });
    });

    tipsReady = true;
    updateTips();


    /*
     * Team type preview
     */
    const typeInputs = document.querySelectorAll(
        'input[name="team_type"]'
    );

    const summaryType =
        document.getElementById('summary-team-type');

    const typeNames = {
        technical: 'Kỹ thuật & CNTT',
        marketing: 'Marketing & Kinh doanh',
        business: 'Vận hành & Doanh nghiệp'
    };

    typeInputs.forEach(function (input) {

        input.addEventListener('change', function () {

            summaryType.textContent =
                typeNames[this.value] || 'Chưa chọn';

        });

    });

    const customTeamTypeWrap = document.getElementById('custom-team-type-wrap');
    const customTeamTypeInput = document.getElementById('team_type_custom');

    function syncCustomTeamType() {
        const selected = document.querySelector('input[name="team_type"]:checked');
        const isOther = selected && selected.value === 'other';

        customTeamTypeWrap.style.display = isOther ? 'block' : 'none';
        customTeamTypeInput.required = isOther;

        if (isOther) {
            summaryType.textContent = customTeamTypeInput.value.trim() || 'Khác';
        }
    }

    typeInputs.forEach(function (input) {
        input.addEventListener('change', syncCustomTeamType);
    });

    customTeamTypeInput.addEventListener('input', function () {
        const selected = document.querySelector('input[name="team_type"]:checked');
        if (selected && selected.value === 'other') {
            summaryType.textContent = this.value.trim() || 'Khác';
        }
    });

    syncCustomTeamType();

});
</script>

@endsection
