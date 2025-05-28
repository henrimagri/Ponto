# Sistema de Gerenciamento de Funcionários - Laravel

## Visão Geral
Sistema completo de gerenciamento de funcionários desenvolvido em PHP Laravel com controle de acesso hierárquico e integração com a API ViaCep para busca automática de endereços.

## Funcionalidades Implementadas

### ✅ Autenticação e Autorização
- Sistema de login/logout baseado em sessões Laravel
- Controle de acesso por hierarquia (Admin > Gestor > Funcionário)
- Middleware personalizado para verificação de roles
- Proteção de rotas sensíveis

### ✅ Gestão de Usuários (CRUD)
- **Criar**: Admins e Gestores podem criar novos funcionários
- **Visualizar**: Controle de visibilidade baseado na hierarquia
- **Editar**: Permissões específicas por role
- **Excluir**: Apenas Admins podem excluir usuários

### ✅ Validações Avançadas
- **CPF**: Algoritmo completo de validação com dígitos verificadores
- **E-mail**: Validação de formato e unicidade
- **CEP**: Integração com API ViaCep para busca automática
- **Campos obrigatórios**: Validação server-side e client-side

### ✅ Interface Responsiva
- Bootstrap 5 para design moderno e responsivo
- Navegação dinâmica baseada no role do usuário
- Feedback visual com toasts e alertas
- Máscaras de entrada para CPF e CEP

### ✅ Funcionalidades Especiais
- **Busca de CEP**: Preenchimento automático de endereço via ViaCep
- **Pesquisa e Filtros**: Sistema de busca por nome, email, CPF e role
- **Relacionamentos**: Gestores podem ter subordinados
- **Paginação**: Lista de usuários com paginação automática

## Estrutura do Banco de Dados

### Tabela `users`
```sql
- id (Primary Key)
- name (Nome completo)
- cpf (CPF único)
- email (E-mail único)
- password (Senha criptografada)
- role (admin|gestor|funcionario)
- dob (Data de nascimento)
- manager_id (Referência ao gestor)
- cep, address, numero, complemento, bairro, cidade, uf (Endereço completo)
- timestamps (created_at, updated_at)
```

## Hierarquia de Acesso

### Administrador (`admin`)
- **Pode ver**: Todos os usuários
- **Pode criar**: Qualquer tipo de usuário
- **Pode editar**: Qualquer usuário
- **Pode excluir**: Qualquer usuário (exceto a si mesmo)

### Gestor (`gestor`)
- **Pode ver**: Seus subordinados + perfil próprio
- **Pode criar**: Apenas funcionários (que ficam como seus subordinados)
- **Pode editar**: Seus subordinados
- **Pode excluir**: Não pode excluir

### Funcionário (`funcionario`)
- **Pode ver**: Apenas seu próprio perfil
- **Pode criar**: Não pode criar
- **Pode editar**: Apenas seu próprio perfil (campos limitados)
- **Pode excluir**: Não pode excluir

## Instalação e Configuração

### 1. Requisitos
- PHP 8.1+
- MySQL 5.7+
- Composer
- Laravel 11.x

### 2. Configuração do Banco
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3308
DB_DATABASE=ponto
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Instalação
```bash
# Clone ou baixe o projeto
cd c:/Ponto

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
# Editar .env com suas configurações

# Gerar chave da aplicação
php artisan key:generate

# Executar migrações
php artisan migrate

# Popular com dados de teste
php artisan db:seed
```

### 4. Iniciar o servidor
```bash
php artisan serve
```
Acesse: http://127.0.0.1:8000

## Usuários de Teste

### Administrador
- **E-mail**: admin@admin.com
- **Senha**: 123456
- **Acesso**: Total ao sistema

### Gestor
- **E-mail**: gestor@teste.com
- **Senha**: 123456
- **Acesso**: Gestão de subordinados

### Funcionário
- **E-mail**: funcionario@teste.com
- **Senha**: 123456
- **Acesso**: Apenas perfil próprio

## Como Testar o Sistema

### 1. Teste de Autenticação
1. Acesse `/login`
2. Teste com cada tipo de usuário
3. Verifique se o dashboard muda conforme o role
4. Teste logout

### 2. Teste de CRUD de Usuários
1. **Como Admin**:
   - Acesse "Funcionários" no menu
   - Crie um novo usuário
   - Edite informações
   - Visualize detalhes
   - Teste exclusão

2. **Como Gestor**:
   - Crie apenas funcionários
   - Edite seus subordinados
   - Verifique que não pode ver outros usuários

3. **Como Funcionário**:
   - Visualize apenas seu perfil
   - Tente editar (campos limitados)
   - Verifique que não tem acesso a outros usuários

### 3. Teste de CEP
1. No formulário de criação/edição
2. Digite um CEP válido (ex: 01310-100)
3. Clique fora do campo
4. Verifique se os campos de endereço são preenchidos automaticamente

### 4. Teste de Validações
1. **CPF**: Digite CPFs inválidos e válidos
2. **E-mail**: Teste formatos inválidos
3. **Campos obrigatórios**: Deixe campos em branco
4. **CEP**: Digite CEPs inválidos

### 5. Teste de Busca e Filtros
1. Na lista de funcionários
2. Use a busca por nome, email ou CPF
3. Filtre por role
4. Teste paginação

## Arquivos Importantes

### Controllers
- `AuthController.php`: Autenticação (login/register/logout)
- `UserController.php`: CRUD de usuários + API CEP
- `DashboardController.php`: Dashboard principal

### Models
- `User.php`: Model principal com relacionamentos e métodos de role

### Middleware
- `CheckRole.php`: Verificação de permissões por role

### Views
- `layouts/app.blade.php`: Layout principal
- `auth/`: Telas de login e registro
- `dashboard/`: Dashboards específicos por role
- `users/`: CRUD de usuários (index, create, edit, show)

### Validações
- `ValidCpf.php`: Regra customizada para validação de CPF

### JavaScript
- `public/js/app-enhanced.js`: Máscaras, validações e funcionalidades extras

## APIs Utilizadas

### ViaCep
- **URL**: https://viacep.com.br/ws/{cep}/json/
- **Uso**: Busca automática de endereço por CEP
- **Endpoint local**: `/api/cep/{cep}`

## Funcionalidades Extras Implementadas

1. **Toast Notifications**: Feedback visual para ações
2. **Máscaras de entrada**: CPF e CEP formatados automaticamente
3. **Validação de CPF em tempo real**: JavaScript + PHP
4. **Loading states**: Indicadores visuais durante requisições
5. **Responsividade completa**: Funciona em mobile e desktop
6. **Pesquisa avançada**: Múltiplos critérios de busca
7. **Flash messages**: Confirmações de ações realizadas

## Próximos Passos (Melhorias Futuras)

1. **Relatórios**: Gerar relatórios de funcionários
2. **Exportação**: Export para PDF/Excel
3. **Fotos de perfil**: Upload de imagens
4. **Histórico**: Log de alterações
5. **API REST**: Endpoints para integração externa
6. **Testes automatizados**: PHPUnit tests
7. **Docker**: Containerização da aplicação
8. **Cache**: Implementar cache para melhor performance

## Solução de Problemas

### Erro de Conexão com Banco
```bash
# Verificar se MySQL está rodando
# Conferir credenciais no .env
php artisan migrate:status
```

### Erro de Permissão
```bash
# Verificar permissões das pastas
chmod -R 775 storage bootstrap/cache
```

### Erro de CEP
- Verificar conexão com internet
- Testar manualmente: https://viacep.com.br/ws/01310100/json/

---

**Sistema desenvolvido com Laravel 11, PHP 8, MySQL e Bootstrap 5**
**Desenvolvido em: Maio 2025**
