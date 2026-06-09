<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OcrController extends Controller
{
    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:12288',
        ]);

        $file = $request->file('image');
        $tempPath = $file->store('ocr_temp', 'local');
        $absolutePath = storage_path('app/private/' . $tempPath);

        try {
            // Process image and extract structured data arrays
            $parsedData = $this->ocrService->readAndParseBarcode($absolutePath);

            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }

            if (!$parsedData) {
                return response()->json(['success' => false, 'message' => 'No barcode detected.'], 422);
            }

            // Write straight to the production conveyor logistics DB
            DB::table('poultry_packs')->insertOrIgnore([
                'full_barcode'     => $parsedData['full_barcode'],
                'gtin'             => $parsedData['gtin'],
                'best_before_date' => $parsedData['best_before_date'],
                'weight_kg'        => $parsedData['weight_kg'],
                'serial_number'    => $parsedData['serial_number'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return response()->json([
                'success' => true,
                'data'    => $parsedData
            ]);

        } catch (Exception $e) {
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
