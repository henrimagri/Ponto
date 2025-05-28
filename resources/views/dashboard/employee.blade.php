@extends('layouts.app')

@section('title', 'Dashboard - Funcionário')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard - Funcionário</h1>
        <p class="text-muted">Bem-vindo(a), {{ $user->name }}!</p>
    </div>    <!-- Botão de Marcação de Ponto -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4 class="card-title mb-3">Registro de Ponto</h4>
                    <p id="horarioAtual" class="mb-4 fs-5">00:00:00</p>
                    
                    <div id="statusPonto" class="mb-3"></div>
                    
                    <button id="registrarPonto" class="btn btn-success btn-lg px-5 py-3">
                        <i class="fas fa-clock me-2"></i>Registrar Ponto
                    </button>
                    
                    <div class="mt-3">
                        <a href="{{ route('ponto.relatorio') }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-1"></i>Ver Relatório de Ponto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações do Funcionário -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Minhas Informações</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nome:</strong> {{ $user->name }}</p>
                            <p><strong>E-mail:</strong> {{ $user->email }}</p>
                            <p><strong>CPF:</strong> {{ $user->cpf }}</p>
                            @if($user->dob)
                                <p><strong>Data de Nascimento:</strong> {{ $user->dob->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($user->manager)
                                <p><strong>Gestor:</strong> {{ $user->manager->name }}</p>
                            @endif
                            @if($user->cep)
                                <p><strong>CEP:</strong> {{ $user->cep }}</p>
                            @endif
                            @if($user->address)
                                <p><strong>Endereço:</strong> 
                                    {{ $user->address }}, {{ $user->numero }}
                                    @if($user->complemento), {{ $user->complemento }}@endif
                                </p>
                                <p><strong>Bairro:</strong> {{ $user->bairro }}</p>
                                <p><strong>Cidade:</strong> {{ $user->cidade }} - {{ $user->uf }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>Ver Perfil Completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        Como funcionário, você pode visualizar apenas suas próprias informações.
                    </p>
                    <p class="small text-muted mb-0">
                        Para alterações em seus dados, entre em contato com seu gestor ou administrador.
                    </p>
                </div>
            </div>
        </div>    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Função para atualizar o relógio em tempo real
    function atualizarRelogio() {
        const agora = new Date();
        const hora = agora.getHours().toString().padStart(2, '0');
        const minuto = agora.getMinutes().toString().padStart(2, '0');
        const segundo = agora.getSeconds().toString().padStart(2, '0');
        
        $('#horarioAtual').text(`${hora}:${minuto}:${segundo}`);
    }
    
    // Atualizar relógio a cada segundo
    setInterval(atualizarRelogio, 1000);
    atualizarRelogio(); // Inicializar
    
    // Função para carregar o status do ponto
    function carregarStatusPonto() {
        $.ajax({
            url: "{{ route('ponto.status') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                let statusHtml = '';
                let btnDisabled = false;
                
                if (response.status === 'success') {
                    // Mostrar as marcações já realizadas
                    if (response.marcacoes.length > 0) {
                        statusHtml = '<div class="alert alert-info mb-3"><strong>Marcações de hoje:</strong><br>';
                        
                        response.marcacoes.forEach(function(marcacao) {
                            statusHtml += `<span class="badge bg-success me-2">${marcacao.tipo}: ${marcacao.hora}</span>`;
                        });
                        
                        statusHtml += '</div>';
                    }
                    
                    // Mostrar qual será a próxima marcação
                    if (response.proximaMarcacao > 0) {
                        statusHtml += `<p class="text-primary">Próxima marcação: <strong>${response.tipoProximaMarcacao}</strong></p>`;
                    } else {
                        statusHtml += '<p class="text-success"><strong>Todas as marcações do dia foram realizadas!</strong></p>';
                        btnDisabled = true;
                    }
                } else if (response.status === 'pending') {
                    statusHtml = '<p class="text-primary">Nenhuma marcação realizada hoje. Próxima marcação: <strong>Entrada</strong></p>';
                } else {
                    statusHtml = '<p class="text-danger">Erro ao carregar status do ponto.</p>';
                }
                
                $('#statusPonto').html(statusHtml);
                $('#registrarPonto').prop('disabled', btnDisabled);
                
                if (btnDisabled) {
                    $('#registrarPonto').removeClass('btn-success').addClass('btn-secondary');
                } else {
                    $('#registrarPonto').removeClass('btn-secondary').addClass('btn-success');
                }
            },
            error: function() {
                $('#statusPonto').html('<div class="alert alert-danger">Erro ao carregar status do ponto.</div>');
            }
        });
    }
    
    // Carregar status inicial
    carregarStatusPonto();
      // Função para registrar o ponto
    $('#registrarPonto').click(function() {
        // Primeiro buscar o status atual do ponto para saber qual será a próxima marcação
        $.ajax({
            url: "{{ route('ponto.status') }}",
            type: "GET",
            dataType: "json",
            success: function(statusResponse) {
                let tipoMarcacao = 'Entrada';
                let numeroMarcacao = 1;
                
                if (statusResponse.status === 'success' && statusResponse.proximaMarcacao > 0) {
                    tipoMarcacao = statusResponse.tipoProximaMarcacao;
                    numeroMarcacao = statusResponse.proximaMarcacao;
                }
                
                // Mostrar confirmação antes de registrar
                Swal.fire({
                    title: 'Confirmar Registro de Ponto',
                    html: `
                        <div class="text-center">
                            <p>Você está registrando o seguinte ponto:</p>
                            <div class="alert alert-info">
                                <strong>Tipo:</strong> ${tipoMarcacao}<br>
                                <strong>Horário:</strong> ${$('#horarioAtual').text()}<br>
                                <strong>Data:</strong> ${new Date().toLocaleDateString('pt-BR')}
                            </div>
                            <p class="small text-muted">Confirme para registrar seu ponto.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Registrar o ponto após confirmação
                        $.ajax({
                            url: "{{ route('ponto.registrar') }}",
                            type: "POST",
                            dataType: "json",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        title: 'Ponto Registrado!',
                                        html: `
                                            <div class="text-center">
                                                <p>Seu ponto foi registrado com sucesso!</p>
                                                <div class="alert alert-success">
                                                    <strong>Data:</strong> ${response.data.data}<br>
                                                    <strong>Hora:</strong> ${response.data.hora}<br>
                                                    <strong>Tipo:</strong> ${response.data.tipo_marcacao}
                                                </div>
                                                <p class="small text-muted">Este registro foi salvo no sistema.</p>
                                            </div>
                                        `,
                                        icon: 'success',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#28a745',
                                        customClass: {
                                            popup: 'animated fadeInDown'
                                        }
                                    }).then(() => {
                                        // Recarregar a página após clicar em OK
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Erro!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: 'Ocorreu um erro ao registrar o ponto. Tente novamente.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Não foi possível obter informações do ponto. Tente novamente.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
@endpush

@endsection
