<?php

namespace App\Http\Controllers;

use App\Services\MPesaService;

class AccessTokenController extends Controller
{
    protected $mPesaService;

    public function __construct(MPesaService $mPesaService)
    {
        $this->mPesaService = $mPesaService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        try {

            $accessToken = $this->mPesaService->getAccessToken();

            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get access token'
                ], 500);
            }

            $response = [
                'success' => true,
                'access_token' => $accessToken
            ];

            return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        }
    }
}
