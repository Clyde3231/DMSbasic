// resources/js/forms/acknowledgement-receipt-form-alpine.js

export function acknowledgementReceiptForm(
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialDeliveredTo = '',
    initialAddress = '',
    initialAttention = '',
    initialDate = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }), // Default formatted date
    initialRefPoNo = '',
    initialItemsData = [],
    initialRemarks = '',
    // URLs passed from Blade
    formDownloadExcelUrl = '/default-ack-receipt-download-url', // Placeholder
    documentStoreUrl = '/default-documents-store-url',         // Placeholder
    documentsIndexUrl = '/documents'                             // Placeholder for dashboard redirect
) {
    return {
        // Config URLs
        downloadExcelUrl: formDownloadExcelUrl,
        storeUrl: documentStoreUrl,
        dashboardUrl: documentsIndexUrl,

        // Form State
        isEdit: isEditMode,
        docId: documentId,
        documentTitle: initialDocTitle, // For saving the document record
        deliveredTo: initialDeliveredTo,
        address: initialAddress,
        attention: initialAttention,
        date: initialDate, // Pre-filled, potentially readonly or settable
        refPoNo: initialRefPoNo,
        items: (Array.isArray(initialItemsData) && initialItemsData.length > 0)
               ? JSON.parse(JSON.stringify(initialItemsData))
               : [{ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' },
                  { quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' }],
        remarks: initialRemarks,

        isSaving: false,
        isDownloadingExcel: false,

        init() {
            if (this.isEdit) {
                console.log('Acknowledgement Receipt Form (External JS) loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Acknowledgement Receipt Form (External JS) loaded in CREATE mode.');
                // Auto-suggest title for new forms
                if (!this.documentTitle.trim() && (this.deliveredTo.trim() || this.date.trim())) {
                    this.documentTitle = `Ack Receipt - ${this.deliveredTo.trim() || 'Recipient'} - ${new Date().toISOString().slice(0,10)}`;
                }
                 // If the date from Blade is a string, ensure it's just used.
                // If you want it to be today's date dynamically unless editing:
                if (!this.isEdit && this.date === new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })) {
                    // This is already the default from the parameters, so no change needed here for create mode.
                    // If you wanted a different default for create vs edit, you'd adjust here.
                }
            }
        },

        addItem() {
            this.items.push({ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                // Clear the first row if only one exists
                this.items[0] = { quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' };
            }
        },

        submitFormPrompt() {
            if (this.isEdit) return this.updateDocument();

            if (!this.documentTitle.trim()) {
                const suggestedName = `Ack Receipt - ${this.deliveredTo.trim() || 'N/A'} - ${new Date().toISOString().slice(0,10)}`;
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
                deliveredTo: this.deliveredTo,
                address: this.address,
                attention: this.attention,
                date: this.date, // The date from the form
                refPoNo: this.refPoNo,
                items: this.items.filter(item => item.quantity || item.unit || item.brandParticulars || item.model || item.partSerialNumber),
                remarks: this.remarks,
            };

            const documentRecordPayload = {
                document_name: this.documentTitle,
                document_type: 'acknowledgement_receipt', // Or 'tools_equipment_receipt'
                recipient: this.deliveredTo || null, // Primary recipient
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
                    alert(result.message || `Acknowledgement Receipt ${isUpdate ? 'updated' : 'saved'} successfully!`);
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

        async downloadAcknowledgementReceiptExcel() {
            this.isDownloadingExcel = true;
            const formDataForExcel = { // Collect all data from Alpine component
                document_title_for_excel: this.documentTitle,
                deliveredTo: this.deliveredTo,
                address: this.address,
                attention: this.attention,
                date: this.date,
                refPoNo: this.refPoNo,
                items: this.items,
                remarks: this.remarks,
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
                    let filename = 'Acknowledgement_Receipt.xlsx';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    a.download = filename; document.body.appendChild(a); a.click();
                    window.URL.revokeObjectURL(url); a.remove();
                } else {
                    const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
                    alert('Error generating Acknowledgement Receipt Excel: ' + (errorData.message || 'Unknown server error'));
                }
            } catch (error) {
                console.error('Error during Acknowledgement Receipt Excel download:', error);
                alert('Network error or script issue during Excel download.');
            } finally {
                this.isDownloadingExcel = false;
            }
        }
    };
}