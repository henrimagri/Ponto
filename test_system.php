<?php
// Teste simples para verificar o Sistema de Gerenciamento de Funcionários em Laravel

echo "=== Teste do Sistema de Gerenciamento de Funcionários em Laravel ===\n\n";

// Teste 1: Verifica se é possível conectar à aplicação
echo "1. Testando conectividade básica...\n";
$response = file_get_contents('http://127.0.0.1:8000/login');
if ($response !== false && strpos($response, 'Login') !== false) {
    echo "✅ Página de login carregada com sucesso\n";
} else {
    echo "❌ Falha ao carregar a página de login\n";
}

// Teste 2: Verifica conexão com o banco de dados executando uma consulta simples
echo "\n2. Testando conexão com o banco de dados...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $userCount = \App\Models\User::count();
    echo "✅ Conexão com o banco de dados bem-sucedida. Encontrados {$userCount} usuários.\n";
    
    // Listar usuários de teste
    $users = \App\Models\User::select('email', 'role')->get();
    echo "Usuários de teste disponíveis:\n";
    foreach ($users as $user) {
        echo "  - {$user->email} ({$user->role})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Falha na conexão com o banco de dados: " . $e->getMessage() . "\n";
}

echo "\n=== Teste Concluído ===\n";
echo "\nAgora você pode testar o sistema manualmente:\n";
echo "1. Abra http://127.0.0.1:8000/login\n";
echo "2. Faça login com:\n";
echo "   - Admin: admin@admin.com / 123456\n";
echo "   - Gestor: gestor@teste.com / 123456\n";
echo "   - Funcionário: funcionario@teste.com / 123456\n";
echo "3. Teste as operações CRUD:\n";
echo "   - Criar novos usuários (apenas Admin/Gestor)\n";
echo "   - Visualizar detalhes do usuário\n";
echo "   - Editar informações do usuário\n";
echo "   - Excluir usuários (apenas Admin)\n";
echo "4. Teste a funcionalidade de busca de CEP\n";
echo "5. Teste o controle de acesso por perfil\n";
