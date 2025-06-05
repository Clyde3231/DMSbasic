<?php

use Illuminate\Support\Facades\Route;
// It's good practice to group controller imports
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\PulloutController; // Assuming this is where downloadExcel is
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RequestForPaymentController; // Add this if new controller
// use App\Http\Controllers\FormController; // If you had another one

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to the documents dashboard
Route::get('/', function () {
    return redirect()->route('documents.index');
});



// Document Management System Routes
Route::resource('documents', DocumentController::class);
// This handles:
// GET       /documents              documents.index     (Your new dashboard)
// POST      /documents              documents.store     (Save new documents)
// GET       /documents/{document}   documents.show      (Preview document)
// PUT/PATCH /documents/{document}   documents.update    (Update document)
// DELETE    /documents/{document}   documents.destroy   (Delete document)
// GET       /documents/create       documents.create    (Generic create form - we'll mostly bypass this)
// GET       /documents/{document}/edit documents.edit   (Edit form)


// Specific Form Display Routes (used by "+ Add New Document" dropdown)
// It's good to group these or prefix them for clarity, e.g., '/forms/...'
Route::prefix('forms')->name('forms.')->group(function () {
    Route::view('/pullout', 'forms.pullout')->name('pullout');
    Route::view('/cash-advance', 'forms.cash-advance')->name('cash-advance');
    Route::view('/reimbursement', 'forms.reimbursement')->name('reimbursement');
    Route::view('/purchase-request', 'forms.purchase-request')->name('purchase-request');
    Route::view('/request-payment', 'forms.request-payment')->name('request-payment');
    Route::view('/acknowledgement-receipt', 'forms.acknowledgment-receipt')->name('acknowledgment-receipt');
    Route::view('/commission-incentiverequest', 'forms.commission-incentiverequest')->name('commission-incentiverequest');
    Route::view('/delivery-receipt', 'forms.delivery-receipt')->name('delivery-receipt');
    Route::view('/purchase-order', 'forms.purchase-order')->name('purchase-order');
    Route::view('/delivery-checklist', 'forms.delivery-checklist')->name('delivery-checklist');
    Route::view('/te-acknowledgement', 'forms.te-acknowledgement')->name('te-acknowledgement');
    Route::view('/borrow-receipt', 'forms.borrow-receipt')->name('borrow-receipt');
   
});

// Excel Download Route (specific to pullout form data)
Route::post('/pullout/download-excel', [PulloutController::class, 'downloadExcel'])->name('form.download.excel');
Route::post('/forms/cash-advance/download-excel', [CashAdvanceController::class, 'downloadCashAdvanceExcel'])->name('forms.cash-advance.download.excel');
Route::post('/forms/reimbursement/download-excel', [ReimbursementController::class, 'downloadExcel'])->name('forms.reimbursement.download.excel');
Route::post('/forms/purchase-request/download-excel', [PurchaseRequestController::class, 'downloadPurchaseRequestExcel'])->name('forms.purchase-request.download.excel');// Authentication routes (e.g., from Breeze or Jetstream) would typically be here or included
Route::post('/forms/request-for-payment/download-excel', [RequestForPaymentController::class, 'downloadRequestForPaymentExcel'])->name('forms.request-for-payment.download.excel');
// require __DIR__.'/auth.php';
Route::resource('documents', DocumentController::class);


// In routes/web.php
// In routes/web.php
Route::patch('/documents/{document}/update-status', [App\Http\Controllers\DocumentController::class, 'updateStatus'])->name('documents.updateStatus');



// --- DOCUMENT SPECIFIC DOWNLOAD ROUTES (GET Requests by ID) ---
Route::prefix('documents/{document}')->group(function () { // {document} will be route-model bound
    // Pull-Out Receipt
    Route::get('/download-pullout', [PulloutController::class, 'downloadSavedPulloutExcel'])->name('documents.download.pullout');

    // Cash Advance
    Route::get('/download-cash-advance', [CashAdvanceController::class, 'downloadSavedCashAdvanceExcel'])->name('documents.download.cash-advance');

    // Purchase Request
    Route::get('/download-purchase-request', [PurchaseRequestController::class, 'downloadSavedPurchaseRequestExcel'])->name('documents.download.purchase-request');

    // Request for Payment
    Route::get('/download-request-for-payment', [RequestForPaymentController::class, 'downloadSavedRequestForPaymentExcel'])->name('documents.download.request-for-payment');

    // --- ADD ROUTES FOR ALL OTHER DOCUMENT TYPES ---
    // Example for Reimbursement (assuming ReimbursementController and method)
    // Route::get('/download-reimbursement', [ReimbursementController::class, 'downloadSavedReimbursementExcel'])->name('documents.download.reimbursement');

    // Example for Commission Incentive Request
    // Route::get('/download-commission-incentive', [CommissionController::class, 'downloadSavedCommissionExcel'])->name('documents.download.commission-incentive');
});
