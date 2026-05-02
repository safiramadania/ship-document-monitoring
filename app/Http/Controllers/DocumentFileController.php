<?php

namespace App\Http\Controllers;

use App\Models\VesselDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends Controller
{
    public function preview(Request $request, VesselDocument $vesselDocument): BinaryFileResponse
    {
        $this->authorizeDocumentAccess($request, $vesselDocument);
        $this->ensureStoredFileExists($vesselDocument);

        return response()->file(Storage::disk('local')->path($vesselDocument->file_path), [
            'Content-Type' => $vesselDocument->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function download(Request $request, VesselDocument $vesselDocument): StreamedResponse
    {
        $this->authorizeDocumentAccess($request, $vesselDocument);
        $this->ensureStoredFileExists($vesselDocument);

        return Storage::disk('local')->download(
            $vesselDocument->file_path,
            $vesselDocument->original_filename ?: 'document'
        );
    }

    private function authorizeDocumentAccess(Request $request, VesselDocument $vesselDocument): void
    {
        $vesselDocument->loadMissing('vessel');

        abort_unless($request->user()->canAccessBranch($vesselDocument->vessel->branch_id), 403);
    }

    private function ensureStoredFileExists(VesselDocument $vesselDocument): void
    {
        abort_unless(
            filled($vesselDocument->file_path) && Storage::disk('local')->exists($vesselDocument->file_path),
            404
        );
    }
}
