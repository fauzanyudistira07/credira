<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return RoleRedirect::redirectFor($request->user());
    }
}
