<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }
    }

    public function index()
    {
        $this->authorizeAdmin();
        $users = User::orderBy('name')->get();
        $roles = Role::orderBy('label')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        return view('settings.index', compact('users', 'roles', 'locations'));
    }

    public function createUser()
    {
        $this->authorizeAdmin();
        $roles = Role::orderBy('label')->get();
        return view('settings.create-user', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        $this->authorizeAdmin();
        $validRoles = Role::pluck('name')->toArray();
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'role'     => ['required', Rule::in($validRoles)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('settings.index')->with('success', 'User berhasil dibuat.');
    }

    public function editUser(User $user)
    {
        $this->authorizeAdmin();
        $roles = Role::orderBy('label')->get();
        return view('settings.edit-user', compact('user', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeAdmin();
        $validRoles = Role::pluck('name')->toArray();
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'      => ['required', Rule::in($validRoles)],
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        $this->authorizeAdmin();
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus akun sendiri.']);
        }
        $user->delete();
        return redirect()->route('settings.index')->with('success', 'User berhasil dihapus.');
    }

    // ─── Role CRUD ───────────────────────────────────────────────────────────

    public function storeRole(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'name'        => 'required|string|max:50|alpha_dash|unique:roles,name',
            'label'       => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create($request->only('name', 'label', 'description'));

        return redirect()->route('settings.index', ['tab' => 'roles'])
            ->with('success', 'Role berhasil ditambahkan.');
    }

    public function updateRole(Request $request, Role $role)
    {
        $this->authorizeAdmin();
        $request->validate([
            'label'       => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        // Name (slug) cannot be changed for protected roles
        $data = $request->only('label', 'description');
        if (!$role->isProtected()) {
            $request->validate(['name' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('roles')->ignore($role->id)]]);
            $data['name'] = $request->name;
        }

        $role->update($data);

        return redirect()->route('settings.index', ['tab' => 'roles'])
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroyRole(Role $role)
    {
        $this->authorizeAdmin();

        if ($role->isProtected()) {
            return back()->with('error', 'Role "' . $role->label . '" tidak dapat dihapus karena digunakan sistem.');
        }

        $inUse = User::where('role', $role->name)->exists();
        if ($inUse) {
            return back()->with('error', 'Role "' . $role->label . '" tidak dapat dihapus karena masih digunakan oleh user.');
        }

        $role->delete();
        return redirect()->route('settings.index', ['tab' => 'roles'])
            ->with('success', 'Role berhasil dihapus.');
    }

    // ─── Lokasi PLTS CRUD ───────────────────────────────────────────────────

    public function storeLocation(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'name'         => 'required|string|max:255|unique:locations,name',
            'code'         => 'nullable|string|max:50',
            'capacity_kwp' => 'nullable|numeric|min:0',
        ]);

        \App\Models\Location::create([
            'name'         => $request->name,
            'code'         => $request->code,
            'capacity_kwp' => $request->capacity_kwp,
            'is_active'    => true,
        ]);

        return redirect()->route('settings.index', ['tab' => 'locations'])
            ->with('success', 'Lokasi PLTS berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, \App\Models\Location $location)
    {
        $this->authorizeAdmin();
        $request->validate([
            'name'         => 'required|string|max:255|unique:locations,name,' . $location->id,
            'code'         => 'nullable|string|max:50',
            'capacity_kwp' => 'nullable|numeric|min:0',
            'is_active'    => 'boolean',
        ]);

        $location->update([
            'name'         => $request->name,
            'code'         => $request->code,
            'capacity_kwp' => $request->capacity_kwp,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('settings.index', ['tab' => 'locations'])
            ->with('success', 'Lokasi PLTS berhasil diperbarui.');
    }

    public function destroyLocation(\App\Models\Location $location)
    {
        $this->authorizeAdmin();
        
        if ($location->users()->exists() || $location->maintenanceSchedules()->exists()) {
            return back()->with('error', 'Lokasi PLTS sedang digunakan dan tidak dapat dihapus.');
        }

        $location->delete();
        
        return redirect()->route('settings.index', ['tab' => 'locations'])
            ->with('success', 'Lokasi PLTS berhasil dihapus.');
    }
}
