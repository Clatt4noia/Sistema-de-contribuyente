<?php

namespace App\Domains\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TransportGuide;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransportGuideFileController extends Controller
{
    use AuthorizesRequests;

    public function xml(Request $request, TransportGuide $transportGuide)
    {
        $this->authorize('view', $transportGuide);
        abort_unless($request->hasValidSignature(), 403);

        $disk = config('greenter.storage.disk_xml_cdr', 'public');
        abort_if(! $transportGuide->xml_path || ! Storage::disk($disk)->exists($transportGuide->xml_path), 404);

        return Storage::disk($disk)->download($transportGuide->xml_path, $transportGuide->full_code . '.xml');
    }

    public function cdr(Request $request, TransportGuide $transportGuide)
    {
        $this->authorize('view', $transportGuide);
        abort_unless($request->hasValidSignature(), 403);

        $disk = config('greenter.storage.disk_xml_cdr', 'public');
        abort_if(! $transportGuide->cdr_path || ! Storage::disk($disk)->exists($transportGuide->cdr_path), 404);

        return Storage::disk($disk)->download($transportGuide->cdr_path, $transportGuide->full_code . '.zip');
    }

    public function pdf(Request $request, TransportGuide $transportGuide)
    {
        $this->authorize('view', $transportGuide);
        abort_unless($request->hasValidSignature(), 403);

        $disk = config('greenter.storage.disk_xml_cdr', 'public');
        abort_if(! $transportGuide->pdf_path || ! Storage::disk($disk)->exists($transportGuide->pdf_path), 404);

        return Storage::disk($disk)->download($transportGuide->pdf_path, $transportGuide->full_code . '.pdf');
    }
}
