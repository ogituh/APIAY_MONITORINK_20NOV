<!DOCTYPE html>
<html lang="en">
<x-head></x-head>

<body class="g-sidenav-show bg-gray-100">
    <x-sidebar />
    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
        {{-- Judul navbar disesuaikan berdasarkan status (role) --}}
        <x-navbar :user="$user"
            title="{{ (int) Auth::user()->status === 1 ? 'Import Master Data' : 'Import Orders' }}" />
        <div class="container-fluid px-4 mt-4">

            {{-- Format Tanggal Konsisten --}}
            @php
                $dateFormat = 'd/m/Y H:i';
            @endphp

            {{-- Notifikasi Sukses/Gagal --}}
            @if (session('success'))
                <div class="alert alert-success text-white fw-bold">{{ session('success') }}</div>
            @endif
            @if (session('import_summary'))
                @php
                    $summary = session('import_summary');
                @endphp
                <div class="alert alert-info text-white">
                    <strong>Summary Import:</strong><br>
                    Total diproses: {{ $summary['total'] }}<br>
                    @if (isset($summary['created']))
                        Data baru ditambahkan: {{ $summary['created'] }}<br>
                    @elseif(isset($summary['updated']))
                        Data diperbarui: {{ $summary['updated'] }}<br>
                    @endif
                    Data skipped: {{ $summary['skipped'] }}
                </div>
            @endif

            {{-- Tombol Download Template (Disesuaikan Status Admin) --}}
            <div class="mb-4">
                @if ((int) Auth::user()->status === 1)
                    <a href="{{ route('orders-template', ['type' => 'empty']) }}" class="btn btn-primary">
                        <i class="bi bi-download me-2"></i> Download Template Master Data
                    </a>
                @else
                    <a href="{{ route('orders-template', ['type' => 'empty']) }}" class="btn btn-success">
                        <i class="bi bi-download me-2"></i> Download Template Orders
                    </a>
                @endif
            </div>

            {{-- Di bagian informasi data terbaru --}}
            @if ((int) Auth::user()->status !== 1)
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-info text-white fw-bold">
                        <i class="bi bi-info-circle me-2"></i> Informasi Data Terbaru
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar-check text-primary me-2"></i>
                                    <strong class="me-2">Tanggal Update Terakhir:</strong>
                                    @if ($lastUploadDate)
                                        <span
                                            class="badge {{ $hasNewAdminData ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $lastUploadDate->format($dateFormat) }}
                                            @if (!$hasNewAdminData && $hasBeenDownloaded)
                                                <small class="ms-1">(Didownload)</small>
                                            @elseif($hasNewAdminData)
                                                <small class="ms-1">(By Admin)</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Belum ada data</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-person-check text-success me-2"></i>
                                    <strong class="me-2">Diupdate Oleh:</strong>
                                    <span
                                        class="badge bg-{{ $lastUploadByLabel === 'Admin' ? 'primary' : 'secondary' }}">
                                        {{ $lastUploadByLabel === '-' ? '—' : $lastUploadByLabel }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-database text-warning me-2"></i>
                                    <strong class="me-2">Total Data Tersedia:</strong>
                                    <span class="badge bg-warning text-dark">{{ $totalOrders }}</span>
                                </div>

                                {{-- 🔥 STATUS INFO --}}
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-arrow-repeat text-info me-2"></i>
                                    <strong class="me-2">Status Data:</strong>
                                    @if ($totalOrders == 0)
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-dash-circle me-1"></i> Tidak Ada Data
                                        </span>
                                    @elseif ($hasNewAdminData)
                                        <span class="badge bg-success">
                                            <i class="bi bi-exclamation-circle me-1"></i> Data Baru Tersedia
                                        </span>
                                    @elseif($hasBeenDownloaded)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-check-circle me-1"></i> Sudah Didownload
                                        </span>
                                    @else
                                        <span class="badge bg-info">
                                            <i class="bi bi-check2-all me-1"></i> Data Tersedia
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Card Form Upload --}}
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div
                    class="card-header {{ (int) Auth::user()->status === 1 ? 'bg-primary' : 'bg-danger' }} text-white fw-bold">
                    @if ((int) Auth::user()->status === 1)
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i> Panduan Upload Master Data (Super Admin)
                    @else
                        <i class="bi bi-file-earmark-text me-2"></i> Panduan Format File Excel Orders
                    @endif
                </div>
                <div class="card-body">

                    @if ((int) Auth::user()->status !== 1)
                        {{-- Tabel Panduan untuk User Biasa --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-hover table-striped text-center">
                                <thead class="table-danger text-white">
                                    <tr>
                                        <th>No</th>
                                        <th>Date Order</th>
                                        <th>Supplier</th>
                                        <th>Part No</th>
                                        <th>Quantity</th>
                                        <th>Stock</th>
                                        <th>Standard</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        <tr>
                                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                                            <td>{{ $order->plan_delv_date ? \Carbon\Carbon::parse($order->plan_delv_date)->format('Y-m') : '-' }}
                                            </td>
                                            <td>{{ $order->supplier }}</td>
                                            <td>{{ $order->part_no }}</td>
                                            <td class="fw-bold text-info">
                                                {{ number_format($order->qty_po, 0, ',', '.') }}
                                            </td>
                                            <td class="fw-bold text-primary">
                                                {{ number_format($order->stock, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $order->standard == 'OK' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $order->standard ?? 'NOK' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-muted">Belum ada data orders dengan stock
                                                tersedia.</td>
                                        </tr>
                                    @endforelse

                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $orders->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        {{-- Info sederhana untuk super admin --}}
                        <div class="alert alert-info text-white mb-4" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Info:</strong> Gunakan file ini untuk memperbarui data Supplier, User, dan Part
                            sekaligus. Pastikan format header sesuai template.
                        </div>
                    @endif

                    {{-- Form Upload --}}
                    <form action="{{ route('import-orders') }}" method="POST" enctype="multipart/form-data"
                        id="uploadForm" class="needs-validation" novalidate>
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold text-primary">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                {{ (int) Auth::user()->status === 1 ? 'Pilih File Master Data' : 'Pilih File Update Stok & Plan' }}
                            </label>

                            <input type="file" name="file" id="file"
                                class="form-control form-control-lg @error('file') is-invalid @enderror"
                                accept=".xlsx,.xls,.csv" required>

                            {{-- Info kecil biar user tahu bisa CSV --}}
                            <div class="form-text mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Format yang didukung:
                                    <span class="badge bg-success">.XLSX</span>
                                    <span class="badge bg-info">.XLS</span>
                                    <span class="badge bg-warning text-dark">.CSV</span>
                                </small>
                            </div>

                            @error('file')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" id="submitButton"
                            class="btn {{ (int) Auth::user()->status === 1 ? 'btn-primary' : 'btn-danger' }} btn-lg w-100 rounded-pill shadow-sm py-3 fs-5 fw-bold">
                            <i class="bi bi-cloud-upload me-2"></i>
                            {{ (int) Auth::user()->status === 1 ? 'Upload Master Data' : 'Upload Data Stok' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- BAGIAN HISTORI PERUBAHAN DATA ORDER --}}
            {{-- ===================================================== --}}
            {{-- <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-info text-white fw-bold">
                    <i class="bi bi-clock-history me-2"></i> Riwayat Perubahan Data
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped text-center">
                            <thead class="table-info text-white">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>Part No</th>
                                    <th>Qty PO Sebelum</th>
                                    <th>Qty PO Sesudah</th>
                                    <th>Stock Sebelum</th>
                                    <th>Stock Sesudah</th>
                                    <th>Standard</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orderHistories as $history)
                                    <tr>
                                        <td>{{ $orderHistories->firstItem() + $loop->index }}</td>
                                        <td>{{ \Carbon\Carbon::parse($history->uploaded_at ?? $history->created_at)->format('d/m/y H:i') }}
                                        </td>
                                        <td>{{ $history->supplier }}</td>
                                        <td>{{ $history->part_no }}</td>
                                        <td class="fw-bold text-secondary">
                                            {{ number_format($history->previous_qty_po, 0, ',', '.') }}
                                        </td>
                                        <td class="fw-bold text-primary">
                                            {{ number_format($history->new_qty_po, 0, ',', '.') }}
                                        </td>
                                        <td class="text-secondary">
                                            {{ number_format($history->previous_stock, 0, ',', '.') }}
                                        </td>
                                        <td class="text-info">
                                            {{ number_format($history->new_stock, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $history->standard == 'OK' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $history->standard ?? 'NOK' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-muted">Belum ada riwayat perubahan data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $orderHistories->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div> --}}

            {{-- ===================================================== --}}
            {{-- BAGIAN HISTORI UPLOAD (Supplier Only) --}}
            {{-- ===================================================== --}}
            @if ((int) Auth::user()->status !== 1)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-secondary text-white fw-bold">
                            <i class="bi bi-database-fill-gear me-2"></i> Riwayat Upload Order Data
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center align-middle">
                                    <thead class="table-secondary text-white">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama File</th>
                                            <th>Tanggal Upload</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($uploadHistories as $history)
                                            <tr>
                                                <td>{{ $loop->iteration + $uploadHistories->firstItem() - 1 }}</td>
                                                <td class="text-start">{{ $history->file_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($history->uploaded_at)->format('d/m/y H:i') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-muted fst-italic py-3">Belum ada
                                                    histori upload order data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $uploadHistories->appends(['tab' => 'orders'])->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ===================================================== --}}
            {{-- BAGIAN HISTORI UPLOAD (Super Admin Only) --}}
            {{-- ===================================================== --}}
            @if ((int) Auth::user()->status === 1)
                <div class="row">
                    {{-- 1. Histori Upload Master Data --}}
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-header bg-primary text-white fw-bold">
                                <i class="bi bi-database-fill-gear me-2"></i> Riwayat Upload Master Data
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover text-center align-middle">
                                        <thead class="table-primary text-white">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama File</th>
                                                <th>Tanggal Upload</th>
                                                <th>Oleh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($masterHistories as $history)
                                                <tr>
                                                    <td>{{ $loop->iteration + $masterHistories->firstItem() - 1 }}</td>
                                                    <td class="text-start">{{ $history->file_name }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($history->uploaded_at)->format('d/m/y H:i') }}
                                                    </td>
                                                    <td><span class="badge bg-info">{{ $history->upload_by }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-muted fst-italic py-3">Belum ada
                                                        histori upload master data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $masterHistories->appends(['tab' => 'master'])->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Histori Upload Semua User --}}
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 rounded-3 h-100">
                            <div class="card-header bg-warning text-dark fw-bold">
                                <i class="bi bi-people-fill me-2"></i> Log Aktivitas Supplier
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-hover text-center align-middle"
                                        style="font-size: 0.9rem;">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>File</th>
                                                <th>Tgl</th>
                                                <th>User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($allUserHistories as $history)
                                                <tr>
                                                    <td class="text-start text-truncate" style="max-width: 150px;"
                                                        title="{{ $history->file_name }}">
                                                        {{ $history->file_name }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($history->uploaded_at)->format('d/m/y H:i') }}
                                                    </td>
                                                    <td><strong>{{ $history->upload_by }}</strong></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-muted py-3">Belum ada aktivitas
                                                        upload dari supplier manapun.</td>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-center mt-2">
                                        {{ $allUserHistories->appends(['tab' => 'all'])->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Histori Upload Pribadi Admin --}}
                    {{-- <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 rounded-3 h-100">
                            <div class="card-header bg-secondary text-white fw-bold">
                                <i class="bi bi-person-fill me-2"></i> Upload Orders Saya
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-center align-middle"
                                        style="font-size: 0.9rem;">
                                        <thead class="table-secondary text-white">
                                            <tr>
                                                <th>File</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($uploadHistories as $history)
                                                <tr>
                                                    <td class="text-start text-truncate" style="max-width: 200px;"
                                                        title="{{ $history->file_name }}">
                                                        {{ $history->file_name }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($history->uploaded_at)->format('d/m/y H:i') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-muted py-3">Anda belum pernah
                                                        upload file orders biasa.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-center mt-2">
                                        {{ $uploadHistories->appends(['tab' => 'personal'])->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            @endif

            {{-- Overlay Spinner Global --}}
            <div id="globalLoader"
                style="
                    display: none;
                    position: fixed;
                    z-index: 9999;
                    inset: 0;
                    background: rgba(255,255,255,0.8);
                    backdrop-filter: blur(2px);
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                ">
                <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"></div>
                <p class="mt-3 fw-bold text-primary fs-5">
                    <i class="bi bi-cloud-upload me-2"></i> Sedang mengupload file, mohon tunggu...
                </p>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.getElementById('uploadForm');
            const globalLoader = document.getElementById('globalLoader');
            const fileInput = document.getElementById('file');
            const submitButton = document.getElementById('submitButton');

            uploadForm.addEventListener('submit', function(e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Pilih file terlebih dahulu!');
                    return;
                }

                globalLoader.style.display = 'flex';
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Memproses...';
            });
        });
    </script>

    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name.toLowerCase();
                const submitBtn = document.getElementById('submitButton');
                const label = document.querySelector('label[for="file"]');

                if (fileName.endsWith('.csv')) {
                    submitBtn.innerHTML = '<i class="bi bi-file-earmark-text me-2"></i> Upload File CSV';
                    label.innerHTML =
                        '<i class="bi bi-file-earmark-text-fill text-success"></i> File CSV Siap Diupload!';
                } else if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
                    submitBtn.innerHTML = '<i class="bi bi-file-earmark-excel me-2"></i> Upload File Excel';
                    label.innerHTML =
                        '<i class="bi bi-file-earmark-spreadsheet-fill text-success"></i> File Excel Siap Diupload!';
                }
            }
        });
    </script>
</body>

</html>
