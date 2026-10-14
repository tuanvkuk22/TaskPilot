@if (session()->has('success'))
    <div class="tp-alert-toast tp-alert-success fade in show" role="alert">
        <div class="tp-alert-icon">
            <i class="fa fa-check-circle"></i>
        </div>
        <div class="tp-alert-content">
            <h4 class="tp-alert-title">Thành công!</h4>
            <div class="tp-alert-message">{!! session()->get('success') !!}</div>
        </div>
        <button type="button" class="tp-alert-close" onclick="this.parentElement.remove()" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
@endif
