<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Tracking\FirebaseTokenService;
use App\Services\Tracking\SharedDevicesService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SharedDevicesService $devices,
        private readonly FirebaseTokenService $tokens,
    ) {}

    /**
     * Regular user's dashboard: live map of the devices shared with them.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('User/Dashboard', [
            'devices' => $this->devices->overviewFor($request->user()),
            'firebaseToken' => $this->tokens->mintTrackingTokenFor($request->user()),
        ]);
    }
}
