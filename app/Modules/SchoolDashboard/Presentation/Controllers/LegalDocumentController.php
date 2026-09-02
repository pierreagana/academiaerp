<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\LegalDocuments\Domain\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegalDocumentController extends Controller
{
    public function index()
    {
        $documents = LegalDocument::withCount('signatures')->orderByDesc('created_at')->get();

        return view('SchoolDashboard::dashboard.legal_documents', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $path = $request->file('file')->store('legal_documents', 'public');

        LegalDocument::create([
            'school_id' => auth()->user()->school_id,
            'title' => $request->title,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document ajouté avec succès.');
    }

    public function destroy(int $id)
    {
        $document = LegalDocument::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document supprimé avec succès.');
    }
}
