<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');


});

Route::view('/documents/cash-advance', 'forms.cash-advance');
Route::view('/documents/reimbursement', 'forms.reimbursement');
Route::view('/documents/purchase-request', 'forms.purchase-request');
Route::view('/documents/request-payment', 'forms.request-payment');
Route::view('/documents/acknowledgement-receipt', 'forms.acknowledgment-receipt');
Route::view('/documents/commission-incentiverequest', 'forms.commission-incentiverequest');
Route::view('/documents/delivery-receipt', 'forms.delivery-receipt');
Route::view('/documents/purchase-order', 'forms.purchase-order');
Route::view('/documents/delivery-checklist', 'forms.delivery-checklist');
