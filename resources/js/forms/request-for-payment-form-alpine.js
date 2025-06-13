// resources/js/forms/request-for-payment-form-alpine.js

export function requestPaymentForm(
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialEmployeeName = '', initialEmployeeNum = '', initialDepartment = '', initialPosition = '',
    initialDateFiled = new Date().toISOString().slice(0,10), initialReferenceNo = 'RFPF-',
    initialPayeeName = '', initialPayeeAddress = '', initialPayeeContact = '',
    initialBankName = '', initialAccountNumber = '', initialSwiftBic = '', initialIban = '',
    initialPaymentDescription = '', initialPaymentAmount = null, initialPaymentCurrency = 'PHP',
    initialPaymentMethods = [], initialPaymentInvoiceRef = '',
    initialSignatureData = '',
    // URLs passed from Blade
    formDownloadExcelUrl = '/default-rfp-download-url',
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
        employeeName: initialEmployeeName, employeeNum: initialEmployeeNum, department: initialDepartment, position: initialPosition,
        dateFiled: initialDateFiled, referenceNo: initialReferenceNo,
        payeeName: initialPayeeName, payeeAddress: initialPayeeAddress, payeeContact: initialPayeeContact,
        bankName: initialBankName, accountNumber: initialAccountNumber, swiftBic: initialSwiftBic, iban: initialIban,
        paymentDescription: initialPaymentDescription, paymentAmount: initialPaymentAmount, paymentCurrency: initialPaymentCurrency,
        paymentMethods: Array.isArray(initialPaymentMethods) ? [...initialPaymentMethods] : [], // Ensure it's an array
        paymentInvoiceRef: initialPaymentInvoiceRef,
        signatureData: initialSignatureData,

        isSaving: false,
        isDownloadingExcel: false,

        init() {
            if (this.isEdit) {
                console.log('Request for Payment Form (External JS) loaded in EDIT mode for ID:', this.docId);
            } else {
                console.log('Request for Payment Form (External JS) loaded in CREATE mode.');
                if (!this.documentTitle.trim() && (this.employeeName.trim() || this.dateFiled.trim())) {
                    this.documentTitle = `RFP - ${this.employeeName.trim() || 'Request'} - ${this.dateFiled}`;
                }
                if (this.referenceNo === 'RFPF-') {
                    this.referenceNo = 'RFPF-' + Date.now().toString().slice(-6);
                }
            }
        },

        submitFormPrompt() {
            if (this.isEdit) return this.updateDocument();
            if (!this.documentTitle.trim()) {
                const suggestedName = `RFP - ${this.employeeName.trim() || 'N/A'} - ${this.dateFiled}`;
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
                employee_name: this.employeeName, employee_num: this.employeeNum, department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                payee_name: this.payeeName, payee_address: this.payeeAddress, payee_contact: this.payeeContact,
                bank_name: this.bankName, account_number: this.accountNumber, swift_bic: this.swiftBic, iban: this.iban,
                payment_description: this.paymentDescription, payment_amount: this.paymentAmount, payment_currency: this.paymentCurrency,
                payment_methods: this.paymentMethods,
                payment_invoice_ref: this.paymentInvoiceRef,
                signature_data: this.signatureData,
            };

            const documentRecordPayload = {
                document_name: this.documentTitle,
                document_type: 'request_for_payment',
                recipient: this.payeeName || this.employeeName || null,
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
                    alert(result.message || `Request for Payment ${isUpdate ? 'updated' : 'saved'} successfully!`);
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

        async downloadRequestForPaymentExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = {
                document_title_for_excel: this.documentTitle,
                employee_name: this.employeeName, employee_num: this.employeeNum, department: this.department, position: this.position,
                date_filed: this.dateFiled, reference_no: this.referenceNo,
                payee_name: this.payeeName, payee_address: this.payeeAddress, payee_contact: this.payeeContact,
                bank_name: this.bankName, account_number: this.accountNumber, swift_bic: this.swiftBic, iban: this.iban,
                payment_description: this.paymentDescription, payment_amount: this.paymentAmount, payment_currency: this.paymentCurrency,
                payment_methods: this.paymentMethods, payment_invoice_ref: this.paymentInvoiceRef,
                signature_data: this.signatureData,
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
                    let filename = 'Request_For_Payment.xlsx';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    a.download = filename; document.body.appendChild(a); a.click();
                    window.URL.revokeObjectURL(url); a.remove();
                } else {
                    const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
                    alert('Error generating Request for Payment Excel: ' + (errorData.message || 'Unknown server error'));
                }
            } catch (error) {
                console.error('Error during Request for Payment Excel download:', error);
                alert('Network error or script issue during Excel download.');
            } finally {
                this.isDownloadingExcel = false;
            }
        }
    };
}