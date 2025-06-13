// resources/js/forms/reimbursement-form-alpine.js

export function reimbursementForm(
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
    initialSignature = '', // For signatureData
    initialReleasedDate = '',
    initialReceivedBy = '',
    // URLs passed from Blade
    formDownloadExcelUrl = '/default-reimbursement-download-url',
    documentStoreUrl = '/default-documents-store-url',
    documentsIndexUrl = '/documents'
) {
    return {
        // Config URLs
        downloadExcelUrl: formDownloadExcelUrl,
        storeUrl: documentStoreUrl,
        dashboardUrl: documentsIndexUrl,

        // Form State
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

        totalAmount: 0,
        isSaving: false,
        isDownloadingExcel: false,

        init() {
            this.calculateTotal();
            if (this.isEdit) {
                console.log('Reimbursement Form (External JS) loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Reimbursement Form (External JS) loaded in CREATE mode.');
                if (!this.documentTitle.trim() && (this.employeeName.trim() || this.dateFiled.trim())) {
                    this.documentTitle = `Reimbursement - ${this.employeeName.trim() || 'Employee'} - ${this.dateFiled}`;
                }
                if (this.referenceNo === 'REIM-') { // Ensure it only appends if it's the base
                    this.referenceNo = 'REIM-' + Date.now().toString().slice(-6);
                }
            }
        },

        addItem() {
            this.items.push({ expense_date: '', receipt_no: '', description: '', amount: null });
        },
        removeItem(index) {
            if (this.items.length > 0) { // Allow removing to an empty list if desired
                this.items.splice(index, 1);
            }
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
            const formFieldsPayload = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                cv_number: this.cvNumber, project_name: this.projectName,
                items: this.items.filter(item => item.expense_date || item.receipt_no || item.description || item.amount),
                total_expenses: this.totalAmount, // Storing calculated total
                signature_data: this.signatureData,
                released_date: this.releasedDate,
                received_by: this.receivedBy,
            };

            const documentRecordPayload = {
                document_name: this.documentTitle,
                document_type: 'reimbursement', // Specific type for this form
                recipient: this.employeeName || null,
                status: 'draft',
                data: formFieldsPayload
            };

            let url = this.storeUrl; // Use stored URL for new documents
            let fetchMethod = 'POST';

            if (isUpdate && this.docId) {
                url = `/documents/${this.docId}`; // Construct update URL
                documentRecordPayload._method = 'PUT';
            } else if (isUpdate && !this.docId) {
                alert("Error: Cannot update. Document ID is missing.");
                this.isSaving = false; return;
            }

            try {
                const response = await fetch(url, {
                    method: 'POST', // Always POST, _method handles PUT/PATCH
                    headers: {
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(documentRecordPayload)
                });
                const result = await response.json();
                if (response.ok) {
                    alert(result.message || `Document ${isUpdate ? 'updated' : 'saved'} successfully!`);
                    const redirectUrl = (isUpdate && this.docId) ? `/documents/${this.docId}` :
                                     ((result.document && result.document.id) ? `/documents/${result.document.id}` : this.dashboardUrl);
                    window.location.href = redirectUrl;
                } else {
                    let errorMsg = result.message || `Error ${isUpdate ? 'updating' : 'saving'} document.`;
                    if (result.errors) errorMsg += "\nDetails:\n" + Object.values(result.errors).flat().join("\n");
                    else if (response.statusText) errorMsg += ` (${response.statusText})`;
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

        async downloadReimbursementExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                cv_number: this.cvNumber, project_name: this.projectName,
                items: this.items, total_amount: this.totalAmount,
                signature_data: this.signatureData, released_date: this.releasedDate,
                received_by: this.receivedBy,
                document_title_for_excel: this.documentTitle
            };

            try {
                const response = await fetch(this.downloadExcelUrl, { // Use the stored URL
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formDataForExcel)
                });
                if (response.ok) {
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
            } catch (error) {
                console.error('Error during Reimbursement Excel download:', error);
                alert('Network error or script issue during Excel download.');
            } finally {
                this.isDownloadingExcel = false;
            }
        }
    };
}