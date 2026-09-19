<?php

namespace App\Http\Controllers;

use App\Services\Tracking\DeviceTrackingService;
use App\Services\Tracking\FirebaseTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    public function __construct(
        private readonly DeviceTrackingService $tracking,
        private readonly FirebaseTokenService $tokens,
    ) {
    }

    /**
     * Renders the live tracking page for a single device. The Firebase
     * custom token and the last known position (for an instant first
     * paint, before the realtime listener connects) are passed as props.
     */
    public function show(Request $request, string $deviceId): Response
    {
        return Inertia::render('Tracking/Show', [
            'deviceId' => $deviceId,
            'firebaseToken' => $this->tokens->mintCustomTokenFor($request->user(), ['role' => 'dashboard']),
            'initialPosition' => $this->tracking->latestPosition($deviceId),
        ]);
    }

    /**
     * Firebase custom tokens expire after 1 hour; the frontend calls this
     * endpoint to get a fresh one without a full page reload.
     */
    public function token(Request $request): JsonResponse
    {
        return response()->json([
            'token' => $this->tokens->mintCustomTokenFor($request->user(), ['role' => 'dashboard']),
        ]);
    }
}
