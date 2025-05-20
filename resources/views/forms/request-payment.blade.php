@extends('layouts.app')
@section('content')
<!DOCTYPE html>
<html lang="en" x-data="{ rows: [ { date: '', receipt: '', description: '', amount: '' } ] }" x-cloak>
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
                <h1 class="text-xl font-semibold text-gray-800">Request for Payment Form</h1>
                <div class="flex space-x-2">
                    <button class="text-sm bg-[#FFA500] text-white px-4 py-2 rounded hover:bg-[#FF8500] transition duration-300">Send</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Save</button>
                    <button class="text-sm bg-[#808080] text-white px-4 py-2 rounded hover:bg-[#606060] transition duration-300">Download</button>
                </div>
            </div>

            <div class="p-6 bg-[#F0F0F0] min-h-full">
                <div class="mx-auto bg-white p-10 rounded shadow-md">
                    <div class="flex justify-center mb-6">
                        <img src="https://via.placeholder.com/200x60?text=Logo" alt="Logo" class="h-16" />
                    </div>

                    <form class="space-y-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">EMPLOYEE NAME:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">DEPARTMENT:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">DATE FILED:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">EMPLOYEE NUMBER:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>


                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">POSITION:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">REFERENCE NO.:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>


                        <label class="block text-gray-700 text-sm font-semibold mb-2">PAYEE INFORMATION</label>
                        <div class="grid grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">PAYEE NAME:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">ADDRESS:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">PHONE/EMAIL:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-6">
                            <div class="col-span-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">BANK DETAILS</label>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Bank Name:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Account Number:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">SWIFT/BIC Code:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                             <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">IBAN:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-6">
                            <div class="col-span-3">
                                 <label class="block text-gray-700 text-sm font-semibold mb-2">Payment Details</label>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Description:</label>
                                <textarea class="border rounded px-4 py-2 w-full focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" style="height: 100px; vertical-align: top; resize: none;"></textarea>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Amount:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                            <div>
                                 <label class="block text-gray-700 text-sm font-semibold mb-2">Currency:</label>
                                <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Payment Method:</label>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="bankTransfer" name="payment_method" value="bankTransfer" class="rounded focus:ring-2 focus:ring-[#2D73C5] h-5 w-5">
                                    <label for="bankTransfer" class="text-gray-700 text-sm">Bank Transfer</label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="cheque" name="payment_method" value="cheque" class="rounded focus:ring-2 focus:ring-[#2D73C5] h-5 w-5">
                                    <label for="cheque" class="text-gray-700 text-sm">Cheque</label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="cash" name="payment_method" value="cash" class="rounded focus:ring-2 focus:ring-[#2D73C5] h-5 w-5">
                                    <label for="cash" class="text-gray-700 text-sm">Cash</label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="other" name="payment_method" value="other" class="rounded focus:ring-2 focus:ring-[#2D73C5] h-5 w-5">
                                    <label for="other" class="text-gray-700 text-sm">Other</label>
                                </div>
                                <div class="flex-1">
                                    <label for="invoiceRef" class="block text-gray-700 text-sm font-semibold mb-2">Invoice / Ref No.:</label>
                                    <input type="text" id="invoiceRef"  class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Signature:</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Approved By (Accounting):</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Noted By (President):</label>
                            <input type="text" class="w-full border rounded px-4 py-2 focus:ring-2 focus:ring-[#2D73C5] focus:outline-none" />
                        </div>

                        <button type="submit" class="bg-[#2D73C5] hover:bg-[#214d91] text-white font-bold py-2 px-4 rounded w-full transition duration-300 text-sm">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="w-64 bg-[#1A3D8A] text-white p-6 flex-shrink-0">
            <h2 class="text-lg font-bold mb-4">Other Form Types</h2>
            <div class="space-y-4">
                <a class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left">Purchase Request</a>
                <a  class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left">Reimbursement</a>
                <a  class="block bg-[#20D760] hover:bg-[#16a046] text-white font-semibold px-4 py-2 rounded text-left">Request for Payment</a>
            </div>
        </div>

    </div>
</body>
</html>
@endsection
