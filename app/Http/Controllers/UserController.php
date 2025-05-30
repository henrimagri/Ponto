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
     * Exibe a lista de usuários.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::with('manager');
        
        // Aplica filtro baseado no perfil
        if ($user->isAdmin()) {
            // Admin vê todos os usuários - sem filtro adicional
        } elseif ($user->isManager()) {
            // Gestor vê apenas seus subordinados
            $query->where('manager_id', $user->id);
        } else {
            // Funcionário vê apenas a si mesmo
            $query->where('id', $user->id);
        }

        // Aplica filtros de busca
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
     * Exibe o formulário de criação de novo usuário.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Apenas admins e gestores podem criar usuários
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Não autorizado');
        }

        return view('users.create');
    }

    /**
     * Salva um novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Apenas admins e gestores podem criar usuários
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['error' => 'Não autorizado'], 403);
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

        // Busca endereço pelo CEP
        $addressData = $this->getAddressFromCep($validated['cep']);
        if (!$addressData) {
            return back()->withErrors(['cep' => 'CEP inválido'])->withInput();
        }

        $validated = array_merge($validated, $addressData);
        $validated['password'] = Hash::make($validated['password']);

        // Define o gestor conforme o perfil e permissões
        if ($user->isManager() && $validated['role'] === 'funcionario') {
            $validated['manager_id'] = $user->id;
        }

        $newUser = User::create($validated);

        return redirect()->route('users.index')->with('success', 'Funcionário criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um usuário.
     */
    public function edit(User $user)
    {
        $authUser = auth()->user();
        
        // Verifica permissões
        if (!$this->canEditUser($authUser, $user)) {
            abort(403, 'Não autorizado');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Exibe os detalhes de um usuário.
     */
    public function show(User $user)
    {
        $authUser = auth()->user();
        
        // Verifica permissões
        if (!$this->canViewUser($authUser, $user)) {
            abort(403, 'Não autorizado');
        }

        $user->load('manager', 'subordinates');
        return view('users.show', compact('user'));
    }

    /**
     * Atualiza um usuário no banco de dados.
     */
    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Verifica permissões
        if (!$this->canEditUser($authUser, $user)) {
            return response()->json(['error' => 'Não autorizado'], 403);
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

        // Busca endereço pelo CEP se alterado
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
     * Remove um usuário do banco de dados.
     */
    public function destroy(User $user)
    {
        $authUser = auth()->user();
        
        // Apenas admins podem excluir usuários
        if (!$authUser->isAdmin()) {
            abort(403, 'Não autorizado');
        }

        // Não pode excluir a si mesmo
        if ($user->id === $authUser->id) {
            return back()->withErrors(['error' => 'Você não pode excluir sua própria conta.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Funcionário excluído com sucesso!');
    }

    /**
     * Busca endereço pelo CEP usando a API ViaCep
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
            // Logar erro se necessário
        }
        
        return null;
    }

    /**
     * Verifica se o usuário pode visualizar outro usuário
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
     * Verifica se o usuário pode editar outro usuário
     */
    private function canEditUser($authUser, $targetUser)
    {
        if ($authUser->isAdmin()) {
            return true;
        }
        
        if ($authUser->isManager()) {
            return $targetUser->manager_id === $authUser->id;
        }
        
        // Funcionários só podem editar a si mesmos (campos limitados)
        return $targetUser->id === $authUser->id;
    }

    /**
     * Busca CEP usando a API ViaCep
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
     * Retorna lista de gestores para dropdowns
     */
    public function getManagers()
    {
        $managers = User::where('role', 'gestor')->select('id', 'name', 'role')->get();
        return response()->json($managers);
    }
}
