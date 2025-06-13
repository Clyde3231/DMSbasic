// resources/js/forms/pullout-form-alpine.js

export function materialForm( // Or pulloutMaterialForm if you aliased it in app.js
    isEditMode = false,
    documentId = null,
    initialDocTitle = '',
    initialClient = '',
    initialAddress = '',
    initialAttention = '',
    initialDate = new Date().toISOString().slice(0,10),
    initialRefPoNo = '',
    initialItemsData = [],
    initialRemarks = '',
    // Corrected: Added commas for these parameters
    formDownloadExcelUrl = '/default-pullout-download-url',
    documentStoreUrl = '/default-documents-store-url',
    documentsIndexUrl = '/documents' // URL for redirecting to dashboard
) {
    return {
        // Store passed-in URLs
        downloadExcelUrl: formDownloadExcelUrl,
        storeUrl: documentStoreUrl,
        dashboardUrl: documentsIndexUrl, // Store dashboard URL

        isEdit: isEditMode,
        docId: documentId,
        documentTitle: initialDocTitle,
        client: initialClient,
        address: initialAddress,
        attention: initialAttention,
        date: initialDate,
        refPoNo: initialRefPoNo,
        items: (Array.isArray(initialItemsData) && initialItemsData.length > 0)
               ? JSON.parse(JSON.stringify(initialItemsData))
               : [{ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' },
                  { quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' }],
        remarks: initialRemarks,

        isSaving: false,
        isDownloading: false,

        init() {
            if (this.isEdit) {
                console.log('Pullout Form (External JS) loaded in EDIT mode for document ID:', this.docId);
            } else {
                console.log('Pullout Form (External JS) loaded in CREATE mode.');
                if (!this.documentTitle.trim() && this.client.trim() && this.date.trim()) {
                   this.documentTitle = `MPO - ${this.client.trim()} - ${this.date}`;
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
                this.items[0] = { quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' };
            }
        },

        async downloadFormAsExcel() {
            this.isDownloading = true;
            const formDataForExcel = {
                client: this.client, address: this.address, attention: this.attention,
                date: this.date, refPoNo: this.refPoNo, items: this.items, remarks: this.remarks,
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
                    const errorData = await response.json().catch(() => ({ message: `Server error: ${response.statusText}` }));
                    alert('Error generating Excel: ' + (errorData.message || 'Unknown server error.'));
                }
            } catch (error) {
                console.error('Error during Excel download:', error);
                alert('Network error or script issue during Excel download.');
            } finally {
                this.isDownloading = false;
            }
        },

        saveDocumentPrompt() {
            if (this.isEdit) {
                return this.updateDocumentData();
            }
            if (!this.documentTitle.trim()) {
                const suggestedName = `MPO - ${this.client.trim() || 'N/A'} - ${this.date.trim()}`;
                const title = prompt("Please enter a name for this new document:", suggestedName);
                if (title === null || title.trim() === "") {
                    alert("Save cancelled. Document name is required.");
                    return;
                }
                this.documentTitle = title.trim();
            }
            this.saveNewDocumentData();
        },

        async saveNewDocumentData() { // For CREATE mode
            this.isSaving = true;
            const formFieldsPayload = {
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
                const response = await fetch(this.storeUrl, { // Use the stored URL
                    method: 'POST',
                    headers: { // Added missing headers
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(documentRecordPayload)
                });
                const result = await response.json();
                if (response.ok) {
                    alert(result.message || 'Document saved successfully!');
                    // Redirect to dashboard
                    window.location.href = this.dashboardUrl; // Use stored dashboard URL
                } else {
                    let errorMsg = result.message || 'Error saving document.';
                    if (result.errors) {
                        errorMsg += "\nDetails:\n" + Object.values(result.errors).flat().join("\n");
                    }
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Save Error:', error);
                alert('Save failed due to a network or script error.');
            } finally {
                this.isSaving = false;
            }
        },

        async updateDocumentData() { // For EDIT mode
            if (!this.isEdit || !this.docId) {
                 console.warn("updateDocumentData called inappropriately."); return;
            }
            this.isSaving = true;

            if (!this.documentTitle.trim()) {
                alert("Document name cannot be empty.");
                this.isSaving = false;
                return;
            }

            const formFieldsPayload = {
                client: this.client, address: this.address, attention: this.attention,
                date: this.date, refPoNo: this.refPoNo, items: this.items, remarks: this.remarks
            };
            const documentUpdatePayload = {
                _method: 'PUT',
                document_name: this.documentTitle,
                recipient: this.client || null,
                data: formFieldsPayload
            };

            const updateUrl = `/documents/${this.docId}`; // Construct URL directly
            try {
                const response = await fetch(updateUrl, {
                    method: 'POST', // Using POST with _method: 'PUT'
                    headers: { // Added missing headers
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(documentUpdatePayload)
                });
                const result = await response.json();
                if (response.ok) {
                    alert(result.message || 'Document updated successfully!');
                    window.location.href = `/documents/${this.docId}`; // Redirect to show page
                } else {
                    let errorMsg = result.message || 'Error updating document.';
                    if (result.errors) {
                        errorMsg += "\nDetails:\n" + Object.values(result.errors).flat().join("\n");
                    }
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('Update Error:', error);
                alert('Update failed due to a network or script error.');
            } finally {
                this.isSaving = false;
            }
        }
    };
}