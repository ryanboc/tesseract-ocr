<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class OcrController extends Controller
{
    protected $ocrService;

    // Laravel 13 automatically resolves and injects your custom service here
    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:12288',
        ]);

        $file = $request->file('image');

        // Store file temporarily
        $tempPath = $file->store('ocr_temp', 'local');
        $absolutePath = storage_path('app/private/' . $tempPath); 

        try {
            // Scan only the barcode
            $barcodes = $this->ocrService->readBarcode($absolutePath);

            // Clean up the temp image
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }

            // Return strictly the barcode array
            return response()->json([
                'barcodes' => $barcodes,
            ]);

        } catch (Exception $e) {
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }

            return response()->json([
                'message' => 'Processing failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
