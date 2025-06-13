// resources/js/app.js

import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Import dashboard logic
import { documentDashboard, documentRow } from './dashboard-alpine.js';
Alpine.data('documentDashboard', documentDashboard);
window.documentRow = documentRow;

// Import pullout form logic
import { materialForm as pulloutMaterialForm } from './forms/pullout-form-alpine.js';
window.pulloutMaterialForm = pulloutMaterialForm;

// Import reimbursement form logic
import { reimbursementForm } from './forms/reimbursement-form-alpine.js';
window.reimbursementForm = reimbursementForm;

// Import cash advance form logic
import { cashAdvanceForm } from './forms/cash-advance-form-alpine.js';
window.cashAdvanceForm = cashAdvanceForm;

// Import purchase request form logic
import { purchaseRequestForm } from './forms/purchase-request-form-alpine.js';
window.purchaseRequestForm = purchaseRequestForm;

// Import request for payment form logic
import { requestPaymentForm } from './forms/request-for-payment-form-alpine.js';
window.requestPaymentForm = requestPaymentForm; // Make it globally available

import { acknowledgementReceiptForm } from './forms/acknowledgement-receipt-form-alpine.js';
window.acknowledgementReceiptForm = acknowledgementReceiptForm; // Make it globally available


Alpine.start();