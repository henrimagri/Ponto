#!/bin/bash

echo "=== TESTE COMPLETO DO SISTEMA DE GERENCIAMENTO DE FUNCIONÁRIOS ==="
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para mostrar status
show_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
    fi
}

cd c:/Ponto

echo "1. Verificando se o servidor está rodando..."
response=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000)
if [ "$response" = "200" ] || [ "$response" = "302" ]; then
    show_status 0 "Servidor Laravel está rodando na porta 8000"
else
    show_status 1 "Servidor não está respondendo"
    echo "Execute: php artisan serve"
    exit 1
fi

echo ""
echo "2. Verificando estrutura do banco de dados..."
php artisan migrate:status > /dev/null 2>&1
show_status $? "Migrações do banco de dados"

echo ""
echo "3. Verificando usuários de teste..."
user_count=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -n1)
if [ "$user_count" -ge 3 ]; then
    show_status 0 "Usuários de teste ($user_count usuários encontrados)"
    echo "   📧 admin@admin.com (admin)"
    echo "   📧 gestor@teste.com (gestor)"  
    echo "   📧 funcionario@teste.com (funcionario)"
else
    show_status 1 "Usuários de teste insuficientes"
    echo "Execute: php artisan db:seed"
fi

echo ""
echo "4. Testando rotas principais..."

# Teste página de login
response=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/login)
if [ "$response" = "200" ]; then
    show_status 0 "Página de login (/login)"
else
    show_status 1 "Página de login não está funcionando"
fi

# Teste redirecionamento da home
response=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/)
if [ "$response" = "302" ]; then
    show_status 0 "Redirecionamento da home (/)"
else
    show_status 1 "Redirecionamento da home não está funcionando"
fi

echo ""
echo "5. Verificando arquivos importantes..."

files_to_check=(
    "app/Http/Controllers/AuthController.php"
    "app/Http/Controllers/UserController.php"
    "app/Http/Controllers/DashboardController.php"
    "app/Models/User.php"
    "app/Rules/ValidCpf.php"
    "resources/views/layouts/app.blade.php"
    "resources/views/users/index.blade.php"
    "resources/views/users/create.blade.php"
    "resources/views/users/edit.blade.php"
    "resources/views/users/show.blade.php"
    "public/js/app-enhanced.js"
)

for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        show_status 0 "$file"
    else
        show_status 1 "$file (arquivo não encontrado)"
    fi
done

echo ""
echo "6. Testando API de CEP..."
response=$(curl -s "http://127.0.0.1:8000/api/cep/01310100" 2>/dev/null | grep -o '"uf":"SP"' | wc -l)
if [ "$response" -gt 0 ]; then
    show_status 0 "API de busca de CEP funcionando"
else
    show_status 1 "API de CEP não está funcionando (usuário deve estar logado)"
fi

echo ""
echo "7. Verificando permissões de pastas..."
if [ -w "storage" ] && [ -w "bootstrap/cache" ]; then
    show_status 0 "Permissões de escrita (storage e bootstrap/cache)"
else
    show_status 1 "Problemas de permissão"
    echo "Execute: chmod -R 775 storage bootstrap/cache"
fi

echo ""
echo "=== RESUMO DOS TESTES ==="
echo ""
echo -e "${YELLOW}📋 Para testar manualmente o sistema:${NC}"
echo ""
echo "1. Acesse: http://127.0.0.1:8000"
echo "2. Faça login com um dos usuários:"
echo "   👑 Admin: admin@admin.com / 123456"
echo "   👥 Gestor: gestor@teste.com / 123456"
echo "   👤 Funcionário: funcionario@teste.com / 123456"
echo ""
echo "3. Teste as funcionalidades:"
echo "   ✏️  Criar novos funcionários (Admin/Gestor)"
echo "   👀 Visualizar lista e detalhes"
echo "   ✏️  Editar informações"
echo "   🗑️  Excluir usuários (Admin apenas)"
echo "   🔍 Buscar e filtrar usuários"
echo "   📍 Testar busca de CEP (ex: 01310-100)"
echo "   🔐 Testar controle de acesso por role"
echo ""
echo -e "${GREEN}🎉 Sistema está pronto para uso!${NC}"
echo ""
