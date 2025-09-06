<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class EmbroideryApiService
{
    private string $apiBaseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.embroidery_api.url');
        $this->apiKey = config('services.embroidery_api.key');
        $this->timeout = config('services.embroidery_api.timeout', 300); // 5 minutes
    }

    /**
     * Convert an embroidery file to another format
     */
    public function convertFile(UploadedFile $file, string $outputFormat): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey
            ])
            ->timeout($this->timeout)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post($this->apiBaseUrl . '/convert', [
                'output_format' => $outputFormat
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Embroidery conversion successful', [
                    'conversion_id' => $data['conversion_id'] ?? null,
                    'original_file' => $file->getClientOriginalName(),
                    'output_format' => $outputFormat
                ]);

                return $data;
            }

            Log::error('Embroidery API conversion failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'file' => $file->getClientOriginalName(),
                'format' => $outputFormat
            ]);

            return [
                'error' => 'Conversion service returned error: ' . $response->status(),
                'details' => $response->json()['error'] ?? 'Unknown error'
            ];

        } catch (\Exception $e) {
            Log::error('Embroidery API connection failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'format' => $outputFormat
            ]);

            return [
                'error' => 'Unable to connect to conversion service',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the download URL for a converted file
     */
    public function getDownloadUrl(string $conversionId, string $format): string
    {
        return $this->apiBaseUrl . '/download?id=' . urlencode($conversionId) . '&format=' . urlencode($format);
    }

    /**
     * Check if the API service is available
     */
    public function checkApiStatus(): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey
            ])
            ->timeout(10)
            ->get($this->apiBaseUrl . '/status');

            if ($response->successful()) {
                return [
                    'available' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'available' => false,
                'error' => 'API returned status: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::warning('Embroidery API status check failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Download a converted file directly
     */
    public function downloadFile(string $conversionId, string $format): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey
            ])
            ->timeout(60)
            ->get($this->getDownloadUrl($conversionId, $format));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'content' => $response->body(),
                    'content_type' => $response->header('Content-Type'),
                    'filename' => 'converted.' . $format
                ];
            }

            return [
                'success' => false,
                'error' => 'Download failed with status: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('File download failed', [
                'conversion_id' => $conversionId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get supported formats
     */
    public function getSupportedFormats(): array
    {
        return [
            'dst' => 'Tajima Embroidery',
            'pes' => 'Brother Embroidery',
            'jef' => 'Janome Embroidery',
            'exp' => 'Melco Embroidery',
            'vp3' => 'Husqvarna Viking',
            'xxx' => 'Singer Embroidery',
            'pcs' => 'Pfaff Embroidery',
            'hus' => 'Husqvarna Embroidery',
            'sew' => 'Janome Sewing',
            'pec' => 'Brother Pecking',
            'vip' => 'Pfaff VIP',
            'csd' => 'Singer CSD'
        ];
    }

    /**
     * Validate if a format is supported
     */
    public function isFormatSupported(string $format): bool
    {
        return array_key_exists(strtolower($format), $this->getSupportedFormats());
    }
}
