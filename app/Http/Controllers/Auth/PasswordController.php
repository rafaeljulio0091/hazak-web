<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\CurrentFirebasePassword;
use App\Services\FirebaseAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request, FirebaseAccountService $accounts): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', new CurrentFirebasePassword($request->user())],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $accounts->updatePassword($request->user(), $validated['password']);

        return back();
    }
}
