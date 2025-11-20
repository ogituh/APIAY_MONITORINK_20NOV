<!DOCTYPE html>
<html lang="en">
<x-head></x-head>

<body class="g-sidenav-show bg-gray-100">
    <x-sidebar />
    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
        <x-navbar :user="$user" title="Report Orders & Stocks" />

        <div class="container-fluid px-4 mt-4">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    Laporan Orders & Stocks per Supplier
                </div>
                <div class="card-body">

                    <!-- Filter tanggal -->
                    <form action="{{ route('report') }}" method="GET" class="row g-3 mb-3 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" class="form-control"
                                value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label fw-semibold">Tanggal Akhir</label>
                            <input type="date" id="end_date" name="end_date" class="form-control"
                                value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i> Tampilkan
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Supplier</th>
                                    <th>Total Orders</th>
                                    <th>Total Stocks</th>
                                    <th>Periode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $suppliers->firstItem() + $index }}</td>
                                        <td class="fw-semibold">{{ $supplier->supplier_name ?? '-' }}</td>
                                        <td>{{ $supplier->orders->count() }}</td>
                                        <td>{{ $supplier->stocks->count() }}</td>
                                        <td>{{ $startDate }} - {{ $endDate }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">Tidak ada data untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $suppliers->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
