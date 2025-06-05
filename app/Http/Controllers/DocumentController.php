<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request; // Make sure this is imported
use Illuminate\Support\Facades\View;
class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
// In DocumentController.php - index() method
public function index(Request $request)
{
    $query = Document::query();

    // Filter by current user (IMPORTANT if not already doing this globally)
    // $query->where('user_id', auth()->id());

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    // Filter by document_type (tag)
    if ($request->filled('document_type')) {
        $query->where('document_type', $request->input('document_type'));
    }

    // Search
    if ($request->filled('search')) {
        $searchTerm = $request->input('search');
        $query->where(function($q) use ($searchTerm) {
            $q->where('document_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('recipient', 'LIKE', "%{$searchTerm}%");
            // Add more fields to search if needed
            // Searching JSON 'data' column content can be more complex and DB-dependent
        });
    }

    $documents = $query->latest()->paginate(15)->appends($request->query());

    return view('documents.index', compact('documents'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // We won't use a generic create form.
        // Users will go directly to specific form types like /forms/pullout
        // So, we can redirect or leave this empty for now.
        return redirect()->route('documents.index')->with('info', 'Please select a document type to create.');
    }   

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'document_name' => 'required|string|max:255',
            // Add 'reimbursement' to the 'in' validation rule
            'document_type' => 'required|string|in:pull_out_receipt,purchase_request,cash_advance,reimbursement',
            'recipient' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,sent,signed,archived', // Added archived
            'data' => 'required|array',
        ]);

        $document = new Document();
        $document->user_id = auth()->id(); // Assign the currently logged-in user's ID
        $document->document_name = $validatedData['document_name'];
        $document->document_type = $validatedData['document_type'];
        $document->recipient = $validatedData['recipient'] ?? null;
        $document->status = $validatedData['status'] ?? 'draft'; // Use provided status or default to 'draft'
        $document->data = $validatedData['data']; // Eloquent will cast this array to JSON

        $document->save();

        // For AJAX requests, return a JSON response
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Document saved successfully!',
                'document' => $document
            ], 201);
        }

        // For traditional form submissions, redirect
        return redirect()->route('documents.index')->with('success', 'Document saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    // In app/Http/Controllers/DocumentController.php - show() method

public function show(Document $document)
{
    $documentType = $document->document_type;
    $formData = $document->data;

    // dd($document->toArray(), $formData, $documentType); // First check: Is document and its data loaded?

    $viewName = match ($documentType) {
        'pull_out_receipt' => 'forms.pullout',
        'purchase_request' => 'forms.purchase-request',
        'cash_advance' => 'forms.cash-advance',
        'reimbursement' => 'forms.reimbursement',
        // ... other cases
        default => null,
    };

    // dd($viewName, View::exists($viewName)); // Second check: Is viewName correct and does the view exist?

    if (!$viewName || !View::exists($viewName)) {
        abort(404, "Preview template for document type '{$documentType}' not found.");
    }

    $dataToPass = [
        'documentData' => $formData,
        'documentRecord' => $document,
        'isPreviewMode' => true,
    ];

    // dd($dataToPass); // Third check: Is the data being prepared for the view correctly?

    return view($viewName, $dataToPass);
}

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(Document $document) // Route Model Binding
    {
        // Optional: Add authorization logic (e.g., only owner or admin can edit)
        // if ($document->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
        //     abort(403, 'Unauthorized action.');
        // }

        // Optional: Only allow editing if the document is in a specific status (e.g., 'draft')
        // if ($document->status !== 'draft') {
        //     return redirect()->route('documents.show', $document)->with('error', 'This document cannot be edited as it is not a draft.');
        // }

        $documentType = $document->document_type;
        $formData = $document->data; // Already an array due to $casts

        $viewName = match ($documentType) {
            'pull_out_receipt' => 'forms.pullout',
            'purchase_request' => 'forms.purchase-request',
            'cash_advance' => 'forms.cash-advance',
            'reimbursement' => 'forms.reimbursement',
            // Add all your document types
            default => null,
        };

        if (!$viewName || !View::exists($viewName)) {
            Log::warning("Preview/Show template for document type '{$documentType}' (ID: {$document->id}) not found or not mapped.");
            abort(404, "Display view for document type '{$documentType}' is not configured.");
        }

        return view($viewName, [
            'documentData'   => $formData,
            'documentRecord' => $document,
            'isPreviewMode'  => false,
            'isEditMode'     => true,
        ]);
    }

// In app/Http/Controllers/DocumentController.php
// In app/Http/Controllers/DocumentController.php
public function updateStatus(Request $request, Document $document)
{
    $validated = $request->validate([
        'status' => 'required|string|in:draft,sent,signed,archived', // Add all your valid statuses
    ]);

    // Optional: Add authorization logic here if needed

    $document->status = $validated['status'];
    $document->save();

    return response()->json([
        'message' => 'Document status updated successfully!',
        'new_status' => $document->status,
        'document' => $document->fresh() // Send back the updated document
    ]);
}
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
{
    $validatedData = $request->validate([
        'document_name' => 'sometimes|required|string|max:255', // 'sometimes' means validate only if present
        // 'document_type' => 'sometimes|required|string|in:[...]', // If type can be changed
        'recipient' => 'nullable|string|max:255', // Keep nullable if it can be emptied
        'status' => 'sometimes|string|in:draft,sent,signed', // If status can be changed
        'data' => 'sometimes|required|array',
    ]);

    // Update only if fields are present in the request
    if ($request->filled('document_name')) $document->document_name = $validatedData['document_name'];
    // if ($request->filled('document_type')) $document->document_type = $validatedData['document_type'];
    if ($request->has('recipient')) $document->recipient = $validatedData['recipient']; // Use has() if it can be set to null
    if ($request->filled('status')) $document->status = $validatedData['status'];
    if ($request->filled('data')) $document->data = $validatedData['data'];

    $document->save();

    if ($request->wantsJson()) {
        return response()->json([
            'message' => 'Document updated successfully!',
            'document' => $document // Send back the updated document
        ]);
    }
    return redirect()->route('documents.show', $document)->with('success', 'Document updated successfully!');
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        // Optionally, delete associated files (e.g., $document->file_path)
        // Storage::delete($document->file_path);

        $document->delete();

        // For AJAX requests from the dashboard
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Document deleted successfully!']);
        }

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully!');
    }
}


