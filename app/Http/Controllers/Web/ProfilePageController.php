<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfilePageController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->user()?->role === User::ROLE_USER) {
            return redirect()->route('user.profile.index');
        }

        return view('dashboard.profile');
    }
}
