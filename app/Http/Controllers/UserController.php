<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\ValidCpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::with('manager');
        
        // Apply role-based filtering
        if ($user->isAdmin()) {
            // Admin sees all users - no additional filtering needed
        } elseif ($user->isManager()) {
            // Manager sees only subordinates
            $query->where('manager_id', $user->id);
        } else {
            // Employee sees only themselves
            $query->where('id', $user->id);
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        $users = $query->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Only admins and managers can create users
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Unauthorized');
        }

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Only admins and managers can create users
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Remover formatação do CEP antes da validação
        $request->merge([
            'cep' => preg_replace('/\D/', '', $request->cep),
            'cpf' => preg_replace('/\D/', '', $request->cpf)
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'unique:users,cpf', new ValidCpf()],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,gestor,funcionario',
            'dob' => 'required|date',
            'cep' => 'required|string|size:8',
            'numero' => 'required|string',
            'complemento' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Get address from CEP
        $addressData = $this->getAddressFromCep($validated['cep']);
        if (!$addressData) {
            return back()->withErrors(['cep' => 'CEP inválido'])->withInput();
        }

        $validated = array_merge($validated, $addressData);
        $validated['password'] = Hash::make($validated['password']);

        // Set manager based on role and current user permissions
        if ($user->isManager() && $validated['role'] === 'funcionario') {
            $validated['manager_id'] = $user->id;
        }

        $newUser = User::create($validated);

        return redirect()->route('users.index')->with('success', 'Funcionário criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions
        if (!$this->canEditUser($authUser, $user)) {
            abort(403, 'Unauthorized');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions
        if (!$this->canViewUser($authUser, $user)) {
            abort(403, 'Unauthorized');
        }

        $user->load('manager', 'subordinates');
        return view('users.show', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions
        if (!$this->canEditUser($authUser, $user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Remover formatação do CEP e CPF antes da validação
        $request->merge([
            'cep' => preg_replace('/\D/', '', $request->cep),
            'cpf' => preg_replace('/\D/', '', $request->cpf)
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => ['required', 'string', Rule::unique('users')->ignore($user->id), new ValidCpf()],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,gestor,funcionario',
            'dob' => 'required|date',
            'cep' => 'required|string|size:8',
            'numero' => 'required|string',
            'complemento' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Get address from CEP if changed
        if ($validated['cep'] !== $user->cep) {
            $addressData = $this->getAddressFromCep($validated['cep']);
            if (!$addressData) {
                return back()->withErrors(['cep' => 'CEP inválido'])->withInput();
            }
            $validated = array_merge($validated, $addressData);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)->with('success', 'Funcionário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $authUser = auth()->user();
        
        // Only admins can delete users
        if (!$authUser->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Cannot delete yourself
        if ($user->id === $authUser->id) {
            return back()->withErrors(['error' => 'Você não pode excluir sua própria conta.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Funcionário excluído com sucesso!');
    }

    /**
     * Get address from CEP using ViaCep API
     */
    private function getAddressFromCep($cep)
    {
        try {
            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
            
            if ($response->successful() && !isset($response->json()['erro'])) {
                $data = $response->json();
                return [
                    'address' => $data['logradouro'],
                    'bairro' => $data['bairro'],
                    'cidade' => $data['localidade'],
                    'uf' => $data['uf'],
                ];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        
        return null;
    }

    /**
     * Check if user can view another user
     */
    private function canViewUser($authUser, $targetUser)
    {
        if ($authUser->isAdmin()) {
            return true;
        }
        
        if ($authUser->isManager()) {
            return $targetUser->manager_id === $authUser->id || $targetUser->id === $authUser->id;
        }
        
        return $targetUser->id === $authUser->id;
    }

    /**
     * Check if user can edit another user
     */
    private function canEditUser($authUser, $targetUser)
    {
        if ($authUser->isAdmin()) {
            return true;
        }
        
        if ($authUser->isManager()) {
            return $targetUser->manager_id === $authUser->id;
        }
        
        // Employees can only edit themselves (limited fields)
        return $targetUser->id === $authUser->id;
    }

    /**
     * Search CEP using ViaCep API
     */
    public function searchCep($cep)
    {
        $cep = preg_replace('/\D/', '', $cep);
        
        if (strlen($cep) != 8) {
            return response()->json(['error' => 'CEP deve ter 8 dígitos'], 400);
        }

        try {
            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
            $data = $response->json();
            
            if (isset($data['erro'])) {
                return response()->json(['error' => 'CEP não encontrado'], 404);
            }
            
            return response()->json([
                'address' => $data['logradouro'] ?? '',
                'bairro' => $data['bairro'] ?? '',
                'cidade' => $data['localidade'] ?? '',
                'uf' => $data['uf'] ?? '',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar CEP'], 500);
        }
    }

    /**
     * Get managers list for dropdowns
     */
    public function getManagers()
    {
        $managers = User::where('role', 'gestor')->select('id', 'name', 'role')->get();
        return response()->json($managers);
    }
}
