<!DOCTYPE html>
<html lang="en">
<x-head></x-head>

<body class="g-sidenav-show bg-gray-100">
    <x-sidebar />
    <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">
        <x-navbar :user="$user" title="Dashboard Monitoring Stok" />

        <div class="container-fluid px-4 mt-4">
            <!-- KPI Cards -->
            <div class="row mb-4">
                @if ($isSuperAdmin)
                    <!-- KPI untuk Super Admin -->
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-primary text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Suppliers</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['total_suppliers'] }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-people-fill text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-success text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Parts</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['total_parts'] }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-box-seam text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-info text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Orders</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['total_orders'] }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-cart-check text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-warning text-dark shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Stock</div>
                                        <div class="h4 fw-bold mb-0">
                                            {{ number_format($kpiData['total_stock_value'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-dark bg-opacity-10 rounded-circle">
                                        <i class="bi bi-box text-dark"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-danger text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">On-Time Delivery</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['on_time_delivery'] }}%</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-clock-history text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-dark text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Stock Accuracy</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['stock_accuracy'] }}%</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-check-circle text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- KPI untuk Supplier -->
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-primary text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Parts</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['total_parts'] }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-box-seam text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-success text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Orders</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['total_orders'] }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-cart-check text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-info text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Stock</div>
                                        <div class="h4 fw-bold mb-0">
                                            {{ number_format($kpiData['total_stock'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-box text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-warning text-dark shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Total Qty PO</div>
                                        <div class="h4 fw-bold mb-0">
                                            {{ number_format($kpiData['total_qty_po'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="icon icon-shape bg-dark bg-opacity-10 rounded-circle">
                                        <i class="bi bi-file-text text-dark"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-danger text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Standard OK Rate</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['standard_ok_rate'] }}%</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-check-circle text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card bg-dark text-white shadow-lg border-0 rounded-3 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-sm fw-bold opacity-7">Stock Adequacy</div>
                                        <div class="h4 fw-bold mb-0">{{ $kpiData['stock_adequacy'] }}%</div>
                                    </div>
                                    <div class="icon icon-shape bg-white bg-opacity-10 rounded-circle">
                                        <i class="bi bi-bar-chart text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Charts Section -->
            <div class="row mb-4">
                <!-- Monthly Orders Chart -->
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="bi bi-graph-up me-2"></i>
                            {{ $isSuperAdmin ? 'Monthly Orders Trend' : 'My Monthly Orders Trend' }} -
                            {{ date('Y') }}
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyOrdersChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="col-xl-4 col-lg-5 mb-4">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="bi bi-trophy me-2"></i>
                            {{ $isSuperAdmin ? 'Top Suppliers Performance' : 'Top Parts Performance' }}
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Charts -->
            <div class="row mb-4">
                <!-- Stock Distribution Chart -->
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-info text-white fw-bold">
                            <i class="bi bi-pie-chart me-2"></i>
                            {{ $isSuperAdmin ? 'Stock Distribution' : 'Stock Adequacy Status' }}
                        </div>
                        <div class="card-body">
                            <canvas id="stockDistributionChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-xl-6 col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-warning text-dark fw-bold">
                            <i class="bi bi-clock-history me-2"></i> Recent Activities
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            @if ($isSuperAdmin)
                                                <th>File</th>
                                                <th>User</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                            @else
                                                <th>File</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentActivities as $activity)
                                            <tr>
                                                <td class="text-truncate" style="max-width: 150px;"
                                                    title="{{ $activity->file_name }}">
                                                    {{ $activity->file_name }}
                                                </td>
                                                @if ($isSuperAdmin)
                                                    <td><span
                                                            class="badge bg-primary">{{ $activity->upload_by }}</span>
                                                    </td>
                                                @endif
                                                <td>
                                                    <span
                                                        class="badge {{ $activity->type == 'master' ? 'bg-success' : 'bg-info' }}">
                                                        {{ $activity->type }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($activity->uploaded_at)->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $isSuperAdmin ? 4 : 3 }}"
                                                    class="text-center text-muted py-3">
                                                    No recent activities
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Orders Chart
            const monthlyCtx = document.getElementById('monthlyOrdersChart').getContext('2d');
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    datasets: [{
                        label: 'Total Orders',
                        data: {!! json_encode(array_fill(0, 12, 0)) !!},
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Total Stock',
                        data: {!! json_encode(array_fill(0, 12, 0)) !!},
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: {!! $chartData['supplier_performance']->pluck($isSuperAdmin ? 'supplier_name' : 'part_no')->toJson() !!},
                    datasets: [{
                        label: '{{ $isSuperAdmin ? 'OK Rate (%)' : 'OK Rate (%)' }}',
                        data: {!! $chartData['supplier_performance']->pluck('ok_rate')->toJson() !!},
                        backgroundColor: '#4e73df',
                        borderColor: '#2e59d9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'OK Rate (%)'
                            }
                        }
                    }
                }
            });

            // Stock Distribution Chart
            const stockCtx = document.getElementById('stockDistributionChart').getContext('2d');
            const stockChart = new Chart(stockCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! $chartData['stock_distribution']->pluck($isSuperAdmin ? 'stock_category' : 'stock_status')->toJson() !!},
                    datasets: [{
                        data: {!! $chartData['stock_distribution']->pluck('count')->toJson() !!},
                        backgroundColor: [
                            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                        ],
                        hoverBackgroundColor: [
                            '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Update monthly chart with real data
            @php
                $monthlyData = array_fill(0, 12, 0);
                $stockData = array_fill(0, 12, 0);
                foreach ($chartData['monthly_orders'] as $monthData) {
                    $monthIndex = $monthData->month - 1;
                    $monthlyData[$monthIndex] = $monthData->total_orders;
                    $stockData[$monthIndex] = $monthData->total_stock;
                }
            @endphp

            monthlyChart.data.datasets[0].data = {!! json_encode($monthlyData) !!};
            monthlyChart.data.datasets[1].data = {!! json_encode($stockData) !!};
            monthlyChart.update();
        });
    </script>
</body>

</html>
