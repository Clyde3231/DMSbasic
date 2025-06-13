@php
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true;

    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';

    $formDataFromController = $isEditMode ? ($documentRecord->data ?? []) : [];

    // Top Employee Info
    $initialEmployeeName = $formDataFromController['employee_name'] ?? '';
    $initialEmployeeNum = $formDataFromController['employee_num'] ?? '';
    $initialDepartment = $formDataFromController['department'] ?? '';
    $initialPosition = $formDataFromController['position'] ?? '';
    $initialDateFiled = $formDataFromController['date_filed'] ?? now()->format('Y-m-d');
    $initialReferenceNo = $formDataFromController['reference_no'] ?? 'RFPF-';

    // Payee Information
    $initialPayeeName = $formDataFromController['payee_name'] ?? '';
    $initialPayeeAddress = $formDataFromController['payee_address'] ?? '';
    $initialPayeeContact = $formDataFromController['payee_contact'] ?? ''; // Phone/Email
    $initialBankName = $formDataFromController['bank_name'] ?? '';
    $initialAccountNumber = $formDataFromController['account_number'] ?? '';
    $initialSwiftBic = $formDataFromController['swift_bic'] ?? '';
    $initialIban = $formDataFromController['iban'] ?? '';

    // Payment Details
    $initialPaymentDescription = $formDataFromController['payment_description'] ?? '';
    $initialPaymentAmount = $formDataFromController['payment_amount'] ?? null;
    $initialPaymentCurrency = $formDataFromController['payment_currency'] ?? 'PHP'; // Default currency
    $initialPaymentMethods = $formDataFromController['payment_methods'] ?? []; // Array for checkboxes
    $initialPaymentInvoiceRef = $formDataFromController['payment_invoice_ref'] ?? '';

    // Signature (if you store this input)
    $initialSignatureData = $formDataFromController['signature_data'] ?? '';


    $makeFieldsReadOnly = false; // ($isPreviewMode ?? false);
@endphp

@extends('layouts.app')

@section('content')
<!DOCTYPE html>
{{-- Updated x-data call to use the global function and pass URLs --}}
<html lang="en" x-data="requestPaymentForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ?? 'null' }},
    '{{ addslashes($initialDocumentTitle) }}',
    '{{ addslashes($initialEmployeeName) }}',
    '{{ addslashes($initialEmployeeNum) }}',
    '{{ addslashes($initialDepartment) }}',
    '{{ addslashes($initialPosition) }}',
    '{{ $initialDateFiled }}',
    '{{ addslashes($initialReferenceNo) }}',
    '{{ addslashes($initialPayeeName) }}',
    '{{ addslashes($initialPayeeAddress) }}',
    '{{ addslashes($initialPayeeContact) }}',
    '{{ addslashes($initialBankName) }}',
    '{{ addslashes($initialAccountNumber) }}',
    '{{ addslashes($initialSwiftBic) }}',
    '{{ addslashes($initialIban) }}',
    '{{ addslashes($initialPaymentDescription) }}',
    {{ $initialPaymentAmount ?? 'null' }},
    '{{ addslashes($initialPaymentCurrency) }}',
    {{ json_encode($initialPaymentMethods) }}, // Ensure this is passed as a valid JSON array string
    '{{ addslashes($initialPaymentInvoiceRef) }}',
    '{{ addslashes($initialSignatureData) }}',
    // Pass URLs:
    '{{ route("forms.request-for-payment.download.excel") }}', {{-- ENSURE THIS ROUTE EXISTS --}}
    '{{ route("documents.store") }}',
    '{{ route("documents.index") }}'
)" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @if($isEditMode)
            Edit: {{ $initialDocumentTitle ?: 'Request for Payment' }}
        @else
            Request for Payment
        @endif
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
        input[readonly] { background-color: #f3f4f6; cursor: default; }
        .form-checkbox { color: #3b82f6; }
        .button-loading::after { content: ""; display: inline-block; width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; margin-left: 8px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-[#F0F0F0] font-sans min-h-screen flex flex-col">

    <div class="bg-[#6FFFA0] w-full px-6 py-2 flex justify-between items-center">
        <div>
             <a href="{{ $isEditMode && $initialDocumentId ? route('documents.show', $initialDocumentId) : route('documents.index') }}" class="text-sm text-black hover:underline">← Back</a>
        </div>
        <div class="text-right text-sm text-black">
            @auth
            <div class="font-semibold">{{ Auth::user()->name ?? 'User Name' }}</div>
            <div class="text-xs text-gray-700">{{ Auth::user()->email ?? 'user@example.com' }}</div>
            @endauth
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <h1 class="text-xl font-semibold text-gray-800">
                     @if($isEditMode)
                        Edit: <span class="font-normal" x-text="documentTitle"></span>
                    @else
                        Request for Payment
                    @endif
                </h1>
                <div class="flex space-x-2">
                     <button @click="downloadRequestForPaymentExcel" :disabled="isDownloadingExcel"
                            class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition duration-300"
                            :class="{ 'button-loading opacity-75 cursor-not-allowed': isDownloadingExcel }">
                        <span x-text="isDownloadingExcel ? 'Downloading...' : 'Download Excel'"></span>
                    </button>
                    {{-- <button class="text-sm bg-[#FFA500] ...">Send</button> --}}
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <form @submit.prevent class="space-y-6">
                        <div class="mb-4">
                            <label for="documentTitle" class="block text-xs font-bold uppercase text-gray-700 mb-1">Document Title (for saving):</label>
                            <input id="documentTitle" type="text" x-model="documentTitle" placeholder="e.g., RFP for Vendor X Payment" class="w-full border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mb-8">
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NAME:</label><input type="text" x-model="employeeName" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NUMBER:</label><input type="text" x-model="employeeNum" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">DEPARTMENT:</label><input type="text" x-model="department" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">POSITION :</label><input type="text" x-model="position" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">DATE FILED:</label><input type="date" x-model="dateFiled" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">REFERENCE NO. :</label><input type="text" x-model="referenceNo" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly></div>
                        </div>

                        <div class="border-2 border-black p-4 mt-8">
                            <p class="text-sm font-semibold text-gray-800 mb-4">Payee Information:</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Payee Name</label><div class="col-span-12 md:col-span-9"><input type="text" x-model="payeeName" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Address</label><div class="col-span-12 md:col-span-9"><input type="text" x-model="payeeAddress" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Phone/Email</label><div class="col-span-12 md:col-span-9"><input type="text" x-model="payeeContact" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                <div class="grid grid-cols-12 gap-x-2 pt-1"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Bank Details</label>
                                    <div class="col-span-12 md:col-span-9 space-y-2">
                                        <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 sm:col-span-4 text-sm text-gray-600">Bank Name</label><div class="col-span-12 sm:col-span-8"><input type="text" x-model="bankName" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                        <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 sm:col-span-4 text-sm text-gray-600">Account Number</label><div class="col-span-12 sm:col-span-8"><input type="text" x-model="accountNumber" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                        <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 sm:col-span-4 text-sm text-gray-600">SWIFT/BIC Code</label><div class="col-span-12 sm:col-span-8"><input type="text" x-model="swiftBic" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                        <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 sm:col-span-4 text-sm text-gray-600">IBAN</label><div class="col-span-12 sm:col-span-8"><input type="text" x-model="iban" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-2 border-black p-4 mt-8">
                            <p class="text-sm font-semibold text-gray-800 mb-4">Payment Details:</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Description</label><div class="col-span-12 md:col-span-9"><input type="text" x-model="paymentDescription" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Amount</label><div class="col-span-12 md:col-span-5"><input type="number" step="0.01" x-model.number="paymentAmount" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Currency</label><div class="col-span-12 md:col-span-5"><input type="text" x-model="paymentCurrency" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Payment Method</label>
                                    <div class="col-span-12 md:col-span-9 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                                        <label class="flex items-center"><input type="checkbox" value="Bank Transfer" x-model="paymentMethods" class="form-checkbox ..." :disabled="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"><span>Bank Transfer</span></label>
                                        <label class="flex items-center"><input type="checkbox" value="Cheque" x-model="paymentMethods" class="form-checkbox ..." :disabled="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"><span>Cheque</span></label>
                                        <label class="flex items-center"><input type="checkbox" value="Cash" x-model="paymentMethods" class="form-checkbox ..." :disabled="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"><span>Cash</span></label>
                                        <label class="flex items-center"><input type="checkbox" value="Other" x-model="paymentMethods" class="form-checkbox ..." :disabled="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"><span>Other</span></label>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center"><label class="col-span-12 md:col-span-3 text-sm text-gray-700">Invoice / Ref No.</label><div class="col-span-12 md:col-span-9"><input type="text" x-model="paymentInvoiceRef" class="w-full border-0 border-b border-black ..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div></div>
                            </div>
                        </div>

                        <div class="mt-10 space-y-8">
                            <div><label class="block text-xs font-bold uppercase text-gray-700">Signature:</label><input type="text" x-model="signatureData" placeholder="Requester signs here / Printed Name" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4 text-sm md:w-2/3 lg:w-1/2" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-8 pt-2">
                                <div><label class="block text-xs font-bold uppercase text-gray-700">Approved by:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1 text-center">Accounting</p></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700">Noted by:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1 text-center">President</p></div>
                            </div>
                        </div>

                        <div class="pt-8" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            <button type="button" @click="isEdit ? updateDocument() : submitFormPrompt()"
                                    :disabled="isSaving"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider"
                                    :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                                <span x-text="isSaving ? (isEdit ? 'UPDATING REQUEST...' : 'SUBMITTING REQUEST...') : (isEdit ? 'UPDATE PAYMENT REQUEST' : 'SUBMIT PAYMENT REQUEST')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

   


</body>
</html>
@endsection