<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->string('q')->toString());

                $query->where(function ($builder) use ($keyword) {
                    $builder
                        ->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%');
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only(['q', 'role']),
            'roles' => User::ROLES,
            'summary' => [
                'total' => User::count(),
                'admin' => User::where('role', User::ROLE_ADMIN)->count(),
                'marketing' => User::where('role', User::ROLE_MARKETING)->count(),
                'ceo' => User::where('role', User::ROLE_CEO)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => User::ROLES,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::ROLES,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()->is($user) && $validated['role'] !== User::ROLE_ADMIN) {
            return back()
                ->withErrors(['role' => 'Akun admin yang sedang login harus tetap ber-role admin.'])
                ->withInput();
        }

        if (! filled($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Data user berhasil diperbarui.');
    }
}
