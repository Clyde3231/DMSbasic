<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{
    public function downloadPurchaseRequestExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            // --- Define the path to your Purchase Request Excel template ---
            $templatePath = storage_path('app/templates/ADM-PCH-001 Purchase Request.xlsx'); // ADJUST FILENAME IF NEEDED

            if (!file_exists($templatePath)) {
                Log::error('Purchase Request Excel template not found at: ' . $templatePath);
                return response()->json(['error' => 'Purchase Request Excel template file not found. Please contact support.'], 500);
            }

            // --- Load the existing template ---
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // --- Populate the template with formData ---

            // Employee Information
            $sheet->setCellValue('C9', $formData['employee_name'] ?? '');      // Employee Name
            $sheet->setCellValue('J9', $formData['employee_num'] ?? '');       // Employee Number
            $sheet->setCellValue('C10', $formData['department_top'] ?? '');    // Department (Top)
            $sheet->setCellValue('J10', $formData['position'] ?? '');          // Position
            $sheet->setCellValue('C11', $formData['date_filed'] ?? '');        // Date Filed (template cell should be date formatted)
            $sheet->setCellValue('J11', $formData['reference_no'] ?? '');      // Reference No.

            // Items Table
            $items = $formData['items'] ?? [];
            $itemStartRow = 16; // Data for items starts at row 15
            // Example: Template has item rows from 15 down to 24 (10 item rows)
            $maxTemplateRowsForItems = 10; // ADJUST THIS: Number of pre-formatted item rows in your template
            $overallTotal = 0;

            for ($i = 0; $i < $maxTemplateRowsForItems; $i++) {
                $currentRow = $itemStartRow + $i;
                if (isset($items[$i])) {
                    $item = $items[$i];
                    $qty = isset($item['qty']) && is_numeric($item['qty']) ? (float)$item['qty'] : 0;
                    $unitPrice = isset($item['unitPrice']) && is_numeric($item['unitPrice']) ? (float)$item['unitPrice'] : 0;
                    $rowTotal = $qty * $unitPrice;
                    $overallTotal += $rowTotal;

                    // Qty: Column B
                    $sheet->setCellValueExplicit('B'.$currentRow, $qty, DataType::TYPE_NUMERIC);

                    // Description: Column C (assuming merged C:I in your template for data rows)
                    $sheet->setCellValue('C'.$currentRow, $item['description'] ?? '');

                    // Unit Price: Column J
                    $sheet->setCellValueExplicit('K'.$currentRow, $unitPrice, DataType::TYPE_NUMERIC);
                    // If template cell J[currentRow] is not pre-formatted for currency:
                    // $sheet->getStyle('J'.$currentRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_PHP_SIMPLE); // Or your currency

                    // Total Price (Calculated): Column K (assuming merged K:L)
                    $sheet->setCellValueExplicit('L'.$currentRow, $rowTotal, DataType::TYPE_NUMERIC);
                    // If template cell K[currentRow] is not pre-formatted for currency:
                    // $sheet->getStyle('K'.$currentRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_PHP_SIMPLE); // Or your currency
                } else {
                    // Clear cells for unused template rows
                    $sheet->setCellValue('B'.$currentRow, ''); // Qty
                    $sheet->setCellValue('C'.$currentRow, ''); // Description
                    $sheet->setCellValue('J'.$currentRow, ''); // Unit Price
                    $sheet->setCellValue('K'.$currentRow, ''); // Total Price
                }
            }

            // Overall Total Amount
            // The label "Total Amount" is in J26, "PHP" in K26, Value in L26
            $sheet->setCellValueExplicit('L26', $overallTotal, DataType::TYPE_NUMERIC);
            // If template cell L26 is not pre-formatted for currency:
            // $sheet->getStyle('L26')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_PHP_SIMPLE);

            // Purpose / Reason
            // Cell B29, likely merged B29:L39
            $sheet->setCellValue('B29', $formData['purpose'] ?? '');

            // Bottom Signature Section Data
            $sheet->setCellValue('C41', $formData['requested_by_signature'] ?? ''); // Requested by - Signature/Printed Name
            $sheet->setCellValue('C43', $formData['department_bottom'] ?? '');      // Department (Bottom)
            $sheet->setCellValue('C44', $formData['date_bottom'] ?? '');            // Date (Bottom, template cell should be date formatted)


            // --- Output the modified spreadsheet ---
            $writer = new Xlsx($spreadsheet);
            $employeeNameSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $formData['employee_name'] ?? 'Employee');
            $outputFileName = 'Purchase_Request_' . $employeeNameSanitized . '_' . ($formData['reference_no'] ?? '') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('PR Excel - Error loading template: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error loading the Purchase Request Excel template.', 'details' => $e->getMessage()], 500);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('PR Excel - PhpSpreadsheet error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error processing the Purchase Request Excel file.', 'details' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('PR Excel - Unexpected error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'An unexpected error occurred generating the Purchase Request Excel.', 'details' => $e->getMessage()], 500);
        }
    }
}