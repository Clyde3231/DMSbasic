{{-- At the top of forms.pullout.blade.php --}}
@php
    // Determine mode: Is this for creating a new document or editing an existing one?
    // The controller will pass $isEditMode = true and $documentRecord when editing.
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true; // For future preview integration

    // --- Initialize variables for form fields ---
    // If in edit mode, use data from $documentRecord and $documentData.
    // Otherwise, use default values for create mode.

    // For the 'documents' table fields themselves (like document_name)
    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';
    // $initialDocumentType = $isEditMode ? ($documentRecord->document_type ?? 'pull_out_receipt') : 'pull_out_receipt'; // Type usually fixed for a specific form
    // $initialStatus = $isEditMode ? ($documentRecord->status ?? 'draft') : 'draft'; // Status might be editable

    // For the 'data' JSON blob (the actual form content)
    $documentData = $isEditMode ? ($documentData ?? []) : []; // $documentData comes from controller in edit/preview

    $initialClient = $documentData['client'] ?? '';
    $initialAddress = $documentData['address'] ?? '';
    $initialAttention = $documentData['attention'] ?? '';
    $initialDate = $documentData['date'] ?? now()->format('Y-m-d'); // Default to today if not set
    $initialRefPoNo = $documentData['refPoNo'] ?? '';
    $initialRemarks = $documentData['remarks'] ?? '';

    // For the 'items' array, ensure it's a valid JSON string for Alpine, or an empty array structure
    $defaultItems = [['quantity' => null, 'unit' => '', 'brandParticulars' => '', 'model' => '', 'partSerialNumber' => ''], ['quantity' => null, 'unit' => '', 'brandParticulars' => '', 'model' => '', 'partSerialNumber' => '']];
    $initialItemsJson = json_encode($documentData['items'] ?? $defaultItems);

    // Read-only state (true if preview, false if create/edit)
    // $isReadOnly = $isPreviewMode; // For future preview integration
    $isReadOnly = false; // For create/edit, fields are NOT read-only
@endphp

@extends('layouts.app') {{-- Or your main layout file --}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="materialForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ? "'".$initialDocumentId."'" : 'null' }},
    '{{ addslashes($initialDocumentTitle) }}',
    '{{ addslashes($initialClient) }}',
    '{{ addslashes($initialAddress) }}',
    '{{ addslashes($initialAttention) }}',
    '{{ $initialDate }}',
    '{{ addslashes($initialRefPoNo) }}',
    {{ $initialItemsJson }},
    '{{ addslashes($initialRemarks) }}'
)" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        @if($isEditMode)
            Edit: {{ $initialDocumentTitle ?: 'Material Pull-Out Form' }}
        @else
            Create Material Pull-Out Form
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
        .input-underline { border: none; border-bottom: 1px solid black; padding-top: 0.125rem; padding-bottom: 0.125rem; font-size: 0.875rem; /* text-sm */ }
        .input-underline:focus { outline: none; ring: 0; border-bottom-color: #2563eb; /* blue-600 */ }
        .table-header-cell { background-color: #f9fafb; /* gray-50 */ font-weight: bold; text-align: center; padding: 0.5rem; border: 1px solid black; font-size: 0.75rem; /* text-xs */ text-transform: uppercase; }
        .table-data-cell { border: 1px solid black; vertical-align: top; }
        .table-cell-textarea { width: 100%; height: 100%; padding: 0.5rem; border: none; font-size: 0.875rem; box-sizing: border-box; resize: none; min-height: 60px; /* Taller cells */ }
        .table-cell-textarea:focus { outline: none; }
        .signature-line { border-bottom: 1px solid black; height: 1.5rem; margin-top: 0.25rem; }
        .button-loading::after { content: ""; display: inline-block; width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; margin-left: 8px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-[#F0F0F0] font-sans min-h-screen flex flex-col">

    <div class="bg-[#6FFFA0] w-full px-6 py-2 flex justify-between items-center">
        <div>
            <a href="{{ $isEditMode ? route('documents.show', $initialDocumentId) : route('documents.index') }}" class="text-sm text-black hover:underline">← Back</a>
        </div>
        <div class="text-right text-sm text-black">
            @auth
            <div class="font-semibold">{{ Auth::user()->name }}</div>
            <div class="text-xs text-gray-700">{{ Auth::user()->email }}</div>
            @endauth
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">
        <div class="flex-1 overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <h1 class="text-xl font-semibold text-gray-800">
                    @if($isEditMode)
                        Edit: <span class="font-normal">{{ $initialDocumentTitle }}</span>
                    @else
                        Material Pull-Out Form
                    @endif
                </h1>
                <div class="flex space-x-2">
                    {{-- Action buttons are now primarily handled by the main submit button at the bottom --}}
                    {{-- But you can keep specific ones like "Download" if needed --}}
                    <button @click="downloadFormAsExcel" :disabled="isDownloading"
                            class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300"
                            :class="{ 'button-loading opacity-75 cursor-not-allowed': isDownloading }">
                        <span x-text="isDownloading ? 'Downloading...' : 'Download Form'">Download Form</span>
                          <button @click="saveDocumentPrompt" ...>Save</button>
                    </button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <form @submit.prevent class="space-y-6">
                        <!-- Header Section: Client Info -->
                        <div class="border border-black p-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div class="space-y-2">
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">CLIENT:</label><input type="text" x-model="client" class="input-underline flex-1" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">ADDRESS:</label><input type="text" x-model="address" class="input-underline flex-1" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">ATTENTION:</label><input type="text" x-model="attention" class="input-underline flex-1" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">DATE:</label><input type="date" x-model="date" class="input-underline flex-1" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">REF/PO NO:</label><input type="text" x-model="refPoNo" class="input-underline flex-1" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">DOC NAME:</label><input type="text" x-model="documentTitle" placeholder="e.g., MPO for Client X" class="input-underline flex-1" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table Section -->
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
                                    <div class="col-span-2 table-data-cell"><textarea x-model.number="item.quantity" class="table-cell-textarea text-center" :readonly="{{ $isReadOnly ? 'true' : 'false' }}" placeholder="0"></textarea></div>
                                    <div class="col-span-1 table-data-cell"><textarea x-model="item.unit" class="table-cell-textarea text-center" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-4 table-data-cell"><textarea x-model="item.brandParticulars" class="table-cell-textarea" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-2 table-data-cell"><textarea x-model="item.model" class="table-cell-textarea" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-3 table-data-cell"><textarea x-model="item.partSerialNumber" class="table-cell-textarea" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <button type="button" @click="removeItem(index)" x-show="!isEdit && !{{ $isReadOnly ? 'true' : 'false' }}" {{-- Show only in create mode --}}
                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                        title="Remove item">×</button>
                                     {{-- In edit mode, removing items might need more complex logic (soft deletes, tracking changes) --}}
                                     {{-- For simplicity, let's allow remove in edit mode too for now, adjust if needed --}}
                                     <button type="button" @click="removeItem(index)" x-show="isEdit && !{{ $isReadOnly ? 'true' : 'false' }}"
                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                        title="Remove item">×</button>
                                </div>
                            </template>
                            <div class="p-2 border-t border-black flex justify-end" x-show="!{{ $isReadOnly ? 'true' : 'false' }}">
                                <button type="button" @click="addItem()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Add Item</button>
                            </div>

                            <div class="grid grid-cols-12 border-t border-black">
                                <div class="col-span-2 table-header-cell !text-left !bg-white !font-semibold !text-gray-700 !border-r-0 !border-b-0">REMARKS:</div>
                                <div class="col-span-10 table-data-cell !border-l-0 !border-b-0">
                                    <textarea x-model="remarks" class="table-cell-textarea min-h-[60px]" :readonly="{{ $isReadOnly ? 'true' : 'false' }}"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center text-sm italic mt-4 py-2">
                            Released the above materials for pull-out
                        </div>

                        <div class="mt-8 pt-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8 text-sm">
                            {{-- Signature lines are typically not editable form fields --}}
                            <div class="md:col-span-2 space-y-10">
                                <div class="grid grid-cols-2 gap-x-8"><div><p class="uppercase font-semibold text-gray-600">Prepared By</p><div class="signature-line"></div></div><div><p class="uppercase font-semibold text-gray-600">Checked By</p><div class="signature-line"></div></div></div>
                                <div class="grid grid-cols-2 gap-x-8"><div><p class="uppercase font-semibold text-gray-600">Acknowledged By</p><div class="signature-line"></div></div><div><p class="uppercase font-semibold text-gray-600">Pulled-Out By</p><div class="signature-line"></div></div></div>
                            </div>
                            <div class="space-y-2">
                                 <div class="flex items-end"><label class="font-semibold mr-2">By:</label><div class="signature-line flex-1"></div></div>
                                <p class="text-xs text-center text-gray-600">Signature over printed name</p>
                                <div class="flex items-end mt-3"><label class="font-semibold mr-2">Date:</label><div class="signature-line flex-1"></div></div>
                            </div>
                        </div>

                        <div class="pt-12" x-show="!{{ $isReadOnly ? 'true' : 'false' }}">
                            <button type="button" @click="isEdit ? updateDocumentData() : saveDocumentPrompt()"
                                    :disabled="isSaving"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider"
                                    :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                                <span x-text="isSaving ? (isEdit ? 'UPDATING DOCUMENT...' : 'SAVING DOCUMENT...') : (isEdit ? 'UPDATE DOCUMENT' : 'SAVE NEW DOCUMENT')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
  
</script>
 @vite('resources/js/pullout.js')
</body>
</html>
@endsection