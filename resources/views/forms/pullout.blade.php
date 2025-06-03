{{-- At the top of forms.pullout.blade.php --}}
@php
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true; // For future preview

    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';

    // $documentData comes from controller for edit/preview, defaults to empty for create
    $formDataFromController = $isEditMode ? ($documentData ?? []) : [];

    $initialClient = $formDataFromController['client'] ?? '';
    $initialAddress = $formDataFromController['address'] ?? '';
    $initialAttention = $formDataFromController['attention'] ?? '';
    $initialDate = $formDataFromController['date'] ?? now()->format('Y-m-d');
    $initialRefPoNo = $formDataFromController['refPoNo'] ?? '';
    $initialRemarks = $formDataFromController['remarks'] ?? '';

    $defaultItems = [['quantity' => null, 'unit' => '', 'brandParticulars' => '', 'model' => '', 'partSerialNumber' => ''], ['quantity' => null, 'unit' => '', 'brandParticulars' => '', 'model' => '', 'partSerialNumber' => '']];
    $initialItemsJson = json_encode($formDataFromController['items'] ?? $defaultItems);

    // Fields are editable in create and edit mode. Read-only would be for a separate preview mode.
    $makeFieldsReadOnly = false; // Set to true if $isPreviewMode is active
@endphp

@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="materialForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ? $initialDocumentId : 'null' }}, {{-- Pass ID as number or null --}}
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
        .input-underline { border: none; border-bottom: 1px solid black; padding-top: 0.125rem; padding-bottom: 0.125rem; font-size: 0.875rem; }
        .input-underline:focus { outline: none; ring: 0; border-bottom-color: #2563eb; }
        .table-header-cell { background-color: #f9fafb; font-weight: bold; text-align: center; padding: 0.5rem; border: 1px solid black; font-size: 0.75rem; text-transform: uppercase; }
        .table-data-cell { border: 1px solid black; vertical-align: top; }
        .table-cell-textarea { width: 100%; height: 100%; padding: 0.5rem; border: none; font-size: 0.875rem; box-sizing: border-box; resize: none; min-height: 60px; }
        .table-cell-textarea:focus { outline: none; }
        .signature-line { border-bottom: 1px solid black; height: 1.5rem; margin-top: 0.25rem; }
        .button-loading::after { content: ""; display: inline-block; width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; margin-left: 8px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-[#F0F0F0] font-sans min-h-screen flex flex-col">

    <div class="bg-[#6FFFA0] w-full px-6 py-2 flex justify-between items-center">
        <div>
            <a href="{{ route('documents.index') }}" class="text-sm text-black hover:underline">← Back to Dashboard</a>
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
                        Edit: <span class="font-normal" x-text="documentTitle ? documentTitle : '{{ addslashes($initialDocumentTitle) }}'">{{-- Alpine will fill this --}}</span>
                    @else

                        Material Pull-Out Form
                    @endif
                </h1>
                <div class="flex space-x-2">
                    {{-- *** CORRECTED BUTTONS - SEPARATED *** --}}
                    <button @click="downloadFormAsExcel" :disabled="isDownloading"
                            class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300"
                            :class="{ 'button-loading opacity-75 cursor-not-allowed': isDownloading }">
                        <span x-text="isDownloading ? 'Downloading...' : 'Download Form'"></span>
                    </button>
                    {{-- This Save button was likely intended for CREATE mode, or an alternative save.
                         The main Save/Update is at the bottom.
                         If this is a "quick save draft" button, its logic needs to be distinct.
                         For now, let's assume the main button at the bottom is primary.
                         You can uncomment and adapt if needed.
                    <!--
                    <button @click="isEdit ? updateDocumentData() : saveDocumentPrompt()"
                            :disabled="isSaving"
                            class="text-sm bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300"
                            :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                        <span x-text="isSaving ? 'Saving...' : (isEdit ? 'Quick Update' : 'Quick Save')"></span>
                    </button>
                    -->
                    --}}
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <form @submit.prevent class="space-y-6">
                        <div class="border border-black p-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div class="space-y-2">
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">CLIENT:</label><input type="text" x-model="client" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">ADDRESS:</label><input type="text" x-model="address" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">ATTENTION:</label><input type="text" x-model="attention" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">DATE:</label><input type="date" x-model="date" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">REF/PO NO:</label><input type="text" x-model="refPoNo" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                    <div class="flex items-center"><label class="w-28 font-semibold uppercase">DOC NAME:</label><input type="text" x-model="documentTitle" placeholder="e.g., MPO for Client X" class="input-underline flex-1" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
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
                                    <div class="col-span-2 table-data-cell"><textarea x-model.number="item.quantity" class="table-cell-textarea text-center" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}" placeholder="0"></textarea></div>
                                    <div class="col-span-1 table-data-cell"><textarea x-model="item.unit" class="table-cell-textarea text-center" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-4 table-data-cell"><textarea x-model="item.brandParticulars" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-2 table-data-cell"><textarea x-model="item.model" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    <div class="col-span-3 table-data-cell"><textarea x-model="item.partSerialNumber" class="table-cell-textarea" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea></div>
                                    {{-- Remove item button active in create and edit mode --}}
                                    <button type="button" @click="removeItem(index)" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}"
                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                        title="Remove item">×</button>
                                </div>
                            </template>
                            <div class="p-2 border-t border-black flex justify-end" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                <button type="button" @click="addItem()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Add Item</button>
                            </div>

                            <div class="grid grid-cols-12 border-t border-black">
                                <div class="col-span-2 table-header-cell !text-left !bg-white !font-semibold !text-gray-700 !border-r-0 !border-b-0">REMARKS:</div>
                                <div class="col-span-10 table-data-cell !border-l-0 !border-b-0">
                                    <textarea x-model="remarks" class="table-cell-textarea min-h-[60px]" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center text-sm italic mt-4 py-2">Released the above materials for pull-out</div>

                        <div class="mt-8 pt-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8 text-sm">
                            <div class="md:col-span-2 space-y-10"><div class="grid grid-cols-2 gap-x-8"><div><p class="uppercase font-semibold text-gray-600">Prepared By</p><div class="signature-line"></div></div><div><p class="uppercase font-semibold text-gray-600">Checked By</p><div class="signature-line"></div></div></div><div class="grid grid-cols-2 gap-x-8"><div><p class="uppercase font-semibold text-gray-600">Acknowledged By</p><div class="signature-line"></div></div><div><p class="uppercase font-semibold text-gray-600">Pulled-Out By</p><div class="signature-line"></div></div></div></div>
                            <div class="space-y-2"><div class="flex items-end"><label class="font-semibold mr-2">By:</label><div class="signature-line flex-1"></div></div><p class="text-xs text-center text-gray-600">Signature over printed name</p><div class="flex items-end mt-3"><label class="font-semibold mr-2">Date:</label><div class="signature-line flex-1"></div></div></div>
                        </div>

                        <div class="pt-12" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
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
    function materialForm(
        isEditMode = false,
        documentId = null,
        initialDocTitle = '',
        initialClient = '',
        initialAddress = '',
        initialAttention = '',
        initialDate = new Date().toISOString().slice(0,10),
        initialRefPoNo = '',
        initialItemsData = [],
        initialRemarks = ''
    ) {
        return {
            isEdit: isEditMode,
            docId: documentId,
            documentTitle: initialDocTitle,
            client: initialClient,
            address: initialAddress,
            attention: initialAttention,
            date: initialDate,
            refPoNo: initialRefPoNo,
            items: (Array.isArray(initialItemsData) && initialItemsData.length > 0) ? JSON.parse(JSON.stringify(initialItemsData)) : [{ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' }],
            remarks: initialRemarks,

            isSaving: false,
            isDownloading: false,

            init() {
                if (this.isEdit) {
                    console.log('Form loaded in EDIT mode for document ID:', this.docId);
                } else {
                    console.log('Form loaded in CREATE mode.');
                }
            },

            addItem() {
                this.items.push({ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' });
            },
            removeItem(index) {
                this.items.splice(index, 1);
            },

            async downloadFormAsExcel() {
                this.isDownloading = true;
                const formDataForExcel = {
                    client: this.client, address: this.address, attention: this.attention,
                    date: this.date, refPoNo: this.refPoNo, items: this.items, remarks: this.remarks,
                };

                try {
                    const response = await fetch('{{ route("form.download.excel") }}', { // Make sure this route name is correct
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(formDataForExcel)
                    });

                    if (response.ok) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none'; a.href = url;
                        const disposition = response.headers.get('content-disposition');
                        let filename = 'Material_Pull_Out_Form.xlsx';
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            const matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                        }
                        a.download = filename; document.body.appendChild(a); a.click();
                        window.URL.revokeObjectURL(url); a.remove();
                    } else {
                        console.error('Excel download failed:', response.status, response.statusText);
                        const errorData = await response.json().catch(() => ({ message: response.statusText }));
                        alert('Error generating Excel file: ' + (errorData.message || response.statusText));
                    }
                } catch (error) {
                    console.error('Error during Excel download:', error);
                    alert('An unexpected error occurred while generating the Excel file.');
                } finally {
                    this.isDownloading = false;
                }
            },

            saveDocumentPrompt() { // Only for CREATE mode
                if (this.isEdit) {
                    console.warn("saveDocumentPrompt called in edit mode. Should use updateDocumentData.");
                    return this.updateDocumentData(); // Or just return if update has its own button
                }
                if (!this.documentTitle.trim()) {
                    const suggestedName = `MPO - ${this.client || 'N/A'} - ${this.date}`;
                    const title = prompt("Please enter a name for this new document:", suggestedName);
                    if (title === null || title.trim() === "") {
                        alert("Save cancelled. Document name is required.");
                        return;
                    }
                    this.documentTitle = title.trim();
                }
                this.saveNewDocumentData();
            },

            async saveNewDocumentData() { // Renamed for clarity: this is for CREATE
                this.isSaving = true;
                const formFieldsPayload = { /* ... collect all form fields for the 'data' JSON ... */
                    client: this.client, address: this.address, attention: this.attention,
                    date: this.date, refPoNo: this.refPoNo, items: this.items, remarks: this.remarks
                };
                const documentRecordPayload = {
                    document_name: this.documentTitle,
                    document_type: 'pull_out_receipt',
                    recipient: this.client || null,
                    status: 'draft',
                    data: formFieldsPayload
                };

                try {
                    const response = await fetch('{{ route("documents.store") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(documentRecordPayload)
                    });
                    const result = await response.json();
                    if (response.ok) {
                        alert(result.message || 'Document saved successfully!');
                        window.location.href = '{{ route("documents.index") }}';
                    } else {
                        // ... error handling ...
                        alert('Error saving: ' + (result.message || 'Unknown error') + (result.errors ? '\n' + Object.values(result.errors).flat().join('\n') : ''));
                    }
                } catch (error) { /* ... */ console.error('Save Error:', error); alert('Save failed due to a network or script error.');
                } finally { this.isSaving = false; }
            },

            async updateDocumentData() { // For EDIT mode
                if (!this.isEdit || !this.docId) return;
                this.isSaving = true;

                if (!this.documentTitle.trim()) {
                    alert("Document name cannot be empty.");
                    this.isSaving = false;
                    return;
                }

                const formFieldsPayload = { /* ... collect all form fields for the 'data' JSON ... */
                    client: this.client, address: this.address, attention: this.attention,
                    date: this.date, refPoNo: this.refPoNo, items: this.items, remarks: this.remarks
                };
                const documentUpdatePayload = {
                    _method: 'PUT',
                    document_name: this.documentTitle,
                    recipient: this.client || null,
                    // status: 'draft', // You might want to allow status changes here too
                    data: formFieldsPayload
                };

                const updateUrl = `/documents/${this.docId}`;
                try {
                    const response = await fetch(updateUrl, {
                        method: 'POST', // Using POST with _method: 'PUT'
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(documentUpdatePayload)
                    });
                    const result = await response.json();
                    if (response.ok) {
                        alert(result.message || 'Document updated successfully!');
                        window.location.href = `/documents/${this.docId}`;
                    } else {
                        // ... error hndling ...
                        alert('Error updating: ' + (result.message || 'Unknown error') + (result.errors ? '\n' + Object.values(result.errors).flat().join('\n') : ''));
                    }
                } catch (error) { /* ... */ console.error('Update Error:', error); alert('Update failed due to a network or script error.');
                } finally { this.isSaving = false; }
            }
        }
    }

    document.addEventListener('alpine:init', () => {
    Alpine.data('materialForm', materialForm);
});

window.Alpine = Alpine;
Alpine.start();
</script>

</body>
</html>
@endsection