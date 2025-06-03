<?php

namespace App\Http\Controllers; // Corrected namespace

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Log;

class ReimbursementController extends Controller
{
    public function downloadExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            $templatePath = storage_path('app/templates/ADM-ACC-003 Reimbursement.xlsx');

            if (!file_exists($templatePath)) {
                Log::error('Reimbursement Excel template not found at: ' . $templatePath);
                return response()->json(['error' => 'Reimbursement Excel template file not found.'], 500);
            }

            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // --- Populate the Reimbursement Template ---

            // Employee Information (cells B8, B9, B10, E8, E9, E10 as before)
            $sheet->setCellValue('B8', $formData['employee_name'] ?? '');
            $sheet->setCellValue('B9', $formData['department'] ?? '');
            // ... (Date Filed, Employee Num, Position, Ref No. as before) ...
            $dateFiledValue = $formData['date_filed'] ?? null;
            if ($dateFiledValue && strtotime($dateFiledValue) !== false) {
                try { $sheet->setCellValue('B10', Date::PHPToExcel($dateFiledValue)); }
                catch (\Exception $e) { $sheet->setCellValue('B10', $dateFiledValue); }
            } else { $sheet->setCellValue('B10', $dateFiledValue ?? ''); }

            $sheet->setCellValue('E8', $formData['employee_num'] ?? '');
            $sheet->setCellValue('E9', $formData['position'] ?? '');
            $sheet->setCellValue('E10', $formData['reference_no'] ?? '');


            // --- CV Number & Project (Labels and Values combined in specified cells) ---

            // For "CV Number: [value]" in cell A13 (and potentially merged with A14 for height if needed in template)
            $cvNumberValue = $formData['cv_number'] ?? '';
            $sheet->setCellValue('A13', 'CV Number: ' . $cvNumberValue);
            // If A13 in your template is formatted for text wrapping and has enough height (or is merged A13:A14), this will work.

            // For "Project : [value]" in cell B13 (and potentially merged with B14 for height if needed in template)
            $projectNameValue = $formData['project_name'] ?? '';
            $sheet->setCellValue('B13', 'Project : ' . $projectNameValue);
            // Similarly, ensure B13 in your template is formatted for text wrapping and height.
            // Note: Your image shows "Project :" for the label. If you want just "Project:", adjust the string.


            // Items Table (A16, B16, C16, E16 for first item, etc., as before)
            // ... (Item population logic remains the same) ...
            $expenseItems = $formData['items'] ?? ($formData['expense_items'] ?? []);
            $itemStartRow = 16;
            $maxTemplateRowsForItems = 10; // ADJUST THIS
            $totalAmount = 0;

            for ($i = 0; $i < $maxTemplateRowsForItems; $i++) {
                $currentRow = $itemStartRow + $i;
                if (isset($expenseItems[$i])) {
                    $item = $expenseItems[$i];
                    $expenseDateValue = $item['expense_date'] ?? null;
                    if ($expenseDateValue && strtotime($expenseDateValue) !== false) {
                        try { $sheet->setCellValue('A'.$currentRow, Date::PHPToExcel($expenseDateValue)); }
                        catch (\Exception $e) { $sheet->setCellValue('A'.$currentRow, $expenseDateValue); }
                    } else { $sheet->setCellValue('A'.$currentRow, $expenseDateValue ?? ''); }
                    $sheet->setCellValue('B'.$currentRow, $item['receipt_no'] ?? '');
                    $sheet->setCellValue('C'.$currentRow, $item['description'] ?? '');
                    $amount = $item['amount'] ?? '';
                    if (is_numeric($amount)) {
                        $sheet->setCellValueExplicit('E'.$currentRow, (float)$amount, DataType::TYPE_NUMERIC);
                        $totalAmount += (float)$amount;
                    } else {
                        $sheet->setCellValue('E'.$currentRow, $amount);
                    }
                } else {
                    $sheet->setCellValue('A'.$currentRow, '');
                    $sheet->setCellValue('B'.$currentRow, '');
                    $sheet->setCellValue('C'.$currentRow, '');
                    $sheet->setCellValue('E'.$currentRow, '');
                }
            }
            $sheet->setCellValue('E27', $totalAmount); // Assuming E27 is for total

            // Bottom Signature-related Section (B33, B36, B39 as before)
            // ... (Signature population logic remains the same) ...
            $sheet->setCellValue('B33', $formData['signature_data'] ?? '');
            $releasedDateValue = $formData['released_date'] ?? null;
            if ($releasedDateValue && strtotime($releasedDateValue) !== false) {
                try { $sheet->setCellValue('B36', Date::PHPToExcel($releasedDateValue)); }
                catch (\Exception $e) { $sheet->setCellValue('B36', $releasedDateValue); }
            } else { $sheet->setCellValue('B36', $releasedDateValue ?? ''); }
            $sheet->setCellValue('B39', $formData['received_by'] ?? '');


            // --- Output ---
            // ... (output logic remains the same) ...
            $writer = new Xlsx($spreadsheet);
            $employeeNameSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $formData['employee_name'] ?? 'Employee');
            $outputFileName = 'Reimbursement_' . $employeeNameSanitized . '_' . ($formData['reference_no'] ?? '') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Reimbursement Excel - Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to generate Reimbursement Excel.', 'details' => $e->getMessage()], 500);
        }
    }
}