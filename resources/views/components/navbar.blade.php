<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

@props(['title', 'user'])

<style>
    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #000000;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        margin: 0 auto;
    }
</style>

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
    navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{ $title }}</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">{{ $title }}</h6>
        </nav>

        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item mx-3 d-none d-lg-block">
                    <span id="currentTime" class="badge bg-danger text-white fw-bold px-3 py-2"
                        style="font-size: 1rem;">
                    </span>
                </li>

                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>

                {{-- DROPDOWN NOTIF --}}
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="dropdownNotif" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa fa-bell text-lg mx-3 cursor-pointer"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownNotif">
                        <li class="text-center text-muted">No notifications</li>
                    </ul>
                </li>

                {{-- DROPDOWN USER --}}
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body font-weight-bold px-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user text-lg me-sm-1"></i>
                        <span class="d-sm-inline d-none">{{ $user->bpid }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-3"
                        aria-labelledby="dropdownMenuButton" style="min-width: 200px;">
                        <li class="text-center mb-3">
                            <div class="rounded-circle bg-secondary bg-gradient text-white d-flex align-items-center justify-content-center p-4 mx-auto"
                                style="width: 40px; height: 40px; font-size: 28px;">
                                <i class="fa fa-user"></i>
                            </div>
                            <h6 class="fw-bold mt-2 mb-0">{{ $user->bpid }}</h6>
                            <small class="text-muted">{{ $user->username }}</small>
                        </li>
                        <li>
                            <hr class="dropdown-divider border-secondary my-2">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit"
                                    class="btn btn-danger btn-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa-solid fa-door-open"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>

{{-- JAM REALTIME --}}
<script>
    function updateTime() {
        const now = new Date();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
        const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        const formatted =
            `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} - ${hours}:${minutes}:${seconds}`;
        document.getElementById('currentTime').textContent = formatted;
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>
