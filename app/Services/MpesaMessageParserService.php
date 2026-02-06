<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

class MpesaMessageParserService
{
    /**
     * Parses a raw M-Pesa message string to extract key payment details.
     *
     * @param string $message
     * @return array|null
     */
    public function parse(string $message): ?array
    {
        // Normalize the message to handle different whitespace variations
        $normalizedMessage = preg_replace('/\s+/', ' ', trim($message));

        $patterns = [
            'default' => '/^([A-Z0-9]+)\s+Confirmed\.\s+Ksh([\d,]+\.\d{2})\s+sent\s+to\s+.+?\s+on\s+(\d{1,2}\/\d{1,2}\/\d{2,4})\s+at\s+(\d{1,2}:\d{2}\s?[AP]M)\./i'
        ];

        $matchedPattern = null;
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $normalizedMessage, $matches)) {
                $matchedPattern = $key;
                break;
            }
        }

        if (!$matchedPattern) {
            return null; // Or throw a custom exception
        }

        try {
            // Extract and clean the data
            $transId = $matches[1];
            $amount = (float) str_replace(',', '', $matches[2]);
            
            $datePart = $matches[3];
            $timePart = strtoupper(str_replace(' ', '', $matches[4]));
            $dateTime = $datePart . ' ' . $timePart;
            $transTime = Carbon::createFromFormat('j/n/y h:iA', $dateTime);

            return [
                'trans_id' => $transId,
                'amount' => $amount,
                'trans_time' => $transTime,
                'raw_text' => $message,
            ];
        } catch (Exception $e) {
            // Log error if needed
            return null;
        }
    }
}
