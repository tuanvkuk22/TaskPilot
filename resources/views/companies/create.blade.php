@extends('layouts.app')

@section('content')
<link href="{{ asset('build/assets/company-form.css') }}?v={{ file_exists(public_path('build/assets/company-form.css')) ? filemtime(public_path('build/assets/company-form.css')) : time() }}" rel="stylesheet">
<div class="col-xs-12 tp-company-form-container-wrap" style="padding-left: 0; padding-right: 0; width: 100%;">
    <div id="company-form-app"></div>
</div>
@endsection

@section('jqueryScript')
<script>
    window.companyFormData = {
        company: null,
        is_edit: false,
        action_url: @json($action_url ?? route('companies.store')),
        back_url: @json($back_url ?? route('companies.index')),
        csrf_token: @json(csrf_token()),
        errors: @json($errors ?? []),
        old: @json($old ?? old()),
    };
</script>
<script type="module" src="{{ asset('build/assets/company-form.js') }}?v={{ file_exists(public_path('build/assets/company-form.js')) ? filemtime(public_path('build/assets/company-form.js')) : time() }}"></script>
@endsection
