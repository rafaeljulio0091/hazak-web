<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Single entry point (login/registration redirects land here): sends
     * each user to the dashboard matching their role.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return to_route($request->user()->isAdmin() ? 'admin.dashboard' : 'my.dashboard');
    }
}
