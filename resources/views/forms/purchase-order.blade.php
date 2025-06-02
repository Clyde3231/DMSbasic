@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{
    companyName: 'Your Company Name',
    companySlogan: 'connecting solutions',
    poNumber: 'PO-2024-001',
    poDate: new Date().toISOString().slice(0,10),
    vendorDetails: ['', '', '', ''],
    shipTo: ['', '', ''],
    contactPerson: '',
    referenceNumber: '',
    project: '',
    paymentTerms: '',
    deliveryDate: '',
    items: [
        { itemNo: '', partNumber: '', description: '', qty: null, unit: '', unitPrice: null },
        { itemNo: '', partNumber: '', description: '', qty: null, unit: '', unitPrice: null },
        { itemNo: '', partNumber: '', description: '', qty: null, unit: '', unitPrice: null }
    ],
    notes: '',
    discount: 0,

    addItem() {
        this.items.push({ itemNo: '', partNumber: '', description: '', qty: null, unit: '', unitPrice: null });
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },
    getItemAmount(item) {
        const qty = parseFloat(item.qty) || 0;
        const unitPrice = parseFloat(item.unitPrice) || 0;
        return (qty * unitPrice);
    },
    get totalSales() {
        return this.items.reduce((sum, item) => sum + this.getItemAmount(item), 0);
    },
    get vatAmount() {
        return this.totalSales * 0.12;
    },
    get amountNetOfVat() {
        return this.totalSales - this.vatAmount;
    },
    get totalAmountDue() {
        const discountVal = parseFloat(this.discount) || 0;
        return this.amountNetOfVat - discountVal;
    },
    formatCurrency(value) {
        const num = parseFloat(value);
        if (isNaN(num) || num === 0) return '-';
        return num.toFixed(2);
    }
}" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Purchase Order Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        [x-cloak] { display: none !important; }
        .input-line {
            border: none;
            border-bottom: 1px solid black;
            padding-top: 0.125rem;
            padding-bottom: 0.125rem;
            font-size: 0.875rem;
        }
        .input-line:focus {
            outline: none;
            ring: 0;
            border-bottom-color: #2563eb; /* blue-600 */
        }
        .table-header {
            background-color: #f9fafb; /* gray-50 */
            font-weight: bold;
            text-align: center;
            padding: 0.5rem;
            border: 1px solid black;
            font-size: 0.75rem; /* text-xs */
            text-transform: uppercase;
        }
        .table-cell {
            padding: 0; /* Remove padding for input to fill cell */
            border: 1px solid black;
            vertical-align: top; /* Align content to top */
        }
        .table-cell-input {
            width: 100%;
            height: 100%;
            padding: 0.5rem;
            border: none;
            font-size: 0.875rem;
            box-sizing: border-box;
        }
        .table-cell-input:focus {
            outline: none;
        }
        .table-cell-textarea {
            width: 100%;
            height: 100%;
            padding: 0.5rem;
            border: none;
            font-size: 0.875rem;
            box-sizing: border-box;
            resize: none; /* Prevent textarea resize */
            min-height: 40px; /* Ensure a minimum height for textareas */
        }
    </style>
</head>
<body class="bg-[#F0F0F0] font-sans min-h-screen flex flex-col">

    <div class="bg-[#6FFFA0] w-full px-6 py-2 flex justify-between items-center">
        <div></div>
        <div class="text-right text-sm text-black">
            <div class="font-semibold">Username</div>
            <div class="text-xs text-gray-700">user@example.com</div>
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">

        <div class="flex-1 overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <h1 class="text-xl font-semibold text-gray-800">Purchase Order</h1>
                <div class="flex space-x-2">
                    <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded hover:bg-[#FF8500] transition duration-300">Send</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Save</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Download</button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl relative">
                    <!-- Watermark - This is a basic way; better with CSS ::before/::after or background image -->
                    <!-- <div class="absolute inset-0 flex items-center justify-center z-0">
                        <span class="text-9xl font-bold text-gray-200 opacity-50 transform -rotate-12">Page 1</span>
                    </div> -->

                    <form class="space-y-6 z-10 relative">
                        <!-- Header Section: Logo and PO Details -->
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center space-x-2">
                                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                    {{-- Placeholder for Logo --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 7a1 1 0 00-2 0v6a1 1 0 102 0V7zm4-1a1 1 0 011 1v5a1 1 0 11-2 0V7a1 1 0 011-1zm3 2a1 1 0 10-2 0v3a1 1 0 102 0V8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xl font-bold" x-text="companyName"></div>
                                    <div class="text-xs text-gray-600" x-text="companySlogan"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="grid grid-cols-2 gap-x-4">
                                    <label class="text-xs font-bold uppercase text-gray-700 text-left">PO NUMBER</label>
                                    <label class="text-xs font-bold uppercase text-gray-700 text-left">PO DATE</label>
                                    <input type="text" x-model="poNumber" class="input-line w-full">
                                    <input type="date" x-model="poDate" class="input-line w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Vendor & Ship To Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-blue-700 mb-1">VENDOR DETAILS:</label>
                                <input type="text" x-model="vendorDetails[0]" class="input-line w-full mb-1">
                                <input type="text" x-model="vendorDetails[1]" class="input-line w-full mb-1">
                                <input type="text" x-model="vendorDetails[2]" class="input-line w-full mb-1">
                                <input type="text" x-model="vendorDetails[3]" class="input-line w-full">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-blue-700 mb-1">SHIP TO:</label>
                                <input type="text" x-model="shipTo[0]" class="input-line w-full mb-1">
                                <input type="text" x-model="shipTo[1]" class="input-line w-full mb-1">
                                <input type="text" x-model="shipTo[2]" class="input-line w-full mb-2">
                                <label class="block text-xs font-bold uppercase text-blue-700 mb-1 mt-2">Contact Person:</label>
                                <input type="text" x-model="contactPerson" class="input-line w-full">
                            </div>
                        </div>

                        <!-- PO Meta Info Table -->
                        <div class="border-2 border-black">
                            <div class="grid grid-cols-12 text-xs">
                                <div class="col-span-3 table-header">REFERENCE NUMBER</div>
                                <div class="col-span-3 table-header">PROJECT</div>
                                <div class="col-span-3 table-header">PAYMENT TERMS</div>
                                <div class="col-span-3 table-header">DELIVERY DATE</div>

                                <div class="col-span-3 table-cell"><input type="text" x-model="referenceNumber" class="table-cell-input"></div>
                                <div class="col-span-3 table-cell"><input type="text" x-model="project" class="table-cell-input"></div>
                                <div class="col-span-3 table-cell"><input type="text" x-model="paymentTerms" class="table-cell-input"></div>
                                <div class="col-span-3 table-cell"><input type="date" x-model="deliveryDate" class="table-cell-input"></div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="border-2 border-black border-t-0">
                             <div class="grid grid-cols-12 text-xs">
                                <div class="col-span-1 table-header">ITEM</div>
                                <div class="col-span-2 table-header">PART NUMBER</div>
                                <div class="col-span-3 table-header">DESCRIPTION</div>
                                <div class="col-span-1 table-header">QTY</div>
                                <div class="col-span-1 table-header">UNIT</div>
                                <div class="col-span-2 table-header">UNIT PRICE</div>
                                <div class="col-span-2 table-header">AMOUNT</div>
                            </div>
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 text-xs relative">
                                    <div class="col-span-1 table-cell"><input type="text" x-model="item.itemNo" class="table-cell-input text-center"></div>
                                    <div class="col-span-2 table-cell"><input type="text" x-model="item.partNumber" class="table-cell-input"></div>
                                    <div class="col-span-3 table-cell"><textarea x-model="item.description" class="table-cell-textarea"></textarea></div>
                                    <div class="col-span-1 table-cell"><input type="number" x-model.number="item.qty" class="table-cell-input text-right" placeholder="0"></div>
                                    <div class="col-span-1 table-cell"><input type="text" x-model="item.unit" class="table-cell-input text-center"></div>
                                    <div class="col-span-2 table-cell"><input type="number" step="0.01" x-model.number="item.unitPrice" class="table-cell-input text-right" placeholder="0.00"></div>
                                    <div class="col-span-2 table-cell">
                                        <input type="text" :value="formatCurrency(getItemAmount(item))" readonly class="table-cell-input text-right bg-gray-50">
                                    </div>
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                        title="Remove item">×</button>
                                </div>
                            </template>
                            <div class="flex justify-end p-2 border-t border-black">
                                <button type="button" @click="addItem()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Add Item</button>
                            </div>
                        </div>

                        <!-- Notes & Totals Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase text-blue-700 mb-1">NOTES:</label>
                                <textarea x-model="notes" class="w-full border border-gray-300 rounded p-2 text-sm min-h-[120px] resize-y"></textarea>
                            </div>
                            <div class="text-xs space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Total Sales (Vat Inclusive)</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold mr-2">PHP</span>
                                        <input type="text" :value="formatCurrency(totalSales)" readonly class="w-24 text-right font-semibold bg-gray-100 py-0.5 px-1 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">less: 12%</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold mr-2">PHP</span>
                                        <input type="text" :value="formatCurrency(vatAmount)" readonly class="w-24 text-right font-semibold bg-gray-100 py-0.5 px-1 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Amount Net of Vat</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold mr-2">PHP</span>
                                        <input type="text" :value="formatCurrency(amountNetOfVat)" readonly class="w-24 text-right font-semibold bg-gray-100 py-0.5 px-1 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">DISCOUNT</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold mr-2">PHP</span>
                                        <input type="number" step="0.01" x-model.number="discount" class="w-24 text-right font-semibold py-0.5 px-1 border border-gray-400 rounded" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center pt-1 border-t border-gray-400 mt-1">
                                    <span class="font-bold text-sm">TOTAL Amount Due</span>
                                    <div class="flex items-center">
                                        <span class="font-bold text-sm mr-2">PHP</span>
                                        <input type="text" :value="formatCurrency(totalAmountDue)" readonly class="w-24 text-right font-bold text-sm bg-gray-100 py-0.5 px-1 border border-gray-300 rounded">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signatures Section -->
                        <div class="pt-10 text-xs">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div>
                                    <label class="block font-semibold mb-1">Prepared By:</label>
                                    <div class="border-b border-black h-8"></div>
                                </div>
                                <div>
                                    <label class="block font-semibold mb-1">Date:</label>
                                    <div class="border-b border-black h-8"></div>
                                </div>
                                 <div>
                                    <label class="block font-semibold mb-1">Approved By:</label>
                                    <div class="border-b border-black h-8"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-8">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Create Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side Menu -->
        <div class="w-64 bg-[#1A3D8A] text-white p-6 flex-shrink-0 hidden md:block">
            <h2 class="text-lg font-bold mb-4">Other Form Types</h2>
            <div class="space-y-4">
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Purchase Request</a>
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Reimbursement</a>
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Request for Payment</a>
            </div>
        </div>
    </div>
</body>
</html>
@endsection