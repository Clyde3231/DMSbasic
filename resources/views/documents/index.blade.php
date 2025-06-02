@extends('layouts.app') {{-- Or your main layout file --}}

@section('content')
<div class="flex h-screen bg-gray-100">
    <!-- Sidebar (from your screenshot) -->
    <div class="w-64 bg-blue-700 text-white p-6 space-y-6 flex-shrink-0">
        <div>
            <h2 class="text-xl font-semibold mb-1">Documents</h2>
            <a href="{{ route('documents.index') }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-600 {{ request()->fullUrlIs(route('documents.index')) && !request()->has('status') && !request()->has('document_type') ? 'bg-blue-800' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>All Documents</span>
            </a>
        </div>
        <div>
            <h3 class="text-lg font-medium mb-2">Filters</h3>
            <a href="{{ route('documents.index', ['status' => 'draft']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-600 {{ request('status') == 'draft' ? 'bg-blue-800' : '' }}">
                <span class="w-3 h-3 bg-yellow-400 rounded-full inline-block"></span>
                <span>Draft</span>
            </a>
            <a href="{{ route('documents.index', ['status' => 'sent']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-600 {{ request('status') == 'sent' ? 'bg-blue-800' : '' }}">
                <span class="w-3 h-3 bg-blue-400 rounded-full inline-block"></span>
                <span>Sent</span>
            </a>
            <a href="{{ route('documents.index', ['status' => 'signed']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-600 {{ request('status') == 'signed' ? 'bg-blue-800' : '' }}">
                <span class="w-3 h-3 bg-green-400 rounded-full inline-block"></span>
                <span>Signed</span>
            </a>
        </div>
        <div>
            <h3 class="text-lg font-medium mb-2">Tags</h3>
            {{-- You might want to fetch these dynamically or define them --}}
            @php
                $documentTypes = [
                    'purchase_request' => 'Purchase Request',
                    'pull_out_receipt' => 'Pull-Out Receipt',
                    'cash_advance' => 'Cash Advance',
                    'reimbursement' => 'Reimbursement',
                    // Add other types here
                ];
            @endphp
            @foreach($documentTypes as $typeKey => $typeName)
            <a href="{{ route('documents.index', ['document_type' => $typeKey]) }}"
               class="block px-3 py-1.5 mb-1 text-sm rounded hover:bg-blue-600
                      {{ request('document_type') == $typeKey ? 'bg-blue-800' : '' }}
                      {{ $typeKey == 'purchase_request' ? 'bg-orange-500 hover:bg-orange-600' :
                         ($typeKey == 'pull_out_receipt' ? 'bg-green-500 hover:bg-green-600' :
                         ($typeKey == 'reimbursement' ? 'bg-purple-500 hover:bg-purple-600' :
                         ($typeKey == 'cash_advance' ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-gray-500 hover:bg-gray-600'))) }}
                      text-white">
                {{ $typeName }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar (simulated, adjust to your app.blade.php structure) -->
        <div class="bg-lime-400 text-gray-800 px-6 py-3 flex justify-between items-center shadow">
            <div class="text-xl font-semibold">Document Management System</div>
            @auth
            <div class="text-right">
                <div class="font-medium">{{ Auth::user()->name ?? 'User Name' }}</div>
                <div class="text-xs">{{ Auth::user()->email ?? 'user@example.com' }}</div>
            </div>
            @endauth
        </div>

        <div class="flex-1 p-6 overflow-y-auto" x-data="documentTable()">
            <div class="bg-white shadow-md rounded-lg p-4 md:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="selectAll" x-model="selectAll" @change="toggleSelectAll" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                        <label for="selectAll" class="text-sm text-gray-700">Select All</label>
                        {{-- Bulk actions dropdown can go here later --}}
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div x-data="{ isOpen: false }" class="relative">
                            <button @click="isOpen = !isOpen" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add New Document
                                <svg class="w-4 h-4 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                            <div x-show="isOpen" @click.away="isOpen = false"
                                 class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 style="display: none;">
                                <div class="py-1">
                                    <a href="{{ route('forms.pullout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Material Pull-Out</a>
                                    <a href="{{ route('forms.purchase-request') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Purchase Request</a>
                                    <a href="{{ route('forms.cash-advance') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cash Advance</a>
                                    <a href="{{ route('forms.reimbursement') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Reimbursement</a>
                                    {{-- Add links to other named form routes here --}}
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('documents.index') }}" method="GET" class="flex items-center">
                            <input type="text" name="search" placeholder="Search" value="{{ request('search') }}" class="border border-gray-300 rounded-l-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded-r-md text-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </form>
                        <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter
                        </button>
                    </div>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Success</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                @if (session('info'))
                     <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Info</p>
                        <p>{{ session('info') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-10">
                                    {{-- Cell for Select All checkbox in header already handled above --}}
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Document Name
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Recipient
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Date Created
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Modified
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse ($documents as $document)
                                <tr x-data="{ selected: false }" class="hover:bg-gray-50">
                                    <td class="px-3 py-4 border-b border-gray-200 text-sm">
                                        <input type="checkbox" x-model="selectedRows[{{ $document->id }}]" @change="updateSelectAllCheckbox"
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 document-checkbox"
                                               value="{{ $document->id }}">
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                        <div class="flex items-center">
                                            {{-- Optional: Icon for document type --}}
                                            {{-- <svg class="w-5 h-5 mr-2 text-gray-400" ...></svg> --}}
                                            <div>
                                                <p class="text-gray-900 font-medium whitespace-no-wrap">{{ $document->document_name }}</p>
                                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                                    @switch($document->document_type)
                                                        @case('purchase_request') bg-orange-100 text-orange-700 @break
                                                        @case('pull_out_receipt') bg-green-100 text-green-700 @break
                                                        @case('reimbursement') bg-purple-100 text-purple-700 @break
                                                        @case('cash_advance') bg-yellow-100 text-yellow-700 @break
                                                        @default bg-gray-100 text-gray-700
                                                    @endswitch
                                                ">
                                                    {{ Str::title(str_replace('_', ' ', $document->document_type)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                        <p class="text-gray-700 whitespace-no-wrap">{{ $document->recipient ?? '-' }}</p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                        <p class="text-gray-700 whitespace-no-wrap">{{ $document->created_at->format('M d, Y') }}</p>
                                        <p class="text-gray-500 text-xs whitespace-no-wrap">{{ $document->created_at->format('h:i A') }}</p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full text-xs
                                            @switch($document->status)
                                                @case('draft') text-yellow-900 bg-yellow-200 @break
                                                @case('sent') text-blue-900 bg-blue-200 @break
                                                @case('signed') text-green-900 bg-green-200 @break
                                                @case('archived') text-gray-700 bg-gray-300 @break
                                                @default text-gray-700 bg-gray-200
                                            @endswitch
                                        ">
                                            {{ Str::title($document->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                        <p class="text-gray-700 whitespace-no-wrap">{{ $document->updated_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm whitespace-no-wrap">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('documents.show', $document) }}" title="View" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            @if($document->status == 'draft') {{-- Only allow edit for drafts --}}
                                            <a href="{{ route('documents.edit', $document) }}" title="Edit" class="text-yellow-600 hover:text-yellow-800">
                                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            @endif
                                            <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete" class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                            {{-- More actions dropdown ... --}}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 border-b border-gray-200 bg-white text-center text-gray-500">
                                        No documents found matching your criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $documents->appends(request()->query())->links() }} {{-- Preserve query strings for pagination --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function documentTable() {
    return {
        selectAll: false,
        selectedRows: {}, // Store selected row IDs here { id: true/false }
        toggleSelectAll(event) {
            const checkboxes = document.querySelectorAll('.document-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
                this.selectedRows[checkbox.value] = event.target.checked;
            });
        },
        updateSelectAllCheckbox() {
            const checkboxes = document.querySelectorAll('.document-checkbox');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            const someChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            if (allChecked) {
                this.selectAll = true;
            } else {
                this.selectAll = false;
            }
            // console.log(this.selectedRows); // For debugging
        },
        // Initialize selectedRows if needed, e.g., from URL or previous state
        init() {
            // If you need to pre-select rows based on some criteria
            document.querySelectorAll('.document-checkbox').forEach(cb => {
                if (this.selectedRows[cb.value] === undefined) {
                    this.selectedRows[cb.value] = false;
                }
            });
        }
    }
}
</script>
@endsection