<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\FirebaseAccountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterUserRequest $request, FirebaseAccountService $accounts): RedirectResponse
    {
        $validated = $request->validated();
        $user = $accounts->register(new RegisterUserData(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        ));

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
