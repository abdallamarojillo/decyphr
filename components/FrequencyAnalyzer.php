<?php

namespace app\components;

use yii\base\Component;

class FrequencyAnalyzer extends Component
{
    // English language letter frequencies (%)
    const ENGLISH_FREQUENCIES = [
        'E' => 12.70, 'T' => 9.06, 'A' => 8.17, 'O' => 7.51, 'I' => 6.97,
        'N' => 6.75, 'S' => 6.33, 'H' => 6.09, 'R' => 5.99, 'D' => 4.25,
        'L' => 4.03, 'C' => 2.78, 'U' => 2.76, 'M' => 2.41, 'W' => 2.36,
        'F' => 2.23, 'G' => 2.02, 'Y' => 1.97, 'P' => 1.93, 'B' => 1.29,
        'V' => 0.98, 'K' => 0.77, 'J' => 0.15, 'X' => 0.15, 'Q' => 0.10, 'Z' => 0.07
    ];

    /**
     * Analyze character frequencies in the text
     */
    public function analyzeCharacterFrequency($text)
    {
        $text = strtoupper($text);
        $length = strlen($text);
        $frequencies = [];
        $letterCount = 0;

        // Count each character
        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $frequencies[$char] = ($frequencies[$char] ?? 0) + 1;
                $letterCount++;
            }
        }

        // Convert to percentages
        foreach ($frequencies as $char => $count) {
            $frequencies[$char] = round(($count / $letterCount) * 100, 2);
        }

        // Sort by frequency (descending)
        arsort($frequencies);

        return $frequencies;
    }

    /**
     * Analyze bigram (2-letter combination) frequencies
     */
    public function analyzeBigramFrequency($text)
    {
        $text = strtoupper(preg_replace('/[^A-Za-z]/', '', $text));
        $length = strlen($text);
        $bigrams = [];
        $totalBigrams = 0;

        for ($i = 0; $i < $length - 1; $i++) {
            $bigram = substr($text, $i, 2);
            $bigrams[$bigram] = ($bigrams[$bigram] ?? 0) + 1;
            $totalBigrams++;
        }

        // Convert to percentages and get top 20
        foreach ($bigrams as $bigram => $count) {
            $bigrams[$bigram] = round(($count / $totalBigrams) * 100, 2);
        }

        arsort($bigrams);
        return array_slice($bigrams, 0, 20, true);
    }

    /**
     * Analyze trigram (3-letter combination) frequencies
     */
    public function analyzeTrigramFrequency($text)
    {
        $text = strtoupper(preg_replace('/[^A-Za-z]/', '', $text));
        $length = strlen($text);
        $trigrams = [];
        $totalTrigrams = 0;

        for ($i = 0; $i < $length - 2; $i++) {
            $trigram = substr($text, $i, 3);
            $trigrams[$trigram] = ($trigrams[$trigram] ?? 0) + 1;
            $totalTrigrams++;
        }

        // Convert to percentages and get top 20
        foreach ($trigrams as $trigram => $count) {
            $trigrams[$trigram] = round(($count / $totalTrigrams) * 100, 2);
        }

        arsort($trigrams);
        return array_slice($trigrams, 0, 20, true);
    }

    /**
     * Calculate Index of Coincidence
     * IC close to 0.065 suggests monoalphabetic substitution
     * IC close to 0.038 suggests polyalphabetic or random
     */
    public function calculateIndexOfCoincidence($text)
    {
        $text = strtoupper(preg_replace('/[^A-Za-z]/', '', $text));
        $length = strlen($text);
        
        if ($length < 2) {
            return 0;
        }

        $frequencies = [];
        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            $frequencies[$char] = ($frequencies[$char] ?? 0) + 1;
        }

        $sum = 0;
        foreach ($frequencies as $count) {
            $sum += $count * ($count - 1);
        }

        $ic = $sum / ($length * ($length - 1));
        return round($ic, 6);
    }

    /**
     * Calculate Shannon entropy
     * Higher entropy = more random/encrypted
     * Lower entropy = more structured/plaintext
     */
    public function calculateEntropy($text)
    {
        $length = strlen($text);
        if ($length == 0) {
            return 0;
        }

        $frequencies = [];
        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            $frequencies[$char] = ($frequencies[$char] ?? 0) + 1;
        }

        $entropy = 0;
        foreach ($frequencies as $count) {
            $probability = $count / $length;
            $entropy -= $probability * log($probability, 2);
        }

        return round($entropy, 6);
    }

    /**
     * Suggest cipher type based on statistical analysis
     */
    public function suggestCipherType($text)
    {
        $ic = $this->calculateIndexOfCoincidence($text);
        $entropy = $this->calculateEntropy($text);
        
        // Check for Base64
        if (preg_match('/^[A-Za-z0-9+\/]+=*$/', trim($text))) {
            return 'Base64 Encoding';
        }

        // Check for Hexadecimal
        if (preg_match('/^[0-9A-Fa-f]+$/', trim($text))) {
            return 'Hexadecimal Encoding';
        }

        // Check IC for cipher type
        if ($ic >= 0.055 && $ic <= 0.085) {
            return 'Monoalphabetic Substitution (e.g., Caesar, Simple Substitution)';
        } elseif ($ic >= 0.035 && $ic <= 0.045) {
            return 'Polyalphabetic Cipher (e.g., Vigenère, Playfair)';
        } elseif ($ic < 0.035) {
            return 'Strong Encryption or Random Data';
        }

        // Check entropy
        if ($entropy > 7.5) {
            return 'Strong Encryption or Compressed Data';
        } elseif ($entropy < 4.0) {
            return 'Simple Encoding or Structured Data';
        }

        return 'Unknown Cipher Type';
    }

    /**
     * Attempt Caesar cipher decryption
     */
    public function breakCaesarCipher($text)
    {
        $results = [];
        $text = strtoupper($text);

        for ($shift = 1; $shift < 26; $shift++) {
            $decrypted = '';
            $length = strlen($text);

            for ($i = 0; $i < $length; $i++) {
                $char = $text[$i];
                if (ctype_alpha($char)) {
                    $charCode = ord($char);
                    $newCharCode = (($charCode - 65 - $shift + 26) % 26) + 65;
                    $decrypted .= chr($newCharCode);
                } else {
                    $decrypted .= $char;
                }
            }

            // Calculate chi-squared score against English frequencies
            $score = $this->calculateChiSquared($decrypted);
            $results[] = [
                'shift' => $shift,
                'text' => $decrypted,
                'score' => $score
            ];
        }

        // Sort by score (lower is better)
        usort($results, function($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        return array_slice($results, 0, 5); // Return top 5 candidates
    }

    /**
     * Calculate chi-squared statistic against English letter frequencies
     */
    private function calculateChiSquared($text)
    {
        $observed = $this->analyzeCharacterFrequency($text);
        $chiSquared = 0;

        foreach (self::ENGLISH_FREQUENCIES as $letter => $expected) {
            $obs = $observed[$letter] ?? 0;
            if ($expected > 0) {
                $chiSquared += pow($obs - $expected, 2) / $expected;
            }
        }

        return round($chiSquared, 2);
    }

    /**
     * Detect XOR encryption patterns
     */
    public function detectXORPattern($text)
    {
        $bytes = unpack('C*', $text);
        if (!$bytes) {
            return null;
        }

        $patterns = [];
        
        // Try single-byte XOR keys
        for ($key = 1; $key < 256; $key++) {
            $decoded = '';
            $printableCount = 0;
            
            foreach ($bytes as $byte) {
                $decodedByte = $byte ^ $key;
                $decoded .= chr($decodedByte);
                if ($decodedByte >= 32 && $decodedByte <= 126) {
                    $printableCount++;
                }
            }

            $printableRatio = $printableCount / count($bytes);
            
            if ($printableRatio > 0.8) {
                $patterns[] = [
                    'key' => $key,
                    'key_hex' => dechex($key),
                    'decoded' => $decoded,
                    'printable_ratio' => round($printableRatio, 2)
                ];
            }
        }

        return $patterns;
    }
}
