<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestAuthController;

// Rota de teste temporária
Route::get('/test', function () {
    return 'Sistema funcionando! Data: ' . now();
});

// Teste de view simples
Route::get('/test-view', function () {
    return view('test-simple');
});

// Teste do layout
Route::get('/test-layout', function () {
    return view('test-layout');
});

// Rota principal - redireciona para login ou dashboard
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Rotas de autenticação
Route::get('/login', [NewAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [NewAuthController::class, 'login']);
Route::get('/register', [NewAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [NewAuthController::class, 'register']);
Route::post('/logout', [NewAuthController::class, 'logout'])->name('logout');

// Test login route
Route::get('/test-login', [TestAuthController::class, 'testLogin']);

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // API para buscar CEP
    Route::get('/api/cep/{cep}', [UserController::class, 'searchCep'])->name('api.cep');
    
    // Rota especial que deve vir antes do resource
    Route::get('/users/managers', [UserController::class, 'getManagers'])->name('users.managers');
    
    // Rotas de registro de ponto
    Route::post('/ponto/registrar', [App\Http\Controllers\PontoController::class, 'registrarPonto'])->name('ponto.registrar');
    Route::get('/ponto/status', [App\Http\Controllers\PontoController::class, 'statusPontoHoje'])->name('ponto.status');
    Route::get('/ponto/relatorio', [App\Http\Controllers\PontoController::class, 'relatorio'])->name('ponto.relatorio');
    Route::get('/ponto/relatorio/funcionario/{id}', [App\Http\Controllers\PontoController::class, 'relatorioFuncionario'])->name('ponto.relatorio.funcionario');
    
    // Rotas de usuários
    Route::resource('users', UserController::class);
});
