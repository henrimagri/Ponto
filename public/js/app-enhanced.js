// Enhanced JavaScript for Laravel Employee Management System

// Initialize when document is ready
$(document).ready(function() {
    // Apply masks and validation to all forms
    initializeMasks();
    initializeCepLookup();
    initializeFormValidation();
});

function initializeMasks() {
    // CPF mask
    $('.cpf-mask, #cpf').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        $(this).val(value);
    });
    
    // CEP mask
    $('.cep-mask, #cep').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        $(this).val(value);
    });
    
    // Phone mask (if needed in future)
    $('.phone-mask').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
        value = value.replace(/(\d{4})-(\d)(\d{4})/, '$1$2-$3');
        $(this).val(value);
    });
}

function initializeCepLookup() {
    // CEP search with improved UX
    $('#cep').on('blur', function() {
        const cep = $(this).val().replace(/\D/g, '');
        const $button = $(this).next('.btn');
        
        if (cep.length === 8) {
            // Show loading state
            const originalText = $button.length ? $button.html() : '';
            if ($button.length) {
                $button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            }
            
            // Clear previous address data
            $('#address, #bairro, #cidade, #uf').val('');
            
            // Search CEP
            $.get(`/api/cep/${cep}`)
                .done(function(data) {
                    $('#address').val(data.address);
                    $('#bairro').val(data.bairro);
                    $('#cidade').val(data.cidade);
                    $('#uf').val(data.uf);
                    
                    // Show success feedback
                    $('#cep').removeClass('is-invalid').addClass('is-valid');
                    showToast('CEP encontrado!', 'success');
                })
                .fail(function(xhr) {
                    $('#cep').removeClass('is-valid').addClass('is-invalid');
                    const errorMsg = xhr.responseJSON?.error || 'CEP não encontrado';
                    showToast(errorMsg, 'error');
                })
                .always(function() {
                    // Restore button state
                    if ($button.length) {
                        $button.html(originalText).prop('disabled', false);
                    }
                });
        }
    });
}

function initializeFormValidation() {
    // CPF validation
    $('#cpf').on('blur', function() {
        const cpf = $(this).val().replace(/\D/g, '');
        if (cpf.length === 11) {
            if (validateCPF(cpf)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
                showToast('CPF inválido', 'error');
            }
        }
    });
    
    // Email validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && emailRegex.test(email)) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else if (email) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        showPasswordStrength(strength);
    });
}

function validateCPF(cpf) {
    // CPF validation algorithm
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let digit1 = 11 - (sum % 11);
    if (digit1 > 9) digit1 = 0;
    
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    let digit2 = 11 - (sum % 11);
    if (digit2 > 9) digit2 = 0;
    
    return (parseInt(cpf.charAt(9)) === digit1 && parseInt(cpf.charAt(10)) === digit2);
}

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

function showPasswordStrength(strength) {
    const $indicator = $('#password-strength');
    if ($indicator.length === 0) {
        $('#password').after('<div id="password-strength" class="small mt-1"></div>');
    }
    
    const levels = ['Muito fraca', 'Fraca', 'Razoável', 'Boa', 'Forte', 'Muito forte'];
    const colors = ['danger', 'danger', 'warning', 'warning', 'success', 'success'];
    
    const level = Math.min(strength, 5);
    $('#password-strength')
        .removeClass('text-danger text-warning text-success')
        .addClass(`text-${colors[level]}`)
        .text(`Força da senha: ${levels[level]}`);
}

function showToast(message, type = 'info') {
    // Create toast if it doesn't exist
    if ($('#toast-container').length === 0) {
        $('body').append(`
            <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            </div>
        `);
    }
    
    const toastClass = type === 'success' ? 'text-bg-success' : 
                     type === 'error' ? 'text-bg-danger' : 'text-bg-info';
    
    const toastId = 'toast-' + Date.now();
    const toast = $(`
        <div id="${toastId}" class="toast ${toastClass}" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Sistema</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `);
    
    $('#toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();
    
    // Auto remove after hide
    toast[0].addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// Load managers for select dropdowns
function loadManagers() {
    $.get('/users/managers')
        .done(function(data) {
            const select = $('#manager_id');
            const currentManagerId = select.data('current');
            const currentUserId = select.data('user-id');
            
            select.empty().append('<option value="">Sem gestor</option>');
            
            data.forEach(manager => {
                // Don't allow user to be their own manager
                if (manager.id != currentUserId) {
                    const selected = manager.id == currentManagerId ? 'selected' : '';
                    select.append(`<option value="${manager.id}" ${selected}>${manager.name} (${manager.role})</option>`);
                }
            });
        })
        .fail(function() {
            showToast('Erro ao carregar gestores', 'error');
        });
}

// Initialize managers dropdown if present
$(document).ready(function() {
    if ($('#manager_id').length) {
        loadManagers();
    }
});
