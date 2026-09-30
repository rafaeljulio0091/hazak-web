<?php

namespace App\Http\Controllers;

use App\Services\Tracking\DeviceTrackingService;
use App\Services\Tracking\FirebaseTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    public function __construct(
        private readonly DeviceTrackingService $tracking,
        private readonly FirebaseTokenService $tokens,
    ) {}

    /**
     * Renders the live tracking page for a single device. The Firebase
     * custom token and the last known position (for an instant first
     * paint, before the realtime listener connects) are passed as props.
     * Admins may open any device; regular users only devices shared with
     * them (DevicePolicy).
     */
    public function show(Request $request, string $deviceId): Response
    {
        Gate::authorize('view-device', $deviceId);

        return Inertia::render('Tracking/Show', [
            'deviceId' => $deviceId,
            'firebaseToken' => $this->tokens->mintTrackingTokenFor($request->user()),
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
            'token' => $this->tokens->mintTrackingTokenFor($request->user()),
        ]);
    }
}
