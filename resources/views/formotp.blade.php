<!DOCTYPE html>
<html lang="en">

<x-head></x-head>

<body class="">
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-75 mt-5 d-flex align-items-center justify-content-center">
                <div class="container">
                    <div class="row justify-content-center">
                        <!-- Logo -->
                        <div class="col-12 text-center mb-3">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/KYB_Corporation_company_logo.svg/2560px-KYB_Corporation_company_logo.svg.png"
                                style="height: 60px; width: auto;">
                        </div>

                        <div class="col-xl-4 col-lg-5 col-md-6">
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="card card-plain blur blur-rounded-sm shadow-lg mt-3 p-3">
                                <div class="card-header text-center">
                                    <h3 class="font-weight-bolder text-danger text-gradient">Verifikasi OTP</h3>
                                    <p class="mb-0">Masukkan kode OTP yang sudah dikirimkan ke nomor Anda</p>
                                </div>
                                <div class="card-body">

                                    {{-- Pesan Nomor HP Tujuan OTP --}}
                                    @if (session('otp_hp'))
                                        @php
                                            $hp = session('otp_hp');
                                            // Masking: ambil 3 digit depan + **** + 2 digit belakang
                                            $masked = substr($hp, 0, 3) . '****' . substr($hp, -2);
                                        @endphp
                                        <p class="text-muted text-center mb-3">
                                            Kode OTP telah dikirim ke nomor <br>
                                            <span class="fw-bold text-dark">{{ $masked }}</span>
                                        </p>

                                        {{-- Tambahan waktu berlaku OTP --}}
                                        <p class="text-center text-danger fw-bold mb-3" id="otpExpiryTimer"
                                            style="font-size: 0.95rem;">
                                            OTP Expires In <span id="otpExpire">05:00</span> minutes
                                        </p>
                                    @endif

                                    <form action="{{ route('otp.verify') }}" method="POST" id="otpForm">
                                        @csrf
                                        <div class="d-flex justify-content-center gap-2 mb-3">
                                            @for ($i = 1; $i <= 6; $i++)
                                                <input type="text" name="otp[]" maxlength="1"
                                                    class="form-control text-center otp-input rounded shadow-sm"
                                                    style="width: 50px; height: 50px; font-size: 1.5rem; font-weight: bold;"
                                                    required>
                                            @endfor
                                        </div>
                                        <button type="submit"
                                            class="btn btn-danger w-100 rounded-pill py-2 fs-5">Verifikasi</button>
                                    </form>
                                    {{-- end form otp --}}

                                    {{-- otp resend --}}
                                    <div class="text-center mt-4">
                                        <p class="text-muted mb-2" style="font-size: 0.95rem;">
                                            Tidak menerima kode OTP?
                                        </p>

                                        <!-- Tombol Kirim Ulang -->
                                        <button type="button" id="resendBtn"
                                            class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold"
                                            style="font-size: 0.9rem;">
                                            <i class="fas fa-redo-alt me-2"></i> Kirim Ulang
                                        </button>

                                        <!-- Countdown -->
                                        <p id="countdown" class="text-muted mt-2"
                                            style="display: none; font-size: 0.9rem;">
                                            Kirim ulang tersedia dalam <span id="timer"
                                                class="fw-bold text-danger">30</span> detik
                                        </p>
                                    </div>
                                    {{-- end otp resend --}}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- skrip otp --}}
    <script>
        // === Script Resend OTP ===
        document.addEventListener('DOMContentLoaded', function() {
            const resendBtn = document.getElementById('resendBtn');
            const countdown = document.getElementById('countdown');
            const timerSpan = document.getElementById('timer');
            let timer = 30;
            let countdownInterval;

            resendBtn.addEventListener('click', function() {
                // Nonaktifkan tombol sementara
                resendBtn.disabled = true;
                resendBtn.classList.add('opacity-50');
                countdown.style.display = 'block';
                timer = 30;
                timerSpan.textContent = timer;

                // 🔥 Kirim request ke route resend OTP
                fetch('{{ route('otp.resend') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Tampilkan notifikasi sederhana
                            alert(data.message);
                        } else {
                            alert('Gagal mengirim ulang OTP: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengirim ulang OTP.');
                    });

                // Jalankan timer countdown
                countdownInterval = setInterval(() => {
                    timer--;
                    timerSpan.textContent = timer;
                    if (timer <= 0) {
                        clearInterval(countdownInterval);
                        resendBtn.disabled = false;
                        resendBtn.classList.remove('opacity-50');
                        countdown.style.display = 'none';
                    }
                }, 1000);
            });
        });
    </script>
    {{-- end skrip otp --}}

    <script>
        document.querySelectorAll('.otp-input').forEach((input, index, inputs) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === "Backspace" && index > 0 && !input.value) {
                    inputs[index - 1].focus();
                }
            });
        });
    </script>

    <script>
        // === Timer masa berlaku OTP (5 menit) ===
        document.addEventListener('DOMContentLoaded', function() {
            const otpExpire = document.getElementById('otpExpire');
            let totalSeconds = 300; // 5 menit = 300 detik

            const countdown = setInterval(() => {
                totalSeconds--;

                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;

                otpExpire.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                // Kalau waktu habis
                if (totalSeconds <= 0) {
                    clearInterval(countdown);
                    otpExpire.textContent = "00:00";
                    alert("Resend OTP.");
                    // Optional: disable form input
                    document.querySelectorAll('.otp-input').forEach(input => input.disabled = true);
                }
            }, 1000);
        });
    </script>

</body>

</html>
