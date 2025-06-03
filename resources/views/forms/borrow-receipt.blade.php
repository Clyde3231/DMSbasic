@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{
    borrowedBy: '',
    address: '',
    attention: '',
    date: '', // Let user input or prefill with current date
    refPoNo: '',
    items: [
        { quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' },
        { quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' } // Start with two rows
    ],
    remarks: '',
    addItem() {
        this.items.push({ quantity: null, unit: '', brandParticulars: '', model: '', partSerialNumber: '' });
    },
    removeItem(index) {
        this.items.splice(index, 1);
    }
}" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Borrowed Items Receipt</title>
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
        .input-underline {
            border: none;
            border-bottom: 1px solid black;
            padding-top: 0.125rem;
            padding-bottom: 0.125rem;
            font-size: 0.875rem; /* text-sm */
        }
        .input-underline:focus {
            outline: none;
            ring: 0;
            border-bottom-color: #2563eb; /* blue-600 */
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
            vertical-align: top;
        }
        .table-cell-textarea { /* Using textarea for all table cells for consistency and multi-line possibility */
            width: 100%;
            height: 100%;
            padding: 0.5rem;
            border: none;
            font-size: 0.875rem;
            box-sizing: border-box;
            resize: none;
            min-height: 60px; /* Taller cells */
        }
        .table-cell-textarea:focus {
            outline: none;
        }
        .signature-line {
            border-bottom: 1px solid black;
            height: 1.5rem;
            margin-top: 0.25rem;
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
                <h1 class="text-xl font-semibold text-gray-800">Borrowed Items Receipt</h1>
                <div class="flex space-x-2">
                    <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded hover:bg-[#FF8500] transition duration-300">Send</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Save</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Download</button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">

                    <form class="space-y-6">
                        <!-- Header Section: Borrowed Info -->
                        <div class="border border-black p-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <label class="w-28 font-semibold uppercase">BORROWED BY:</label>
                                        <input type="text" x-model="borrowedBy" class="input-underline flex-1">
                                    </div>
                                    <div class="flex items-center">
                                        <label class="w-28 font-semibold uppercase">ADDRESS:</label>
                                        <input type="text" x-model="address" class="input-underline flex-1">
                                    </div>
                                    <div class="flex items-center">
                                        <label class="w-28 font-semibold uppercase">ATTENTION:</label>
                                        <input type="text" x-model="attention" class="input-underline flex-1">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <label class="w-28 font-semibold uppercase">DATE:</label>
                                        <input type="date" x-model="date" class="input-underline flex-1">
                                    </div>
                                    <div class="flex items-center">
                                        <label class="w-28 font-semibold uppercase">REF/PO NO:</label>
                                        <input type="text" x-model="refPoNo" class="input-underline flex-1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table Section -->
                        <div class="border-2 border-black">
                            <!-- Table Headers -->
                            <div class="grid grid-cols-12">
                                <div class="col-span-2 table-header-cell">QUANTITY</div>
                                <div class="col-span-1 table-header-cell">UNIT</div>
                                <div class="col-span-4 table-header-cell">BRAND/PARTICULARS</div>
                                <div class="col-span-2 table-header-cell">MODEL</div>
                                <div class="col-span-3 table-header-cell">PART/SERIAL NUMBER</div>
                            </div>
                            <!-- Table Rows -->
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 relative">
                                    <div class="col-span-2 table-data-cell">
                                        <textarea x-model.number="item.quantity" class="table-cell-textarea text-center" placeholder="0"></textarea>
                                    </div>
                                    <div class="col-span-1 table-data-cell">
                                        <textarea x-model="item.unit" class="table-cell-textarea text-center"></textarea>
                                    </div>
                                    <div class="col-span-4 table-data-cell">
                                        <textarea x-model="item.brandParticulars" class="table-cell-textarea"></textarea>
                                    </div>
                                    <div class="col-span-2 table-data-cell">
                                        <textarea x-model="item.model" class="table-cell-textarea"></textarea>
                                    </div>
                                    <div class="col-span-3 table-data-cell">
                                        <textarea x-model="item.partSerialNumber" class="table-cell-textarea"></textarea>
                                    </div>
                                    <button type="button" @click="removeItem(index)"
                                        class="absolute top-1/2 -translate-y-1/2 -right-6 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                        title="Remove item">×</button>
                                </div>
                            </template>
                            <div class="p-2 border-t border-black flex justify-end">
                                <button type="button" @click="addItem()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Add Item</button>
                            </div>

                            <!-- Remarks Section -->
                            <div class="grid grid-cols-12 border-t border-black">
                                <div class="col-span-2 table-header-cell !text-left !bg-white !font-semibold !text-gray-700 !border-r-0 !border-b-0">REMARKS:</div>
                                <div class="col-span-10 table-data-cell !border-l-0 !border-b-0">
                                    <textarea x-model="remarks" class="table-cell-textarea min-h-[60px]"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Received Confirmation -->
                        <div class="text-center text-sm italic mt-4 py-2">
                            Received the above tools/equipments in good order and condition
                        </div>

                        <!-- Signatures Section -->
                        <div class="mt-8 pt-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8 text-sm">
                            <div class="md:col-span-2 space-y-10">
                                <div class="grid grid-cols-2 gap-x-8">
                                    <div>
                                        <p class="uppercase font-semibold text-gray-600">Prepared By</p>
                                        <div class="signature-line"></div>
                                    </div>
                                    <div>
                                        <p class="uppercase font-semibold text-gray-600">Released By</p>
                                        <div class="signature-line"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-x-8">
                                    <div>
                                        <p class="uppercase font-semibold text-gray-600">Checked By</p>
                                        <div class="signature-line"></div>
                                    </div>
                                    <div>
                                        <!-- This cell is empty on the left side as per image -->
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                 <div class="flex items-end">
                                    <label class="font-semibold mr-2">By:</label>
                                    <div class="signature-line flex-1"></div>
                                 </div>
                                <p class="text-xs text-center text-gray-600">Signature over printed name</p>
                                <div class="flex items-end mt-3">
                                    <label class="font-semibold mr-2">Date:</label>
                                    <div class="signature-line flex-1"></div>
                                </div>
                            </div>
                        </div>


                        <!-- Submit Button -->
                        <div class="pt-12">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Submit Receipt
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
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Tools/Equipment Receipt</a>
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Delivery Receipt</a>
                <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left text-sm transition duration-150">Purchase Order</a>
            </div>
        </div>
    </div>
</body>
</html>
@endsection