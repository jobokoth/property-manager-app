<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Property Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap Theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="app-body">
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark app-navbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <span class="brand-mark">
                        <i class="fa-solid fa-building"></i>
                    </span>
                    <span class="brand-text">{{ config('app.name', 'Property Manager') }}</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fa-solid fa-chart-pie me-1"></i> Dashboard
                                </a>
                            </li>

                            @can('properties.view')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('properties*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-building me-1"></i> Properties
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('properties.index') }}">View Properties</a></li>
                                        @can('properties.create')
                                            <li><a class="dropdown-item" href="{{ route('properties.create') }}">Add Property</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can('properties.manage_units')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('units*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-door-open me-1"></i> Units
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('units.index') }}">View Units</a></li>
                                        @can('properties.manage_units')
                                            <li><a class="dropdown-item" href="{{ route('units.create') }}">Add Unit</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can('properties.manage_tenants')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('tenancies*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-users me-1"></i> Tenancies
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('tenancies.index') }}">View Tenancies</a></li>
                                        @can('properties.manage_tenants')
                                            <li><a class="dropdown-item" href="{{ route('tenancies.create') }}">Add Tenancy</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can('payments.view')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('payments*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-credit-card me-1"></i> Payments
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('payments.index') }}">View Payments</a></li>
                                        @can('payments.ingest_mpesa')
                                            <li><a class="dropdown-item" href="{{ route('payments.create') }}">Record Payment</a></li>
                                            <li><a class="dropdown-item" href="{{ route('mpesa-messages.create') }}">Upload M-Pesa Message</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @can('requests.view')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('service-requests*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-screwdriver-wrench me-1"></i> Service Requests
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('service-requests.index') }}">View Requests</a></li>
                                        @can('requests.create')
                                            <li><a class="dropdown-item" href="{{ route('service-requests.create') }}">New Request</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan

                            @if(auth()->user()->hasRole('vendor'))
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('vendor*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-toolbox me-1"></i> Vendor
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                                        <li><a class="dropdown-item" href="{{ route('vendor.quotes') }}">My Quotes</a></li>
                                        <li><a class="dropdown-item" href="{{ route('vendor.invoices') }}">My Invoices</a></li>
                                        <li><a class="dropdown-item" href="{{ route('vendor.payments') }}">My Payments</a></li>
                                    </ul>
                                </li>
                            @endif

                            @if(auth()->user()->hasRole('caretaker'))
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('my-tasks*') || request()->is('caretaker-tasks*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-clipboard-list me-1"></i> Caretaker
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('caretaker-tasks.my-tasks') }}">My Tasks</a></li>
                                        <li><a class="dropdown-item" href="{{ route('service-requests.index') }}">Service Requests</a></li>
                                    </ul>
                                </li>
                            @endif

                            @can('properties.manage_caretakers')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('caretakers*') || request()->is('caretaker-tasks*') || request()->is('tenant-invites*') || request()->is('manage/*') || request()->is('messages*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-users-gear me-1"></i> Manage
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('messages.index') }}">Messages</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('caretakers.index') }}">Caretakers</a></li>
                                        <li><a class="dropdown-item" href="{{ route('caretaker-tasks.index') }}">Caretaker Tasks</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('tenant-invites.index') }}">Tenant Invitations</a></li>
                                        <li><a class="dropdown-item" href="{{ route('manage.tenants.index') }}">Manage Tenants</a></li>
                                        <li><a class="dropdown-item" href="{{ route('manage.vendors.index') }}">Manage Vendors</a></li>
                                    </ul>
                                </li>
                            @endcan

                            @if(auth()->user()->hasRole('super_admin'))
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('admin*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-cog me-1"></i> Admin
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">User Management</a></li>
                                    </ul>
                                </li>
                            @endif
                        @endauth
                    </ul>

                </div>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto align-items-center">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item me-2">
                                <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Login</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="btn btn-light btn-sm" href="{{ route('register') }}">Get Started</a>
                            </li>
                        @endif
                    @else
                        <!-- Notification Bell -->
                        <li class="nav-item me-2">
                            <a class="nav-link position-relative {{ request()->is('messages*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                                <i class="fa-solid fa-paper-plane"></i>
                                @if($unreadMessageCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                        {{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-bell"></i>
                                @if($unreadNotificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    @if($unreadNotificationCount > 0)
                                        <span class="badge bg-primary">{{ $unreadNotificationCount }} new</span>
                                    @endif
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @forelse($recentNotifications as $notification)
                                    <li>
                                        <a class="dropdown-item {{ $notification->status !== 'read' ? 'fw-bold' : '' }}" href="{{ route('notifications.index') }}">
                                            <div class="d-flex align-items-start">
                                                @if($notification->status !== 'read')
                                                    <span class="badge bg-primary me-2" style="margin-top: 4px;">New</span>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <div class="text-truncate" style="max-width: 220px;">{{ $notification->message->subject ?? 'Notification' }}</div>
                                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item text-muted">No notifications</span></li>
                                @endforelse
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center text-primary" href="{{ route('notifications.index') }}">View All Notifications</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="avatar-sm">
                                    <i class="fa-solid fa-circle-user"></i>
                                </span>
                                <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa-solid fa-user me-2"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endguest
                </ul>
            </div>
        </nav>

        <div class="app-shell">
            @auth
                <aside class="app-sidebar">
                    <div class="sidebar-section">
                        <p class="sidebar-title">Workspace</p>
                        <a class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fa-solid fa-chart-pie"></i> Dashboard
                        </a>
                        @can('properties.view')
                            <a class="sidebar-link {{ request()->is('properties*') ? 'active' : '' }}" href="{{ route('properties.index') }}">
                                <i class="fa-solid fa-building"></i> Properties
                            </a>
                        @endcan
                        @can('properties.manage_units')
                            <a class="sidebar-link {{ request()->is('units*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                                <i class="fa-solid fa-door-open"></i> Units
                            </a>
                        @endcan
                        @can('properties.manage_tenants')
                            <a class="sidebar-link {{ request()->is('tenancies*') ? 'active' : '' }}" href="{{ route('tenancies.index') }}">
                                <i class="fa-solid fa-users"></i> Tenancies
                            </a>
                        @endcan
                        @can('payments.view')
                            <a class="sidebar-link {{ request()->is('payments*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                                <i class="fa-solid fa-credit-card"></i> Payments
                            </a>
                            <a class="sidebar-link {{ request()->is('mpesa-messages*') ? 'active' : '' }}" href="{{ route('mpesa-messages.create') }}">
                                <i class="fa-solid fa-mobile-screen-button"></i> Upload M-Pesa
                            </a>
                        @endcan
                        @can('requests.view')
                            <a class="sidebar-link {{ request()->is('service-requests*') ? 'active' : '' }}" href="{{ route('service-requests.index') }}">
                                <i class="fa-solid fa-screwdriver-wrench"></i> Service Requests
                            </a>
                        @endcan
                        @if(auth()->user()->hasRole(['tenant', 'owner', 'super_admin']))
                            <a class="sidebar-link {{ request()->is('statements*') ? 'active' : '' }}" href="{{ route('statements.index') }}">
                                <i class="fa-solid fa-file-invoice"></i> Statements
                            </a>
                        @endif
                        <a class="sidebar-link {{ request()->is('notifications*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                            <i class="fa-solid fa-bell"></i> Notifications
                            @if($unreadNotificationCount > 0)
                                <span class="badge bg-danger ms-auto">{{ $unreadNotificationCount }}</span>
                            @endif
                        </a>
                        <a class="sidebar-link {{ request()->is('messages*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                            <i class="fa-solid fa-paper-plane"></i> Messages
                        </a>
                        @can('water.readings.view')
                            <a class="sidebar-link {{ request()->is('water*') ? 'active' : '' }}" href="{{ route('water.index') }}">
                                <i class="fa-solid fa-droplet"></i> Water
                            </a>
                        @endcan
                        @if(auth()->user()->hasRole('vendor'))
                            <p class="sidebar-title mt-4">Vendor</p>
                            <a class="sidebar-link {{ request()->is('vendor/dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}">
                                <i class="fa-solid fa-toolbox"></i> My Jobs
                            </a>
                            <a class="sidebar-link {{ request()->is('vendor/quotes') ? 'active' : '' }}" href="{{ route('vendor.quotes') }}">
                                <i class="fa-solid fa-file-invoice-dollar"></i> My Quotes
                            </a>
                            <a class="sidebar-link {{ request()->is('vendor/invoices') ? 'active' : '' }}" href="{{ route('vendor.invoices') }}">
                                <i class="fa-solid fa-file-invoice"></i> My Invoices
                            </a>
                        @endif
                        @if(auth()->user()->hasRole('caretaker'))
                            <p class="sidebar-title mt-4">Caretaker</p>
                            <a class="sidebar-link {{ request()->is('my-tasks') ? 'active' : '' }}" href="{{ route('caretaker-tasks.my-tasks') }}">
                                <i class="fa-solid fa-clipboard-list"></i> My Tasks
                            </a>
                        @endif
                        @can('properties.manage_caretakers')
                            <p class="sidebar-title mt-4">Management</p>
                            <a class="sidebar-link {{ request()->is('messages*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                                <i class="fa-solid fa-paper-plane"></i> Messages
                            </a>
                            <a class="sidebar-link {{ request()->is('caretakers*') ? 'active' : '' }}" href="{{ route('caretakers.index') }}">
                                <i class="fa-solid fa-user-tie"></i> Caretakers
                            </a>
                            <a class="sidebar-link {{ request()->is('caretaker-tasks*') && !request()->is('my-tasks') ? 'active' : '' }}" href="{{ route('caretaker-tasks.index') }}">
                                <i class="fa-solid fa-tasks"></i> Caretaker Tasks
                            </a>
                            <a class="sidebar-link {{ request()->is('tenant-invites*') ? 'active' : '' }}" href="{{ route('tenant-invites.index') }}">
                                <i class="fa-solid fa-envelope-open-text"></i> Tenant Invites
                            </a>
                            <a class="sidebar-link {{ request()->is('manage/tenants*') ? 'active' : '' }}" href="{{ route('manage.tenants.index') }}">
                                <i class="fa-solid fa-user-group"></i> Manage Tenants
                            </a>
                            <a class="sidebar-link {{ request()->is('manage/vendors*') ? 'active' : '' }}" href="{{ route('manage.vendors.index') }}">
                                <i class="fa-solid fa-truck"></i> Manage Vendors
                            </a>
                        @endcan
                        @if(auth()->user()->hasRole('super_admin'))
                            <p class="sidebar-title mt-4">Administration</p>
                            <a class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fa-solid fa-users-cog"></i> User Management
                            </a>
                        @endif
                        <p class="sidebar-title mt-4">Account</p>
                        <a class="sidebar-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                </aside>
                <main class="app-main">
                    <div class="container-xl">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show app-alert" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show app-alert" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            @else
                <main class="app-content">
                    <div class="container-xl">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show app-alert" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show app-alert" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            @endauth
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>
</html>
