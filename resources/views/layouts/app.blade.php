<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskPilot') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-ui.css') }}" rel="stylesheet">
    <script src="https://use.fontawesome.com/874dbadbd7.js" defer></script>
</head>
<body>
<div id="app">
    <nav class="navbar navbar-default navbar-static-top tp-navbar" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Mở điều hướng</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ auth()->check() ? route('home') : route('welcome') }}">
                    <span class="tp-brand-mark"><i class="fa fa-check"></i></span> TaskPilot
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                @auth
                    <ul class="nav navbar-nav">
                        <li class="{{ request()->routeIs('home') ? 'active' : '' }}"><a href="{{ route('home') }}">Tổng quan</a></li>
                        <li class="{{ request()->routeIs('projects.*') ? 'active' : '' }}"><a href="{{ route('projects.index') }}">Dự án</a></li>
                        <li class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}"><a href="{{ route('tasks.index') }}">Công việc</a></li>
                        <li class="{{ request()->routeIs('teams.*') ? 'active' : '' }}"><a href="{{ route('teams.index') }}">Đội nhóm</a></li>
                        <li class="{{ request()->routeIs('companies.*') ? 'active' : '' }}"><a href="{{ route('companies.index') }}">Khách hàng</a></li>
                    </ul>
                @endauth
                <ul class="nav navbar-nav navbar-right">
                    @guest
                        <li><a href="{{ route('login') }}"><i class="fa fa-sign-in"></i> Đăng nhập</a></li>
                        <li><a href="{{ route('register') }}"><i class="fa fa-user-plus"></i> Đăng ký</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                <i class="fa fa-user-circle-o"></i> {{ auth()->user()->name }} <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i> Đăng xuất</a></li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="tp-main">
      <div class="container">
        @include('partials.errors')
        @include('partials.success')
        <div class="row">@yield('content')</div>
      </div>
    </main>
</div>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/jira-tag-input.js') }}"></script>
@yield('jqueryScript')
</body>
</html>
