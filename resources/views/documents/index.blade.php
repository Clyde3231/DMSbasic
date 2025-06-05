@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-blue-800 text-white p-6 space-y-6 flex-shrink-0 overflow-y-auto">
        <div>
            <h2 class="text-xl font-semibold mb-1">Documents</h2>
            <a href="{{ route('documents.index') }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-700 {{ request()->fullUrlIs(route('documents.index')) && !request()->has('status') && !request()->has('document_type') ? 'bg-blue-900 font-semibold' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>All Documents</span>
            </a>
        </div>
        <div>
            <h3 class="text-lg font-medium mb-2">Filters</h3>
            <a href="{{ route('documents.index', ['status' => 'draft']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-700 {{ request('status') == 'draft' ? 'bg-blue-900 font-semibold' : '' }}">
                <span class="w-3 h-3 bg-yellow-400 rounded-full inline-block"></span>
                <span>Draft</span>
            </a>
            <a href="{{ route('documents.index', ['status' => 'sent']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-700 {{ request('status') == 'sent' ? 'bg-blue-900 font-semibold' : '' }}">
                <span class="w-3 h-3 bg-blue-400 rounded-full inline-block"></span>
                <span>Sent</span>
            </a>
            <a href="{{ route('documents.index', ['status' => 'signed']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-700 {{ request('status') == 'signed' ? 'bg-blue-900 font-semibold' : '' }}">
                <span class="w-3 h-3 bg-green-400 rounded-full inline-block"></span>
                <span>Signed</span>
            </a>
            {{-- You can add an "Archived" filter here too if needed --}}
            <a href="{{ route('documents.index', ['status' => 'archived']) }}" class="flex items-center space-x-2 px-2 py-1.5 rounded hover:bg-blue-700 {{ request('status') == 'archived' ? 'bg-blue-900 font-semibold' : '' }}">
                <span class="w-3 h-3 bg-gray-400 rounded-full inline-block"></span>
                <span>Archived</span>
            </a>
        </div>
        <div>
            <h3 class="text-lg font-medium mb-2">Tags</h3>
            @php
                // This array should be the single source of truth for document types and their display
                $documentTypesForDisplay = [
                    'cash-advance' => ['name' => 'Cash Advance', 'color' => 'bg-yellow-500 hover:bg-yellow-600'],
                    'reimbursement' => ['name' => 'Reimbursement', 'color' => 'bg-purple-500 hover:bg-purple-600'],
                    'request-payment' => ['name' => 'Request For Payment', 'color' => 'bg-indigo-500 hover:bg-indigo-600'],
                    'commission-incentiverequest' => ['name' => 'Commission - Incentive', 'color' => 'bg-pink-500 hover:bg-pink-600'],
                    'purchase-request' => ['name' => 'Purchase Request', 'color' => 'bg-orange-500 hover:bg-orange-600'],
                    'purchase-order' => ['name' => 'Purchase Order', 'color' => 'bg-teal-500 hover:bg-teal-600'],
                    'delivery-receipt' => ['name' => 'Delivery Receipt', 'color' => 'bg-cyan-500 hover:bg-cyan-600'],
                    'acknowledgement-receipt' => ['name' => 'Acknowledgement Receipt', 'color' => 'bg-lime-500 hover:bg-lime-600'],
                    'delivery-checklist' => ['name' => 'Delivery Checklist', 'color' => 'bg-red-500 hover:bg-red-600'], // Example new color
                    'te-acknowledgement' => ['name' => 'Tools & Equipment', 'color' => 'bg-sky-500 hover:bg-sky-600'], // Example new color
                    'pullout' => ['name' => 'Pull-Out Receipt', 'color' => 'bg-green-500 hover:bg-green-600'],
                    'borrow-receipt' => ['name' => 'Borrow Receipt', 'color' => 'bg-fuchsia-500 hover:bg-fuchsia-600'],
                ];
            @endphp
            @foreach($documentTypesForDisplay as $typeKey => $typeInfo)
            <a href="{{ route('documents.index', ['document_type' => $typeKey]) }}"
               class="block px-3 py-2 mb-1 text-sm rounded text-white text-center font-medium
                      {{ $typeInfo['color'] }}
                      {{ request('document_type') == $typeKey ? 'ring-2 ring-offset-2 ring-offset-blue-800 ring-white' : '' }}">
                {{ $typeInfo['name'] }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <div class="bg-lime-400 text-gray-800 px-6 py-3 flex justify-between items-center shadow">
            <div class="text-xl font-semibold">Document Management System</div>
            @auth
            <div class="text-right">
                <div class="font-medium">{{ Auth::user()->name ?? 'User Name' }}</div>
                <div class="text-xs">{{ Auth::user()->email ?? 'user@example.com' }}</div>
            </div>
            @endauth
        </div>

        <div class="flex-1 p-6 overflow-y-auto" x-data="documentDashboard()" x-init="init()">
            <div class="bg-white shadow-md rounded-lg p-4 md:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="selectAll" x-model="selectAll" @change="toggleSelectAll($event)" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                        <label for="selectAll" class="text-sm text-gray-700">Select All</label>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div x-data="{ isOpen: false }" class="relative">
                            <button @click="isOpen = !isOpen" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add New Document
                                <svg class="w-4 h-4 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                            <div x-show="isOpen" @click.away="isOpen = false" class="absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 max-h-80 overflow-y-auto" x-transition style="display: none;">
                                <div class="py-1">
                                    {{-- Using $documentTypesForDisplay for the Add New dropdown as well --}}
                                    @foreach($documentTypesForDisplay as $routeKey => $typeInfo)
                                        @if(Route::has('forms.'.$routeKey)) {{-- Assuming route name suffix matches $typeKey --}}
                                            <a href="{{ route('forms.'.$routeKey) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ $typeInfo['name'] }}</a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('documents.index') }}" method="GET" class="flex items-center">
                            <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" class="border border-gray-300 rounded-l-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-40 sm:w-auto">
                            <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded-r-md text-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </form>
                        {{-- Filter Button (can be enhanced later) --}}
                        {{-- <button class="bg-gray-200 ...">Filter</button> --}}
                    </div>
                </div>

                @if (session('success')) <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p class="font-bold">Success</p><p>{{ session('success') }}</p></div> @endif
                @if (session('error')) <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p class="font-bold">Error</p><p>{{ session('error') }}</p></div> @endif
                @if (session('info')) <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert"><p class="font-bold">Info</p><p>{{ session('info') }}</p></div> @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-10"></th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Document Name</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Recipient</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Created</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Modified</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                              @forelse ($documents as $document)
                        {{-- Each row gets its own Alpine component instance --}}
                        <tr x-data="documentRow({{ $document->id }}, '{{ $document->status }}')" class="hover:bg-gray-50">


                            <td class="px-3 py-4 border-b border-gray-200 text-sm">
                                {{-- Checkbox now dispatches an event on change --}}


                                <input type="checkbox"
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 document-checkbox"
                                       :value="docId"
                                       :checked="$root.selectedRows[docId]" {{-- Read state from root component's selectedRows --}}
                                       @change="dispatchSelectionChange($event)"> {{-- Dispatch event on change --}}
                            </td>

                            <td class="px-5 py-4 border-b border-gray-200 text-sm">
                                <div class="flex items-center">
                                    <div>
                                        <p class="text-gray-900 font-medium whitespace-no-wrap">{{ $document->document_name }}</p>
                                        {{-- Document Type Tag (using $documentTypesForDisplay from @php block) --}}
                                        @if(isset($documentTypesForDisplay[$document->document_type]))
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ explode(' ', $documentTypesForDisplay[$document->document_type]['color'])[0] }} {{ str_replace('hover:', 'text-', explode(' ', $documentTypesForDisplay[$document->document_type]['color'])[1] ?? 'text-gray-800') }} opacity-90">
                                                 {{ $documentTypesForDisplay[$document->document_type]['name'] }}
                                            </span>
                                        @else
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-gray-200 text-gray-700">{{ Str::title(str_replace('_', ' ', $document->document_type)) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm"><p class="text-gray-700 whitespace-no-wrap">{{ $document->recipient ?? '-' }}</p></td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm"><p class="text-gray-700 whitespace-no-wrap">{{ $document->created_at->format('M d, Y') }}</p><p class="text-gray-500 text-xs">{{ $document->created_at->format('h:i A') }}</p></td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm">

                                        
                                        <div class="relative" @click.away="showStatusDropdown = false">
                                            <button @click="showStatusDropdown = !showStatusDropdown" :disabled="isUpdatingStatus"
                                                    class="inline-flex items-center justify-center px-3 py-1 font-semibold leading-tight rounded-full text-xs focus:outline-none transition-colors duration-150"
                                                    :class="{
                                                        'text-yellow-900 bg-yellow-200 hover:bg-yellow-300': currentStatus === 'draft',
                                                        'text-blue-900 bg-blue-200 hover:bg-blue-300': currentStatus === 'sent',
                                                        'text-green-900 bg-green-200 hover:bg-green-300': currentStatus === 'signed',
                                                        'text-gray-700 bg-gray-300 hover:bg-gray-400': currentStatus === 'archived',
                                                        'text-gray-700 bg-gray-200 hover:bg-gray-300': !['draft', 'sent', 'signed', 'archived'].includes(currentStatus),
                                                        'opacity-50 cursor-wait': isUpdatingStatus
                                                    }">
                                                <span x-text="isUpdatingStatus ? '...' : (currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1))"></span>
                                                <svg class="w-3 h-3 ml-1 fill-current transform transition-transform duration-200" :class="{'rotate-180': showStatusDropdown}" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                                            </button>
                                            <div x-show="showStatusDropdown"
                                                 class="absolute z-20 mt-1 w-36 bg-white rounded-md shadow-lg border border-gray-200 origin-top-right right-0 sm:left-0 sm:origin-top-left"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 transform scale-95"
                                                 x-transition:enter-end="opacity-100 transform scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 transform scale-100"
                                                 x-transition:leave-end="opacity-0 transform scale-95"
                                                 style="display:none;">
                                                <a @click="updateThisRowStatus('draft')" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Draft</a>
                                                <a @click="updateThisRowStatus('sent')" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sent</a>
                                                <a @click="updateThisRowStatus('signed')" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Signed</a>
                                                <a @click="updateThisRowStatus('archived')" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Archived</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm"><p class="text-gray-700 whitespace-no-wrap">{{ $document->updated_at->diffForHumans() }}</p></td>
                                    <td class="px-5 py-4 border-b border-gray-200 text-sm whitespace-no-wrap">
                                        <div class="flex items-center space-x-3">
                                            <button @click="$parent.downloadDocument(docId, '{{ $document->document_type }}', '{{ addslashes($document->document_name) }}')" title="Download" class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </button>
                                         
                                            <a href="{{ route('documents.edit', $document) }}" title="Edit" class="text-yellow-600 hover:text-yellow-800 p-1 rounded-full hover:bg-yellow-100">
                                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            
                                            <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?');" class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete" class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 border-b border-gray-200 bg-white text-center text-gray-500">No documents found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $documents->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
</div>


@endsection