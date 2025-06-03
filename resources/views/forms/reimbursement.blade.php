@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{
    employeeName: '',
    department: '',
    dateFiled: new Date().toISOString().slice(0,10),
    employeeNumber: '',
    position: '',
    referenceNo: 'REIM-00001',
    cvNumber: '',
    project: '',
    expenses: [
        { date: '', receiptNo: '', description: '', amount: null },
        { date: '', receiptNo: '', description: '', amount: null },
        { date: '', receiptNo: '', description: '', amount: null } // Start with a few rows
    ],
    signature: '',
    releasedDate: '',
    receivedBy: '',

    addExpense() {
        this.expenses.push({ date: '', receiptNo: '', description: '', amount: null });
    },
    removeExpense(index) {
        if (this.expenses.length > 1) { // Keep at least one row, or adjust as needed
            this.expenses.splice(index, 1);
        }
    },
    get totalExpenses() {
        return this.expenses.reduce((sum, expense) => sum + (parseFloat(expense.amount) || 0), 0);
    },
    formatCurrency(value) {
        const num = parseFloat(value);
        if (isNaN(num) || num === 0) return '-'; // Display '-' for zero or invalid
        return num.toFixed(2);
    }
}" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reimbursement Form</title>
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
            font-size: 0.875rem; /* text-sm */
        }
        .input-line:focus {
            outline: none;
            ring: 0;
            border-bottom-color: #2563eb; /* blue-600 */
        }
        .input-line-static { /* For readonly Reference No. */
            border: none;
            border-bottom: 1px solid black;
            padding-top: 0.125rem;
            padding-bottom: 0.125rem;
            font-size: 0.875rem;
            background-color: #f9fafb; /* gray-50 */
        }
        .table-header-cell {
            background-color: #f9fafb; /* gray-50 */
            font-weight: bold;
            text-align: center;
            padding: 0.5rem;
            border: 1px solid black;
            font-size: 0.75rem; /* text-xs */
            text-transform: uppercase;
        }
        .table-data-cell {
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
            min-height: 38px; /* Ensure cells have consistent height */
        }
        .table-cell-input:focus {
            outline: none;
        }
        .signature-line-input { /* For signature fields at the bottom */
            border-bottom: 1px solid black;
            height: 1.5rem; /* approx 24px */
            margin-top: 0.25rem; /* mt-1 */
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
                <h1 class="text-xl font-semibold text-gray-800">Reimbursement Form</h1>
                <div class="flex space-x-2">
                    <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded hover:bg-[#FF8500] transition duration-300">Send</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Save</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Download</button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">

                    <form class="space-y-6">
                        <!-- Top Employee Info Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">EMPLOYEE NAME:</label>
                                    <input type="text" x-model="employeeName" class="input-line flex-1">
                                </div>
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">DEPARTMENT:</label>
                                    <input type="text" x-model="department" class="input-line flex-1">
                                </div>
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">DATE FILED:</label>
                                    <input type="date" x-model="dateFiled" class="input-line flex-1">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">EMPLOYEE NUMBER:</label>
                                    <input type="text" x-model="employeeNumber" class="input-line flex-1">
                                </div>
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">POSITION :</label>
                                    <input type="text" x-model="position" class="input-line flex-1">
                                </div>
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">REFERENCE NO. :</label>
                                    <input type="text" x-model="referenceNo" class="input-line-static flex-1 font-semibold" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- CV Number / Project Section -->
                        <div class="border-y border-black py-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 text-sm">
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">CV Number:</label>
                                    <input type="text" x-model="cvNumber" class="input-line flex-1">
                                </div>
                                <div class="flex items-center">
                                    <label class="w-32 font-semibold uppercase">PROJECT:</label>
                                    <input type="text" x-model="project" class="input-line flex-1">
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Table Section -->
                        <div class="border-2 border-black">
                            <!-- Table Headers -->
                            <div class="grid grid-cols-12">
                                <div class="col-span-2 table-header-cell">DATE</div>
                                <div class="col-span-2 table-header-cell">RECEIPT NO.</div>
                                <div class="col-span-6 table-header-cell">DESCRIPTION</div>
                                <div class="col-span-2 table-header-cell">AMOUNT</div>
                            </div>
                            <!-- Table Rows -->
                            <template x-for="(expense, index) in expenses" :key="index">
                                <div class="grid grid-cols-12 relative">
                                    <div class="col-span-2 table-data-cell">
                                        <input type="date" x-model="expense.date" class="table-cell-input">
                                    </div>
                                    <div class="col-span-2 table-data-cell">
                                        <input type="text" x-model="expense.receiptNo" class="table-cell-input">
                                    </div>
                                    <div class="col-span-6 table-data-cell">
                                        <input type="text" x-model="expense.description" class="table-cell-input">
                                    </div>
                                    <div class="col-span-2 table-data-cell">
                                        <input type="number" step="0.01" x-model.number="expense.amount" class="table-cell-input text-right" placeholder="0.00">
                                    </div>
                                    <button type="button" @click="removeExpense(index)" x-show="expenses.length > 1"
                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                        title="Remove expense">×</button>
                                </div>
                            </template>
                            <!-- Add Expense Button -->
                            <div class="p-2 border-t border-black flex justify-end">
                                <button type="button" @click="addExpense()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Add Expense</button>
                            </div>
                            <!-- Total Expenses Row -->
                            <div class="grid grid-cols-12 border-t border-black bg-gray-50">
                                <div class="col-span-8"></div> <!-- Empty cells -->
                                <div class="col-span-2 table-header-cell !text-right !bg-gray-50 !border-l-0">TOTAL EXPENSES</div>
                                <div class="col-span-1 table-header-cell !text-center !bg-gray-50 !border-l-0 !border-r-0">PHP</div>
                                <div class="col-span-1 table-data-cell !border-l-0">
                                    <input type="text" :value="formatCurrency(totalExpenses)" readonly class="table-cell-input text-right font-semibold bg-gray-100">
                                </div>
                            </div>
                        </div>

                        <!-- Signatures Section -->
                        <div class="mt-8 pt-6 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8 text-sm">
                            <!-- Left Column Signatures -->
                            <div class="space-y-8">
                                <div>
                                    <label class="block font-semibold uppercase">Signature:</label>
                                    <div class="signature-line-input"></div>
                                </div>
                                <div>
                                    <label class="block font-semibold uppercase">Released Date:</label>
                                    <input type="date" x-model="releasedDate" class="input-line w-full mt-1">
                                </div>
                                <div>
                                    <label class="block font-semibold uppercase">Received By:</label>
                                    <div class="signature-line-input"></div>
                                </div>
                            </div>
                            <!-- Right Column Signatures -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block font-semibold uppercase">Noted by:</label>
                                    <div class="signature-line-input"></div>
                                    <p class="text-xs text-center text-gray-700 mt-0.5">Department Head</p>
                                </div>
                                <div>
                                    <label class="block font-semibold uppercase">Released By:</label>
                                    <div class="signature-line-input"></div>
                                    <p class="text-xs text-center text-gray-700 mt-0.5">Accounting</p>
                                </div>
                                <div>
                                    <label class="block font-semibold uppercase">Approved by:</label>
                                    <div class="signature-line-input"></div>
                                    <p class="text-xs text-center text-gray-700 mt-0.5">President</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-12">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Submit Reimbursement
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
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Cash Advance</a>
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Purchase Request</a>
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Request for Payment</a>
            </div>
        </div>
    </div>
</body>
</html>
@endsection