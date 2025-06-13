@php
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true;

    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';

    $formDataFromController = $isEditMode ? ($documentRecord->data ?? []) : [];

    $initialDeliveredTo = $formDataFromController['deliveredTo'] ?? ($formDataFromController['delivered_to'] ?? '');
    $initialAddress = $formDataFromController['address'] ?? '';
    $initialAttention = $formDataFromController['attention'] ?? '';

    // --- CORRECTED DATE FORMATTING ---
    // Format: "Sunday, May 18, 2025"
    // PHP date format characters: l (full day name), F (full month name), j (day of month), Y (year)
    $defaultDateString = now()->format('l, F j, Y');

    $initialDate = $isEditMode ? ($formDataFromController['date'] ?? $defaultDateString) : $defaultDateString;
    // If $formDataFromController['date'] is already a string in this format from the database,
    // and you want to preserve it, this is fine. If it's a Y-m-d string from the DB,
    // you might want to reformat it here if $isEditMode is true:
    // if ($isEditMode && isset($formDataFromController['date'])) {
    //     try {
    //         $initialDate = \Carbon\Carbon::parse($formDataFromController['date'])->format('l, F j, Y');
    //     } catch (\Exception $e) {
    //         $initialDate = $formDataFromController['date']; // Fallback if parse fails
    //     }
    // } else {
    //     $initialDate = $defaultDateString;
    // }


    $initialRefPoNo = $formDataFromController['refPoNo'] ?? ($formDataFromController['ref_po_no'] ?? '');
    $initialRemarks = $formDataFromController['remarks'] ?? '';

    $defaultItems = [['quantity' => null, 'unit' => '', 'brandParticulars' => '', 'model' => '', 'partSerialNumber' => ''], ['quantity' => null, 'unit' => '', 'brandParticulars' => '', 'model' => '', 'partSerialNumber' => '']];
    $initialItemsJson = json_encode($formDataFromController['items'] ?? $defaultItems);

    $makeFieldsReadOnly = false; // ($isPreviewMode ?? false);
@endphp

@extends('layouts.app')

@section('content')
<!DOCTYPE html>
{{-- Updated x-data call --}}
<html lang="en" x-data="acknowledgementReceiptForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ?? 'null' }},
    '{{ addslashes($initialDocumentTitle) }}',
    '{{ addslashes($initialDeliveredTo) }}',
    '{{ addslashes($initialAddress) }}',
    '{{ addslashes($initialAttention) }}',
    '{{ addslashes($initialDate) }}',
    '{{ addslashes($initialRefPoNo) }}',
    {{ $initialItemsJson }},
    '{{ addslashes($initialRemarks) }}',
    // Pass URLs:
    '{{ route("forms.acknowledgement-receipt.download.excel") }}', {{-- YOU NEED TO CREATE THIS ROUTE --}}
    '{{ route("documents.store") }}',
    '{{ route("documents.index") }}'
)" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @if($isEditMode)
            Edit: {{ $initialDocumentTitle ?: 'Acknowledgement Receipt' }}
        @else
            Create {{-- Tools/Equipment or --}} Acknowledgement Receipt
        @endif
    </title>
    {{-- CDNs for Tailwind and Alpine (Alpine can be removed if globally included via app.js) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script src="https://unpkg.com/alpinejs" defer></script> --}} {{-- Assuming Alpine is in app.js --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
        .input-underline { border: none; border-bottom: 1px solid black; padding-top: 0.125rem; padding-bottom: 0.125rem; font-size: 0.875rem; }
        .input-underline:focus { outline: none; ring: 0; border-bottom-color: #2563eb; }
        .input-underline-static { border: none; border-bottom: 1px solid black; padding-top: 0.125rem; padding-bottom: 0.125rem; font-size: 0.875rem; background-color: #f9fafb; }
        .table-header-cell { background-color: #f9fafb; font-weight: bold; text-align: center; padding: 0.5rem; border: 1px solid black; font-size: 0.75rem; text-transform: uppercase; }
        .table-data-cell { border: 1px solid black; vertical-align: top; }
        .table-cell-textarea { width: 100%; height: 100%; padding: 0.5rem; border: none; font-size: 0.875rem; box-sizing: border-box; resize: none; min-height: 60px; }
        .signature-line { border-bottom: 1px solid black; height: 1.5rem; margin-top: 0.25rem; }
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
                        {{-- Tools/Equipment Receipt or --}} Acknowledgement Receipt
                    @endif
                </h1>
                <div class="flex space-x-2">
                    {{-- <button class="text-sm bg-[#FFA500] ...">Send</button> --}}
                    <button @click="downloadAcknowledgementReceiptExcel" :disabled="isDownloadingExcel"
                            class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition duration-300"
                            :class="{ 'button-loading opacity-75 cursor-not-allowed': isDownloadingExcel }">
                        <span x-text="isDownloadingExcel ? 'Downloading...' : 'Download Excel'"></span>
                    </button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <form @submit.prevent class="space-y-6">
                        <div class="mb-4">
                            <label for="documentTitle" class="block text-xs font-bold uppercase text-gray-700 mb-1">Document Title (for saving):</label>
                            <input id="documentTitle" type="text" x-model="documentTitle" placeholder="e.g., Ack Receipt for Laptops" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                        </div>

                        <div class="border border-black p-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div class="space-y-2">
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">DELIVERED TO:</label><input type="text" x-model="deliveredTo" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">ADDRESS:</label><input type="text" x-model="address" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">ATTENTION:</label><input type="text" x-model="attention" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">DATE:</label><input type="text" x-model="date" class="input-underline-static flex-1" readonly></div> {{-- Date is usually fixed or from server --}}
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">REF/PO NO:</label><input type="text" x-model="refPoNo" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border-2 border-black">
                            <div class="grid grid-cols-12">
                                <div class="col-span-2 table-header-cell">QUANTITY</div>
                                <div class="col-span-1 table-header-cell">UNIT</div>
                                <div class="col-span-4 table-header-cell">BRAND/PARTICULARS</div>
                                <div class="col-span-2 table-header-cell">MODEL</div>
                                <div class="col-span-3 table-header-cell">PART/SERIAL NUMBER</div>
                            </div>
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 relative">
                                    <div class="col-span-2 table-data-cell"><textarea x-model.number="item.quantity" class="table-cell-textarea text-center" placeholder="0" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-1 table-data-cell"><textarea x-model="item.unit" class="table-cell-textarea text-center" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-4 table-data-cell"><textarea x-model="item.brandParticulars" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-2 table-data-cell"><textarea x-model="item.model" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-3 table-data-cell"><textarea x-model="item.partSerialNumber" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1 && !{{ $makeFieldsReadOnly ? 'true' : 'false' }}" class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 ...">×</button>
                                </div>
                            </template>
                            <div class="p-2 border-t border-black flex justify-end" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                <button type="button" @click="addItem()" class="text-xs bg-blue-600 ...">Add Item</button>
                            </div>
                            <div class="grid grid-cols-12 border-t border-black">
                                <div class="col-span-2 table-header-cell !text-left !bg-white ...">REMARKS:</div>
                                <div class="col-span-10 table-data-cell !border-l-0 !border-b-0"><textarea x-model="remarks" class="table-cell-textarea min-h-[60px]" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                            </div>
                        </div>

                        <div class="text-center text-sm italic mt-4 py-2">Received the above tools/equipments in good order and condition</div>

                        <div class="mt-8 pt-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8 text-sm">
                            {{-- Static Signature Section --}}
                            <div class="md:col-span-2 space-y-10">
                                <div class="grid grid-cols-2 gap-x-8"><div><p>Prepared By</p><div class="signature-line"></div></div><div><p>Checked By</p><div class="signature-line"></div></div></div>
                                <div class="grid grid-cols-2 gap-x-8"><div><p>Acknowledged By</p><div class="signature-line"></div></div><div><p>Delivered By</p><div class="signature-line"></div></div></div>
                            </div>
                            <div class="space-y-2">
                                 <div class="flex items-end"><label>By:</label><div class="signature-line flex-1"></div></div>
                                <p class="text-xs text-center">Signature over printed name</p>
                                <div class="flex items-end mt-3"><label>Date:</label><div class="signature-line flex-1"></div></div>
                            </div>
                        </div>

                        <div class="pt-12" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            <button type="button" @click="isEdit ? updateDocument() : submitFormPrompt()"
                                    :disabled="isSaving"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full ..."
                                    :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                                <span x-text="isSaving ? (isEdit ? 'UPDATING RECEIPT...' : 'SAVING RECEIPT...') : (isEdit ? 'UPDATE ACKNOWLEDGEMENT RECEIPT' : 'SAVE ACKNOWLEDGEMENT RECEIPT')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Right Side Menu --}}
    </div>
</body>
</html>
@endsection