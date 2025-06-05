// resources/js/app.js

import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Import dashboard logic
import { documentDashboard, documentRow } from './dashboard-alpine.js'; // Assuming this path
Alpine.data('documentDashboard', documentDashboard);
window.documentRow = documentRow; // For x-data="documentRow(params)"




Alpine.start();