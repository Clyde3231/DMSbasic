@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{
    rows: [ { qty: null, description: '', unitPrice: null } ],
    get totalAmount() {
        return this.rows.reduce((sum, row) => {
            const quantity = parseFloat(row.qty) || 0;
            const price = parseFloat(row.unitPrice) || 0;
            return sum + (quantity * price);
        }, 0).toFixed(2);
    },
    addRow() {
        this.rows.push({ qty: null, description: '', unitPrice: null });
    },
    removeRow(index) {
        if (this.rows.length > 1) {
            this.rows.splice(index, 1);
        }
    },
    calculateRowTotal(row) {
        const quantity = parseFloat(row.qty) || 0;
        const price = parseFloat(row.unitPrice) || 0;
        return (quantity * price).toFixed(2);
    }
}" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Purchase Request Form</title>
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
        /* Custom style for readonly inputs to match the design */
        input[readonly] {
            background-color: #f3f4f6; /* gray-100 */
            cursor: default;
        }
        .table-cell-input {
            width: 100%;
            padding: 0.5rem; /* p-2 */
            border: none;
            /* border-right: 1px solid black; */
            /* border-bottom: 1px solid black; */
            box-sizing: border-box;
            font-size: 0.875rem; /* text-sm */
            min-height: 40px; /* Ensure cells have some height */
            display: flex;
            align-items: center;
        }
        .table-cell-input:focus {
            outline: none;
            ring: 0;
        }
         .table-cell-textarea {
            width: 100%;
            padding: 0.5rem; /* p-2 */
            border: none;
            box-sizing: border-box;
            font-size: 0.875rem; /* text-sm */
            resize: none;
            min-height: 40px;
        }
        .table-cell-textarea:focus {
            outline:none;
            ring:0;
        }

    </style>
</head>
<body class="bg-[#F0F0F0] font-sans min-h-screen flex flex-col">

    <div class="bg-[#6FFFA0] w-full px-6 py-2 flex justify-between items-center">
        <div></div> <!-- Can be used for a logo or title if needed -->
        <div class="text-right text-sm text-black">
            <div class="font-semibold">Username</div>
            <div class="text-xs text-gray-700">user@example.com</div>
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">

        <div class="flex-1 overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                <h1 class="text-xl font-semibold text-gray-800">Purchase Request</h1>
                <div class="flex space-x-2">
                    <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded hover:bg-[#FF8500] transition duration-300">Send</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Save</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Download</button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <!-- Logo (Optional) -->
                    <div class="flex justify-center mb-8">
                        {{-- <img src="https://via.placeholder.com/200x60?text=Company+Logo" alt="Logo" class="h-12 md:h-16" /> --}}
                    </div>

                    <form class="space-y-6">
                        <!-- Top Section: Employee Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mb-8">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NAME:</label>
                                <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NUMBER:</label>
                                <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">DEPARTMENT:</label>
                                <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">POSITION :</label>
                                <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">DATE FILED:</label>
                                <input type="date" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">REFERENCE NO. :</label>
                                <input type="text" value="PRN-00001" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly />
                            </div>
                        </div>

                        <!-- Middle Section: Items Table -->
                        <div class="mt-8">
                            <div class="border-2 border-black">
                                <!-- Headers -->
                                <div class="grid grid-cols-12 bg-gray-50 text-sm">
                                    <div class="col-span-1 p-2 text-center font-bold border-b-2 border-r-2 border-black">Qty</div>
                                    <div class="col-span-6 p-2 text-center font-bold border-b-2 border-r-2 border-black">Description</div>
                                    <div class="col-span-2 p-2 text-center font-bold border-b-2 border-r-2 border-black">Unit Price</div>
                                    <div class="col-span-3 p-2 text-center font-bold border-b-2 border-black">Total Price</div>
                                </div>
                                <!-- Dynamic Rows -->
                                <div id="item-rows-container">
                                    <template x-for="(row, index) in rows" :key="index">
                                        <div class="grid grid-cols-12 relative" :class="index < rows.length ? 'border-b border-black' : ''">
                                            <div class="col-span-1 border-r-2 border-black h-full">
                                                <input type="number" x-model.number="row.qty" placeholder="0"
                                                       class="table-cell-input text-center">
                                            </div>
                                            <div class="col-span-6 border-r-2 border-black h-full">
                                                <textarea x-model="row.description" placeholder="Item description"
                                                          class="table-cell-textarea"></textarea>
                                            </div>
                                            <div class="col-span-2 border-r-2 border-black h-full">
                                                <input type="number" x-model.number="row.unitPrice" placeholder="0.00" step="0.01"
                                                       class="table-cell-input text-right">
                                            </div>
                                            <div class="col-span-2 h-full">
                                                <input type="text" :value="calculateRowTotal(row)" readonly
                                                       class="table-cell-input text-right bg-gray-100">
                                            </div>
                                            <div class="col-span-1 flex items-center justify-center h-full border-l-2 border-black">
                                                <template x-if="rows.length > 1">
                                                    <button type="button" @click="removeRow(index)"
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold text-xs rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                                        title="Remove item">
                                                        ×
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                     <!-- Ensure there's always at least one empty-looking row if no items, or match image's two initial rows -->
                                    <template x-if="rows.length === 0">
                                        <div class="grid grid-cols-12 border-b border-black">
                                            <div class="col-span-1 p-2 border-r-2 border-black h-10"></div>
                                            <div class="col-span-6 p-2 border-r-2 border-black h-10"></div>
                                            <div class="col-span-2 p-2 border-r-2 border-black h-10"></div>
                                            <div class="col-span-3 p-2 h-10"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- Add More Button -->
                            <div class="flex justify-end mt-3">
                                <button type="button"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-4 py-2 rounded font-semibold transition duration-300 text-xs uppercase"
                                    @click="addRow()">
                                    ➕ Add Item
                                </button>
                            </div>
                        </div>

                        <!-- Total Amount Section -->
                        <div class="mt-4 flex justify-end items-center space-x-2">
                            <span class="text-sm font-bold uppercase text-gray-700">Total Amount</span>
                            <span class="border border-black px-3 py-1 text-sm font-semibold bg-gray-100">PHP</span>
                            <input type="text" :value="totalAmount" readonly class="border border-black px-3 py-1 text-sm w-32 text-right font-semibold bg-gray-100" />
                        </div>

                        <!-- Purpose / Reason Section -->
                        <div class="mt-8">
                            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">PURPOSE / REASON:</label>
                            <textarea class="w-full border-2 border-black focus:ring-1 focus:ring-blue-600 focus:border-blue-600 p-2 text-sm min-h-[100px] md:min-h-[120px] resize-y" placeholder="State the purpose or reason for this request..."></textarea>
                        </div>


                        <!-- Bottom Section: Signatures -->
                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-8">
                            <!-- Left Column -->
                            <div class="space-y-10">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700">Requested by :</label>
                                    <div class="mt-6 border-b-2 border-black h-6"></div>
                                    <p class="text-xs text-gray-600 mt-1 text-center">Signature over printed name</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700">Department :</label>
                                    <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-1 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700">Date :</label>
                                    <input type="date" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-1 text-sm" />
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="space-y-10 flex flex-col justify-between">
                                 <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700">Checked by :</label>
                                    <div class="mt-6 border-b-2 border-black h-6"></div>
                                    <p class="text-xs text-gray-800 font-semibold mt-1 text-center">Department Head</p>
                                </div>
                                <div class="pt-2"> <!-- Added padding to align with 'Approved by' based on image -->
                                    <label class="block text-xs font-bold uppercase text-gray-700">Approved by:</label>
                                    <div class="mt-6 border-b-2 border-black h-6"></div>
                                    <p class="text-xs text-gray-800 font-semibold mt-1 text-center">President</p>
                                </div>
                                <div></div> <!-- Spacer to push approved by down if needed, or adjust spacing -->
                            </div>
                        </div>


                        <!-- Submit Button -->
                        <div class="pt-8">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side Menu -->
        <div class="w-64 bg-[#1A3D8A] text-white p-6 flex-shrink-0 hidden md:block"> <!-- Hidden on small screens -->
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