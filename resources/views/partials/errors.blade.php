@if (isset($errors) && count($errors) > 0)
    <div class="tp-alert-toast tp-alert-danger fade in show" role="alert">
        <div class="tp-alert-icon">
            <i class="fa fa-exclamation-circle"></i>
        </div>
        <div class="tp-alert-content">
            <h4 class="tp-alert-title">Có lỗi xảy ra!</h4>
            <ul class="tp-alert-list mb-0">
                @foreach ($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="tp-alert-close" onclick="this.parentElement.remove()" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
@endif