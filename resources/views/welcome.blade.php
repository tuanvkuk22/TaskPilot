@extends('layouts.app')

@push('styles')
<style>
/* Landing page minimal page-specific styles */
.landing-hero{ background: linear-gradient(180deg, rgba(49,90,208,0.04) 0%, rgba(238,244,255,0.45) 100%); padding:72px 0; border-radius:12px; }
.hero-inner{ display:flex; gap:36px; align-items:center; }
.hero-copy{ flex:1; }
.hero-copy h1{ font-size:40px; margin-bottom:8px; }
.hero-copy p.lead{ color:var(--tp-muted); font-size:17px; }
.hero-ctas{ display:flex; gap:12px; margin-top:16px; }
.hero-illustration img{ width:100%; border-radius:12px; box-shadow:0 12px 40px rgba(23,43,77,0.06); }
@media (max-width:900px){ .hero-inner{ flex-direction:column; } }
</style>
@endpush

@section('content')

<section class="landing-hero">
    <div class="container">
        <div class="hero-inner">
            <div class="hero-copy">
                <h1>Quản lý dự án đơn giản. Làm việc hiệu quả hơn.</h1>
                <p class="lead">Sắp xếp công việc, phối hợp đội nhóm và giao nhiệm vụ rõ ràng — mọi thứ tập trung trong TaskPilot.</p>

                <div class="hero-ctas">
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-primary">Bắt đầu miễn phí</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">Đăng nhập</a>
                    @else
                        <a href="{{ url('/home') }}" class="btn btn-primary">Mở bảng điều khiển</a>
                    @endguest
                </div>
            </div>

            <div class="hero-illustration" style="width:380px;">
                <img src="/images/illustration-dashboard.png" alt="Dashboard illustration">
            </div>
        </div>
    </div>
</section>

<section style="padding:48px 0;">
    <div class="container">
        <div class="tp-feature-grid">
            <div class="tp-card">
                <i class="fa fa-tasks" style="font-size:20px; color:var(--tp-primary)"></i>
                <h4 style="margin-top:10px">Quản lý công việc</h4>
                <p class="text-muted">Sắp xếp, phân loại, và theo dõi nhiệm vụ với workflow trực quan.</p>
            </div>
            <div class="tp-card">
                <i class="fa fa-briefcase" style="font-size:20px; color:var(--tp-primary)"></i>
                <h4 style="margin-top:10px">Quản lý dự án</h4>
                <p class="text-muted">Theo dõi tiến độ dự án, milestone và báo cáo ngắn gọn.</p>
            </div>
            <div class="tp-card">
                <i class="fa fa-users" style="font-size:20px; color:var(--tp-primary)"></i>
                <h4 style="margin-top:10px">Cộng tác đội</h4>
                <p class="text-muted">Giao việc, thảo luận và cập nhật trạng thái theo thời gian thực.</p>
            </div>
        </div>
    </div>
</section>

<section class="container footer-cta">
    <div class="tp-card" style="text-align:center">
        <h3>Quản lý công việc của đội ngũ dễ dàng hơn với TaskPilot.</h3>
        <div style="margin-top:12px">
            <a href="{{ route('register') }}" class="btn btn-primary">Bắt đầu miễn phí</a>
        </div>
    </div>
</section>

@endsection
