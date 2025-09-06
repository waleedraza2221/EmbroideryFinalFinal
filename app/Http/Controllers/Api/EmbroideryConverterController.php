<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmbroideryConverterController extends Controller
{
    /**
     * Supported embroidery formats
     */
    private $supportedFormats = [
        'dst', 'pes', 'jef', 'exp', 'vp3', 'xxx', 'pcs', 'hus', 'sew', 'pec', 'vip', 'csd'
    ];

    /**
     * Convert embroidery file format
     */
    public function convert(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'file' => 'required|file|max:51200', // 50MB max
                'output_format' => 'required|string|in:' . implode(',', $this->supportedFormats)
            ]);

            $file = $request->file('file');
            $outputFormat = strtolower($request->input('output_format'));
            
            // Get file extension
            $inputFormat = strtolower($file->getClientOriginalExtension());
            
            // Validate input format
            if (!in_array($inputFormat, $this->supportedFormats)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unsupported input format: ' . $inputFormat
                ], 400);
            }

            // Check if conversion is needed
            if ($inputFormat === $outputFormat) {
                return response()->json([
                    'success' => false,
                    'error' => 'Input and output formats are the same'
                ], 400);
            }

            // Generate unique filenames
            $uniqueId = Str::uuid();
            $inputFileName = $uniqueId . '_input.' . $inputFormat;
            $outputFileName = $uniqueId . '_output.' . $outputFormat;
            
            // Storage paths
            $tempDir = 'temp/embroidery/';
            $inputPath = storage_path('app/' . $tempDir . $inputFileName);
            $outputPath = storage_path('app/' . $tempDir . $outputFileName);
            
            // Ensure temp directory exists
            Storage::makeDirectory($tempDir);
            
            // Save uploaded file
            $file->storeAs($tempDir, $inputFileName);
            
            // Log conversion attempt
            Log::info('Embroidery conversion started', [
                'input_format' => $inputFormat,
                'output_format' => $outputFormat,
                'file_size' => $file->getSize(),
                'unique_id' => $uniqueId
            ]);

            // Perform conversion using libembroidery
            $conversionResult = $this->performConversion($inputPath, $outputPath, $inputFormat, $outputFormat);
            
            if (!$conversionResult['success']) {
                // Clean up input file
                Storage::delete($tempDir . $inputFileName);
                
                return response()->json([
                    'success' => false,
                    'error' => $conversionResult['error']
                ], 500);
            }

            // Check if output file was created
            if (!Storage::exists($tempDir . $outputFileName)) {
                // Clean up
                Storage::delete($tempDir . $inputFileName);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Conversion failed - output file not created'
                ], 500);
            }

            // Get file info
            $outputFileSize = Storage::size($tempDir . $outputFileName);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $downloadFileName = $originalName . '.' . $outputFormat;

            // Log successful conversion
            Log::info('Embroidery conversion completed', [
                'unique_id' => $uniqueId,
                'output_size' => $outputFileSize,
                'download_name' => $downloadFileName
            ]);

            // Clean up input file
            Storage::delete($tempDir . $inputFileName);

            return response()->json([
                'success' => true,
                'message' => 'Conversion completed successfully',
                'data' => [
                    'download_id' => $uniqueId,
                    'filename' => $downloadFileName,
                    'size' => $outputFileSize,
                    'format' => strtoupper($outputFormat)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Embroidery conversion error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred during conversion'
            ], 500);
        }
    }

    /**
     * Download converted file
     */
    public function download($downloadId)
    {
        try {
            $tempDir = 'temp/embroidery/';
            
            // Find the output file for this download ID
            $files = Storage::files($tempDir);
            $outputFile = null;
            
            foreach ($files as $file) {
                if (strpos($file, $downloadId . '_output.') !== false) {
                    $outputFile = $file;
                    break;
                }
            }
            
            if (!$outputFile || !Storage::exists($outputFile)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found or expired'
                ], 404);
            }

            $filePath = storage_path('app/' . $outputFile);
            $extension = pathinfo($outputFile, PATHINFO_EXTENSION);
            $filename = 'converted_embroidery.' . $extension;

            // Schedule file deletion after download
            $this->scheduleFileCleanup($outputFile);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/octet-stream',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Download error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Download failed'
            ], 500);
        }
    }

    /**
     * Perform the actual conversion using PyEmbroidery
     */
    private function performConversion($inputPath, $outputPath, $inputFormat, $outputFormat)
    {
        try {
            // Get Python script path
            $scriptPath = base_path('scripts/convert_embroidery.py');
            
            if (!file_exists($scriptPath)) {
                return [
                    'success' => false,
                    'error' => 'Conversion script not found.'
                ];
            }

            // Check if Python is available
            $pythonCommand = $this->getPythonCommand();
            if (!$pythonCommand) {
                return [
                    'success' => false,
                    'error' => 'Python3 not found. Please install Python 3 and PyEmbroidery.'
                ];
            }

            // Build the conversion command
            $command = sprintf(
                '%s "%s" "%s" "%s"',
                $pythonCommand,
                $scriptPath,
                $inputPath,
                $outputPath
            );

            // Execute the conversion with timeout
            $output = [];
            $returnCode = 0;
            
            // Add timeout to prevent hanging
            $timeoutCommand = PHP_OS_FAMILY === 'Windows' 
                ? $command 
                : "timeout 300 $command";
                
            exec($timeoutCommand . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                Log::error('PyEmbroidery conversion failed', [
                    'command' => $command,
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output)
                ]);

                return [
                    'success' => false,
                    'error' => 'Conversion failed: ' . implode(' ', $output)
                ];
            }

            Log::info('PyEmbroidery conversion successful', [
                'input_format' => $inputFormat,
                'output_format' => $outputFormat,
                'output' => implode("\n", $output)
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Conversion error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Python command for the system
     */
    private function getPythonCommand()
    {
        // Check common Python commands
        $possibleCommands = ['python3', 'python'];

        foreach ($possibleCommands as $cmd) {
            // Check if command exists and can run
            $testCommand = PHP_OS_FAMILY === 'Windows' 
                ? "where $cmd >nul 2>&1" 
                : "which $cmd >/dev/null 2>&1";
                
            $output = [];
            $returnCode = 0;
            exec($testCommand, $output, $returnCode);
            
            if ($returnCode === 0) {
                // Verify it's Python 3
                exec("$cmd --version 2>&1", $versionOutput, $versionCode);
                if ($versionCode === 0 && !empty($versionOutput)) {
                    $version = implode(' ', $versionOutput);
                    if (strpos(strtolower($version), 'python 3') !== false) {
                        return $cmd;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Schedule file cleanup
     */
    private function scheduleFileCleanup($filePath)
    {
        // Delete file after 1 hour
        $delay = 3600; // 1 hour in seconds
        
        // In a production environment, you might want to use a job queue for this
        // For now, we'll use a simple approach
        register_shutdown_function(function() use ($filePath, $delay) {
            ignore_user_abort(true);
            sleep($delay);
            Storage::delete($filePath);
        });
    }

    /**
     * Get supported formats
     */
    public function getSupportedFormats()
    {
        return response()->json([
            'success' => true,
            'formats' => $this->supportedFormats
        ]);
    }

    /**
     * Clean up old temporary files
     */
    public function cleanup()
    {
        try {
            $tempDir = 'temp/embroidery/';
            $files = Storage::files($tempDir);
            $deletedCount = 0;
            
            foreach ($files as $file) {
                // Delete files older than 24 hours
                if (Storage::lastModified($file) < (time() - 86400)) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleaned up $deletedCount old files"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
