<!DOCTYPE html>
<html lang="en">

<x-head></x-head>

<body class="bg-light">

    <div id="page-content-wrapper" class="w-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-danger" id="sidebarToggle">☰</button>
                <h5 class="ms-3 mb-0 fw-bold text-danger text-uppercase fw-bolder" style="letter-spacing: 2px">
                    Import Data Excel
                </h5>

                <div class="ms-auto d-flex align-items-center">
                    @if (Auth::check())
                        <div class="alert alert-info mb-0 me-3 py-1 px-3">
                            Anda login sebagai: <strong>{{ Auth::user()->username }}</strong>
                        </div>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Yakin ingin logout?')">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>
        {{-- <x-sidebar /> --}}


        <div class="container-fluid px-4 mt-4">
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

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-danger text-white fw-bold">
                    Panduan Format File Excel
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center">
                            <thead class="table-danger text-white">
                                <tr>
                                    <th>No</th>
                                    <th>Supplier</th>
                                    <th>Part No</th>
                                    <th>Quantity</th>
                                    <th>Insert Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stocks as $stock)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $stock->supplier->bpid }}</td>
                                        <td>{{ $stock->part->name }}</td>
                                        <td>{{ $stock->quantity }}</td>
                                        <td>{{ $stock->insert_date }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">Contoh data diisi sesuai format</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label fw-semibold">Pilih File Excel</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls"
                                required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 rounded-pill py-2 fs-5">
                            <i class="bi bi-upload me-2"></i> Upload File
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-secondary text-white fw-bold">
                    Riwayat Upload
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama File</th>
                                    <th>Tanggal Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($histories as $history)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $history->file_name }}</td>
                                        <td>{{ $history->uploaded_at }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">Belum ada histori upload.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    document.getElementById("sidebarToggle").addEventListener("click", function() {
        document.getElementById("wrapper").classList.toggle("toggled");
    });
</script>

</html>
