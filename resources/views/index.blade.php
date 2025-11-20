<!DOCTYPE html>
<html lang="en">
<x-head></x-head>

<body class="bg-light min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 px-4 d-flex align-items-center">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('login.view') }}">
            <img src="{{ asset('img/logo-kyb.png') }}" style="height: 70px; width: auto;" alt="Logo">
        </a>

        <div class="ms-auto">
            <span id="currentTime" class="badge bg-danger text-white fw-bold px-3 py-2" style="font-size: 1rem;">
            </span>
        </div>
    </nav>

    <div class="container-fluid mt-5">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-center align-items-center border-0">
                <div class="row text-center">
                    <div class="col-12">
                        <h1 class="mb-2 fw-bold text-uppercase" style="letter-spacing: 2px">Board Monitoring Supplier
                        </h1>
                    </div>
                </div>
            </div>

            <form action="{{ route('monitoring') }}" method="GET" class="row g-3">
                <div class="row">
                    <div class="col-2 mx-2">
                        <label for="start_date" class="form-label fw-bold small d-block">Start Date</label>
                        <input type="date" class="form-control short-date-input" id="start_date" name="start_date"
                            value="{{ $startDate }}" required>
                        <label for="end_date" class="form-label fw-bold small d-block">End Date</label>
                        <input type="date" class="form-control short-date-input" id="end_date" name="end_date"
                            value="{{ $endDate }}"12 required>
                    </div>

                    <div class="row mx-2 mt-4">
                        <div class="col-4">
                            <button type="submit" class="fs-6 badge bg-gradient-primary me-2 mb-2 border-0">
                                Filter
                            </button>

                            <a href="{{ route('monitoring') }}"
                                class="fs-6 badge bg-gradient-secondary me-2 border-0616 mb-2"
                                style="text-decoration: none">
                                Reset
                            </a>

                            <a href="{{ route('monitoring.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                                class="fs-6 badge bg-gradient-success mb-2 border-0" style="text-decoration: none">
                                Export Excel
                            </a>
                        </div>
                    </div>
            </form>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="monitoring" class="table table-hover align-middle table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Supplier</th>
                                <th class="text-center">Total Item</th>
                                <th class="text-center">OK</th>
                                <th class="text-center">NOK</th>
                                <th class="text-center">Last Update</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliersWithSummary as $data)
                                @php
                                    $supplier = $data['supplier'];
                                    $summary = $data['summary'];

                                    $isVeryRecent = false;
                                    if ($summary['last_update']) {
                                        $lastUpdate = \Carbon\Carbon::parse($summary['last_update']);
                                        $isVeryRecent = $lastUpdate->diffInMinutes(now()) <= 5;
                                    }

                                    $percentage =
                                        $summary['total_items'] > 0
                                            ? round(($summary['ok_count'] / $summary['total_items']) * 100, 1)
                                            : 0;
                                @endphp

                                <tr class="{{ $isVeryRecent ? 'highlight-new animate__animated animate__fadeInDown' : '' }}"
                                    style="{{ $isVeryRecent ? 'animation-delay: ' . $loop->index * 0.15 . 's;' : '' }}">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $supplier->name }}</strong><br>
                                        <small class="text-muted">BPID: {{ $supplier->bpid }}</small>

                                        @if ($isVeryRecent)
                                            <span
                                                class="badge bg-danger animate__animated animate__heartBeat animate__infinite ms-2">
                                                BARU!
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center"><span
                                            class="badge bg-info fs-6">{{ $summary['total_items'] }}</span></td>
                                    <td class="text-center"><span
                                            class="badge bg-success fs-6">{{ $summary['ok_count'] }}</span></td>
                                    <td class="text-center"><span
                                            class="badge bg-danger fs-6">{{ $summary['nok_count'] }}</span></td>
                                    <td class="text-center">
                                        @if ($summary['last_update'])
                                            <span class="fw-bold text-primary">
                                                {{ \Carbon\Carbon::parse($summary['last_update'])->format('d M Y H:i') }}
                                            </span>
                                            @if ($isVeryRecent)
                                                <br><small class="text-success fw-bold">baru saja</small>
                                            @endif
                                        @else
                                            <span class="text-muted">No Data</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="badge bg-gradient-primary fs-6 border-0" data-bs-toggle="modal"
                                            data-bs-target="#detailModal-{{ $supplier->bpid }}" type="button">
                                            <!-- TAMBAHKAN type="button" -->
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        @if ($suppliersWithSummary->count() > 0)
                            @php
                                $totalItems = $suppliersWithSummary->sum('summary.total_items');
                                $totalOK = $suppliersWithSummary->sum('summary.ok_count');
                                $totalNOK = $suppliersWithSummary->sum('summary.nok_count');
                                $overallPercentage = $totalItems > 0 ? round(($totalOK / $totalItems) * 100, 1) : 0;
                            @endphp
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-center"><strong>{{ $totalItems }}</strong></td>
                                    <td class="text-center"><strong class="text-success">{{ $totalOK }}</strong>
                                    </td>
                                    <td class="text-center"><strong class="text-danger">{{ $totalNOK }}</strong>
                                    </td>
                                    <td>-</td>
                                    <td class="text-center">
                                        <strong
                                            class="{{ $overallPercentage >= 80 ? 'text-success' : ($overallPercentage >= 50 ? 'text-warning' : 'text-danger') }}">
                                            {{ $overallPercentage }}%
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                {{-- MODAL DETAIL (TIDAK DIUBAH SAMA SEKALI) --}}
                @foreach ($suppliersWithSummary as $data)
                    @php
                        $supplier = $data['supplier'];
                        $summary = $data['summary'];
                    @endphp
                    <div class="modal fade" id="detailModal-{{ $supplier->bpid }}" tabindex="-1"
                        aria-labelledby="detailModalLabel-{{ $supplier->bpid }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="detailModalLabel-{{ $supplier->bpid }}">
                                        Detail Supplier {{ $supplier->name }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4"><strong>Supplier:</strong> {{ $supplier->name }}</div>
                                        <div class="col-md-4"><strong>BPID:</strong> {{ $supplier->bpid }}</div>
                                        <div class="col-md-4"><strong>Total Items:</strong> <span
                                                class="badge bg-info">{{ $summary['total_items'] }}</span></div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="modalTable-{{ $supplier->bpid }}"
                                            class="table table-bordered table-hover table-striped text-center">
                                            <thead class="table-danger text-white">
                                                <tr>
                                                    <th class="text-center">No</th>
                                                    <th class="text-center">Date Order</th>
                                                    <th class="text-center">Part No</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-center">Stock</th>
                                                    <th class="text-center">Standard</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($summary['all_orders'] as $order)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $order->plan_delv_date }}</td>
                                                        <td>{{ $order->part_no }}</td>
                                                        <td class="fw-bold text-info">
                                                            {{ number_format($order->qty_po, 0, ',', '.') }}</td>
                                                        <td class="fw-bold text-primary">
                                                            {{ number_format($order->stock, 0, ',', '.') }}</td>
                                                        <td>
                                                            <span
                                                                class="badge {{ $order->standard == 'OK' ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $order->standard ?? 'NOK' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="card bg-success text-white text-center p-2">
                                                <h6 class="mb-0">OK</h6>
                                                <h4 class="mb-0">{{ $summary['ok_count'] }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-danger text-white text-center p-2">
                                                <h6 class="mb-0">NOK</h6>
                                                <h4 class="mb-0">{{ $summary['nok_count'] }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-info text-white text-center p-2">
                                                <h6 class="mb-0">Rate</h6>
                                                <h4 class="mb-0">
                                                    {{ $summary['total_items'] > 0 ? round(($summary['ok_count'] / $summary['total_items']) * 100, 1) : 0 }}%
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- SEMUA YANG BERHUBUNGAN AUTO REFRESH DIHAPUS -->
    <!-- Hanya jam real-time + hapus highlight baru setelah 10 detik -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        tr.highlight-new {
            background: linear-gradient(90deg, #ff9a9e 0%, #fad0c4 99%) !important;
            box-shadow: 0 4px 15px rgba(255, 105, 105, 0.4);
            font-weight: bold;
        }

        @keyframes pulseGlow {
            from {
                box-shadow: 0 0 5px #ff6b6b;
            }

            to {
                box-shadow: 0 0 20px #ff3838;
            }
        }

        tr.highlight-new td {
            animation: pulseGlow 2s infinite alternate;
        }
    </style>

    <!-- SCRIPT BARU: HANYA JAM + HAPUS HIGHLIGHT (TIDAK ADA REFRESH) -->
    <script>
        // Jam real-time di navbar
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

        // Hanya update jam, TIDAK refresh halaman
        setInterval(updateTime, 1000);
        updateTime();

        // Hapus highlight "BARU!" setelah 10 detik
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.querySelectorAll('tr.highlight-new').forEach(row => {
                    row.classList.remove('highlight-new', 'animate__animated',
                        'animate__fadeInDown');
                    row.style.cssText = '';
                });
            }, 10000);
        });
    </script>

    <!-- DataTables tetap jalan -->
    <script>
        $(document).ready(function() {
            $('#monitoring').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                autoWidth: false,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        next: ">",
                        previous: "<"
                    }
                }
            });

            $('table[id^="modalTable"]').each(function() {
                $(this).DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    autoWidth: false,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        paginate: {
                            next: ">",
                            previous: "<"
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
