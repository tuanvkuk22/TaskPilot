
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>TaskPilot</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Application CSS (contains Bootstrap compiled) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Project UI overrides (local) -->
    <link href="{{ asset('css/custom-ui.css') }}" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --tp-primary: #315ad0;
            --tp-primary-hover: #274a9a;
            --tp-text: #172b4d;
            --tp-muted: #6b778c;
            --tp-border: #e8eef7;
            --tp-bg: #f5f8fc;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont,
                'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--tp-text);
            background: var(--tp-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
            font-size: 15px;
        }

        /* Navbar */
        .tp-navbar {
            background: #fff;
            border-bottom: 1px solid var(--tp-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            height: 64px;
        }

        .tp-navbar .navbar-brand {
            color: var(--tp-text);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .tp-navbar .navbar-brand:hover {
            color: var(--tp-primary);
        }

        .tp-navbar .nav-link {
            color: var(--tp-text);
            font-weight: 500;
            padding: 0.75rem 0.9rem !important;
            transition: color 0.2s ease;
        }

        .tp-navbar .nav-link:hover,
        .tp-navbar .nav-link:focus {
            color: var(--tp-primary);
        }

        .tp-navbar .nav-link i {
            margin-right: 6px;
        }

        /* Dropdown */
        .tp-navbar .dropdown-menu {
            min-width: 190px;
            margin-top: 8px;
            padding: 0.4rem;
            border: 1px solid var(--tp-border);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(23, 43, 77, 0.12);
        }

        .tp-navbar .dropdown-item {
            padding: 0.65rem 0.8rem;
            border-radius: 7px;
            color: var(--tp-text);
            font-size: 14px;
        }

        .tp-navbar .dropdown-item:hover,
        .tp-navbar .dropdown-item:focus {
            color: var(--tp-primary);
            background: #f3f6fc;
        }

        .tp-navbar .dropdown-item i {
            width: 18px;
            margin-right: 6px;
        }

        /* Main content */
        .tp-main {
            min-height: calc(100vh - 64px);
            padding-top: 30px;
            padding-bottom: 40px;
        }

        /* Alerts */
        .alert {
            border-radius: 10px;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--tp-primary);
            border-color: var(--tp-primary);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--tp-primary-hover);
            border-color: var(--tp-primary-hover);
        }

        /* Form controls */
        .form-control,
        .form-select {
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--tp-primary);
            box-shadow: 0 0 0 0.2rem rgba(49, 90, 208, 0.15);
        }

        /* Mobile */
        @media (max-width: 991.98px) {
            .tp-navbar .navbar-nav {
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .tp-navbar .nav-link {
                padding: 0.7rem 0.5rem !important;
            }

            .tp-navbar .dropdown-menu {
                margin-top: 0;
                border: 0;
                box-shadow: none;
                background: #f8f9fc;
            }
        }
    </style>
</head>

<body>

    <div id="app">

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg tp-navbar">
            <div class="container">

                <!-- Brand -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    TaskPilot
                </a>

                <!-- Mobile Toggle -->
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#app-navbar-collapse"
                    aria-controls="app-navbar-collapse"
                    aria-expanded="false"
                    aria-label="Chuyển điều hướng"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation Content -->
                <div
                    class="collapse navbar-collapse"
                    id="app-navbar-collapse"
                >

                    <!-- Left Navigation -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    </ul>

                    <!-- Right Navigation -->
                    <ul class="navbar-nav ms-auto">

                        @guest

                            <!-- Login -->
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="{{ route('login') }}"
                                >
                                    <i class="fa fa-sign-in" aria-hidden="true"></i>
                                    Đăng nhập
                                </a>
                            </li>

                            <!-- Register -->
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="{{ route('register') }}"
                                >
                                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                                    Đăng ký
                                </a>
                            </li>

                        @else

                            <!-- Companies -->
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="{{ route('companies.index') }}"
                                >
                                    <i class="fa fa-building" aria-hidden="true"></i>
                                    Công ty
                                </a>
                            </li>

                            <!-- Projects -->
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="{{ route('projects.index') }}"
                                >
                                    <i class="fa fa-briefcase" aria-hidden="true"></i>
                                    Dự án
                                </a>
                            </li>

                            <!-- Tasks -->
                            <li class="nav-item">
                                <a
                                    class="nav-link"
                                    href="{{ route('tasks.index') }}"
                                >
                                    <i class="fa fa-tasks" aria-hidden="true"></i>
                                    Công việc
                                </a>
                            </li>

                            @if(Auth::user()->role_id == 1)

                                {{-- Admin navigation can be added here --}}

                            @endif

                            <!-- User Dropdown -->
                            <li class="nav-item dropdown">

                                <a
                                    class="nav-link dropdown-toggle"
                                    href="#"
                                    id="userDropdown"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    {{ Auth::user()->name }}
                                </a>

                                <ul
                                    class="dropdown-menu dropdown-menu-end"
                                    aria-labelledby="userDropdown"
                                >
                                    <!-- Logout -->
                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="{{ route('logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        >
                                            <i
                                                class="fa fa-power-off"
                                                aria-hidden="true"
                                            ></i>
                                            Đăng xuất
                                        </a>

                                        <form
                                            id="logout-form"
                                            action="{{ route('logout') }}"
                                            method="POST"
                                            class="d-none"
                                        >
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>

                            </li>

                        @endguest

                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="tp-main">

            <div class="container">

                @include('partials.errors')
                @include('partials.success')

                @yield('content')

            </div>

        </main>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('jqueryScript')
</body>
</html>
