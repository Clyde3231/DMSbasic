<?php

namespace App\Http\Controllers; // Or your specific controller namespace

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;   // If formatting dates explicitly
use PhpOffice\PhpSpreadsheet\Cell\DataType; // For numeric types
use Illuminate\Support\Facades\Log;
use App\Models\Document; // If downloading a saved document by ID

class AcknowledgementReceiptController extends Controller // Or your existing controller name
{
    /**
     * Downloads an Excel representation of the CURRENT Acknowledgement Receipt data from the web form.
     * Called via POST from the form page itself.
     */
    public function downloadCurrentAcknowledgementReceiptExcel(Request $request)
    {
        try {
            $formData = $request->json()->all();

            $templatePath = storage_path('app/templates/ADM-PCH-004 Acknowledgement Receipt.xlsx'); // ADJUST FILENAME IF NEEDED

            if (!file_exists($templatePath)) {
                Log::error('Ack Receipt Excel template not found: ' . $templatePath);
                return response()->json(['error' => 'Acknowledgement Receipt Excel template file not found.'], 500);
            }

            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // --- Populate the template with formData ---

            // AR No. (H6) - Using referenceNo from form, or a dedicated ar_no if available
            $sheet->setCellValue('H6', $formData['ar_no'] ?? ($formData['refPoNo'] ?? 'N/A'));

            // Header Info
            $sheet->setCellValue('B10', $formData['deliveredTo'] ?? ''); // DELIVERED TO
            $sheet->setCellValue('B11', $formData['address'] ?? '');     // ADDRESS
            $sheet->setCellValue('B12', $formData['attention'] ?? '');   // ATTENTION

            // Date in H10 - The form sends a pre-formatted string like "Sunday, May 18, 2025"
            // If the template cell H10 is formatted as General or Text, this is fine.
            // If it's formatted as Date, PhpSpreadsheet might try to interpret it.
            // For pre-formatted strings, often best to set as string if issues arise with date cells.
            $sheet->setCellValue('H10', $formData['date'] ?? ''); // DATE

            $sheet->setCellValue('H11', $formData['refPoNo'] ?? '');      // REF/PO NO

            // Items Table
            $items = $formData['items'] ?? [];
            $itemStartRow = 15; // Data for items starts at row 15
            $maxTemplateRowsForItems = 26; // Rows 15 to 40 inclusive

            for ($i = 0; $i < $maxTemplateRowsForItems; $i++) {
                $currentRow = $itemStartRow + $i;
                if (isset($items[$i])) {
                    $item = $items[$i];

                    // Quantity: Column A (merged A:B)
                    $quantity = $item['quantity'] ?? '';
                    if ($quantity !== '' && is_numeric($quantity)) {
                        $sheet->setCellValueExplicit('A'.$currentRow, (float)$quantity, DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValue('A'.$currentRow, $quantity);
                    }

                    // Unit: Column C
                    $sheet->setCellValue('C'.$currentRow, $item['unit'] ?? '');

                    // Brand/Particulars: Column D (merged D:H)
                    $sheet->setCellValue('D'.$currentRow, $item['brandParticulars'] ?? '');

                    // Model data is not in this template based on headers (Quantity, Unit, Brand/Particulars, Part/Serial)
                    // If 'model' data exists in $formData['items'] and needs to go somewhere, adjust below.
                    // For now, we map based on the template's visible headers.

                    // Part/Serial Number: Column I (merged I:J)
                    $sheet->setCellValue('I'.$currentRow, $item['partSerialNumber'] ?? '');
                } else {
                    // Clear cells for unused template rows
                    $sheet->setCellValue('A'.$currentRow, ''); // Quantity
                    $sheet->setCellValue('C'.$currentRow, ''); // Unit
                    $sheet->setCellValue('D'.$currentRow, ''); // Brand/Particulars
                    $sheet->setCellValue('I'.$currentRow, ''); // Part/Serial
                }
            }

            // Remarks
            // Cell B41, merged B41:J41
            $sheet->setCellValue('B41', $formData['remarks'] ?? '');


            // --- Output the modified spreadsheet ---
            $writer = new Xlsx($spreadsheet);
            $deliveredToSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $formData['deliveredTo'] ?? 'Recipient');
            $outputFileName = 'Acknowledgement_Receipt_' . $deliveredToSanitized . '_' . date('Ymd') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('AckRpt Excel - Error loading template: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error loading the Acknowledgement Receipt Excel template.', 'details' => $e->getMessage()], 500);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('AckRpt Excel - PhpSpreadsheet error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error processing the Acknowledgement Receipt Excel file.', 'details' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('AckRpt Excel - Unexpected error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'An unexpected error occurred generating the Acknowledgement Receipt Excel.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Downloads an Excel representation of a SAVED Acknowledgement Receipt.
     * Typically called via GET from the dashboard, using Route Model Binding.
     */
    public function downloadSavedAcknowledgementReceiptExcel(Document $document)
    {
        try {
            // Authorization check can go here
            if ($document->document_type !== 'acknowledgement_receipt' && $document->document_type !== 'tools_equipment_receipt') { // Allow for both types if needed
                abort(400, 'Invalid document type for this download.');
            }

            $formData = $document->data;
            if (empty($formData)) {
                return response("No data for this saved Acknowledgement Receipt.", 404);
            }

            $templatePath = storage_path('app/templates/ADM-PCH-004 Acknowledgement Receipt.xlsx');
            if (!file_exists($templatePath)) { /* ... error handling ... */ }

            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // --- Populate using the same logic as above, but with $formData from $document->data ---
            $sheet->setCellValue('H6', $formData['ar_no'] ?? ($formData['refPoNo'] ?? 'N/A'));
            $sheet->setCellValue('B10', $formData['deliveredTo'] ?? '');
            $sheet->setCellValue('B11', $formData['address'] ?? '');
            $sheet->setCellValue('B12', $formData['attention'] ?? '');
            $sheet->setCellValue('H10', $formData['date'] ?? '');
            $sheet->setCellValue('H11', $formData['refPoNo'] ?? '');

            $items = $formData['items'] ?? [];
            $itemStartRow = 15;
            $maxTemplateRowsForItems = 26; // Rows 15 to 40

            for ($i = 0; $i < $maxTemplateRowsForItems; $i++) {
                $currentRow = $itemStartRow + $i;
                if (isset($items[$i])) {
                    $item = $items[$i];
                    $quantity = $item['quantity'] ?? '';
                     if ($quantity !== '' && is_numeric($quantity)) {
                        $sheet->setCellValueExplicit('A'.$currentRow, (float)$quantity, DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValue('A'.$currentRow, $quantity);
                    }
                    $sheet->setCellValue('C'.$currentRow, $item['unit'] ?? '');
                    $sheet->setCellValue('D'.$currentRow, $item['brandParticulars'] ?? '');
                    $sheet->setCellValue('I'.$currentRow, $item['partSerialNumber'] ?? '');
                } else {
                    $sheet->setCellValue('A'.$currentRow, ''); $sheet->setCellValue('C'.$currentRow, '');
                    $sheet->setCellValue('D'.$currentRow, ''); $sheet->setCellValue('I'.$currentRow, '');
                }
            }
            $sheet->setCellValue('B41', $formData['remarks'] ?? '');


            $writer = new Xlsx($spreadsheet);
            $documentNameSanitized = preg_replace('/[^A-Za-z0-9_.-]/', '_', $document->document_name);
            $outputFileName = $documentNameSanitized . '_AckRpt_' . date('Ymd', strtotime($document->created_at)) . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'. $outputFileName .'"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error("Error generating saved AckRpt Excel (ID: {$document->id}): " . $e->getMessage());
            return response("Error generating Excel. Details: " . $e->getMessage(), 500);
        }
    }
}