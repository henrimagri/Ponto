@extends('layouts.app')

@section('title', 'Lista de Funcionários')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users me-2"></i>
            @if(auth()->user()->isAdmin())
                Funcionários
            @else
                Meus Subordinados
            @endif
        </h1>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i>Novo Funcionário
        </a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nome, email ou CPF..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">Todos os cargos</option>
                            @if(auth()->user()->isAdmin())
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                                <option value="gestor" {{ request('role') === 'gestor' ? 'selected' : '' }}>Gestor</option>
                            @endif
                            <option value="funcionario" {{ request('role') === 'funcionario' ? 'selected' : '' }}>Funcionário</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Cargo</th>
                            @if(auth()->user()->isAdmin())
                                <th>Gestor</th>
                            @endif
                            <th>Cidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2 text-secondary"></i>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->cpf ?? '-' }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Administrador</span>
                                @elseif($user->role === 'gestor')
                                    <span class="badge bg-warning text-dark">Gestor</span>
                                @else
                                    <span class="badge bg-info">Funcionário</span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td>{{ $user->manager?->name ?? '-' }}</td>
                            @endif
                            <td>{{ $user->cidade ?? '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary" 
                                       title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->role !== 'funcionario')
                                        <a href="{{ route('ponto.relatorio.funcionario', $user->id) }}" class="btn btn-sm btn-outline-info" 
                                           title="Ver Ponto">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isAdmin() || (auth()->user()->isManager() && $user->manager_id === auth()->id()))
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isAdmin() && $user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    title="Excluir"
                                                    onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? '7' : '6' }}" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Nenhum funcionário encontrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} 
                    de {{ $users->total() }} registros
                </div>
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
