@php
    // Determine mode: Is this for creating a new document or editing an existing one?
    $isEditMode = isset($isEditMode) && $isEditMode === true;
    // $isPreviewMode = isset($isPreviewMode) && $isPreviewMode === true; // For future preview

    $initialDocumentId = $isEditMode ? ($documentRecord->id ?? null) : null;
    $initialDocumentTitle = $isEditMode ? ($documentRecord->document_name ?? '') : '';

    $formDataFromController = $isEditMode ? ($documentRecord->data ?? []) : [];

    // Standard Employee Info
    $initialEmployeeName = $formDataFromController['employee_name'] ?? '';
    $initialEmployeeNum = $formDataFromController['employee_num'] ?? '';
    $initialDepartment = $formDataFromController['department'] ?? '';
    $initialPosition = $formDataFromController['position'] ?? '';
    $initialDateFiled = $formDataFromController['date_filed'] ?? now()->format('Y-m-d');
    $initialReferenceNo = $formDataFromController['reference_no'] ?? 'REIM-'; // Base for new ref no

    // Reimbursement Specific Fields
    $initialCvNumber = $formDataFromController['cv_number'] ?? '';
    $initialProjectName = $formDataFromController['project_name'] ?? '';

    // Expense Items
    $defaultItems = [['expense_date' => '', 'receipt_no' => '', 'description' => '', 'amount' => null]];
    $initialItemsJson = json_encode($formDataFromController['items'] ?? ($formDataFromController['expense_items'] ?? $defaultItems));

    // Bottom Signature-like fields
    $initialSignature = $formDataFromController['signature_data'] ?? '';
    $initialReleasedDate = $formDataFromController['released_date'] ?? '';
    $initialReceivedBy = $formDataFromController['received_by'] ?? '';

    $makeFieldsReadOnly = false; // Will be true if $isPreviewMode is active
@endphp

@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="reimbursementForm(
    {{ $isEditMode ? 'true' : 'false' }},
    {{ $initialDocumentId ?? 'null' }},
    '{{ addslashes($initialDocumentTitle) }}',
    '{{ addslashes($initialEmployeeName) }}',
    '{{ addslashes($initialEmployeeNum) }}',
    '{{ addslashes($initialDepartment) }}',
    '{{ addslashes($initialPosition) }}',
    '{{ $initialDateFiled }}',
    '{{ addslashes($initialReferenceNo) }}',
    '{{ addslashes($initialCvNumber) }}',
    '{{ addslashes($initialProjectName) }}',
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
            Edit: {{ $initialDocumentTitle ?: 'Reimbursement Form' }}
        @else
            Create Reimbursement Form
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
        .table-header-cell { background-color: #f3f4f6; /* gray-100 */ font-weight: 600; text-align: center; padding: 0.5rem; border: 1px solid black; font-size: 0.75rem; text-transform: uppercase; }
        .table-data-cell { border: 1px solid black; vertical-align: top; } /* Keep consistent with pullout */
        .table-cell-input { width: 100%; padding: 0.5rem; border: none; font-size: 0.875rem; box-sizing: border-box; background-color: transparent; }
        .table-cell-input:focus { outline: none; }
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
                        Reimbursement Form
                    @endif
                </h1>
                <div class="flex space-x-2">
                    <button @click="downloadReimbursementExcel" :disabled="isDownloadingExcel"
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
                            <input id="documentTitle" type="text" x-model="documentTitle" placeholder="e.g., Reimbursement for Office Supplies" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                        </div>

                        <!-- Top Section: Employee Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div><label class="block text-xs font-bold uppercase text-gray-700">EMPLOYEE NAME:</label><input type="text" x-model="employeeName" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700">EMPLOYEE NUMBER:</label><input type="text" x-model="employeeNum" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700">DEPARTMENT:</label><input type="text" x-model="department" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700">POSITION :</label><input type="text" x-model="position" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700">DATE FILED:</label><input type="date" x-model="dateFiled" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            <div><label class="block text-xs font-bold uppercase text-gray-700">REFERENCE NO.:</label><input type="text" x-model="referenceNo" class="w-full input-underline bg-gray-100" readonly></div>
                        </div>

                        <!-- CV Number and Project Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 pt-4 border-t border-b border-gray-300 pb-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700">CV NUMBER:</label>
                                <input type="text" x-model="cvNumber" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700">PROJECT:</label>
                                <input type="text" x-model="projectName" class="w-full input-underline" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            </div>
                        </div>

                        <!-- Middle Section: Expense Items Table -->
                        <div class="mt-6">
                            <div class="border-2 border-black">
                                <!-- Headers -->
                                <div class="grid grid-cols-12">
                                    <div class="col-span-2 table-header-cell">DATE</div>
                                    <div class="col-span-3 table-header-cell">RECEIPT NO.</div>
                                    <div class="col-span-5 table-header-cell">DESCRIPTION</div>
                                    <div class="col-span-2 table-header-cell">AMOUNT</div>
                                </div>
                                <!-- Dynamic Rows -->
                                <div id="expense-items-container">
                                    <template x-for="(item, index) in items" :key="index">
                                        <div class="grid grid-cols-12 items-stretch"> {{-- items-stretch for equal height --}}
                                            <div class="col-span-2 table-data-cell"><input type="date" x-model="item.expense_date" class="table-cell-input" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                            <div class="col-span-3 table-data-cell"><input type="text" x-model="item.receipt_no" class="table-cell-input" placeholder="Receipt #" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                            <div class="col-span-5 table-data-cell"><input type="text" x-model="item.description" class="table-cell-input" placeholder="Expense description" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                            <div class="col-span-2 table-data-cell relative">
                                                <input type="number" step="0.01" x-model.number="item.amount" class="table-cell-input text-right pr-2" placeholder="0.00" @input="calculateTotal" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                                <template x-if="items.length > 1 && !{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                                    <button type="button" @click="removeItem(index)"
                                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-xs rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none"
                                                        title="Remove Expense">×</button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <!-- Total Expenses Row -->
                                <div class="grid grid-cols-12 border-t-2 border-black">
                                    <div class="col-span-7 p-2 text-right font-bold text-sm uppercase"></div>
                                    <div class="col-span-3 p-2 text-right font-bold text-sm uppercase border-l-2 border-black">TOTAL EXPENSES</div>
                                    <div class="col-span-2 p-2 text-right font-semibold border-l-2 border-black text-sm" x-text="formattedTotalAmount">0.00</div>
                                </div>
                            </div>
                            <!-- Add More Button -->
                            <div class="flex justify-end mt-3" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                                <button type="button"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-3 py-1.5 rounded font-semibold transition duration-300 text-xs uppercase"
                                    @click="addItem">
                                    ➕ Add Expense
                                </button>
                            </div>
                        </div>

                        <!-- Bottom Section: Signatures -->
                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                            <div class="space-y-8">
                                <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Signature (Employee):</label><input type="text" x-model="signatureData" placeholder="Employee signs here / Printed Name" class="w-full input-underline mt-4" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Released Date:</label><input type="date" x-model="releasedDate" class="w-full input-underline mt-4" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                                <div><label class="block text-xs font-bold uppercase text-gray-700 mb-1">Received By:</label><input type="text" x-model="receivedBy" placeholder="Name" class="w-full input-underline mt-4" :readonly="{{ $makeFieldsReadOnly ? 'true' : 'false' }}"></div>
                            </div>
                            <div class="space-y-6">
                                <div class="pt-1"><label class="block text-xs font-bold uppercase text-gray-700">Noted By:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1">Department Head</p></div>
                                <div class="pt-1"><label class="block text-xs font-bold uppercase text-gray-700">Released By:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1">Accounting</p></div>
                                <div class="pt-1"><label class="block text-xs font-bold uppercase text-gray-700">Approved By:</label><div class="mt-4 border-b-2 border-black h-6"></div><p class="text-xs text-gray-800 font-semibold mt-1">President</p></div>
                            </div>
                        </div>

                        <!-- Main Submit/Update Button -->
                        <div class="pt-8" x-show="!{{ $makeFieldsReadOnly ? 'true' : 'false' }}">
                            <button type="button" @click="isEdit ? updateDocument() : submitFormPrompt()"
                                    :disabled="isSaving"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider"
                                    :class="{ 'button-loading opacity-75 cursor-not-allowed': isSaving }">
                                <span x-text="isSaving ? (isEdit ? 'UPDATING REIMBURSEMENT...' : 'SUBMITTING REIMBURSEMENT...') : (isEdit ? 'UPDATE REIMBURSEMENT' : 'SUBMIT REIMBURSEMENT')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="w-64 bg-[#1A3D8A] text-white p-6 flex-shrink-0 hidden md:block">
            <h2 class="text-lg font-bold mb-4">Other Form Types</h2>
             <div class="space-y-4">
                <a href="{{ route('forms.cash-advance') }}" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Cash Advance</a>
                <a href="{{ route('forms.pullout') }}" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Material Pull-Out</a>
            </div>
        </div>
    </div>

<script>
function reimbursementForm(
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialEmployeeName = '',
    initialEmployeeNum = '',
    initialDepartment = '',
    initialPosition = '',
    initialDateFiled = new Date().toISOString().slice(0,10),
    initialReferenceNo = 'REIM-',
    initialCvNumber = '',
    initialProjectName = '',
    initialItemsData = [{ expense_date: '', receipt_no: '', description: '', amount: null }],
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
        cvNumber: initialCvNumber,
        projectName: initialProjectName,
        items: (Array.isArray(initialItemsData) && initialItemsData.length > 0) ? JSON.parse(JSON.stringify(initialItemsData)) : [{ expense_date: '', receipt_no: '', description: '', amount: null }],
        signatureData: initialSignature,
        releasedDate: initialReleasedDate,
        receivedBy: initialReceivedBy,

        totalAmount: 0, // For reactive total
        isSaving: false,
        isDownloadingExcel: false,

        init() {
            this.calculateTotal(); // Calculate total on init
            if (this.isEdit) {
                console.log('Reimbursement Form loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Reimbursement Form loaded in CREATE mode.');
                if (!this.documentTitle.trim() && (this.employeeName.trim() || this.dateFiled.trim())) {
                    this.documentTitle = `Reimbursement - ${this.employeeName.trim() || 'Employee'} - ${this.dateFiled}`;
                }
                if (this.referenceNo === 'REIM-') {
                    this.referenceNo = 'REIM-' + Date.now().toString().slice(-6);
                }
            }
        },

        addItem() {
            this.items.push({ expense_date: '', receipt_no: '', description: '', amount: null });
        },
        removeItem(index) {
            this.items.splice(index, 1);
            this.calculateTotal();
        },
        calculateTotal() {
            this.totalAmount = this.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        },
        get formattedTotalAmount() {
            return this.totalAmount.toFixed(2);
        },

        submitFormPrompt() {
            if (this.isEdit) return this.updateDocument();
            if (!this.documentTitle.trim()) {
                const suggestedName = `Reimbursement - ${this.employeeName.trim() || 'N/A'} - ${this.dateFiled}`;
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
        cv_number: this.cvNumber,         // <<< Check this
        project_name: this.projectName,   // <<< Check this
        items: this.items.filter(item => item.description || item.amount || item.receipt_no || item.expense_date),
        total_expenses: this.totalAmount,
        signature_data: this.signatureData,
        released_date: this.releasedDate,
        received_by: this.receivedBy,
    };

    const documentRecordPayload = {
        document_name: this.documentTitle,
        document_type: 'reimbursement',
        recipient: this.employeeName || null,
        status: 'draft', // Consider if status can be changed from edit form
        data: formFieldsPayload
    };

    let url = '{{ route("documents.store") }}';
    if (isUpdate && this.docId) {
        url = `/documents/${this.docId}`;
        documentRecordPayload._method = 'PUT';
    } else if (isUpdate && !this.docId) {
        // ... (error handling) ...
        return;
    }

    // >>> ADD THESE CONSOLE LOGS <<<
    if (isUpdate) {
        console.log('Attempting to UPDATE document with ID:', this.docId);
    } else {
        console.log('Attempting to CREATE new document.');
    }
    console.log('Form Fields Payload (for data column):', JSON.parse(JSON.stringify(formFieldsPayload)));
    console.log('Document Record Payload (to server):', JSON.parse(JSON.stringify(documentRecordPayload)));

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify(documentRecordPayload)
                });
                const result = await response.json();
                if (response.ok) {
                    alert(result.message || `Document ${isUpdate ? 'updated' : 'saved'} successfully!`);
                    const redirectUrl = (isUpdate && this.docId) ? `/documents/${this.docId}` : ((result.document && result.document.id) ? `/documents/${result.document.id}` : '{{ route("documents.index") }}');
                    window.location.href = redirectUrl;
                } else {
                    let errorMsg = `Error ${isUpdate ? 'updating' : 'saving'} document.`; /* ... (error handling) ... */
                    if (result.message) errorMsg = result.message;
                    if (result.errors) errorMsg += "\nDetails:\n" + Object.values(result.errors).flat().join("\n");
                    else if(response.statusText) errorMsg += ` (${response.statusText})`;
                    alert(errorMsg);
                }
            } catch (error) { console.error(`Error during ${isUpdate ? 'update' : 'save'}:`, error); alert(`Operation failed. Check console.`);
            } finally { this.isSaving = false; }
        },

        saveNewDocument() { this.saveOrUpdate(false); },
        updateDocument() { this.saveOrUpdate(true); },

        async downloadReimbursementExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                cv_number: this.cvNumber, project_name: this.projectName,
                items: this.items, total_amount: this.totalAmount, // Send calculated total
                signature_data: this.signatureData, released_date: this.releasedDate,
                received_by: this.receivedBy,
                document_title_for_excel: this.documentTitle
            };

            try {
                // YOU WILL NEED TO CREATE THIS ROUTE AND CONTROLLER METHOD for reimbursement Excel
                const response = await fetch('{{ route("forms.reimbursement.download.excel") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify(formDataForExcel)
                });
                if (response.ok) { /* ... (blob download logic) ... */
                    const blob = await response.blob(); const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a'); a.style.display = 'none'; a.href = url;
                    const disposition = response.headers.get('content-disposition');
                    let filename = 'Reimbursement_Form.xlsx';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    a.download = filename; document.body.appendChild(a); a.click();
                    window.URL.revokeObjectURL(url); a.remove();
                } else {
                    const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
                    alert('Error generating Reimbursement Excel: ' + (errorData.message || 'Unknown server error'));
                }
            } catch (error) { console.error('Error during Reimbursement Excel download:', error); alert('Network error during Excel download.');
            } finally { this.isDownloadingExcel = false; }
        }
    }
}
</script>

</body>
</html>
@endsection