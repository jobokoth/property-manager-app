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
