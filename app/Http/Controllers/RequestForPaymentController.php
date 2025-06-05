<?php

namespace App\Http\Controllers; // Or your specific controller namespace

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;   // If formatting dates explicitly
use PhpOffice\PhpSpreadsheet\Cell\DataType; // For numeric types
use Illuminate\Support\Facades\Log;

class RequestForPaymentController extends Controller // Or your existing controller name
{
    public function downloadRequestForPaymentExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            // --- Define the path to your Request for Payment Excel template ---
            $templatePath = storage_path('app/templates/ADM-ACC-004 Request For Payment Form.xlsx'); // ADJUST FILENAME IF NEEDED

            if (!file_exists($templatePath)) {
                Log::error('Request for Payment Excel template not found at: ' . $templatePath);
                return response()->json(['error' => 'Request for Payment Excel template file not found.'], 500);
            }

            // --- Load the existing template ---
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // --- Populate the template with formData ---

            // Top Employee Information
            $sheet->setCellValue('C9', $formData['employee_name'] ?? '');
            $sheet->setCellValue('I9', $formData['employee_num'] ?? '');
            $sheet->setCellValue('C10', $formData['department'] ?? ''); // Assuming 'department' from Alpine matches 'department_top'
            $sheet->setCellValue('I10', $formData['position'] ?? '');
            $sheet->setCellValue('C11', $formData['date_filed'] ?? ''); // Ensure cell in template is date formatted
            $sheet->setCellValue('I11', $formData['reference_no'] ?? '');

            // Payee Information
            $sheet->setCellValue('C17', $formData['payee_name'] ?? '');
            $sheet->setCellValue('C18', $formData['payee_address'] ?? '');
            $sheet->setCellValue('C19', $formData['payee_contact'] ?? ''); // Phone/Email

            // Bank Details (within Payee Info)
            $sheet->setCellValue('E21', $formData['bank_name'] ?? '');
            $sheet->setCellValue('E22', $formData['account_number'] ?? '');
            $sheet->setCellValue('E23', $formData['swift_bic'] ?? '');
            $sheet->setCellValue('E24', $formData['iban'] ?? '');

            // Payment Details
            $sheet->setCellValue('C30', $formData['payment_description'] ?? '');

            // Amount & Currency - Assuming Amount in C31, Currency in G31 (adjust if different in template)
            $paymentAmount = $formData['payment_amount'] ?? null;
            if (is_numeric($paymentAmount)) {
                $sheet->setCellValueExplicit('C31', (float)$paymentAmount, DataType::TYPE_NUMERIC);
                // If cell C31 in template is not pre-formatted for currency:
                // $sheet->getStyle('C31')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_PHP_SIMPLE); // Or appropriate format
            } else {
                $sheet->setCellValue('C31', $paymentAmount);
            }
            $sheet->setCellValue('G31', $formData['payment_currency'] ?? 'PHP'); // Example for Currency in G31

            // Payment Method (convert array of selected methods to a string)
            $paymentMethodsArray = $formData['payment_methods'] ?? [];
            $paymentMethodsString = implode(', ', $paymentMethodsArray);
            $sheet->setCellValue('C32', $paymentMethodsString); // Assuming Payment Method value goes in C32 (merged C32:G32 or similar)

            $sheet->setCellValue('C33', $formData['payment_invoice_ref'] ?? ''); // Invoice / Ref No.

            // Signature (Requester)
            $sheet->setCellValue('C38', $formData['signature_data'] ?? ''); // This is the input for requester's name/signature


            // --- Output the modified spreadsheet ---
            $writer = new Xlsx($spreadsheet);
            $payeeNameSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $formData['payee_name'] ?? 'Payee');
            $outputFileName = 'Request_For_Payment_' . $payeeNameSanitized . '_' . ($formData['reference_no'] ?? '') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('RFP Excel - Error loading template: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error loading the Request for Payment Excel template.', 'details' => $e->getMessage()], 500);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('RFP Excel - PhpSpreadsheet error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error processing the Request for Payment Excel file.', 'details' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('RFP Excel - Unexpected error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'An unexpected error occurred generating the Request for Payment Excel.', 'details' => $e->getMessage()], 500);
        }
    }
}