<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

class PulloutController extends Controller
{
    public function downloadExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // --- Page Setup ---
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1); // Try to fit content from B to L in one page width
            $sheet->getPageSetup()->setFitToHeight(0);
            // Adjusted margins slightly to give more space if needed with the blank column A
            $sheet->getPageMargins()->setTop(0.5)->setRight(0.15)->setLeft(0.15)->setBottom(0.5);

            // --- Column Widths (B to L, column A is blank) ---
                 $sheet->getColumnDimension('A')->setWidth(1.1);  // Blank Spacer Column A - make it narrow
            $sheet->getColumnDimension('B')->setWidth(11.57);  // For CLIENT Label (was A)
            $sheet->getColumnDimension('C')->setWidth(12); // For Client Data (was B)
            $sheet->getColumnDimension('D')->setWidth(0);  // Was C (narrow gap)
            $sheet->getColumnDimension('E')->setWidth(8);  // Unit (was D)
            $sheet->getColumnDimension('F')->setWidth(12); // Brand/Particulars part 1 (was E)
            $sheet->getColumnDimension('G')->setWidth(12); // Brand/Particulars part 2 (was F)
            $sheet->getColumnDimension('H')->setWidth(14); // Model (was G)
            $sheet->getColumnDimension('I')->setWidth(11.57);  // For DATE Label (was H)
            $sheet->getColumnDimension('J')->setWidth(12); // Date Data & Part/Serial (was I)
            $sheet->getColumnDimension('K')->setWidth(12); // Part/Serial (was J)
            $sheet->getColumnDimension('L')->setWidth(12); // Part/Serial (was K) - New last column


            // --- Row Heights ---
            for ($i = 1; $i <= 55; $i++) { $sheet->getRowDimension($i)->setRowHeight(15); }
            $sheet->getRowDimension(2)->setRowHeight(25);
            $sheet->getRowDimension(8)->setRowHeight(12);

            // --- Logo and Company Info (Start from Column B) ---
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Company Logo');
            $logoPath = public_path('images/logo.png');
            if (file_exists($logoPath)) {
                $drawing->setPath($logoPath);
                $drawing->setHeight(75);
                $drawing->setCoordinates('B1'); // Shifted to B1
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
                $sheet->mergeCells('B1:E7'); // Shifted merge (B to E instead of A to D)
            } else {
                $sheet->setCellValue('B1', 'Logo Not Found'); // Shifted
                Log::warning('Logo image not found at: ' . $logoPath);
            }

            $sheet->mergeCells('B8:E8'); // Shifted
            $sheet->setCellValue('B8', 'VAT. REG. TIN: 009-597-211-0000'); // Shifted
            $sheet->getStyle('B8')->getFont()->setSize(8);
            $sheet->getStyle('B8')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

            // --- Title "PULL-OUT RECEIPT" (Columns I-L) ---
            $sheet->mergeCells('I2:L2'); // Shifted (was H-K, now I-L)
            $sheet->setCellValue('I2', 'PULL-OUT RECEIPT');
            $sheet->getStyle('I2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // --- Header Section: Client Info (Border around B10:L13) ---
            $clientInfoStartRow = 10;
            $currentRow = $clientInfoStartRow;

            // CLIENT (Label B, Data C:H)
            $sheet->setCellValue('B'.$currentRow, 'CLIENT:'); // Was A
            $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells('C'.$currentRow.':H'.$currentRow); // Was B:G, now C:H
            $sheet->setCellValue('C'.$currentRow, $formData['client'] ?? '');
            $sheet->getStyle('C'.$currentRow.':H'.$currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

            // DATE (Label I, Data J:L)
            $sheet->setCellValue('I'.$currentRow, 'DATE:'); // Was H
            $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells('J'.$currentRow.':L'.$currentRow); // Was I:K, now J:L
            $dateValue = $formData['date'] ?? null;
            if ($dateValue && strtotime($dateValue) !== false) {
                try { $sheet->setCellValue('J'.$currentRow, Date::PHPToExcel($dateValue));
                      $sheet->getStyle('J'.$currentRow.':J'.$currentRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                } catch (\Exception $e) { $sheet->setCellValue('J'.$currentRow, $dateValue); }
            } else { $sheet->setCellValue('J'.$currentRow, $dateValue); }
            $sheet->getStyle('J'.$currentRow.':L'.$currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('J'.$currentRow.':L'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $currentRow++;

            // ADDRESS (Label B, Data C:H)
            $sheet->setCellValue('B'.$currentRow, 'ADDRESS:'); // Was A
           $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells('C'.$currentRow.':H'.$currentRow); // Was B:G
            $sheet->setCellValue('C'.$currentRow, $formData['address'] ?? '');
            $sheet->getStyle('C'.$currentRow.':H'.$currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

            // REF/PO NO (Label I, Data J:L)
            $sheet->setCellValue('I'.$currentRow, 'REF/PO NO:'); // Was H
            $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells('J'.$currentRow.':L'.$currentRow); // Was I:K
            $sheet->setCellValue('J'.$currentRow, $formData['refPoNo'] ?? '');
            $sheet->getStyle('J'.$currentRow.':L'.$currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $currentRow++;

            // ATTENTION (Label B, Data C:H)
            $sheet->setCellValue('B'.$currentRow, 'ATTENTION:'); // Was A
            $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergeCells('C'.$currentRow.':H'.$currentRow); // Was B:G
            $sheet->setCellValue('C'.$currentRow, $formData['attention'] ?? '');
            $sheet->getStyle('C'.$currentRow.':H'.$currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            $clientInfoEndRow = $currentRow;

            // Border around client info block (B to L)
            $sheet->getStyle('B'.$clientInfoStartRow.':L'.$clientInfoEndRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
            $currentRow = 15;

            // --- Items Table Section (Start from Column B) ---
            // Adjusting item table column dimensions based on the new layout from B to L
            // QTY(B), UNIT(C), BRAND(E-G), MODEL(H-I), PART/SERIAL(J-L)
            // Column D is the narrow gap (1 width)
            
            $sheet->getColumnDimension('B')->setWidth(11.57);  // For CLIENT Label (was A)
            $sheet->getColumnDimension('C')->setWidth(12); // For Client Data (was B)
            $sheet->getColumnDimension('D')->setWidth(0);  // Was C (narrow gap)
            $sheet->getColumnDimension('E')->setWidth(8);  // Unit (was D)
            $sheet->getColumnDimension('F')->setWidth(12); // Brand/Particulars part 1 (was E)
            $sheet->getColumnDimension('G')->setWidth(12); // Brand/Particulars part 2 (was F)
            $sheet->getColumnDimension('H')->setWidth(14); // Model (was G)
            $sheet->getColumnDimension('I')->setWidth(11.57);  // For DATE Label (was H)
            $sheet->getColumnDimension('J')->setWidth(12); // Date Data & Part/Serial (was I)
            $sheet->getColumnDimension('K')->setWidth(12); // Part/Serial (was J)
            $sheet->getColumnDimension('L')->setWidth(12); // Part/Serial (was K) - New last column


            $tableHeaderStartRow = $currentRow;
            $sheet->setCellValue('B'.$currentRow, 'QUANTITY');              // Was A
            $sheet->setCellValue('C'.$currentRow, 'UNIT');                  // Was B
            $sheet->mergeCells('E'.$currentRow.':G'.$currentRow); $sheet->setCellValue('E'.$currentRow, 'BRAND/PARTICULARS'); // Was D:F, now E:G
            $sheet->mergeCells('H'.$currentRow.':I'.$currentRow); $sheet->setCellValue('H'.$currentRow, 'MODEL');             // Was G:H, now H:I
            $sheet->mergeCells('J'.$currentRow.':L'.$currentRow); $sheet->setCellValue('J'.$currentRow, 'PART/SERIAL NUMBER'); // Was I:K, now J:L

            $headerStyleArray = [ /* ... same ... */
                'font' => ['bold' => true, 'size' => 9],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
            ];
            $sheet->getStyle('B'.$currentRow.':L'.$currentRow)->applyFromArray($headerStyleArray); // Shifted B:L
            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            $currentRow++;

            $items = $formData['items'] ?? []; $itemRowHeight = 50; $maxItemRows = 1;
            for ($i = 0; $i < $maxItemRows; $i++) {
                $item = $items[$i] ?? null;
                $quantity = ($item && isset($item['quantity'])) ? $item['quantity'] : '';
                if ($quantity !== '' && is_numeric($quantity)) { $sheet->setCellValueExplicit('B'.$currentRow, $quantity, DataType::TYPE_NUMERIC); } // Was A
                else { $sheet->setCellValue('B'.$currentRow, $quantity); }
                $sheet->getStyle('B'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue('C'.$currentRow, ($item && isset($item['unit'])) ? $item['unit'] : ''); // Was B
                $sheet->getStyle('C'.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->mergeCells('E'.$currentRow.':G'.$currentRow); $sheet->setCellValue('E'.$currentRow, ($item && isset($item['brandParticulars'])) ? $item['brandParticulars'] : ''); // Was D:F
                $sheet->mergeCells('H'.$currentRow.':I'.$currentRow); $sheet->setCellValue('H'.$currentRow, ($item && isset($item['model'])) ? $item['model'] : '');             // Was G:H
                $sheet->mergeCells('J'.$currentRow.':L'.$currentRow); $sheet->setCellValue('J'.$currentRow, ($item && isset($item['partSerialNumber'])) ? $item['partSerialNumber'] : ''); // Was I:K
                $sheet->getStyle('B'.$currentRow.':L'.$currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN); // Shifted B:L
                $sheet->getStyle('B'.$currentRow.':L'.$currentRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getRowDimension($currentRow)->setRowHeight($itemRowHeight);
                $currentRow++;
            }
            $tableEndRow = $currentRow - 1;
            $sheet->getStyle('B'.($tableHeaderStartRow).':L'.$tableEndRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK); // Shifted B:L

            // --- Remarks Section (Start from column B) ---
            $remarksRow = $tableEndRow + 1;
            $sheet->setCellValue('B'.$remarksRow, 'REMARKS:'); // Was A
            $remarksLabelStyle = $sheet->getStyle('B'.$remarksRow);
            $remarksLabelStyle->getFont()->setBold(true);
            $remarksLabelStyle->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->mergeCells('C'.$remarksRow.':L'.$remarksRow); // Was B:K, now C:L
            $sheet->setCellValue('C'.$remarksRow, $formData['remarks'] ?? '');
            $sheet->getStyle('C'.$remarksRow.':L'.$remarksRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getRowDimension($remarksRow)->setRowHeight(max(30, 15 * (substr_count($formData['remarks'] ?? '', "\n") + 2)));
            $sheet->getStyle('B'.$remarksRow.':L'.$remarksRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK); // Shifted B:L
            $sheet->getStyle('B'.$remarksRow)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN); // Line after "REMARKS:"

            // Restore general column widths for signature area, starting from B
        
              $sheet->getColumnDimension('B')->setWidth(11.57);  // For CLIENT Label (was A)
           
            $sheet->getColumnDimension('D')->setWidth(0);  // Was C (narrow gap)
            $sheet->getColumnDimension('E')->setWidth(8);  // Unit (was D)
           
            $sheet->getColumnDimension('H')->setWidth(14); // Model (was G)
            $sheet->getColumnDimension('I')->setWidth(11.57);  // For DATE Label (was H)
            $sheet->getColumnDimension('J')->setWidth(12); // Date Data & Part/Serial (was I)
      
            $sheet->getColumnDimension('L')->setWidth(12); // Part/Serial (was K) - New last column                                              // Was K

            // --- Released Confirmation (Merge B:L) ---
            $releasedRow = $remarksRow + 2;
            $sheet->mergeCells('B'.$releasedRow.':L'.$releasedRow); // Was A:K
            $sheet->setCellValue('B'.$releasedRow, 'Released the above materials for pull-out');
            $sheet->getStyle('B'.$releasedRow)->getFont()->setItalic(true)->setSize(9);
            $sheet->getStyle('B'.$releasedRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // --- Signatures Section (All shifted one column right) ---
            $sigTopRow = $releasedRow + 2;
            $sigTextStyle = ['font' => ['size' => 9, 'italic' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM]];
           
            $signatureRowHeight = 20;

            // Row 1
            $sheet->mergeCells('B'.$sigTopRow.':C'.$sigTopRow); $sheet->setCellValue('B'.$sigTopRow, 'PREPARED BY'); // Was A:B
            $sheet->getStyle('B'.$sigTopRow.':C'.$sigTopRow)->applyFromArray($sigTextStyle);
            $sheet->mergeCells('E'.$sigTopRow.':F'.$sigTopRow); $sheet->setCellValue('E'.$sigTopRow, 'CHECKED BY'); // Was D:E
            $sheet->getStyle('E'.$sigTopRow.':F'.$sigTopRow)->applyFromArray($sigTextStyle);
            $sheet->setCellValue('I'.$sigTopRow, 'By:'); // Was H
            $sheet->getStyle('I'.$sigTopRow)->getFont()->setSize(9)->setBold(true)->setItalic(false);
            $sheet->getStyle('I'.$sigTopRow)->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM)->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCells('J'.$sigTopRow.':L'.$sigTopRow); $sheet->getStyle('J'.$sigTopRow.':L'.$sigTopRow); // Was I:K
            $sheet->getRowDimension($sigTopRow)->setRowHeight($signatureRowHeight);

            // Row 2
            $sigMidRow1 = $sigTopRow + 1;
            $sheet->mergeCells('B'.$sigMidRow1.':C'.$sigMidRow1); $sheet->getStyle('B'.$sigMidRow1.':C'.$sigMidRow1); // Was A:B
            $sheet->mergeCells('E'.$sigMidRow1.':F'.$sigMidRow1); $sheet->getStyle('E'.$sigMidRow1.':F'.$sigMidRow1); // Was D:E
            $sheet->mergeCells('J'.$sigMidRow1.':L'.$sigMidRow1); $sheet->setCellValue('J'.$sigMidRow1, 'Signature over printed name'); // Was I:K
            $sheet->getStyle('J'.$sigMidRow1.':L'.$sigMidRow1)->getFont()->setSize(8);
$sheet->getStyle('J'.$sigMidRow1.':L'.$sigMidRow1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($sigMidRow1)->setRowHeight(max(12, $signatureRowHeight));

            // Row 3
            $sigMidRow2 = $sigTopRow + 2;
            $sheet->mergeCells('B'.$sigMidRow2.':C'.$sigMidRow2); $sheet->setCellValue('B'.$sigMidRow2, 'ACKNOWLEDGED BY'); // Was A:B
            $sheet->getStyle('B'.$sigMidRow2.':C'.$sigMidRow2)->applyFromArray($sigTextStyle);
            $sheet->mergeCells('E'.$sigMidRow2.':F'.$sigMidRow2); $sheet->setCellValue('E'.$sigMidRow2, 'PULLED-OUT BY'); // Was D:E
            $sheet->getStyle('E'.$sigMidRow2.':F'.$sigMidRow2)->applyFromArray($sigTextStyle);
            $sheet->setCellValue('I'.$sigMidRow2, 'Date:'); // Was H
            $sheet->getStyle('I'.$sigMidRow2)->getFont()->setSize(9)->setBold(true)->setItalic(false);
            $sheet->getStyle('I'.$sigMidRow2)->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM)->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCells('J'.$sigMidRow2.':L'.$sigMidRow2); $sheet->getStyle('J'.$sigMidRow2.':L'.$sigMidRow2); // Was I:K
            $sheet->getRowDimension($sigMidRow2)->setRowHeight($signatureRowHeight);

            // Row 4
            $sigBottomRow = $sigTopRow + 3;
            $sheet->mergeCells('B'.$sigBottomRow.':C'.$sigBottomRow); $sheet->getStyle('B'.$sigBottomRow.':C'.$sigBottomRow); // Was A:B
            $sheet->mergeCells('E'.$sigBottomRow.':F'.$sigBottomRow); $sheet->getStyle('E'.$sigBottomRow.':F'.$sigBottomRow); // Was D:E
            $sheet->getRowDimension($sigBottomRow)->setRowHeight($signatureRowHeight);

            // --- Form Number (Merge B:L) ---
            $formNoRow = $sigBottomRow + 2;
            $sheet->mergeCells('B'.$formNoRow.':L'.$formNoRow); // Was A:K
            $sheet->setCellValue('B'.$formNoRow, 'Form No. ADM-WHS-003');
            $sheet->getStyle('J'.$sigMidRow1.':L'.$sigMidRow1)->getFont()->setSize(10);
$sheet->getStyle('J'.$sigMidRow1.':L'.$sigMidRow1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


            // --- Output ---
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Pull_Out_Receipt_Shifted_'.date('Ymd_His').'.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $fileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Error generating Excel: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to generate Excel file.', 'details' => $e->getMessage()], 500);
        }
    }
}