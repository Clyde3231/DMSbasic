<?php

use Illuminate\Support\Facades\Route;
// It's good practice to group controller imports
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PulloutController; // Assuming this is where downloadExcel is
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

// Authentication routes (e.g., from Breeze or Jetstream) would typically be here or included
// require __DIR__.'/auth.php';
Route::resource('documents', DocumentController::class);