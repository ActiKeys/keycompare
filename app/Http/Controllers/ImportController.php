<?php

namespace App\Http\Controllers;

use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function __construct(private ProductImportService $importer) {}

    /**
     * POST /api/import
     * Accepts JSON body with "products" array.
     * Authenticated via Bearer token (env: IMPORT_API_TOKEN).
     */
    public function import(Request $request): JsonResponse
    {
        // Optional: simple token auth
        $expectedToken = config('keycompare.import_token') ?: env('IMPORT_API_TOKEN');
        if ($expectedToken) {
            $provided = $request->bearerToken();
            if ($provided !== $expectedToken) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Invalid or missing API token. Set Authorization: Bearer <IMPORT_API_TOKEN>',
                ], 401);
            }
        }

        // Accept JSON body or uploaded file
        $data = null;
        if ($request->hasFile('file')) {
            $content = file_get_contents($request->file('file')->getRealPath());
            $data = json_decode($content, true);
        } else {
            $data = $request->all();
        }

        if (!is_array($data)) {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid JSON payload',
            ], 400);
        }

        $validator = Validator::make($data, [
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.link' => 'required|string',
            'products.*.stores' => 'array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $log = $this->importer->import($data, source: 'api');

        return response()->json([
            'ok' => $log->status !== 'failed',
            'import_id' => $log->id,
            'status' => $log->status,
            'products' => [
                'total' => $log->products_count,
                'created' => $log->products_created,
                'updated' => $log->products_updated,
            ],
            'duration_ms' => $log->duration_ms,
            'errors' => $log->errors,
        ], $log->status === 'failed' ? 500 : 200);
    }
}
