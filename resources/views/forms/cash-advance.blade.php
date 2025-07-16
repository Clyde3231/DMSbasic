@php
    // Determine mode: Is this for creating a new document or editing an existing one?
    // The controller will pass $isEditMode = true and $documentRecord when editing.
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true; // For future preview integration

    // --- Initialize variables for form fields ---
    // If in edit mode, use data from $documentRecord (which contains 'data' sub-array).
    // Otherwise, use default values for create mode.

    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';

    // $formDataFromController contains the 'data' JSON blob from the document record
    $formDataFromController = $isEditMode ? ($documentRecord->data ?? []) : [];

    $initialEmployeeName = $formDataFromController['employee_name'] ?? '';
    $initialEmployeeNum = $formDataFromController['employee_num'] ?? '';
    $initialDepartment = $formDataFromController['department'] ?? '';
    $initialPosition = $formDataFromController['position'] ?? '';
    $initialDateFiled = $formDataFromController['date_filed'] ?? now()->format('Y-m-d'); // Default to today
    $initialReferenceNo = $formDataFromController['reference_no'] ?? 'CA-'; // Will be completed client-side for new

    // The 'items' for cash advance (amount & details)
    $defaultItems = [['amount' => '', 'details' => '']];
    $initialItemsJson = json_encode($formDataFromController['items'] ?? $defaultItems);

    // Fields from the bottom signature-like section
    $initialSignature = $formDataFromController['signature_data'] ?? ''; // Example: if you store this
    $initialReleasedDate = $formDataFromController['released_date'] ?? '';
    $initialReceivedBy = $formDataFromController['received_by'] ?? '';

    // This variable would be set to true if in a separate "preview" mode.
    // For create and edit, fields are generally editable.
    $makeFieldsReadOnly = false; // ($isPreviewMode ?? false);
@endphp

@extends('layouts.app') {{-- Or your main layout file --}}

@section('content')
<!DOCTYPE html>
{{-- Updated x-data call to use the global function and pass URLs --}}
<html lang="en" x-data="cashAdvanceForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ?? 'null' }},
    '{{ addslashes($initialDocumentTitle) }}',
    '{{ addslashes($initialEmployeeName) }}',
    '{{ addslashes($initialEmployeeNum) }}',
    '{{ addslashes($initialDepartment) }}',
    '{{ addslashes($initialPosition) }}',
    '{{ $initialDateFiled }}',
    '{{ addslashes($initialReferenceNo) }}',
    {{ $initialItemsJson }},
    '{{ addslashes($initialSignature) }}',
    '{{ $initialReleasedDate }}',
    '{{ addslashes($initialReceivedBy) }}',
    // Pass URLs:
    '{{ route("forms.cash-advance.download.excel") }}', {{-- ENSURE THIS ROUTE EXISTS --}}
    '{{ route("documents.store") }}',
    '{{ route("documents.index") }}'
)" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @if($isEditMode)
            Edit: {{ $initialDocumentTitle ?: 'Cash Advance Form' }}
        @else
            Create Cash Advance Form
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
        .input-underline { border: none; border-bottom: 1px solid black; padding-top: 0.125rem; padding-bottom: 0.125rem; font-size: 0.875rem; }
        .input-underline:focus { outline: none; ring: 0; border-bottom-color: #2563eb; }
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
                        Cash Advance Form
                    @endif
                </h1>
                <div class="flex space-x-2">
                    <button @click="downloadCashAdvanceExcel" :disabled="isDownloadingExcel"
                            class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition duration-300"
                            :class="{ 'button-loading opacity-75 cursor-not-allowed': isDownloadingExcel }">
                        <span x-text="isDownloadingExcel ? 'Downloading...' : 'Download Excel'"></span>
                    </button>
                    {{-- Other header buttons like "Send" can be added here if needed --}}
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <form @submit.prevent class="space-y-6">
                        {{-- Document Title Input (for saving to 'documents' table) --}}
                        <div class="mb-4">
                            <label for="documentTitle" class="block text-xs font-bold uppercase text-gray-700 mb-1">Document Title (for saving):</label>
                            <input id="documentTitle" type="text" x-model="documentTitle" placeholder="e.g., CA for Travel Expenses" class="w-full border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                        </div>

                        <!-- Top Section: Employee Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NAME:</label><input type="text" x-model="employeeName" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NUM</label><input type="text" x-model="employeeNum" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">DEPARTMENT:</label><input type="text" x-model="department" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">POSITION :</label><input type="text" x-model="position" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">DATE FILED:</label><input type="date" x-model="dateFiled" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">REFERENCE NO.</label><input type="text" x-model="referenceNo" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly></div>
                        </div>

                        <!-- Middle Section: Amount/Details Table -->
                        <div class="mt-8">
                            <div class="border-2 border-black">
                                <div class="grid grid-cols-12 bg-gray-50">
                                    <div class="col-span-3 p-2 text-center font-bold border-b-2 border-r-2 border-black text-sm uppercase">AMOUNT</div>
                                    <div class="col-span-9 p-2 text-center font-bold border-b-2 border-black text-sm uppercase">DETAILS</div>
                                </div>
                                <div id="details-rows-container">
                                    <template x-for="(item, index) in items" :key="index">
                                        <div class="grid grid-cols-12 relative" :class="index < items.length - 1 ? 'border-b-2 border-black' : ''">
                                            <div class="col-span-3 border-r-2 border-black">
                                                <textarea x-model="item.amount" class="w-full p-2 border-0 focus:ring-0 min-h-[100px] md:min-h-[150px] resize-none align-top text-sm" placeholder="Enter amount" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea>
                                            </div>
                                            <div class="col-span-9">
                                                <textarea x-model="item.details" class="w-full p-2 border-0 focus:ring-0 min-h-[100px] md:min-h-[150px] resize-none align-top text-sm" placeholder="Enter details" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea>
                                            </div>
                                            <template x-if="items.length > 1 && !{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                                <button type="button" @click="items.splice(index, 1)"
                                                    class="absolute top-1 right-1 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                                    title="Remove item">×</button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex justify-end mt-3" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                <button type="button"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-4 py-2 rounded font-semibold transition duration-300 text-xs uppercase"
                                    @click="items.push({ amount: '', details: '' })">
                                    ➕ Add Item
                                </button>
                            </div>
                        </div>

                        <!-- Bottom Section: Signatures & Other Data -->
                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                            <div class="space-y-8">
                                <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Signature (Employee):</label><input type="text" x-model="signatureData" placeholder="Employee signs here / Printed Name" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Released Date:</label><input type="date" x-model="releasedDate" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Received By (Employee):</label><input type="text" x-model="receivedBy" placeholder="Employee signs here / Printed Name" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4 text-sm" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            </div>
                            <div class="space-y-6"> {{-- These are typically non-editable placeholders for physical signatures --}}
                                <div class="pt-1"><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Noted By:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1">Department Head</p></div>
                                <div class="pt-1"><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Released By:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1">Accounting</p></div>
                                <div class="pt-1"><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Approved By:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1">President</p></div>
                            </div>
                        </div>

                        <!-- Main Submit/Update Button -->
                        <div class="pt-8" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            <button type="button" @click="isEdit ? updateDocument() : submitFormPrompt()"
                                    :disabled="isSaving"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider"
                                    :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                                <span x-text="isSaving ? (isEdit ? 'UPDATING CASH ADVANCE...' : 'SUBMITTING CASH ADVANCE...') : (isEdit ? 'UPDATE CASH ADVANCE' : 'SUBMIT CASH ADVANCE')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

</body>
</html>
@endsection