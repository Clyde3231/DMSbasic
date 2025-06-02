 function materialForm(
        isEditMode = false, // Is this form in edit mode?
        documentId = null,  // ID of the document if in edit mode
        initialDocTitle = '',
        initialClient = '',
        initialAddress = '',
        initialAttention = '',
        initialDate = new Date().toISOString().slice(0,10),
        initialRefPoNo = '',
        initialItemsData = [], // Expecting an array here from json_encode
        initialRemarks = ''
    ) {
        return {
            isEdit: isEditMode,
            docId: documentId,
            documentTitle: initialDocTitle, // Renamed for clarity
            client: initialClient,
            address: initialAddress,
            attention: initialAttention,
            date: initialDate,
            refPoNo: initialRefPoNo,
            items: Array.isArray(initialItemsData) && initialItemsData.length > 0 ? JSON.parse(JSON.stringify(initialItemsData)) : [{ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' }], // Deep copy
            remarks: initialRemarks,

            isSaving: false,
            isDownloading: false,
            // isSending: false, // If you add a send feature

            init() {
                if (this.isEdit) {
                    console.log('Form loaded in EDIT mode for document ID:', this.docId);
                } else {
                    console.log('Form loaded in CREATE mode.');
                     // Auto-suggest title for new documents (optional)
                    // if (!this.documentTitle && this.client && this.date) {
                    //    this.documentTitle = `MPO - ${this.client} - ${this.date}`;
                    // }
                }
            },

            addItem() {
                this.items.push({ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' });
            },
            removeItem(index) {
                this.items.splice(index, 1);
            },

            downloadFormAsExcel() {
                this.isDownloading = true;
                const excelData = {
                    client: this.client, address: this.address, attention: this.attention,
                    date: this.date, refPoNo: this.refPoNo, items: this.items, remarks: this.remarks,
                };
                fetch('{{ route("form.download.excel") }}', { /* ... your existing fetch logic ... */ })
                .catch(error => console.error('Excel Download Error:', error))
                .finally(() => this.isDownloading = false);
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