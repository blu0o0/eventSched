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
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(180deg, #001829 0%, #002a45 100%);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: transform var(--transition-base);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 28px 24px;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: #ffffff;
            transition: transform var(--transition-base);
        }

        .sidebar-brand:hover {
            transform: translateX(4px);
        }

        .sidebar-logo {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 4px;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .sidebar-brand-text {
            flex: 1;
        }

        .sidebar-brand-text h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
            letter-spacing: -0.3px;
        }

        .sidebar-brand-text small {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 24px 0;
        }

        .nav-section {
            padding: 12px 24px 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 8px;
        }

        .nav-item {
            margin: 3px 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all var(--transition-base);
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--primary-light);
            transform: scaleY(0);
            transition: transform var(--transition-base);
            border-radius: 0 2px 2px 0;
        }

        .nav-link:hover {
            background: rgba(44, 192, 160, 0.15);
            color: #ffffff;
            transform: translateX(6px);
        }

        .nav-link:hover::before {
            transform: scaleY(1);
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: #ffffff;
            box-shadow: var(--shadow-primary);
            font-weight: 600;
        }

        .nav-link.active::before {
            transform: scaleY(1);
            background: white;
        }

        .nav-link i {
            font-size: 20px;
            width: 24px;
            text-align: center;
            opacity: 0.9;
        }

        .sidebar-footer {
            flex-shrink: 0;
            padding: 20px 16px;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.3) 30%, rgba(0, 0, 0, 0.4) 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(31, 149, 90, 0.3);
        }

        .user-details {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.2px;
        }

        .user-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: 11px;
            margin: 4px 0 0 0;
            font-weight: 500;
        }

        .btn-logout {
            width: 100%;
            padding: 12px;
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            transition: all var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            letter-spacing: -0.2px;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #fecaca;
            border-color: rgba(239, 68, 68, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }

        .btn-logout:active {
            transform: translateY(0);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
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

        .page-subtitle {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: var(--gray-500);
            font-weight: 500;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ Auth::user()->isOsas() ? route('admin.reservations.index') : route('admin.dashboard') }}" class="sidebar-brand">
                <img src="https://th.bing.com/th/id/OIP.YFWeW9_VhHAAEQFvvsJxhgAAAA?o=7&rm=3&rs=1&pid=ImgDetMain" alt="ISU Logo" class="sidebar-logo">
                <div class="sidebar-brand-text">
                    <h5>Event Scheduling</h5>
                    <small>ISU Santiago</small>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            @if(Auth::user()->isAdministrator())
                <div class="nav-section">Main</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            @endif

            <div class="nav-section">Management</div>
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}" href="{{ route('admin.reservations.index') }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Reservations</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.calendar.*') ? 'active' : '' }}" href="{{ route('admin.calendar.index') }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Calendar</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.venues.map') ? 'active' : '' }}" href="{{ route('admin.venues.map') }}">
                    <i class="bi bi-map"></i>
                    <span>Venue Map</span>
                </a>
            </div>

            @if(Auth::user()->isAdministrator())
                <div class="nav-section">Administration</div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.venues.*') ? 'active' : '' }}" href="{{ route('admin.venues.index') }}">
                        <i class="bi bi-building"></i>
                        <span>Venues</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.emergency.*') ? 'active' : '' }}" href="{{ route('admin.emergency.index') }}">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Emergency</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="user-details">
                    <p class="user-name">{{ Auth::user()->name }}</p>
                    <p class="user-role">{{ Auth::user()->isAdministrator() ? 'Administrator' : 'OSAS Staff' }}</p>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
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