<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PlaceholderPageController extends Controller
{
    public function __invoke(
        string $role,
        string $page,
        string $title,
        string $description,
    ): View {
        return view('dashboard.placeholder', compact('role', 'page', 'title', 'description'));
    }
}
