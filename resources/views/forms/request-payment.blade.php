@extends('layouts.app')

{{--Test Commen--}}

@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{}" x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Request for Payment Form</title>
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
        .form-checkbox {
            color: #3b82f6; /* blue-600 */
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
                <h1 class="text-xl font-semibold text-gray-800">Request for Payment</h1>
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
                                <input type="text" value="RFPF-00001" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm bg-gray-100" readonly />
                            </div>
                        </div>

                        <!-- Payee Information Section -->
                        <div class="border-2 border-black p-4 mt-8">
                            <p class="text-sm font-semibold text-gray-800 mb-4">Payee Information:</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Payee Name</label>
                                    <div class="col-span-12 md:col-span-9">
                                        <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Address</label>
                                    <div class="col-span-12 md:col-span-9">
                                        <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Phone/Email</label>
                                    <div class="col-span-12 md:col-span-9">
                                        <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 pt-1">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Bank Details</label>
                                    <div class="col-span-12 md:col-span-9 space-y-2">
                                        <div class="grid grid-cols-12 gap-x-2 items-center">
                                            <label class="col-span-12 sm:col-span-4 text-sm text-gray-600">Bank Name</label>
                                            <div class="col-span-12 sm:col-span-8">
                                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-12 gap-x-2 items-center">
                                            <label class="col-span-12 sm:col-span-4 text-sm text-gray-600">Account Number</label>
                                            <div class="col-span-12 sm:col-span-8">
                                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-12 gap-x-2 items-center">
                                            <label class="col-span-12 sm:col-span-4 text-sm text-gray-600">SWIFT/BIC Code</label>
                                            <div class="col-span-12 sm:col-span-8">
                                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-12 gap-x-2 items-center">
                                            <label class="col-span-12 sm:col-span-4 text-sm text-gray-600">IBAN</label>
                                            <div class="col-span-12 sm:col-span-8">
                                                <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details Section -->
                        <div class="border-2 border-black p-4 mt-8">
                            <p class="text-sm font-semibold text-gray-800 mb-4">Payment Details:</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Description</label>
                                    <div class="col-span-12 md:col-span-9">
                                        <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Amount</label>
                                    <div class="col-span-12 md:col-span-5">
                                        <input type="number" step="0.01" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Currency</label>
                                    <div class="col-span-12 md:col-span-5">
                                        <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Payment Method</label>
                                    <div class="col-span-12 md:col-span-9 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                                        <label class="flex items-center space-x-1 cursor-pointer">
                                            <input type="checkbox" class="form-checkbox h-4 w-4 border-gray-400 rounded focus:ring-blue-500">
                                            <span>Bank Transfer</span>
                                        </label>
                                        <label class="flex items-center space-x-1 cursor-pointer">
                                            <input type="checkbox" class="form-checkbox h-4 w-4 border-gray-400 rounded focus:ring-blue-500">
                                            <span>Cheque</span>
                                        </label>
                                        <label class="flex items-center space-x-1 cursor-pointer">
                                            <input type="checkbox" class="form-checkbox h-4 w-4 border-gray-400 rounded focus:ring-blue-500">
                                            <span>Cash</span>
                                        </label>
                                        <label class="flex items-center space-x-1 cursor-pointer">
                                            <input type="checkbox" class="form-checkbox h-4 w-4 border-gray-400 rounded focus:ring-blue-500">
                                            <span>Other</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-x-2 items-center">
                                    <label class="col-span-12 md:col-span-3 text-sm text-gray-700">Invoice / Ref No.</label>
                                    <div class="col-span-12 md:col-span-9">
                                        <input type="text" class="w-full border-0 border-b border-black focus:ring-0 focus:border-blue-600 py-1 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature Section -->
                        <div class="mt-10 space-y-8">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-700">Signature:</label>
                                <div class="mt-4 border-b-2 border-black h-6 md:w-2/3 lg:w-1/2"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-8 pt-2">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700">Approved by:</label>
                                    <div class="mt-4 border-b-2 border-black h-6"></div>
                                    <p class="text-xs text-gray-800 font-semibold mt-1 text-center">Accounting</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-700">Noted by:</label>
                                    <div class="mt-4 border-b-2 border-black h-6"></div>
                                    <p class="text-xs text-gray-800 font-semibold mt-1 text-center">President</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-8">
                            <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-3 px-6 rounded w-full transition duration-300 text-sm uppercase tracking-wider">
                                Submit Payment Request
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