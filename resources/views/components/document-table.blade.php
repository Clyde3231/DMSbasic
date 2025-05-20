<div class="p-6 relative">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-semibold mb-2">Document Management System</h1>
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox" />
                <span>Select All</span>
            </label>
        </div>

        <div class="flex items-center space-x-4">
            <div class="relative">
                <button
                    id="add-new-document-button"
                    class="bg-blue-700 text-white px-4 py-2 rounded shadow hover:bg-blue-800 flex items-center justify-between"
                >
                    ➕ Add New Document
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 ml-2">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 011.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div
                    id="document-type-dropdown"
                    class="absolute z-10 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden"
                >
                <ul class="py-2 text-sm text-gray-700">
            <li><a href="/documents/cash-advance" class="block px-4 py-2 hover:bg-gray-100">Cash Advance</a></li>
            <li><a href="/documents/reimbursement" class="block px-4 py-2 hover:bg-gray-100">Reimbursement</a></li>
            <li><a href="/documents/purchase-request" class="block px-4 py-2 hover:bg-gray-100">Purchase Request</a></li>
            <li><a href="/documents/commission-incentiverequest" class="block px-4 py-2 hover:bg-gray-100">Commission - Incentive Request</a></li>
            <li><a href="/documents/purchase-order" class="block px-4 py-2 hover:bg-gray-100">Purchase Order</a></li>
            <li><a href="/documents/delivery-receipt" class="block px-4 py-2 hover:bg-gray-100">Delivery Receipt</a></li>
            <li><a href="/documents/acknowledgement-receipt" class="block px-4 py-2 hover:bg-gray-100">Acknowledgement Receipt</a></li>
            <li><a href="/documents/delivery-checklist" class="block px-4 py-2 hover:bg-gray-100">Delivery Checklist</a></li>
        </ul>
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                    
                    </div>
                </div>
            </div>
            <input type="text" placeholder="Search" class="border px-3 py-2 rounded" />
            <button class="bg-blue-700 text-white px-4 py-2 rounded shadow hover:bg-blue-800">
                🔍 Filter
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b">
                <tr class="text-gray-600 font-semibold">
                    <th class="p-4"><input type="checkbox" /></th>
                    <th class="p-4">Document Name</th>
                    <th class="p-4">Recipient</th>
                    <th class="p-4">Date Created</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Modified</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr class="hover:bg-gray-50">
                    <td class="p-4"><input type="checkbox" /></td>
                    <td class="p-4">
                        <div class="flex items-center space-x-2">
                            <span>🔗</span>
                            <div>
                                <p class="font-medium">ADM-PCH-001 Purchase Request</p>
                                <span class="bg-orange-400 text-white px-2 py-1 rounded text-xs shadow">
                                    Purchase Request
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">Stiv Rogers</td>
                    <td class="p-4">1/03/2023</td>
                    <td class="p-4">
                        <span class="inline-flex items-center">
                            <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span> Draft
                        </span>
                    </td>
                    <td class="p-4 text-gray-500">2 hours ago</td>
                    <td class="p-4 space-x-2 text-lg">⬇️ ✏️ ⋮</td>
                </tr>
                </tbody>
        </table>
    </div>

    <script>
        const addNewDocumentButton = document.getElementById('add-new-document-button');
        const documentTypeDropdown = document.getElementById('document-type-dropdown');

        addNewDocumentButton.addEventListener('click', () => {
            documentTypeDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!addNewDocumentButton.contains(event.target) && !documentTypeDropdown.contains(event.target)) {
                documentTypeDropdown.classList.add('hidden');
            }
        });
    </script>
</div>
