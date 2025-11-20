<?php

namespace App\Template;

use App\Models\Order;
use App\Models\OrdersAdmin;   // ← GANTI DARI Order KE OrdersAdmin
use App\Models\Part;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class TemplateOrderDownload implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $isSuperAdmin;

    public function __construct(bool $isSuperAdmin = false)
    {
        $this->isSuperAdmin = $isSuperAdmin;
    }

    public function array(): array
    {
        $user = Auth::user();
        $bpid = $user->bpid;
        $isSuperAdmin = $this->isSuperAdmin;

        $data = [];

        // === SUPER ADMIN → HANYA TEMPLATE KOSONG (HEADER SAJA) ===
        if ($isSuperAdmin) {
            $data[] = [
                'Supplier'       => '',
                'Plan Delv Date' => Carbon::today()->format('Y-m'),
                'Part No'        => '',
                'Part Name'      => '',
                'Qty PO'         => 0,
                'Hari Kerja'     => 23,
                'Stock'          => '',
                'Standart'       => '',
            ];

            return $data; // langsung return, ga usah lanjut
        }

        // === SUPPLIER → AMBIL DATA DARI MASTER (OrdersAdmin) ===
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        $masters = \App\Models\OrdersAdmin::with('part')
            ->where('supplier', $bpid)
            ->whereMonth('plan_delv_date', $month)
            ->whereYear('plan_delv_date', $year)
            ->orderBy('plan_delv_date')
            ->get();

        // Cek apakah ada update baru dari admin
        $lastDownloaded = Order::where('supplier', $bpid)
            ->where('downloaded_by_supplier', true)
            ->max('updated_at');

        $latestAdminUpdate = $masters->max('updated_at');
        $hasNewData = $latestAdminUpdate && (!$lastDownloaded || $latestAdminUpdate > $lastDownloaded);

        if ($hasNewData) {
            $data[] = [
                'Supplier'       => 'PERINGATAN: Admin baru update Qty PO! Harap isi ulang stok hari ini.',
                'Plan Delv Date' => '',
                'Part No'        => '',
                'Part Name'      => '',
                'Qty PO'         => '',
                'Hari Kerja'     => '',
                'Stock'          => '',
                'Standart'       => ''
            ];
        }

        foreach ($masters as $m) {
            $data[] = [
                'Supplier'       => $m->supplier,
                'Plan Delv Date' => Carbon::parse($m->plan_delv_date)->format('Ymd'),
                'Part No'        => $m->part_no,
                'Part Name'      => $m->part?->name ?? '',
                'Qty PO'         => $m->qty_po ?? 0,
                'Hari Kerja'     => 23,
                'Stock'          => '',
                'Standart'       => '',
            ];
        }

        // Kalau supplier tapi ga ada data (jarang terjadi)
        if (empty($data) || count($data) === ($hasNewData ? 1 : 0)) {
            $data[] = [
                'Supplier'       => $bpid,
                'Plan Delv Date' => Carbon::today()->format('Ymd'),
                'Part No'        => '',
                'Part Name'      => '',
                'Qty PO'         => 0,
                'Hari Kerja'     => 23,
                'Stock'          => '',
                'Standart'       => ''
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            ['Supplier', 'Plan Delv Date', 'Part No', 'Part Name', 'Qty PO', 'Hari Kerja', 'Stock', 'Standart'],
            ['', '', '', '', '', '', '', 'Otomatis (OK/NOK)']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'font' => ['color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow();

                $sheet->freezePane('A3');
                $sheet->setAutoFilter('A1:H2');

                if ($highestRow > 2) {
                    for ($row = 3; $row <= $highestRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(22);

                        // Format angka
                        $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');

                        // Rumus Standart: Stock / (Qty PO ÷ Hari Kerja) > 1.5 → OK
                        $formula = "=IF(IFERROR(G{$row}/(E{$row}/F{$row}),0)>1.5,\"OK\",\"NOK\")";
                        $sheet->setCellValue("H{$row}", $formula);
                    }

                    // Conditional formatting OK / NOK
                    $range = "H3:H{$highestRow}";
                    $ok = new Conditional();
                    $ok->setConditionType(Conditional::CONDITION_CELLIS)
                        ->setOperatorType(Conditional::OPERATOR_EQUAL)
                        ->addCondition('"OK"')
                        ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB('FFC6EFCE');
                    $ok->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF006100');

                    $nok = new Conditional();
                    $nok->setConditionType(Conditional::CONDITION_CELLIS)
                        ->setOperatorType(Conditional::OPERATOR_EQUAL)
                        ->addCondition('"NOK"')
                        ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB('FFFFC7CE');
                    $nok->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF9C0006');

                    $sheet->getStyle($range)->setConditionalStyles([$ok, $nok]);
                }

                // Proteksi sheet (hanya supplier yang di-protect)
                if (!$this->isSuperAdmin) {
                    $sheet->getProtection()->setPassword('kayaba123');
                    $sheet->getProtection()->setSheet(true);

                    if ($highestRow > 2) {
                        $sheet->getStyle("A3:H{$highestRow}")->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                        $sheet->getStyle("G3:G{$highestRow}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED); // Stock bisa diedit
                    }
                }
            },
        ];
    }
}
