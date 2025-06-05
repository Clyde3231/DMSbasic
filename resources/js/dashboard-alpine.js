// resources/js/dashboard-alpine.js

// Export the functions that will be used as Alpine component initializers
export function documentDashboard() {
    return {
        selectAll: false,
        selectedRows: {}, // Stores { docId: true/false }

        toggleSelectAll(event) {
            const isChecked = event.target.checked;
            const checkboxes = document.querySelectorAll('.document-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const docId = checkbox.value;
                this.selectedRows[docId] = isChecked;
            });
        },

        updateSelectAllCheckboxState() {
            const checkboxes = document.querySelectorAll('.document-checkbox');
            if (checkboxes.length === 0) {
                this.selectAll = false;
                return;
            }
            this.selectAll = Array.from(checkboxes).every(checkbox => checkbox.checked);
        },

        init() {
            document.querySelectorAll('.document-checkbox').forEach(cb => {
                this.selectedRows[cb.value] = cb.checked; // Reflect initial checked state if any
            });
            this.updateSelectAllCheckboxState();
            console.log('Document Dashboard (Vite & Alpine) Initialized');
        },

        async downloadDocument(docId, documentType, documentName = 'document') {
            let downloadUrl = '';
            // These URLs now point to GET routes designed to download a *saved* document by its ID.
            // The backend controller for these routes will fetch the document and its data.
            switch (documentType) {
                case 'pull_out_receipt':
                    downloadUrl = `/documents/${docId}/download-pullout`; // Example route
                    break;
                case 'cash_advance':
                    downloadUrl = `/documents/${docId}/download-cash-advance`; // Example route
                    break;
                case 'purchase_request':
                    downloadUrl = `/documents/${docId}/download-purchase-request`; // Example route
                    break;
                case 'request_for_payment':
                    downloadUrl = `/documents/${docId}/download-request-for-payment`; // Example route
                    break;
                case 'reimbursement':
                    downloadUrl = `/documents/${docId}/download-reimbursement`;
                    break;
                case 'commission_incentive_request':
                    downloadUrl = `/documents/${docId}/download-commission-incentive`;
                    break;
                case 'purchase_order':
                    downloadUrl = `/documents/${docId}/download-purchase-order`;
                    break;
                case 'delivery_receipt':
                    downloadUrl = `/documents/${docId}/download-delivery-receipt`;
                    break;
                case 'acknowledgement_receipt':
                    downloadUrl = `/documents/${docId}/download-acknowledgement-receipt`;
                    break;
                case 'delivery_checklist':
                    downloadUrl = `/documents/${docId}/download-delivery-checklist`;
                    break;
                case 'tools_equipment_acknowledgement':
                    downloadUrl = `/documents/${docId}/download-tools-equipment`;
                    break;
                case 'borrow_receipt':
                    downloadUrl = `/documents/${docId}/download-borrow-receipt`;
                    break;
                default:
                    alert('Download not configured for this document type: ' + documentType);
                    console.warn('Download route not found for type:', documentType);
                    return;
            }

            if (!downloadUrl) {
                alert('Download URL could not be determined for this document type.');
                return;
            }

            console.log(`Initiating download for Document ID ${docId} (Type: ${documentType}) from ${downloadUrl}`);

            // For GET requests that trigger a file download, simply navigating is often enough,
            // or using a temporary anchor link.
            const a = document.createElement('a');
            a.href = downloadUrl;
            // The browser will get the filename from the Content-Disposition header set by the server.
            // a.download = ''; // Not strictly necessary if server sets Content-Disposition
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            // Note: If the server responds with an error (e.g., 404, 500) for the GET request,
            // this client-side code won't directly catch it as a fetch promise error.
            // The browser will handle the navigation to the error page or the failed download.
            // For more robust error handling of the download itself, you might use fetch,
            // check response.ok, and then handle the blob, but for simple GET downloads,
            // direct navigation or an anchor click is common.
        }
    };
}

export function documentRow(docId, initialStatus) {
    return {
        docId: docId,
        currentStatus: initialStatus,
        isUpdatingStatus: false,
        showStatusDropdown: false,

        async updateThisRowStatus(newStatus) {
            if (this.currentStatus === newStatus && !this.isUpdatingStatus) {
                this.showStatusDropdown = false;
                return;
            }
            this.isUpdatingStatus = true;
            this.showStatusDropdown = false;

            try {
                const response = await fetch(`/documents/${this.docId}/update-status`, { // Route for status update
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const result = await response.json();
                if (response.ok) {
                    this.currentStatus = result.new_status || newStatus;
                    // Optionally dispatch an event or show a small success toast
                    // Example: window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Status updated!' }}));
                } else {
                    alert('Error updating status: ' + (result.message || 'Server error. Please try again.'));
                    console.error('Status update failed:', result);
                }
            } catch (e) {
                alert('Failed to update status. Check your network connection or contact support.');
                console.error('Status update network/script error:', e);
            } finally {
                this.isUpdatingStatus = false;
            }
        }
    };
}