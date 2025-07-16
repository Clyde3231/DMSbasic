<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType; // If you need to specify numeric type for amount
use Illuminate\Support\Facades\Log;

class CashAdvanceController extends Controller
{
    public function downloadCashAdvanceExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            // --- Define the path to your Cash Advance Excel template ---
            $templatePath = storage_path('app/templates/ADM-ACC-001 Cash Advance.xls'); // ADJUST FILENAME IF NEEDED

            if (!file_exists($templatePath)) {
                Log::error('Cash Advance Excel template not found at: ' . $templatePath);
                return response()->json(['error' => 'Cash Advance Excel template file not found. Please contact support.'], 500);
            }

            // --- Load the existing template ---
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet(); // Or $spreadsheet->getSheetByName('YourSheetName');

            // --- Populate the template with formData based on your Excel image ---

            // Top Right Info (Document Number and Type - often static in template or from other source)
            // $sheet->setCellValue('E4', $formData['document_adm_acc_no'] ?? 'ADM-ACC-001'); // Example, if this is dynamic
            // $sheet->setCellValue('F4', 'CASH ADVANCE'); // Usually static in template

            // Employee Information
            $sheet->setCellValue('B8', $formData['employee_name'] ?? '');   // EMPLOYEE NAME Value
            $sheet->setCellValue('B9', $formData['department'] ?? '');      // DEPARTMENT Value
            $sheet->setCellValue('B10', $formData['date_filed'] ?? '');     // DATE FILED Value (format in template)

            $sheet->setCellValue('F8', $formData['employee_num'] ?? '');    // EMPLOYEE NUMBER Value
            $sheet->setCellValue('F9', $formData['position'] ?? '');        // POSITION Value
            $sheet->setCellValue('F10', $formData['reference_no'] ?? '');   // REFERENCE NO. Value (e.g., CA-00001)


            // Items Table (Amount and Details)
            $items = $formData['items'] ?? [];
            $itemStartRow = 14; // Data for items starts at row 14 (Amount Header is row 13)
            // Let's assume your template has item rows from 14 down to 23 (10 item rows)
            $maxTemplateRowsForItems = 10; // ADJUST THIS: Number of pre-formatted item rows in your template

            for ($i = 0; $i < $maxTemplateRowsForItems; $i++) {
                $currentRow = $itemStartRow + $i;
                if (isset($items[$i])) {
                    $item = $items[$i];

                    // Amount: Column B (likely merged B:C in data rows based on header in image)
                    // Assuming B is the start of the merge for Amount data.
                    // If your template cell B[current_row] is pre-formatted as currency/number, this is fine.
                    // Otherwise, to ensure it's treated as a number:
                    $amount = $item['amount'] ?? '';
                    if (is_numeric($amount)) {
                        $sheet->setCellValueExplicit('B'.$currentRow, (float)$amount, DataType::TYPE_NUMERIC);
                        // If you want a specific currency format and it's not in template:
                        // $sheet->getStyle('B'.$currentRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE); // Or your currency
                    } else {
                        $sheet->setCellValue('B'.$currentRow, $amount);
                    }

                    // Details: Column D (likely merged D:F or D:G or D to end of table)
                    // Assuming D is the start of the merge for Details data.
                    $sheet->setCellValue('D'.$currentRow, $item['details'] ?? '');
                } else {
                    // Clear cells for unused template rows to ensure no old data or placeholders remain
                    $sheet->setCellValue('B'.$currentRow, ''); // Clear Amount
                    $sheet->setCellValue('D'.$currentRow, ''); // Clear Details
                }
            }

            // Dynamic Row Insertion (More Complex - use with caution if template has content below items)
            /*
            if (count($items) > $maxTemplateRowsForItems) {
                $rowsToInsert = count($items) - $maxTemplateRowsForItems;
                $insertionPointRow = $itemStartRow + $maxTemplateRowsForItems; // Insert after the last template row
                $sheet->insertNewRowBefore($insertionPointRow, $rowsToInsert);

                // You would then need to copy formatting from a template row to the new rows
                // And then populate these newly inserted rows
                for ($i = $maxTemplateRowsForItems; $i < count($items); $i++) {
                    $currentRow = $itemStartRow + $i; // This is now one of the newly inserted rows
                    $item = $items[$i];
                    // ... setCellValue for B and D for these new rows ...
                    // ... apply styling by copying from a template row if needed ...
                    // e.g., $sheet->duplicateStyle($sheet->getStyle('B'.($itemStartRow)), 'B'.$currentRow);
                }
            }
            */


            // Bottom Section (Signatures, Dates - often these are for manual filling or come from other sources)
            // If these are to be populated from the form:
            $sheet->setCellValue('B25', $formData['signature_data'] ?? ''); // Signature (Employee) Value
            $sheet->setCellValue('B27', $formData['released_date'] ?? '');  // Released Date Value (format in template)
            $sheet->setCellValue('B29', $formData['received_by'] ?? '');    // Received By Value

            // "Noted By", "Released By", "Approved By" labels and signature lines are usually static in template.

            // --- Output the modified spreadsheet ---
            $writer = new Xlsx($spreadsheet);
            $employeeNameSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $formData['employee_name'] ?? 'Employee');
            $outputFileName = 'Cash_Advance_' . $employeeNameSanitized . '_' . ($formData['reference_no'] ?? '') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('CA Excel - Error loading template: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error loading the Cash Advance Excel template. Please contact support.', 'details' => $e->getMessage()], 500);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('CA Excel - PhpSpreadsheet error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error processing the Cash Advance Excel file. Please contact support.', 'details' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('CA Excel - Unexpected error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'An unexpected error occurred generating the Cash Advance Excel. Please contact support.', 'details' => $e->getMessage()], 500);
        }
    }
}