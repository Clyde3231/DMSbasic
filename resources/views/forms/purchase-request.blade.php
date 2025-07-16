@php
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true; // For future preview

    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';

    $formDataFromController = $isEditMode ? ($documentRecord->data ?? []) : [];

    // Fields for the top section
    $initialEmployeeName = $formDataFromController['employee_name'] ?? '';
    $initialEmployeeNum = $formDataFromController['employee_num'] ?? '';
    $initialDepartmentTop = $formDataFromController['department_top'] ?? ''; // Distinguished from department in signature
    $initialPosition = $formDataFromController['position'] ?? '';
    $initialDateFiled = $formDataFromController['date_filed'] ?? now()->format('Y-m-d');
    $initialReferenceNo = $formDataFromController['reference_no'] ?? 'PRN-'; // Base for new ref no

    // Items for the table
    $defaultItems = [['qty' => null, 'description' => '', 'unitPrice' => null]];
    $initialItemsJson = json_encode($formDataFromController['items'] ?? $defaultItems);

    // Purpose/Reason
    $initialPurpose = $formDataFromController['purpose'] ?? '';

    // Fields for the bottom signature section
    $initialRequestedBySignature = $formDataFromController['requested_by_signature'] ?? ''; // Example if you store it
    $initialDepartmentBottom = $formDataFromController['department_bottom'] ?? '';
    $initialDateBottom = $formDataFromController['date_bottom'] ?? now()->format('Y-m-d');

    $makeFieldsReadOnly = false; // ($isPreviewMode ?? false);
@endphp

@extends('layouts.app')

@section('content')
<!DOCTYPE html>
{{-- Updated x-data call to use the global function and pass URLs --}}
<html lang="en" x-data="purchaseRequestForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ?? 'null' }},
    '{{ addslashes($initialDocumentTitle) }}',
    '{{ addslashes($initialEmployeeName) }}',
    '{{ addslashes($initialEmployeeNum) }}',
    '{{ addslashes($initialDepartmentTop) }}',
    '{{ addslashes($initialPosition) }}',
    '{{ $initialDateFiled }}',
    '{{ addslashes($initialReferenceNo) }}',
    {{ $initialItemsJson }},
    '{{ addslashes($initialPurpose) }}',
    '{{ addslashes($initialRequestedBySignature) }}',
    '{{ addslashes($initialDepartmentBottom) }}',
    '{{ $initialDateBottom }}',
    // Pass URLs:
    '{{ route("forms.purchase-request.download.excel") }}', {{-- ENSURE THIS ROUTE EXISTS --}}
    '{{ route("documents.store") }}',
    '{{ route("documents.index") }}'
)" x-cloak>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @if($isEditMode)
            Edit: {{ $initialDocumentTitle ?: 'Purchase Request Form' }}
        @else
            Purchase Request Form
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
        .table-cell-input { width: 100%; padding: 0.5rem; border: none; box-sizing: border-box; font-size: 0.875rem; min-height: 40px; display: flex; align-items: center; }
        .table-cell-input:focus { outline: none; ring: 0; }
        .table-cell-textarea { width: 100%; padding: 0.5rem; border: none; box-sizing: border-box; font-size: 0.875rem; resize: none; min-height: 40px; }
        .table-cell-textarea:focus { outline:none; ring:0; }
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
                        Purchase Request
                    @endif
                </h1>
                <div class="flex space-x-2">
                    <button @click="downloadPurchaseRequestExcel" :disabled="isDownloadingExcel"
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
                            <input id="documentTitle" type="text" x-model="documentTitle" placeholder="e.g., PR for Office Supplies" class="w-full border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mb-8">
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NAME:</label><input type="text" x-model="employeeName" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NUMBER:</label><input type="text" x-model="employeeNum" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">DEPARTMENT:</label><input type="text" x-model="departmentTop" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">POSITION :</label><input type="text" x-model="position" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">DATE FILED:</label><input type="date" x-model="dateFiled" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">REFERENCE NO. :</label><input type="text" x-model="referenceNo" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly></div>
                        </div>

                        <div class="mt-8">
                            <div class="border-2 border-black">
                                <div class="grid grid-cols-12 bg-gray-50 text-sm">
                                    <div class="col-span-1 p-2 text-center font-bold border-b-2 border-r-2 border-black">Qty</div>
                                    <div class="col-span-6 p-2 text-center font-bold border-b-2 border-r-2 border-black">Description</div>
                                    <div class="col-span-2 p-2 text-center font-bold border-b-2 border-r-2 border-black">Unit Price</div>
                                    <div class="col-span-3 p-2 text-center font-bold border-b-2 border-black">Total Price</div>
                                </div>
                                <div id="item-rows-container">
                                    <template x-for="(item, index) in items" :key="index">
                                        <div class="grid grid-cols-12 relative border-b border-black">
                                            <div class="col-span-1 border-r-2 border-black h-full"><input type="number" x-model.number="item.qty" placeholder="0" class="table-cell-input text-center" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                            <div class="col-span-6 border-r-2 border-black h-full"><textarea x-model="item.description" placeholder="Item description" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                            <div class="col-span-2 border-r-2 border-black h-full"><input type="number" x-model.number="item.unitPrice" placeholder="0.00" step="0.01" class="table-cell-input text-right" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                            <div class="col-span-2 h-full"><input type="text" :value="calculateRowTotal(item)" readonly class="table-cell-input text-right bg-gray-100"></div>
                                            <div class="col-span-1 flex items-center justify-center h-full border-l-2 border-black">
                                                <template x-if="items.length > 1 && !{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                                    <button type="button" @click="removeItem(index)" class="bg-red-500 hover:bg-red-700 text-white font-bold text-xs rounded-full p-0 w-5 h-5 ...">×</button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex justify-end mt-3" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                <button type="button" class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-4 py-2 ..." @click="addItem()">➕ Add Item</button>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end items-center space-x-2">
                            <span class="text-sm font-bold uppercase text-gray-700">Total Amount</span>
                            <span class="border border-black px-3 py-1 text-sm font-semibold bg-gray-100">PHP</span>
                            <input type="text" :value="overallTotalAmount" readonly class="border border-black px-3 py-1 text-sm w-32 text-right font-semibold bg-gray-100" />
                        </div>

                        <div class="mt-8">
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">PURPOSE / REASON:</label>
                            <textarea x-model="purpose" class="w-full border-2 border-black focus:ring-1 focus:ring-blue-600 focus:border-blue-600 p-2 text-sm min-h-[100px] md:min-h-[120px] resize-y" placeholder="State the purpose or reason for this request..." :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea>
                        </div>

                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-8">
                            <div class="space-y-10">
                                <div><label class="block text-xs font-bold uppercase text-gray-700">Requested by :</label><input type="text" x-model="requestedBySignature" placeholder="Printed Name / Signature" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-6 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"><p class="text-xs text-gray-600 mt-1 text-center">Signature over printed name</p></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700">Department :</label><input type="text" x-model="departmentBottom" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700">Date :</label><input type="date" x-model="dateBottom" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            </div>
                            <div class="space-y-10 flex flex-col justify-between">
                                 <div><label class="block text-xs font-bold uppercase text-gray-700">Checked by :</label><div class="mt-6 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1 text-center">Department Head</p></div>
                                <div class="pt-2"><label class="block text-xs font-bold uppercase text-gray-700">Approved by:</label><div class="mt-6 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1 text-center">President</p></div>
                                <div></div>
                            </div>
                        </div>

                        <div class="pt-8" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            <button type="button" @click="isEdit ? updateDocument() : submitFormPrompt()"
                                    :disabled="isSaving"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider"
                                    :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                                <span x-text="isSaving ? (isEdit ? 'UPDATING REQUEST...' : 'SUBMITTING REQUEST...') : (isEdit ? 'UPDATE PURCHASE REQUEST' : 'SUBMIT PURCHASE REQUEST')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    
    </body>
</html>

@endsection