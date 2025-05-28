@extends('layouts.app')

@section('title', 'Dashboard - Gestor')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard - Gestor</h1>
    </div>

    <!-- Card de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">{{ $totalSubordinates }}</h4>
                            <p class="card-text">Funcionários Subordinados</p>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Subordinados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Meus Subordinados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Data de Nascimento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subordinates as $subordinate)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2 text-secondary"></i>
                                    {{ $subordinate->name }}
                                </div>
                            </td>
                            <td>{{ $subordinate->email }}</td>
                            <td>{{ $subordinate->cpf }}</td>                            <td>{{ $subordinate->dob ? $subordinate->dob->format('d/m/Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('users.show', $subordinate) }}" class="btn btn-sm btn-outline-primary" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('ponto.relatorio.funcionario', $subordinate->id) }}" class="btn btn-sm btn-outline-info" title="Ver Ponto">
                                    <i class="fas fa-clock"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Você não possui funcionários subordinados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $subordinates->links() }}
        </div>
    </div>
</div>
@endsection
