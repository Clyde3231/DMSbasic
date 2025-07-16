// resources/js/forms/cash-advance-form-alpine.js

export function cashAdvanceForm(
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialEmployeeName = '',
    initialEmployeeNum = '',
    initialDepartment = '',
    initialPosition = '',
    initialDateFiled = new Date().toISOString().slice(0,10),
    initialReferenceNo = 'CA-',
    initialItemsData = [{ amount: '', details: '' }],
    initialSignature = '',
    initialReleasedDate = '',
    initialReceivedBy = '',
    // URLs passed from Blade
    formDownloadExcelUrl = '/default-cash-advance-download-url',
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
        items: (Array.isArray(initialItemsData) && initialItemsData.length > 0) ? JSON.parse(JSON.stringify(initialItemsData)) : [{ amount: '', details: '' }],
        signatureData: initialSignature,
        releasedDate: initialReleasedDate,
        receivedBy: initialReceivedBy,

        isSaving: false,
        isDownloadingExcel: false,

        init() {
            if (this.isEdit) {
                console.log('Cash Advance Form (External JS) loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Cash Advance Form (External JS) loaded in CREATE mode.');
                if (!this.documentTitle.trim() && (this.employeeName.trim() || this.dateFiled.trim())) {
                    this.documentTitle = `CA - ${this.employeeName.trim() || 'Employee'} - ${this.dateFiled}`;
                }
                if (this.referenceNo === 'CA-') {
                    this.referenceNo = 'CA-' + Date.now().toString().slice(-6);
                }
            }
        },
        // Add item to the items array
        addItem() {
            this.items.push({ amount: '', details: '' });
        },
        // Remove item from the items array
        removeItem(index) {
            if (this.items.length > 1) { // Optionally keep at least one item row
                this.items.splice(index, 1);
            } else { // Or clear the first item if only one exists
                this.items[0] = { amount: '', details: '' };
            }
        },


        submitFormPrompt() {
            if (this.isEdit) return this.updateDocument();
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
            const formFieldsPayload = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                items: this.items.filter(item => item.amount || item.details),
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

            let url = this.storeUrl;
            if (isUpdate && this.docId) {
                url = `/documents/${this.docId}`;
                documentRecordPayload._method = 'PUT';
            } else if (isUpdate && !this.docId) {
                alert("Error: Cannot update. Document ID is missing.");
                this.isSaving = false; return;
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
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

        async downloadCashAdvanceExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                items: this.items, signature_data: this.signatureData,
                released_date: this.releasedDate, received_by: this.receivedBy,
                document_title_for_excel: this.documentTitle
            };

            try {
                const response = await fetch(this.downloadExcelUrl, { // Use stored URL
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
    };
}