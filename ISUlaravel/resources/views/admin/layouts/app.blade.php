<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Event Scheduling Reservation System - ISU Santiago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}">
    <style>
        /* Top Navbar */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            border-bottom: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 1000;
            box-shadow: 0 4px 16px rgba(45, 134, 89, 0.3);
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 3px;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .navbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .navbar-center {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-icon-link {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            font-size: 20px;
            position: relative;
            border-radius: 12px;
        }

        .nav-icon-link::before {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #ffffff;
            border-radius: 2px;
            transition: width var(--transition-fast);
        }

        .nav-icon-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .nav-icon-link:hover::before {
            width: 20px;
        }

        .nav-icon-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .nav-icon-link.active::before {
            width: 20px;
            background: #ffffff;
        }

        .nav-icon-link i {
            font-size: 20px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-name-header {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            white-space: nowrap;
        }

        .user-avatar-header {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-logout-header {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: var(--radius-md);
            font-size: 18px;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
        }

        .btn-logout-header:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #fecaca;
            border-color: rgba(239, 68, 68, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding-top: 64px;
        }

        .content-wrapper {
            padding: 32px;
            max-width: 1600px;
        }

        .page-header {
            background: white;
            padding: 28px 32px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 28px;
            border: 1px solid var(--gray-200);
        }

        .page-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
            letter-spacing: -0.5px;
        }

        /* Tab text color */
        .nav-tabs .nav-link {
            color: #000;
        }
        .nav-tabs .nav-link.active {
            color: #000;
        }

        .page-subtitle {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: var(--gray-500);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 20px;
            }
            
            .page-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .page-title {
                font-size: 24px;
            }

            .user-name-header {
                display: none;
            }

            .navbar-center {
                gap: 4px;
            }

            .nav-icon-link {
                width: 38px;
                height: 38px;
                font-size: 18px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>
<body>
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="navbar-left">
            <img src="https://th.bing.com/th/id/OIP.YFWeW9_VhHAAEQFvvsJxhgAAAA?o=7&rm=3&rs=1&pid=ImgDetMain" alt="ISU Logo" class="navbar-logo">
            <span class="navbar-title">@yield('title', 'Admin Panel')</span>
        </div>

        <div class="navbar-center">
            @if(Auth::user()->isAdministrator())
                <a class="nav-icon-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                </a>
            @endif

            <a class="nav-icon-link {{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}" href="{{ route('admin.reservations.index') }}" title="Reservations">
                <i class="bi bi-calendar-check"></i>
            </a>
            <a class="nav-icon-link {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}" href="{{ route('admin.calendar.index') }}" title="Calendar">
                <i class="bi bi-calendar3"></i>
            </a>
            <a class="nav-icon-link {{ request()->routeIs('admin.venues.map') ? 'active' : '' }}" href="{{ route('admin.venues.map') }}" title="Venue Map">
                <i class="bi bi-map"></i>
            </a>

            @if(Auth::user()->isAdministrator())
                <a class="nav-icon-link {{ request()->routeIs('admin.venues.index') ? 'active' : '' }}" href="{{ route('admin.venues.index') }}" title="Venues">
                    <i class="bi bi-building"></i>
                </a>
                <a class="nav-icon-link {{ request()->routeIs('admin.emergency.*') ? 'active' : '' }}" href="{{ route('admin.emergency.index') }}" title="Emergency">
                    <i class="bi bi-exclamation-triangle"></i>
                </a>
                <a class="nav-icon-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" title="Users">
                    <i class="bi bi-people"></i>
                </a>
            @endif
        </div>

        <div class="navbar-right">
            <div class="user-info-header">
                <span class="user-name-header">{{ Auth::user()->name }}</span>
                <div class="user-avatar-header">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout-header" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>