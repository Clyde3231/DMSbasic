<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Log;

class PulloutController extends Controller
{
    public function downloadExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            $templatePath = storage_path('app/templates/ADM-WHS-003 Pullout.xlsx'); // ADJUST IF YOUR FILENAME IS DIFFERENT

            if (!file_exists($templatePath)) {
                Log::error('Excel template not found at: ' . $templatePath);
                return response()->json(['error' => 'Excel template file not found. Please contact support.'], 500);
            }

            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // --- Populate Template ---

            // AR No. - Assuming refPoNo can be used, or you add a dedicated field to formData
            // If AR No. has a different source, adjust $formData key.
            $sheet->setCellValue('I6', $formData['refPoNo'] ?? ($formData['arNo'] ?? 'N/A')); // AR No. value in I6

            // Client Information
            $sheet->setCellValue('C10', $formData['client'] ?? '');    // CLIENT Value
            $sheet->setCellValue('C11', $formData['address'] ?? '');   // ADDRESS Value
            $sheet->setCellValue('C13', $formData['attention'] ?? ''); // ATTENTION Value

            // Date & REF/PO (REF/PO might be same as AR No. or different)
            $dateValue = $formData['date'] ?? null;
            if ($dateValue && strtotime($dateValue) !== false) {
                try {
                    $sheet->setCellValue('I10', Date::PHPToExcel($dateValue)); // DATE Value
                } catch (\Exception $e) {
                    Log::warning("Failed to convert date '{$dateValue}' to Excel date format: " . $e->getMessage());
                    $sheet->setCellValue('I10', $dateValue);
                }
            } else {
                $sheet->setCellValue('I10', $dateValue ?? '');
            }
            $sheet->setCellValue('I11', $formData['refPoNo'] ?? ''); // REF/PO NO Value

            // Items Table
            $items = $formData['items'] ?? [];
            $itemStartRow = 16; // Data for items starts at row 15
            $maxTemplateRowsForItems = 25; // Number of item rows in your template (e.g., from row 15 to 39 is 25 rows)

            for ($i = 0; $i < $maxTemplateRowsForItems; $i++) {
                $currentRow = $itemStartRow + $i;
                if (isset($items[$i])) {
                    $item = $items[$i];

                    // Quantity: Column B
                    $quantity = $item['quantity'] ?? '';
                    if ($quantity !== '' && is_numeric($quantity)) {
                        $sheet->setCellValueExplicit('B'.$currentRow, $quantity, DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValue('B'.$currentRow, $quantity);
                    }

                    // Unit: Column C
                    $sheet->setCellValue('C'.$currentRow, $item['unit'] ?? '');

                    // Brand/Particulars: Starts at Column D (assuming merged D:F in your template)
                    $sheet->setCellValue('D'.$currentRow, $item['brandParticulars'] ?? '');

                    // Model: Starts at Column G (assuming merged G:H in your template)
                    $sheet->setCellValue('I'.$currentRow, $item['model'] ?? ''); // <<< CORRECTED from F

                    // Part/Serial Number: Starts at Column I (assuming merged I:K in your template)
                    $sheet->setCellValue('K'.$currentRow, $item['partSerialNumber'] ?? ''); // <<< CORRECTED from G
                } else {
                    // Clear cells for unused template rows
                    $sheet->setCellValue('B'.$currentRow, '');
                    $sheet->setCellValue('C'.$currentRow, '');
                    $sheet->setCellValue('D'.$currentRow, ''); // Brand/Particulars
                    $sheet->setCellValue('G'.$currentRow, ''); // Model
                    $sheet->setCellValue('I'.$currentRow, ''); // Part/Serial
                }
            }

            // Remarks
            $sheet->setCellValue('C41', $formData['remarks'] ?? ''); // REMARKS Value (merged C41:K41)

            // --- Output ---
            $writer = new Xlsx($spreadsheet);
            $clientNameSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $formData['client'] ?? 'UnknownClient');
            $outputFileName = 'PullOut_Receipt_' . $clientNameSanitized . '_' . date('Ymd') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('Error loading Excel template: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error loading the Excel template. Check server logs.', 'details' => $e->getMessage()], 500);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('Error processing Excel file with PhpSpreadsheet: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error processing the Excel file. Check server logs.', 'details' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error generating Excel: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'An unexpected error occurred. Check server logs.', 'details' => $e->getMessage()], 500);
        }
    }
}