@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{
    rows: [ { poNumber: '', projectName: '', dateStarted: '', completionDate: '' } ],
    addRow() {
        this.rows.push({ poNumber: '', projectName: '', dateStarted: '', completionDate: '' });
    },
    removeRow(index) {
        if (this.rows.length > 1) {
            this.rows.splice(index, 1);
        }
    }
}" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Client Information Report</title>
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
        input[readonly] {
            background-color: #f3f4f6; /* gray-100 */
            cursor: default;
        }
        .table-cell-input {
            width: 100%;
            padding: 0.5rem; /* p-2 */
            border: none;
            box-sizing: border-box;
            font-size: 0.875rem; /* text-sm */
            min-height: 60px; /* Ensure cells have some height, matching image */
            display: flex;
            align-items: center;
        }
        .table-cell-input:focus, .table-cell-textarea:focus {
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
            min-height: 60px; /* Ensure cells have some height */
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
                <h1 class="text-xl font-semibold text-gray-800">Commission Incentive Request</h1>
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

                    <form class="space-y-8">
                        <!-- Top Section: Employee Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mb-8">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NAME:</label>
                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NUMBER:</label>
                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">DEPARTMENT:</label>
                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">POSITION :</label>
                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">DATE FILED:</label>
                                <input type="date" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">REFERENCE NO. :</label>
                                <input type="text" value="CIR-00001" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly />
                            </div>
                        </div>

                        <!-- Middle Section: Project Details Table -->
                        <div class="mt-8">
                            <div class="border-2 border-black">
                                <!-- Headers -->
                                <div class="grid grid-cols-12 bg-gray-50 text-sm">
                                    <div class="col-span-3 p-2 text-center font-bold border-b-2 border-r-2 border-black">PO / Proposal Number</div>
                                    <div class="col-span-5 p-2 text-center font-bold border-b-2 border-r-2 border-black">Project Name</div>
                                    <div class="col-span-2 p-2 text-center font-bold border-b-2 border-r-2 border-black">Date Started</div>
                                    <div class="col-span-2 p-2 text-center font-bold border-b-2 border-black">Completion Date</div>
                                </div>
                                <!-- Dynamic Rows -->
                                <div id="project-rows-container">
                                    <template x-for="(row, index) in rows" :key="index">
                                        <div class="grid grid-cols-12 relative" :class="index < rows.length ? 'border-b border-black' : ''">
                                            <div class="col-span-3 border-r-2 border-black h-full">
                                                <textarea x-model="row.poNumber" placeholder="Enter PO/Proposal #"
                                                       class="table-cell-textarea"></textarea>
                                            </div>
                                            <div class="col-span-5 border-r-2 border-black h-full">
                                                <textarea x-model="row.projectName" placeholder="Enter project name"
                                                          class="table-cell-textarea"></textarea>
                                            </div>
                                            <div class="col-span-2 border-r-2 border-black h-full">
                                                <input type="date" x-model="row.dateStarted"
                                                       class="table-cell-input">
                                            </div>
                                            <div class="col-span-2 h-full">
                                                <input type="date" x-model="row.completionDate"
                                                       class="table-cell-input">
                                            </div>
                                            <!-- Remove Button for rows -->
                                            <button type="button" @click="removeRow(index)"
                                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                                title="Remove item" x-show="rows.length > 1">
                                                ×
                                            </button>
                                        </div>
                                    </template>
                                    <!-- To ensure at least one row's structure is visible visually if array is empty or to match excel look -->
                                     <template x-if="rows.length === 0">
                                        <div class="grid grid-cols-12 border-b border-black">
                                            <div class="col-span-3 p-2 border-r-2 border-black h-[60px]"></div>
                                            <div class="col-span-5 p-2 border-r-2 border-black h-[60px]"></div>
                                            <div class="col-span-2 p-2 border-r-2 border-black h-[60px]"></div>
                                            <div class="col-span-2 p-2 h-[60px]"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                             <!-- Add More Button -->
                            <div class="flex justify-end mt-3">
                                <button type="button"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-4 py-2 rounded font-semibold transition duration-300 text-xs uppercase"
                                    @click="addRow()">
                                    ➕ Add Project
                                </button>
                            </div>
                        </div>


                        <!-- Signature & Approval Section -->
                        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-10 pt-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700">Signature:</label>
                                <div class="mt-8 border-b-2 border-black h-6 w-full md:w-2/3"></div>
                            </div>
                            <div class="md:pt-0"> <!-- Removed pt-8 to align "Approved By" better if signature takes less vertical space -->
                                <label class="block text-xs font-bold uppercase text-gray-700">Approved by:</label>
                                <div class="mt-8 border-b-2 border-black h-6 w-full md:w-2/3"></div>
                                <p class="text-xs text-gray-800 font-semibold mt-1 text-center md:text-left" style="max-width: calc( (100% * 2/3) );">President</p>
                            </div>
                        </div>

                        <!-- Form Number Footer -->
                        <div class="mt-12 pt-8 border-t border-gray-200">
                            <p class="text-xs text-gray-600">Form No. : ADM-ACC-005</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-8">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Submit Report
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