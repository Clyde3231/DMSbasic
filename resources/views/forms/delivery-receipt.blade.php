@extends('layouts.app')

{{-- Test Comment --}}

@section('content')
<div class="bg-[#6FFFA0] w-full px-6 py-2 flex justify-between items-center">
    <div></div> {{-- Placeholder for potential logo or left-aligned content --}}
    <div class="text-right text-sm text-black">
        <div class="font-semibold">Username</div>
        <div class="text-xs text-gray-700">user@example.com</div>
    </div>
</div>

<div class="flex flex-1 overflow-hidden">

    {{-- Main Content Area --}}
    <div class="flex-1 overflow-y-auto" x-data="deliveryForm()">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white sticky top-0 z-10">
            <h1 class="text-xl font-semibold text-gray-800">Delivery Receipt Form</h1>
            <div class="flex space-x-2">
                <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded-md hover:bg-[#FF8500] shadow-sm">Send</button>
                <button class="text-sm bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 shadow-sm">Save</button>
                <button class="text-sm bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 shadow-sm">Download</button>
            </div>
        </div>

        <div class="p-4 sm:p-6 bg-[#F0F0F0] min-h-full">
            {{-- Form Container --}}
            <form class="mx-auto bg-white p-6 sm:p-10 rounded-lg shadow-xl max-w-5xl">
                {{-- Logo --}}
                <div class="flex justify-center mb-8">
                    <img src="https://placehold.co/200x60/EBF4FF/333333?text=COMPANY+LOGO" alt="Logo" class="h-16" />
                </div>

                {{-- Top Section: Delivered To, Date, etc. --}}
                <div class="border border-black w-full mx-auto mb-6 text-xs sm:text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                        {{-- Left Column --}}
                        <div class="border-b md:border-b-0 md:border-r border-black p-3">
                            <div class="flex mb-2 items-center">
                                <label class="w-28 font-bold shrink-0">DELIVERED TO:</label>
                                <input type="text" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                            <div class="flex mb-2 items-center">
                                <label class="w-28 font-bold shrink-0">ADDRESS:</label>
                                <input type="text" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                            <div class="flex items-center">
                                <label class="w-28 font-bold shrink-0">BUS. STYLE:</label>
                                <input type="text" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="p-3">
                            <div class="flex mb-2 items-center">
                                <label class="w-24 font-bold shrink-0">DATE:</label>
                                <input type="date" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                            <div class="flex mb-2 items-center">
                                <label class="w-24 font-bold shrink-0">REF/PO NO:</label>
                                <input type="text" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                            <div class="flex mb-2 items-center">
                                <label class="w-24 font-bold shrink-0">TIN:</label>
                                <input type="text" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                            <div class="flex items-center">
                                <label class="w-24 font-bold shrink-0">TERMS:</label>
                                <input type="text" class="flex-1 border-b border-gray-400 outline-none focus:border-blue-500 py-1 px-2" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items Table Section --}}
                <div class="mb-6 text-xs sm:text-sm">
                    {{-- Table Headers --}}
                    <div class="grid grid-cols-12 gap-0 border border-black bg-gray-100 font-bold">
                        <div class="col-span-2 border-r border-black p-2 text-center">QUANTITY</div>
                        <div class="col-span-2 border-r border-black p-2 text-center">UNIT</div>
                        <div class="col-span-5 border-r border-black p-2 text-center">PARTICULARS</div>
                        <div class="col-span-3 p-2 text-center">PART/SERIAL NUMBER</div>
                    </div>

                    {{-- Table Rows --}}
                    <div class="border-l border-r border-b border-black min-h-[150px]"> {{-- Container for rows to keep bottom border consistent --}}
                        <template x-for="(row, index) in rows" :key="index">
                            <div class="grid grid-cols-12 gap-0 border-b border-black relative items-start">
                                <div class="col-span-2 border-r border-black p-1">
                                    <input type="number" class="w-full outline-none focus:ring-1 focus:ring-blue-500 p-1 text-center" x-model="row.quantity" placeholder="Qty" />
                                </div>
                                <div class="col-span-2 border-r border-black p-1">
                                    <input type="text" class="w-full outline-none focus:ring-1 focus:ring-blue-500 p-1 text-center" x-model="row.unit" placeholder="Unit" />
                                </div>
                                <div class="col-span-5 border-r border-black p-1">
                                    <textarea class="w-full outline-none focus:ring-1 focus:ring-blue-500 p-1 h-12 resize-y" x-model="row.particulars" placeholder="Details"></textarea>
                                </div>
                                <div class="col-span-3 p-1">
                                    <textarea class="w-full outline-none focus:ring-1 focus:ring-blue-500 p-1 h-12 resize-y" x-model="row.part_serial_number" placeholder="Part/Serial No."></textarea>
                                </div>
                                <button type="button"
                                        @click="removeRow(index)"
                                        x-show="rows.length > 1"
                                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-700 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center leading-none p-0.5">
                                    <span>&times;</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Add More Button --}}
                <div class="flex justify-end mb-6">
                    <button type="button"
                            class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-4 py-2 rounded-md font-semibold text-xs sm:text-sm shadow-sm"
                            @click="addRow()">
                        ➕ ADD ITEM
                    </button>
                </div>

                {{-- Remarks Section --}}
                <div class="mb-6 text-xs sm:text-sm">
                    <label class="block font-bold mb-1">REMARKS:</label>
                    <textarea class="w-full border border-black outline-none focus:ring-1 focus:ring-blue-500 p-2 h-20 resize-y" placeholder="Enter remarks here..."></textarea>
                </div>

                {{-- Received Confirmation --}}
                <div class="mb-8 text-center text-xs sm:text-sm italic">
                    Received the above merchandise in good order and condition
                </div>

                {{-- Signature Section --}}
                <div class="border border-black w-full mx-auto p-3 text-xs sm:text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block font-bold mb-1">PREPARED BY:</label>
                            <input type="text" class="w-full border-b border-gray-400 outline-none focus:border-blue-500 py-1" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">CHECKED BY:</label>
                            <input type="text" class="w-full border-b border-gray-400 outline-none focus:border-blue-500 py-1" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">BY:</label>
                            <input type="text" class="w-full border-b border-gray-400 outline-none focus:border-blue-500 py-1 mb-1" />
                            <span class="block text-xs text-gray-600 text-center">Signature over printed name</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold mb-1">APPROVED BY:</label>
                            <input type="text" class="w-full border-b border-gray-400 outline-none focus:border-blue-500 py-1" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">DELIVERED BY:</label>
                            <input type="text" class="w-full border-b border-gray-400 outline-none focus:border-blue-500 py-1" />
                        </div>
                        <div class="md:mt-2"> {{-- Align with "By:" signature --}}
                             <label class="block font-bold mb-1">DATE:</label>
                            <input type="date" class="w-full border-b border-gray-400 outline-none focus:border-blue-500 py-1" />
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="mt-10 flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md text-sm shadow-sm">
                        SUBMIT DELIVERY RECEIPT
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Right Sidebar --}}
    <div class="w-64 bg-[#1A3D8A] text-white p-6 flex-shrink-0 hidden md:block"> {{-- Hidden on small screens --}}
        <h2 class="text-lg font-bold mb-4">Other Form Types</h2>
        <div class="space-y-3">
            <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded-md text-sm text-center transition duration-150">Purchase Request</a>
            <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded-md text-sm text-center transition duration-150">Reimbursement</a>
            <a href="#" class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded-md text-sm text-center transition duration-150">Request for Payment</a>
        </div>
    </div>
</div>

{{-- Alpine.js Script for Dynamic Rows --}}
<script>
    function deliveryForm() {
        return {
            rows: [{
                quantity: '',
                unit: '',
                particulars: '',
                part_serial_number: ''
            }],
            addRow() {
                this.rows.push({
                    quantity: '',
                    unit: '',
                    particulars: '',
                    part_serial_number: ''
                });
            },
            removeRow(index) {
                // Prevent removing the last row if you always want at least one row
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                } else {
                    // Optionally clear the fields of the last row instead of removing it
                    // this.rows[0] = { quantity: '', unit: '', particulars: '', part_serial_number: '' };
                    // Or show a message, e.g., using a small notification component
                    console.log("Cannot remove the last item row.");
                }
            }
        }
    }
</script>

{{-- Include Alpine.js if not already included in layouts.app --}}
{{-- <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script> --}}
{{-- For Alpine v3 --}}
{{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

@endsection
