<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Check if user is administrator
     */
    protected function ensureAdmin()
    {
        if (!auth()->user()->isAdministrator()) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();
        $query = User::query();

        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->ensureAdmin();
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]{8,}$/', 'confirmed'],
            'role' => 'required|in:administrator,osas,main_proponent,general_user',
        ], [
            'password.regex' => 'Password must contain: 8+ characters, at least one letter (a-z, A-Z), at least one number (0-9), and at least one symbol (@$!%*?&.,)',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $this->ensureAdmin();
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]{8,}$/', 'confirmed'],
            'role' => 'required|in:administrator,osas,main_proponent,general_user',
        ], [
            'password.regex' => 'Password must contain: 8+ characters, at least one letter (a-z, A-Z), at least one number (0-9), and at least one symbol (@$!%*?&.,)',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $this->ensureAdmin();
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
