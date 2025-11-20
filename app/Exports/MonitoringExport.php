<?php

namespace App\Exports;

use App\Models\Supplier;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class MonitoringExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $isFiltered;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->isFiltered = $startDate && $endDate;
    }

    public function collection()
    {
        // Ambil semua supplier (sama seperti di view)
        $suppliers = Supplier::with('stocks.part')->get();

        $allRows = collect();

        foreach ($suppliers as $supplier) {
            $bpid = $supplier->bpid;

            // 1. Coba ambil dari orders (data supplier)
            $query = Order::where('supplier', $bpid);

            if ($this->isFiltered) {
                $query->whereBetween('plan_delv_date', [$this->startDate, $this->endDate]);
            } else {
                // Tanpa filter → hanya ambil data dengan last update terbaru dari supplier ini
                $latestHistory = OrderHistory::where('supplier', $bpid)
                    ->latest('created_at')
                    ->first();

                if ($latestHistory) {
                    // Ambil semua order yang dibuat pada sesi update terakhir
                    $sessionDate = Carbon::parse($latestHistory->created_at)->format('Y-m-d');
                    $query->whereDate('created_at', $sessionDate)
                        ->orWhereDate('updated_at', $sessionDate);
                } else {
                    // Kalau supplier belum pernah update → skip (atau ambil dari admin, lihat bawah)
                    continue;
                }
            }

            $orders = $query->get();

            // 2. Jika supplier belum pernah isi (orders kosong) → ambil dari orders_admins
            if ($orders->isEmpty() && ! $this->isFiltered) {
                // Hanya jika tidak ada filter, baru pakai data admin terbaru
                $adminData = DB::table('orders_admins')
                    ->where('supplier', $bpid)
                    ->whereYear('plan_delv_date', now()->year)
                    ->whereMonth('plan_delv_date', now()->month)
                    ->get();

                $orders = $adminData->map(function ($item) {
                    return (object) [
                        'part_no'        => $item->part_no,
                        'qty_po'         => $item->qty_po,
                        'stock'          => $item->stock ?? 0,
                        'plan_delv_date' => $item->plan_delv_date,
                        'standard'       => is_null($item->stock) || $item->stock == ''
                            ? null
                            : ($item->stock >= $item->qty_po ? 'OK' : 'NOK'),
                        'part'           => (object) ['part_no' => $item->part_no, 'name' => $item->part_name ?? ''],
                    ];
                });
            } elseif ($orders->isEmpty() && $this->isFiltered) {
                // Kalau ada filter dan supplier belum isi di periode itu → tetap tampilkan dari admin
                $adminData = DB::table('orders_admins')
                    ->where('supplier', $bpid)
                    ->whereBetween('plan_delv_date', [$this->startDate, $this->endDate])
                    ->get();

                $orders = $adminData->map(function ($item) {
                    return (object) [
                        'part_no'        => $item->part_no,
                        'qty_po'         => $item->qty_po,
                        'stock'          => $item->stock ?? 0,
                        'plan_delv_date' => $item->plan_delv_date,
                        'standard'       => is_null($item->stock) || $item->stock == ''
                            ? null
                            : ($item->stock >= $item->qty_po ? 'OK' : 'NOK'),
                        'part'           => (object) ['part_no' => $item->part_no, 'name' => $item->part_name ?? ''],
                    ];
                });
            }

            // Mapping ke format Excel
            foreach ($orders as $order) {
                $allRows->push([
                    'Supplier'       => $supplier->name ?? $supplier->bpid,
                    'Plan Delv Date' => $order->plan_delv_date ? Carbon::parse($order->plan_delv_date)->format('Ym') : '',
                    'Part No'        => $order->part?->part_no ?? $order->part_no ?? '',
                    'Part Name'      => $order->part?->name ?? ($order->part->name ?? ''),
                    'Qty PO'         => $order->qty_po ?? 0,
                    'Hari Kerja'     => 23,
                    'Stock'          => $order->stock ?? 0,
                    'Standart'       => $order->standard ?? '',
                ]);
            }
        }

        return $allRows;
    }

    public function headings(): array
    {
        return [
            ['Supplier', 'Plan Delv Date', 'Part No', 'Part Name', 'Qty PO', 'Hari Kerja', 'Stock', 'Standart'],
            ['', '(yyyymm)', '', '', '', '', '', 'Otomatis (OK/NOK)']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['italic' => true, 'bold' => true, 'color' => ['rgb' => '888888']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert title rows
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');

                if ($this->isFiltered) {
                    $start = Carbon::parse($this->startDate);
                    $end   = Carbon::parse($this->endDate);
                    if ($start->format('Y-m') === $end->format('Y-m')) {
                        $judul = "DATA MONITORING BULAN " . $start->translatedFormat('F Y');
                    } else {
                        $judul = "DATA MONITORING PERIODE " . $start->translatedFormat('F Y') . " - " . $end->translatedFormat('F Y');
                    }
                } else {
                    $judul = "DATA MONITORING BULAN " . now()->translatedFormat('F Y') . " (Update Terbaru)";
                }

                $sheet->setCellValue('A1', $judul);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'DC3545']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(40);

                $sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d F Y H:i') . ' WIB');
                $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(2)->setRowHeight(25);

                // YM di header (baris 4)
                $ym = $this->startDate ? Carbon::parse($this->startDate)->format('Ym') : now()->format('Ym');
                $sheet->setCellValue('B4', $ym);

                // Freeze & filter
                $sheet->freezePane('A5');
                $sheet->setAutoFilter('A3:H4');

                // Rumus + conditional formatting
                $highestRow = $sheet->getHighestRow();
                for ($row = 5; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                    $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $formula = '=IF(IFERROR(G' . $row . '/(E' . $row . '/F' . $row . '),0)>1.5,"OK","NOK")';
                    $sheet->setCellValue("H{$row}", $formula);
                }

                $conditionalOK = new Conditional();
                $conditionalOK->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(Conditional::OPERATOR_EQUAL)
                    ->addCondition('"OK"')
                    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB('FFC6EFCE');
                $conditionalOK->getStyle()->getFont()->getColor()->setARGB('FF006100');

                $conditionalNOK = new Conditional();
                $conditionalNOK->setConditionType(Conditional::CONDITION_CELLIS)
                    ->setOperatorType(Conditional::OPERATOR_EQUAL)
                    ->addCondition('"NOK"')
                    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB('FFFFC7CE');
                $conditionalNOK->getStyle()->getFont()->getColor()->setARGB('FF9C0006');

                $sheet->getStyle("H5:H{$highestRow}")->setConditionalStyles([$conditionalOK, $conditionalNOK]);
            },
        ];
    }
}
