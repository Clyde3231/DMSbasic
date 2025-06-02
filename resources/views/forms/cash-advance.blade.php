@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{ rows: [ { amount: '', details: '' } ] }" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cash Advance Form</title>
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
                <h1 class="text-xl font-semibold text-gray-800">Cash Advance Form</h1>
                <div class="flex space-x-2">
                    <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded hover:bg-[#FF8500] transition duration-300">Send</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Save</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Download</button>
                </div>
            </div>

            <div class="p-4 md:p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-6 md:p-10 shadow-xl">
                    <!-- Logo (Optional, as per original example) -->
                    <div class="flex justify-center mb-8">
                        {{-- <img src="https://via.placeholder.com/200x60?text=Company+Logo" alt="Logo" class="h-12 md:h-16" /> --}}
                        {{-- Placeholder for logo if any --}}
                    </div>

                    <form class="space-y-6">
                        <!-- Top Section: Employee Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NAME:</label>
                                <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">EMPLOYEE NUM</label>
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
                                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">REFERENCE NO.</label>
                                <input type="text" value="CA-00001" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly />
                            </div>
                        </div>

                        <!-- Middle Section: Amount/Details Table -->
                        <div class="mt-8">
                            <div class="border-2 border-black">
                                <!-- Headers -->
                                <div class="grid grid-cols-12 bg-gray-50">
                                    <div class="col-span-3 p-2 text-center font-bold border-b-2 border-r-2 border-black text-sm uppercase">AMOUNT</div>
                                    <div class="col-span-9 p-2 text-center font-bold border-b-2 border-black text-sm uppercase">DETAILS</div>
                                </div>
                                <!-- Dynamic Rows -->
                                <div id="details-rows-container">
                                    <template x-for="(row, index) in rows" :key="index">
                                        <div class="grid grid-cols-12 relative" :class="index < rows.length - 1 ? 'border-b-2 border-black' : ''">
                                            <div class="col-span-3 border-r-2 border-black">
                                                <textarea x-model="row.amount"
                                                       class="w-full p-2 border-0 focus:ring-0 min-h-[150px] md:min-h-[200px] resize-none align-top text-sm"
                                                       placeholder="Enter amount"></textarea>
                                            </div>
                                            <div class="col-span-9">
                                                <textarea x-model="row.details"
                                                       class="w-full p-2 border-0 focus:ring-0 min-h-[150px] md:min-h-[200px] resize-none align-top text-sm"
                                                       placeholder="Enter details"></textarea>
                                            </div>
                                            <!-- Remove Button for rows -->
                                            <template x-if="rows.length > 1">
                                                <button type="button" @click="rows.splice(index, 1)"
                                                    class="absolute top-1 right-1 bg-red-500 hover:bg-red-700 text-white font-bold text-[10px] rounded-full p-0 w-5 h-5 flex items-center justify-center leading-none transition duration-150"
                                                    title="Remove item">
                                                    ×
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- Add More Button -->
                            <div class="flex justify-end mt-3">
                                <button type="button"
                                    class="bg-[#2D73C5] hover:bg-[#214d91] text-white px-4 py-2 rounded font-semibold transition duration-300 text-xs uppercase"
                                    @click="rows.push({ amount: '', details: '' })">
                                    ➕ Add Item
                                </button>
                            </div>
                        </div>

                        <!-- Bottom Section: Signatures -->
                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Left Column -->
                            <div class="space-y-8">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Signature:</label>
                                    <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Released Date:</label>
                                    <input type="date" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Received By:</label>
                                    <input type="text" class="w-full border-0 border-b-2 border-black focus:ring-0 focus:border-blue-600 py-1 mt-4 text-sm" />
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="space-y-6">
                                <div class="pt-1">
                                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Noted By:</label>
                                    <div class="mt-4 border-b-2 border-black h-6"></div> <!-- Signature line -->
                                    <p class="text-xs text-gray-800 font-semibold mt-1">Department Head</p>
                                </div>
                                <div class="pt-1">
                                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Released By:</label>
                                     <div class="mt-4 border-b-2 border-black h-6"></div> <!-- Signature line -->
                                    <p class="text-xs text-gray-800 font-semibold mt-1">Accounting</p>
                                </div>
                                <div class="pt-1">
                                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Approved By:</label>
                                    <div class="mt-4 border-b-2 border-black h-6"></div> <!-- Signature line -->
                                    <p class="text-xs text-gray-800 font-semibold mt-1">President</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-8">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Submit
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