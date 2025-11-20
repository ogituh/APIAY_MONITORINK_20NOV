{{-- <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ url('/') }}">
            <img src="{{ url('https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/KYB_Corporation_company_logo.svg/2560px-KYB_Corporation_company_logo.svg.png') }}"
                class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">Kayaba Indonesia</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">


    <div class="collapse navbar-collapse w-auto max-height-vh-100 h-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            {{-- Dashboard --}}
{{-- Monitoring  --}}
{{-- <x-navlink href="{{ url('/') }}" :active="request()->is('/')" icon="fa-eye">
                Monitoring
                <x-navlink href="{{ url('/dashboard-d') }}" :active="request()->is('dashboard-d')" icon="fa-chart-pie">
                    Dashboard
                </x-navlink>
            </x-navlink> --}}

{{-- Import Stock --}}
{{-- <x-navlink href="{{ url('/import') }}" :active="request()->is('import')" icon="fa-database">
                Import Stock
            </x-navlink> --}}

{{-- Import Orders --}}
{{-- <x-navlink href="{{ url('/order-view') }}" :active="request()->is('order-view')" icon="fa-file-import">
                Import Orders
            </x-navlink> --}}

{{-- Export Data
            <x-navlink href="{{ url('/export') }}" :active="request()->is('export')" icon="fa-file-export">
                Export Data
            </x-navlink> --}}

{{-- Report --}}
{{-- <x-navlink href="{{ url('/report') }}" :active="request()->is('report')" icon="fa-chart-line">
                Report
            </x-navlink> --}}

{{-- Batas navigasi
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">
                    Account Pages
                </h6>
            </li> --}}

{{-- Profile --}}
{{-- <x-navlink href="{{ url('/profile') }}" :active="request()->is('profile')" icon="fa-user">
                Profile
            </x-navlink> --}}

{{-- Sign Up --}}
{{-- <x-navlink href="{{ url('/register') }}" :active="request()->is('register')" icon="fa-user-plus">
                Sign Up
            </x-navlink> --}}

{{-- Logout --}}
{{-- <li class="nav-item">
                <a id="navlink" class="nav-link {{ request()->is('logout') ? 'active' : '' }}" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div id="icon-container"
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sign-out text-dark fs-6"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</aside> --}}
<!-- End Sidebar -->

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ url('/') }}">
            <img src="{{ url('https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/KYB_Corporation_company_logo.svg/2560px-KYB_Corporation_company_logo.svg.png') }}"
                class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">Kayaba Indonesia</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">

    @php
        $user = Auth::user();
        $isAdmin = $user && (int) $user->status === 1;
    @endphp

    <div class="collapse navbar-collapse w-auto max-height-vh-100 h-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            {{-- Menu khusus untuk Super Admin --}}
            @if ($isAdmin)
                {{-- Dashboard --}}
                <x-navlink href="{{ url('/dashboard') }}" :active="request()->is('dashboard')" icon="fa-chart-pie">
                    Dashboard
                </x-navlink>
            @else
                {{-- Menu untuk User non=1 --}}
                <x-navlink href="{{ url('/dashboard-d') }}" :active="request()->is('dashboard-d')" icon="fa-chart-pie">
                    Dashboard
                </x-navlink>
            @endif
            @if ($isAdmin)
            {{-- Monitoring --}}
            <x-navlink href="{{ route('monitoring') }}" :active="request()->is('/monitoring')" icon="fa-eye">
                Monitoring
            </x-navlink>
            @endif
            {{-- @if ($isAdmin) --}}
            {{-- Import Stock
                <x-navlink href="{{ url('/import') }}" :active="request()->is('import')" icon="fa-database">
                    Import Stock
                </x-navlink>
            @endif --}}

            {{-- Import Orders / Import Master Data --}}
            @if ($isAdmin)
                {{-- Jika status = 1 (super admin) --}}
                <x-navlink href="{{ url('/order-view') }}" :active="request()->is('order-view')" icon="fa-file-import">
                    Import Master Data
                </x-navlink>
            @else
                {{-- Jika bukan admin --}}
                <x-navlink href="{{ url('/order-view') }}" :active="request()->is('order-view')" icon="fa-file-import">
                    Import Orders
                </x-navlink>
            @endif

            @if ($isAdmin)
                {{-- Report --}}
                {{-- <x-navlink href="{{ url('/report') }}" :active="request()->is('report')" icon="fa-chart-line">
                    Report
                </x-navlink> --}}
            @endif

            {{-- Batas navigasi --}}
            {{-- Logout --}}
            <li class="nav-item mt-3">
                <a id="navlink" class="nav-link {{ request()->is('logout') ? 'active' : '' }}" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div id="icon-container"
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sign-out text-dark fs-6"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</aside>
<!-- End Sidebar -->
