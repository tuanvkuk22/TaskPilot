@if(file_exists(public_path('build/assets/ai-assignee.css')))
<link href="{{ asset('build/assets/ai-assignee.css') }}?v={{ filemtime(public_path('build/assets/ai-assignee.css')) }}" rel="stylesheet">
@endif
<script type="module" src="{{ asset('build/assets/ai-assignee-widget.js') }}?v={{ file_exists(public_path('build/assets/ai-assignee-widget.js')) ? filemtime(public_path('build/assets/ai-assignee-widget.js')) : time() }}"></script>
