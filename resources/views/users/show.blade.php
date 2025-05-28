@extends('layouts.app')

@section('title', 'Visualizar Funcionário')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-user me-2"></i>{{ $user->name }}</h1>
        <div>
            @if(auth()->user()->isAdmin() || (auth()->user()->isManager() && $user->manager_id === auth()->id()) || auth()->id() === $user->id)
                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
            @endif
            @if(auth()->user()->isAdmin() || (auth()->user()->isManager() && $user->manager_id === auth()->id()) || auth()->id() === $user->id)
                <a href="{{ route('ponto.relatorio.funcionario', $user->id) }}" class="btn btn-info me-2">
                    <i class="fas fa-clock me-1"></i>Relatório de Ponto
                </a>
            @endif
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informações Pessoais -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informações Pessoais</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome Completo:</label>
                                <p class="form-control-plaintext">{{ $user->name }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">E-mail:</label>
                                <p class="form-control-plaintext">{{ $user->email }}</p>
                            </div>
                            
                            @if($user->cpf)
                            <div class="mb-3">
                                <label class="form-label fw-bold">CPF:</label>
                                <p class="form-control-plaintext">{{ $user->cpf }}</p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cargo:</label>
                                <p class="form-control-plaintext">
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger fs-6">Administrador</span>
                                    @elseif($user->role === 'gestor')
                                        <span class="badge bg-warning text-dark fs-6">Gestor</span>
                                    @else
                                        <span class="badge bg-info fs-6">Funcionário</span>
                                    @endif
                                </p>
                            </div>
                            
                            @if($user->dob)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Data de Nascimento:</label>
                                <p class="form-control-plaintext">{{ $user->dob->format('d/m/Y') }}</p>
                            </div>
                            @endif
                            
                            @if($user->manager)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Gestor Responsável:</label>
                                <p class="form-control-plaintext">
                                    <a href="{{ route('users.show', $user->manager) }}" class="text-decoration-none">
                                        {{ $user->manager->name }}
                                    </a>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            @if($user->cep)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Endereço</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">CEP:</label>
                                <p class="form-control-plaintext">{{ $user->cep }}</p>
                            </div>
                            
                            @if($user->address)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Logradouro:</label>
                                <p class="form-control-plaintext">{{ $user->address }}</p>
                            </div>
                            @endif
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Número:</label>
                                <p class="form-control-plaintext">{{ $user->numero ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            @if($user->complemento)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Complemento:</label>
                                <p class="form-control-plaintext">{{ $user->complemento }}</p>
                            </div>
                            @endif
                            
                            @if($user->bairro)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bairro:</label>
                                <p class="form-control-plaintext">{{ $user->bairro }}</p>
                            </div>
                            @endif
                            
                            @if($user->cidade)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cidade/UF:</label>
                                <p class="form-control-plaintext">{{ $user->cidade }} - {{ $user->uf }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Informações do Sistema -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Cadastrado em:</strong><br>
                        <small class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="mb-2">
                        <strong>Última atualização:</strong><br>
                        <small class="text-muted">{{ $user->updated_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>

            <!-- Subordinados (para gestores) -->
            @if($user->isManager() && $user->subordinates->count() > 0)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Subordinados ({{ $user->subordinates->count() }})</h6>
                </div>
                <div class="card-body">
                    @foreach($user->subordinates as $subordinate)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <a href="{{ route('users.show', $subordinate) }}" class="text-decoration-none">
                                {{ $subordinate->name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $subordinate->email }}</small>
                        </div>
                    </div>
                    @if(!$loop->last)<hr class="my-2">@endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
