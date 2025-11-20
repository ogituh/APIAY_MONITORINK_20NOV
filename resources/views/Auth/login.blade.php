<!--
=========================================================
* Soft UI Dashboard - v1.0.3
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard
* Copyright 2021 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)

* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">

<x-head></x-head>

<body class="bg-light">
    <main class="main-content mt-0">
        <section>
            <div
                class="page-header min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden">

                <div class="container">
                    <div class="row justify-content-center">

                        <!-- Logo -->
                        <div class="col-12 text-center mb-4">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/KYB_Corporation_company_logo.svg/2560px-KYB_Corporation_company_logo.svg.png"
                                style="height: 60px; width: auto;">
                        </div>

                        <!-- Card Login -->
                        <div class="col-xl-4 col-lg-5 col-md-7">
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="card shadow-lg border-0 rounded-4 p-4"
                                style="background: #fff; border: 1px solid #dee2e6; animation: fadeIn 0.6s ease;">

                                <!-- ✅ Ganti bagian ini -->
                                <div class="card-header bg-transparent text-center border-0 mb-3">
                                    <h3 class="fw-bold text-danger text-gradient mb-1">Monitoring Supplier 📦</h3>
                                    <p class="text-muted mb-0">Silakan login untuk mengakses sistem</p>
                                </div>

                                <div class="card-body">

                                    @if ($errors->any())
                                        <div class="alert alert-danger py-2">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <!-- Username -->
                                        <div class="mb-3 position-relative">
                                            <label for="username" class="form-label fw-bold text-muted">
                                                <i class="fas fa-user me-2"></i>Username
                                            </label>
                                            <input type="text" name="username" id="username"
                                                class="form-control border shadow-sm py-3 fs-5"
                                                placeholder="Masukkan Username" required>
                                        </div>

                                        <!-- Password -->
                                        <div class="mb-3 position-relative">
                                            <label for="password" class="form-label fw-bold text-muted">
                                                <i class="fas fa-lock me-2"></i>Password
                                            </label>
                                            <div class="position-relative">
                                                <input id="password" type="password" name="password"
                                                    class="form-control border shadow-sm py-3 fs-5"
                                                    placeholder="Masukkan Password" required>
                                                <button type="button" id="toggle-password"
                                                    class="btn position-absolute end-0 top-50 translate-middle-y me-2"
                                                    style="color: #6c757d;">
                                                    <i id="toggle-password-icon" class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Captcha -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Captcha</label>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span id="captcha-img" class="border rounded p-2 bg-light shadow-sm">
                                                    {!! captcha_img('default') !!}
                                                </span>
                                                <button type="button" id="reload-captcha"
                                                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                                    <i class="fas fa-sync-alt"></i> Refresh
                                                </button>
                                            </div>
                                            <input id="captcha" type="text"
                                                class="form-control border shadow-sm py-3 fs-5" name="captcha"
                                                placeholder="Masukkan Captcha" required>
                                        </div>

                                        <!-- Tombol -->
                                        <div class="text-center mt-4">
                                            <button type="submit"
                                                class="btn bg-gradient-danger w-100 text-uppercase fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 py-3">
                                                <i class="fas fa-sign-in-alt"></i> Sign In
                                            </button>
                                        </div>

                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-warning text-uppercase w-100 fw-bold shadow-sm py-3" 
                                                    data-bs-toggle="modal" data-bs-target="#monitoringModal">
                                                <i class="fas fa-desktop"></i> Monitoring
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <footer class="text-center mt-4">
                                <p class="text-secondary small mb-0">
                                    ©
                                    <script>
                                        document.write(new Date().getFullYear())
                                    </script> PT Kayaba Indonesia
                                </p>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- Modal -->
    <div class="modal fade" id="monitoringModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="monitoringForm">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-key"></i> Akses Monitoring
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if (session('monitoring_error'))
                            <div class="alert alert-danger">{{ session('monitoring_error') }}</div>
                        @endif
                        <label class="form-label fw-bold">Password Monitoring</label>
                        <input type="password" class="form-control form-control-lg" 
                            name="monitoring_password" required autofocus>
                        <div id="alert-container"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">
                            Masuk Monitoring
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-control {
            font-size: 1.2rem !important;
            border: 1px solid #ced4da !important;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #adb5bd;
            opacity: 1;
            transition: opacity 0.2s ease;
        }

        .form-control:focus::placeholder {
            opacity: 0;
        }

        .form-control:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 6px rgba(220, 53, 69, 0.4);
        }

        label {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .btn:hover {
            transform: scale(1.02);
            transition: all 0.2s ease;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggle-password-icon');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            this.style.color = isHidden ? '#dc3545' : '#6c757d';
        });

        // Reload captcha
        document.getElementById('reload-captcha').addEventListener('click', function() {
            fetch('{{ route('reload.captcha') }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captcha-img').innerHTML = data.captcha;
                });
        });
    </script>
    <script>
        document.getElementById('monitoringForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = this.monitoring_password.value;
            const alertDiv = document.getElementById('alert-container');

            fetch('{{ route('monitoring.verify') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ monitoring_password: password })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route('monitoring') }}';
                } else {
                    alertDiv.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show mt-3">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`;
                }
            });
        });
    </script>
</body>

</html>
