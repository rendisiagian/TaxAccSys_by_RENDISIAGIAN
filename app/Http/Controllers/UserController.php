<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['role', 'companies'])
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', [
            'user'      => new User(),
            'roles'     => Role::all(),
            'companies' => Company::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'role_id'   => 'required|exists:roles,id',
            'locale'    => 'required|in:id,en',
            'is_active' => 'boolean',
            'companies' => 'required|array|min:1',
            'companies.*' => 'exists:companies,id',
        ]);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => bcrypt($validated['password']),
            'role_id'            => $validated['role_id'],
            'locale'             => $validated['locale'],
            'is_active'          => $request->boolean('is_active'),
            'current_company_id' => $validated['companies'][0],
        ]);

        $user->companies()->attach($validated['companies']);

        return redirect()->route('users.index')
            ->with('success', __('Data saved successfully.'));
    }

    public function edit(User $user)
    {
        return view('users.form', [
            'user'      => $user->load('companies'),
            'roles'     => Role::all(),
            'companies' => Company::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'role_id'   => 'required|exists:roles,id',
            'locale'    => 'required|in:id,en',
            'is_active' => 'boolean',
            'companies' => 'required|array|min:1',
            'companies.*' => 'exists:companies,id',
        ]);

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role_id'   => $validated['role_id'],
            'locale'    => $validated['locale'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $user->update($data);
        $user->companies()->sync($validated['companies']);

        return redirect()->route('users.index')
            ->with('success', __('Data updated successfully.'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', __('Data deleted successfully.'));
    }
}
