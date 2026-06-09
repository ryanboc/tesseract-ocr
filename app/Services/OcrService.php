<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use TarfinLabs\ZbarPhp\Zbar; // <-- Update the import here
use Exception;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Extract standard text using Tesseract.
     */
    public function extractText(string $filePath, string $lang = 'eng'): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found at path: {$filePath}");
        }

        try {
            $ocr = new TesseractOCR($filePath);
            $ocr->lang($lang);
            $ocr->executable('/usr/bin/tesseract'); 
            return $ocr->run();
        } catch (Exception $e) {
            Log::error("OCR Processing Failed: " . $e->getMessage());
            throw new Exception("Failed to process image text.");
        }
    }

    /**
     * Extract Barcode data using TarfinLabs ZBar wrapper.
     */
    public function readBarcode(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found at path: {$filePath}");
        }

        try {
            // Instantiate the modern Zbar scanner
            $zbar = new Zbar($filePath);
            
            // Scan returns a collection or string array of discovered barcodes
            $barcodeText = $zbar->scan(); 

            // Wrap in an array if it finds something, or return empty array if null
            return $barcodeText ? [$barcodeText] : [];
            
        } catch (Exception $e) {
            Log::error("Barcode Scanning Failed: " . $e->getMessage());
            return []; 
        }
    }
}