<?php

namespace App\Http\Controllers;

use App\Models\TransportGuide;
use App\Models\TransportGuideItem;
use App\Models\Company;
use App\Services\Sunat\DispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DispatchController extends Controller
{
    protected $dispatchService;

    public function __construct(DispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    // POST /gre
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'series' => 'required|string|size:4',
            'correlative' => 'required|integer',
            'issue_date' => 'required|date',
            'remitente_ruc' => 'required|exists:companies,ruc', // Debe existir config
            'destinatario_document_number' => 'required',
            'destinatario_name' => 'required',
            'items' => 'required|array',
            'items.*.description' => 'required',
            // ... más validaciones
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $guide = TransportGuide::create([
                'type' => 'remitente',
                'series' => $request->series,
                'correlative' => $request->correlative,
                'issue_date' => $request->issue_date,
                'issue_time' => $request->input('issue_time', now()->format('H:i:s')),
                'remitente_ruc' => $request->remitente_ruc,
                'destinatario_document_number' => $request->destinatario_document_number,
                'destinatario_name' => $request->destinatario_name,
                'transfer_reason_code' => $request->input('transfer_reason_code', '01'),
                'gross_weight' => $request->input('gross_weight', 1),
                'origin_ubigeo' => $request->input('origin_ubigeo', '150101'),
                'origin_address' => $request->input('origin_address', '-'),
                'destination_ubigeo' => $request->input('destination_ubigeo', '150101'),
                'destination_address' => $request->input('destination_address', '-'),
                'transport_mode_code' => $request->input('transport_mode_code', '01'), 
                'sunat_status' => 'draft',
                // Completar mapeo de campos obligatorios según modelo
            ]);

            foreach ($request->items as $item) {
                $guide->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_of_measure' => $item['unit'] ?? 'NIU',
                    'weight' => 0, // Default
                ]);
            }

            return response()->json([
                'message' => 'Guía creada',
                'id' => $guide->id
            ], 201);
        });
    }

    // POST /gre/{id}/firmar
    public function firmar($id)
    {
        $guide = TransportGuide::with(['items'])->findOrFail($id);
        
        try {
            $this->dispatchService->createAndSign($guide);
            return response()->json([
                'message' => 'Documento firmado correctamente',
                'xml_path' => $guide->xml_path
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /gre/{id}/enviar
    public function enviar($id)
    {
        $guide = TransportGuide::with(['items'])->findOrFail($id);

        try {
            $result = $this->dispatchService->send($guide);
            return response()->json([
                'success' => $result->isSuccess(),
                'sunat_status' => $guide->sunat_status,
                'sunat_notes' => $guide->sunat_notes
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
