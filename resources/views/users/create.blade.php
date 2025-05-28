@extends('layouts.app')

@section('title', 'Criar Funcionário')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-user-plus me-2"></i>Novo Funcionário</h1>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF *</label>
                            <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                   id="cpf" name="cpf" value="{{ old('cpf') }}" required 
                                   placeholder="000.000.000-00">
                            @error('cpf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="role" class="form-label">Cargo *</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Selecione...</option>
                                @if(auth()->user()->isAdmin())
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="gestor" {{ old('role') === 'gestor' ? 'selected' : '' }}>Gestor</option>
                                @endif
                                <option value="funcionario" {{ old('role') === 'funcionario' ? 'selected' : '' }}>Funcionário</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="dob" class="form-label">Data de Nascimento *</label>
                            <input type="date" class="form-control @error('dob') is-invalid @enderror" 
                                   id="dob" name="dob" value="{{ old('dob') }}" required>
                            @error('dob')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="manager_id" class="form-label">Gestor</label>
                            <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                                <option value="">Sem gestor</option>
                                <!-- Options populated by JavaScript -->
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Endereço</h5>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="cep" class="form-label">CEP *</label>
                            <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                   id="cep" name="cep" value="{{ old('cep') }}" required 
                                   placeholder="00000-000">
                            @error('cep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">Logradouro</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                   id="address" name="address" value="{{ old('address') }}" readonly>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="numero" class="form-label">Número *</label>
                            <input type="text" class="form-control @error('numero') is-invalid @enderror" 
                                   id="numero" name="numero" value="{{ old('numero') }}" required>
                            @error('numero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control @error('complemento') is-invalid @enderror" 
                                   id="complemento" name="complemento" value="{{ old('complemento') }}">
                            @error('complemento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control @error('bairro') is-invalid @enderror" 
                                   id="bairro" name="bairro" value="{{ old('bairro') }}" readonly>
                            @error('bairro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control @error('cidade') is-invalid @enderror" 
                                   id="cidade" name="cidade" value="{{ old('cidade') }}" readonly>
                            @error('cidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-1">
                        <div class="mb-3">
                            <label for="uf" class="form-label">UF</label>
                            <input type="text" class="form-control @error('uf') is-invalid @enderror" 
                                   id="uf" name="uf" value="{{ old('uf') }}" readonly>
                            @error('uf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Salvar
                    </button>
                </div>
            </form>
        </div>    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Função para carregar os gestores
    function loadManagers() {
        $.ajax({
            url: "{{ route('users.managers') }}",
            type: "GET",
            dataType: "json",
            success: function(data) {
                let managerSelect = $('#manager_id');
                managerSelect.find('option:not(:first)').remove();
                
                $.each(data, function(index, manager) {
                    let roleText = manager.role === 'admin' ? 'Administrador' : 'Gestor';
                    managerSelect.append(
                        $('<option></option>')
                            .attr('value', manager.id)
                            .text(manager.name + ' (' + roleText + ')')
                    );
                });
            },
            error: function(xhr, status, error) {
                console.error("Erro ao carregar gestores: " + error);
            }
        });
    }

    // Inicialmente desabilitar o campo gestor (será habilitado apenas quando o cargo for "funcionário")
    $('#manager_id').prop('disabled', true);

    // Carregar gestores quando a página carregar
    loadManagers();

    // Controlar a visibilidade/habilitação do campo gestor conforme o cargo selecionado
    $('#role').change(function() {
        let role = $(this).val();
        let managerField = $('#manager_id');
        
        if (role === 'funcionario') {
            managerField.prop('disabled', false);
        } else {
            managerField.prop('disabled', true);
            managerField.val(''); // Limpa a seleção quando não for funcionário
        }
    });

    // Inicializar o estado do campo gestor baseado no valor inicial do cargo
    let initialRole = $('#role').val();
    if (initialRole === 'funcionario') {
        $('#manager_id').prop('disabled', false);
    }
    
    // Máscara para CPF
    $('#cpf').on('input', function() {
        let cpf = $(this).val().replace(/\D/g, '');
        if (cpf.length > 11) cpf = cpf.substring(0, 11);
        
        if (cpf.length > 0) {
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(cpf);
        }
    });

    // Formatação e busca de CEP
    $('#cep').on('input', function() {
        // Remove caracteres não numéricos
        let cep = $(this).val().replace(/\D/g, '');
        if (cep.length > 8) cep = cep.substring(0, 8);
        
        if (cep.length > 5) {
            $(this).val(cep.substring(0, 5) + '-' + cep.substring(5));
        } else {
            $(this).val(cep);
        }
    });
    
    $('#cep').on('blur', function() {
        // Remove caracteres não numéricos
        let cep = $(this).val().replace(/\D/g, '');
        
        if (cep.length === 8) {
            // Busca os dados do CEP
            $.ajax({
                url: `/api/cep/${cep}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    // Adicione aqui um indicador de carregamento se desejar
                },
                success: function(data) {
                    $('#address').val(data.address);
                    $('#bairro').val(data.bairro);
                    $('#cidade').val(data.cidade);
                    $('#uf').val(data.uf);
                },
                error: function() {
                    alert('CEP não encontrado. Verifique o número informado.');
                }
            });
        }
    });
});
</script>
@endpush

@endsection
