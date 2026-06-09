<?php

namespace App\Services;

use TarfinLabs\ZbarPhp\Zbar;
use Carbon\Carbon;
use Exception;

class OcrService
{
    public function readAndParseBarcode(string $filePath): ?array
    {
        $zbar = new Zbar($filePath);
        $rawBarcode = $zbar->scan();

        if (!$rawBarcode) {
            return null;
        }

        // Standard GS1 string strings don't include the literal parentheses '()', 
        // they strip them into flat numeric sequences.
        // Example Raw: 019933320401034115280412310300160421000000014526
        
        // 1. Extract GTIN (01) - 14 digits after the '01' indicator
        $gtin = substr($rawBarcode, 2, 14);

        // 2. Extract Best Before (15) - 6 digits format YYMMDD
        $rawDate = substr($rawBarcode, 18, 6); 
        $bestBefore = Carbon::createFromFormat('Ymd', '20' . $rawDate)->format('Y-m-d');

        // 3. Extract Weight (3103) - 6 digits indicating weight in kg with 3 decimals
        $rawWeight = substr($rawBarcode, 28, 6); 
        $weightKg = (float)$rawWeight / 1000; // Transforms '001604' into 1.604

        // 4. Extract Serial No (21) - Pull the remaining variable length string elements
        // Your specific serial prefix sequence has an extended padding length
        $serialNumber = substr($rawBarcode, 36); 
        // Strip out leading string padding zeroes to leave the pure tracking index: 14526
        $serialClean = ltrim($serialNumber, '0'); 

        return [
            'full_barcode'     => $rawBarcode,
            'gtin'             => $gtin,
            'best_before_date' => $bestBefore,
            'weight_kg'        => $weightKg,
            'serial_number'    => $serialClean,
        ];
    }
}