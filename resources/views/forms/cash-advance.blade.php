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
    '{{ addslashes($initialReceivedBy) }}'
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

  

<script>
function cashAdvanceForm(
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialEmployeeName = '',
    initialEmployeeNum = '',
    initialDepartment = '',
    initialPosition = '',
    initialDateFiled = new Date().toISOString().slice(0,10),
    initialReferenceNo = 'CA-', // Base for new ref no
    initialItemsData = [{ amount: '', details: '' }],
    initialSignature = '',
    initialReleasedDate = '',
    initialReceivedBy = ''
) {
    return {
        isEdit: isEditMode,
        docId: documentId,
        documentTitle: initialDocTitle,
        employeeName: initialEmployeeName,
        employeeNum: initialEmployeeNum,
        department: initialDepartment,
        position: initialPosition,
        dateFiled: initialDateFiled,
        referenceNo: initialReferenceNo,
        items: (Array.isArray(initialItemsData) && initialItemsData.length > 0) ? JSON.parse(JSON.stringify(initialItemsData)) : [{ amount: '', details: '' }],
        signatureData: initialSignature,
        releasedDate: initialReleasedDate,
        receivedBy: initialReceivedBy,

        isSaving: false,
        isDownloadingExcel: false,

        init() {
            if (this.isEdit) {
                console.log('Cash Advance Form loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Cash Advance Form loaded in CREATE mode.');
                // Auto-suggest title for new CA forms
                if (!this.documentTitle.trim() && (this.employeeName.trim() || this.dateFiled.trim())) {
                    this.documentTitle = `CA - ${this.employeeName.trim() || 'Employee'} - ${this.dateFiled}`;
                }
                // Generate a more unique reference number for new forms client-side (backend should verify/finalize)
                if (this.referenceNo === 'CA-') {
                    this.referenceNo = 'CA-' + Date.now().toString().slice(-6);
                }
            }
        },

        submitFormPrompt() {
            if (this.isEdit) return this.updateDocument(); // Should be called by 'UPDATE' button directly

            if (!this.documentTitle.trim()) {
                const suggestedName = `CA - ${this.employeeName.trim() || 'N/A'} - ${this.dateFiled}`;
                const title = prompt("Please enter a name for this document (for saving):", suggestedName);
                if (title === null || title.trim() === "") {
                    alert("Save cancelled. Document name is required."); return;
                }
                this.documentTitle = title.trim();
            }
            this.saveNewDocument();
        },

        async saveOrUpdate(isUpdate = false) {
            this.isSaving = true;
            const formFieldsPayload = { // Data for the JSON 'data' column
                employee_name: this.employeeName,
                employee_num: this.employeeNum,
                department: this.department,
                position: this.position,
                date_filed: this.dateFiled,
                reference_no: this.referenceNo,
                items: this.items.filter(item => item.amount || item.details), // Filter out empty item rows
                signature_data: this.signatureData,
                released_date: this.releasedDate,
                received_by: this.receivedBy,
            };

            const documentRecordPayload = {
                document_name: this.documentTitle,
                document_type: 'cash_advance',
                recipient: this.employeeName || null,
                status: 'draft',
                data: formFieldsPayload
            };

            let url = '{{ route("documents.store") }}';
            let fetchMethod = 'POST';

            if (isUpdate && this.docId) {
                url = `/documents/${this.docId}`;
                documentRecordPayload._method = 'PUT'; // Laravel handles this with POST
            } else if (isUpdate && !this.docId) {
                alert("Error: Cannot update document without a Document ID.");
                this.isSaving = false;
                return;
            }

            try {
                const response = await fetch(url, {
                    method: 'POST', // Always POST, _method handles PUT for updates
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(documentRecordPayload)
                });
                const result = await response.json();

                if (response.ok) {
                    alert(result.message || `Document ${isUpdate ? 'updated' : 'saved'} successfully!`);
                    if (isUpdate && this.docId) {
                        window.location.href = `/documents/${this.docId}`; // Redirect to show page
                    } else if (result.document && result.document.id) { // For new documents, redirect to its show page
                        window.location.href = `/documents/${result.document.id}`;
                    } else {
                        window.location.href = '{{ route("documents.index") }}';
                    }
                } else {
                    let errorMsg = `Error ${isUpdate ? 'updating' : 'saving'} document.`;
                    if (result.message) errorMsg = result.message;
                    if (result.errors) {
                        errorMsg += "\nDetails:\n" + Object.values(result.errors).flat().join("\n");
                    } else if (response.statusText) {
                        errorMsg += ` (${response.statusText})`;
                    }
                    alert(errorMsg);
                }
            } catch (error) {
                console.error(`Error during ${isUpdate ? 'update' : 'save'}:`, error);
                alert(`Operation failed due to a network or script error. Check console.`);
            } finally {
                this.isSaving = false;
            }
        },

        saveNewDocument() { this.saveOrUpdate(false); },
        updateDocument() { this.saveOrUpdate(true); },

        async downloadCashAdvanceExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                items: this.items, signature_data: this.signatureData,
                released_date: this.releasedDate, received_by: this.receivedBy,
                // Pass documentTitle as well if your Excel template needs it directly
                document_title_for_excel: this.documentTitle
            };

            try {
                // YOU WILL NEED TO CREATE THIS ROUTE AND CONTROLLER METHOD
                const response = await fetch('{{ route("forms.cash-advance.download.excel") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formDataForExcel)
                });

                if (response.ok) { /* ... (blob download logic from pullout form) ... */
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none'; a.href = url;
                    const disposition = response.headers.get('content-disposition');
                    let filename = 'Cash_Advance_Form.xlsx';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    a.download = filename; document.body.appendChild(a); a.click();
                    window.URL.revokeObjectURL(url); a.remove();
                } else {
                    const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
                    alert('Error generating Cash Advance Excel: ' + (errorData.message || 'Unknown server error'));
                }
            } catch (error) {
                console.error('Error during Cash Advance Excel download:', error);
                alert('Network error or script issue during Excel download.');
            } finally {
                this.isDownloadingExcel = false;
            }
        }
    }
}
</script>

</body>
</html>
@endsection