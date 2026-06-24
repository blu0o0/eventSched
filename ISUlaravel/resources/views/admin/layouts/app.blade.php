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
            width: 260px;
            background: linear-gradient(180deg, #001829 0%, #002a45 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .sidebar-logo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 3px;
        }

        .sidebar-brand-text {
            flex: 1;
        }

        .sidebar-brand-text h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.3;
        }

        .sidebar-brand-text small {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
        }

        .nav-section {
            padding: 10px 20px 5px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 10px;
        }

        .nav-item {
            margin: 2px 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 14px;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(45, 134, 89, 0.2);
            color: #ffffff;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
        }

        .nav-link i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            flex-shrink: 0;
            padding: 20px;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.3) 30%, rgba(0, 0, 0, 0.4) 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
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
        }

        .user-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: 11px;
            margin: 0;
        }

        .btn-logout {
            width: 100%;
            padding: 10px;
            background: rgba(220, 53, 69, 0.15);
            color: #ff6b7a;
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: rgba(220, 53, 69, 0.25);
            color: #ff858f;
            border-color: rgba(220, 53, 69, 0.5);
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            background: #f5f7fa;
        }

        .content-wrapper {
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .page-title {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #001829;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 999;
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 15px;
            box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

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
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>