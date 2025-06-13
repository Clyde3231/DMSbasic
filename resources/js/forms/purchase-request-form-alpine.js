// resources/js/forms/purchase-request-form-alpine.js

export function purchaseRequestForm(
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialEmployeeName = '',
    initialEmployeeNum = '',
    initialDepartmentTop = '',
    initialPosition = '',
    initialDateFiled = new Date().toISOString().slice(0,10),
    initialReferenceNo = 'PRN-',
    initialItemsData = [{ qty: null, description: '', unitPrice: null }],
    initialPurpose = '',
    initialRequestedBySignature = '',
    initialDepartmentBottom = '',
    initialDateBottom = new Date().toISOString().slice(0,10),
    // URLs passed from Blade
    formDownloadExcelUrl = '/default-pr-download-url',
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
        departmentTop: initialDepartmentTop,
        position: initialPosition,
        dateFiled: initialDateFiled,
        referenceNo: initialReferenceNo,
        items: (Array.isArray(initialItemsData) && initialItemsData.length > 0) ? JSON.parse(JSON.stringify(initialItemsData)) : [{ qty: null, description: '', unitPrice: null }],
        purpose: initialPurpose,
        requestedBySignature: initialRequestedBySignature,
        departmentBottom: initialDepartmentBottom,
        dateBottom: initialDateBottom,

        isSaving: false,
        isDownloadingExcel: false,

        get overallTotalAmount() {
            return this.items.reduce((sum, item) => {
                const quantity = parseFloat(item.qty) || 0;
                const price = parseFloat(item.unitPrice) || 0;
                return sum + (quantity * price);
            }, 0).toFixed(2);
        },

        addItem() {
            this.items.push({ qty: null, description: '', unitPrice: null });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else { // Clear if only one row left
                this.items[0] = { qty: null, description: '', unitPrice: null };
            }
        },
        calculateRowTotal(item) {
            const quantity = parseFloat(item.qty) || 0;
            const price = parseFloat(item.unitPrice) || 0;
            return (quantity * price).toFixed(2);
        },

        init() {
            if (this.isEdit) {
                console.log('Purchase Request Form (External JS) loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Purchase Request Form (External JS) loaded in CREATE mode.');
                if (!this.documentTitle.trim() && (this.employeeName.trim() || this.dateFiled.trim())) {
                    this.documentTitle = `PR - ${this.employeeName.trim() || 'Request'} - ${this.dateFiled}`;
                }
                if (this.referenceNo === 'PRN-') { // Ensure it only appends if it's the base
                    this.referenceNo = 'PRN-' + Date.now().toString().slice(-6);
                }
            }
        },

        submitFormPrompt() {
            if (this.isEdit) return this.updateDocument();
            if (!this.documentTitle.trim()) {
                const suggestedName = `PR - ${this.employeeName.trim() || 'N/A'} - ${this.dateFiled}`;
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
                department_top: this.departmentTop, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                items: this.items.filter(item => item.qty || item.description || item.unitPrice),
                total_amount: this.overallTotalAmount,
                purpose: this.purpose,
                requested_by_signature: this.requestedBySignature,
                department_bottom: this.departmentBottom,
                date_bottom: this.dateBottom,
            };

            const documentRecordPayload = {
                document_name: this.documentTitle,
                document_type: 'purchase_request',
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
                    alert(result.message || `Purchase Request ${isUpdate ? 'updated' : 'saved'} successfully!`);
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

        async downloadPurchaseRequestExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = {
                employee_name: this.employeeName, employee_num: this.employeeNum,
                department_top: this.departmentTop, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                items: this.items, overall_total_amount: this.overallTotalAmount,
                purpose: this.purpose, requested_by_signature: this.requestedBySignature,
                department_bottom: this.departmentBottom, date_bottom: this.dateBottom,
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
                    let filename = 'Purchase_Request.xlsx';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    a.download = filename; document.body.appendChild(a); a.click();
                    window.URL.revokeObjectURL(url); a.remove();
                } else {
                    const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
                    alert('Error generating Purchase Request Excel: ' + (errorData.message || 'Unknown server error'));
                }
            } catch (error) {
                console.error('Error during Purchase Request Excel download:', error);
                alert('Network error or script issue during Excel download.');
            } finally {
                this.isDownloadingExcel = false;
            }
        }
    };
}