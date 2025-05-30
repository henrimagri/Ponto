@extends('layouts.app')

@section('title', 'Relatório de Ponto')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-alt me-2"></i>Relatório de Ponto</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Voltar
        </a>
    </div>    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('ponto.relatorio') }}" class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ optional($dataInicio)->format('Y-m-d') }}">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ optional($dataFim)->format('Y-m-d') }}">
                </div>
                
                <div class="col-md-4">
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        
                        <a href="{{ route('ponto.relatorio') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Meus Registros</h5>
                
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($registros->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Nenhum registro de ponto encontrado.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Entrada</th>
                                <th>Saída (Almoço)</th>
                                <th>Retorno (Almoço)</th>
                                <th>Saída</th>
                                <th>Total (Horas)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registros as $registro)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($registro->data)->format('d/m/Y') }}</td>
                                <td>{{ $registro->marcacao1 ? $registro->marcacao1->format('H:i:s') : '-' }}</td>
                                <td>{{ $registro->marcacao2 ? $registro->marcacao2->format('H:i:s') : '-' }}</td>
                                <td>{{ $registro->marcacao3 ? $registro->marcacao3->format('H:i:s') : '-' }}</td>
                                <td>{{ $registro->marcacao4 ? $registro->marcacao4->format('H:i:s') : '-' }}</td>                                <td>
                                    @if($registro->todasMarcacoesRealizadas)
                                        @php
                                            $horasTrabalhadas = is_callable($registro->calcularHorasTrabalhadas)
                                                ? call_user_func($registro->calcularHorasTrabalhadas)
                                                : (is_array($registro->calcularHorasTrabalhadas) ? $registro->calcularHorasTrabalhadas : ['horas'=>0,'minutos'=>0]);
                                            echo $horasTrabalhadas['horas'] . 'h ' . $horasTrabalhadas['minutos'] . 'm';
                                        @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-info-circle me-2"></i>Resumo:</h6>
                        <p class="mb-1"><strong>Total de registros:</strong> {{ $registros->count() }}</p>
                        <p class="mb-1"><strong>Registros completos:</strong> 
                            {{ $registros->filter(function($registro) { return $registro->todasMarcacoesRealizadas; })->count() }}
                        </p>
                        <p class="mb-1"><strong>Registros incompletos:</strong> 
                            {{ $registros->filter(function($registro) { return !$registro->todasMarcacoesRealizadas; })->count() }}
                        </p>
                          @php
                        // Calcular o total geral de horas trabalhadas
                        $totalMinutosGeral = 0;
                        $registrosCompletos = $registros->filter(function($registro) { return $registro->todasMarcacoesRealizadas; });
                        foreach ($registrosCompletos as $registro) {
                            $horasTrabalhadas = is_callable($registro->calcularHorasTrabalhadas)
                                ? call_user_func($registro->calcularHorasTrabalhadas)
                                : (is_array($registro->calcularHorasTrabalhadas) ? $registro->calcularHorasTrabalhadas : ['total_minutos'=>0]);
                            $totalMinutosGeral += $horasTrabalhadas['total_minutos'] ?? 0;
                        }
                        
                        $horasGeral = floor($totalMinutosGeral / 60);
                        $minutosGeral = $totalMinutosGeral % 60;
                        @endphp
                        
                        <p class="mb-0"><strong>Total de horas trabalhadas:</strong> 
                            <span class="text-primary fw-bold">{{ $horasGeral }}h {{ $minutosGeral }}m</span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .btn, footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
    }
    
    .card-header {
        background-color: white !important;
        color: black !important;
    }
    
    body {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .container {
        max-width: 100% !important;
        width: 100% !important;
    }
}
</style>
@endsection
