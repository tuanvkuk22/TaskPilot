@extends('layouts.app')
@section('content')
<div class="tp-project-show">
    {{-- Project Header --}}
    <div class="tp-project-wrapper">
        @include('projects.partials.project-nav', ['active' => 'documents'])
    </div>

    <div style="max-width:1100px;margin:0 auto;padding:0 16px;">
        <div class="jira-page-header">
            <div>
                <h1><i class="fa fa-file-text-o"></i> Tài liệu dự án</h1>
                <p>Quản lý liên kết tài liệu, Google Docs, Notion, Confluence và file của dự án <strong>{{ $project->name }}</strong>.</p>
            </div>
            <div class="jira-page-actions">
                <button class="btn-jira btn-jira-primary" onclick="openModal('addDocModal')">
                    <i class="fa fa-plus"></i> Thêm tài liệu
                </button>
            </div>
        </div>

        {{-- Filter bar --}}
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
            @foreach(['all'=>'Tất cả','link'=>'Link','google_doc'=>'Google Doc','notion'=>'Notion','confluence'=>'Confluence','file'=>'File'] as $k=>$label)
                <button onclick="filterDocs('{{ $k }}')" data-filter="{{ $k }}"
                    class="btn-jira btn-jira-subtle doc-filter-btn {{ $k === 'all' ? 'active-filter' : '' }}"
                    style="{{ $k === 'all' ? 'border-color:var(--primary);color:var(--primary);' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Documents Grid --}}
        <div class="jira-card">
            @if($project->documents->isEmpty())
                <div class="jira-empty">
                    <i class="fa fa-file-o"></i>
                    <h3>Chưa có tài liệu</h3>
                    <p>Thêm liên kết tài liệu để cả nhóm dễ dàng truy cập.</p>
                    <button class="btn-jira btn-jira-primary" style="margin-top:16px;" onclick="openModal('addDocModal')">
                        <i class="fa fa-plus"></i> Thêm tài liệu đầu tiên
                    </button>
                </div>
            @else
                <table class="jira-table" id="docsTable">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Tên tài liệu</th>
                            <th>Loại</th>
                            <th>Mô tả</th>
                            <th>Người thêm</th>
                            <th>Ngày thêm</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($project->documents as $doc)
                        <tr data-type="{{ $doc->type }}">
                            <td>
                                <span class="doc-type-icon doc-{{ $doc->type }}">
                                    @switch($doc->type)
                                        @case('google_doc') <i class="fa fa-google"></i> @break
                                        @case('notion') N @break
                                        @case('confluence') <i class="fa fa-book"></i> @break
                                        @case('file') <i class="fa fa-file"></i> @break
                                        @default <i class="fa fa-link"></i>
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <a href="{{ $doc->url }}" target="_blank" style="font-weight:600;color:var(--primary);text-decoration:none;">
                                    {{ $doc->title }} <i class="fa fa-external-link" style="font-size:11px;"></i>
                                </a>
                            </td>
                            <td>
                                <span class="jira-chip jira-chip-grey" style="text-transform:none;">{{ $doc->type }}</span>
                            </td>
                            <td style="color:var(--text-muted);max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $doc->description ?: '—' }}
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="jira-avatar">{{ strtoupper(substr($doc->user->name ?? $doc->user->email, 0, 1)) }}</span>
                                    <span>{{ $doc->user->name ?? $doc->user->email }}</span>
                                </div>
                            </td>
                            <td style="color:var(--text-muted);font-size:12px;">{{ $doc->created_at->format('d/m/Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('projects.documents.destroy', [$project, $doc]) }}" onsubmit="return confirm('Xóa tài liệu này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-jira btn-jira-subtle" style="padding:5px 10px;font-size:12px;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Add Document Modal --}}
<div class="jira-modal-backdrop" id="addDocModalBackdrop" onclick="closeModal('addDocModal')"></div>
<div class="jira-modal" id="addDocModal">
    <h2><i class="fa fa-plus-circle" style="color:var(--primary);margin-right:8px;"></i>Thêm tài liệu</h2>
    <form method="POST" action="{{ route('projects.documents.store', $project) }}">
        @csrf
        <div class="form-group">
            <label>Tên tài liệu *</label>
            <input name="title" required maxlength="255" placeholder="Ví dụ: Thiết kế giao diện v2" value="{{ old('title') }}">
        </div>
        <div class="form-group">
            <label>Đường dẫn (URL) *</label>
            <input name="url" type="url" required placeholder="https://docs.google.com/..." value="{{ old('url') }}">
        </div>
        <div class="form-group">
            <label>Loại tài liệu</label>
            <select name="type">
                <option value="link">Link thông thường</option>
                <option value="google_doc">Google Docs / Drive</option>
                <option value="notion">Notion</option>
                <option value="confluence">Confluence</option>
                <option value="file">File đính kèm</option>
            </select>
        </div>
        <div class="form-group">
            <label>Mô tả</label>
            <textarea name="description" rows="3" placeholder="Mô tả ngắn về tài liệu...">{{ old('description') }}</textarea>
        </div>
        <div class="jira-modal-footer">
            <button type="button" class="btn-jira btn-jira-subtle" onclick="closeModal('addDocModal')">Hủy</button>
            <button type="submit" class="btn-jira btn-jira-primary"><i class="fa fa-save"></i> Lưu tài liệu</button>
        </div>
    </form>
</div>
@endsection

@section('jqueryScript')
<script>
function openModal(id) {
    document.getElementById(id + 'Backdrop').classList.add('open');
    document.getElementById(id).classList.add('open');
}
function closeModal(id) {
    document.getElementById(id + 'Backdrop').classList.remove('open');
    document.getElementById(id).classList.remove('open');
}
function filterDocs(type) {
    document.querySelectorAll('.doc-filter-btn').forEach(btn => {
        btn.style.borderColor = '';
        btn.style.color = '';
    });
    const activeBtn = document.querySelector('[data-filter="'+type+'"]');
    if (activeBtn) { activeBtn.style.borderColor = 'var(--primary)'; activeBtn.style.color = 'var(--primary)'; }
    document.querySelectorAll('#docsTable tbody tr').forEach(row => {
        row.style.display = (type === 'all' || row.dataset.type === type) ? '' : 'none';
    });
}
@if($errors->any())
    openModal('addDocModal');
@endif
@if(session('success'))
    setTimeout(() => { const a = document.createElement('div'); a.style.cssText='position:fixed;top:20px;right:20px;background:#00875a;color:#fff;padding:12px 20px;border-radius:8px;z-index:9999;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.2);'; a.textContent='{{ session('success') }}'; document.body.appendChild(a); setTimeout(() => a.remove(), 3500); }, 100);
@endif
</script>
@endsection
