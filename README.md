
# Sistema de Registro de Ponto - Ponto

Este projeto é um sistema completo de registro de ponto para funcionários, desenvolvido em Laravel. Ele permite o controle de jornada de trabalho, com marcação de horários, relatórios e gestão de usuários com diferentes níveis de acesso.

## Como funciona

O sistema permite que cada funcionário registre até 4 marcações de ponto por dia:
- **Entrada**
- **Saída para almoço**
- **Retorno do almoço**
- **Saída**

Cada registro é salvo em uma única linha na tabela de pontos, facilitando o controle e o cálculo de horas trabalhadas.

### Principais módulos e funcionalidades

- **Autenticação e Perfis**: Login seguro, com perfis de Administrador, Gestor e Funcionário.
- **Gestão de Usuários**: Cadastro, edição, visualização e exclusão de funcionários, com hierarquia de gestores e subordinados.
- **Registro de Ponto**: Interface simples para marcação dos horários, com confirmação e feedback visual.
- **Relatórios de Ponto**:
  - Visualização dos registros diários, com cálculo automático de horas trabalhadas.
  - Filtros por período (data inicial e final) para análise personalizada.
  - Resumo de registros completos, incompletos e total de horas trabalhadas.
  - Relatório individual para cada funcionário, acessível por administradores e gestores.
- **Dashboard**:
  - Visão geral para administradores com estatísticas de usuários.
  - Listagem de funcionários e atalhos para relatórios e edição.
- **Responsividade**: Interface adaptada para desktop e mobile.

### Tecnologias Utilizadas
- Laravel (PHP)
- MySQL
- Bootstrap 5
- JavaScript (AJAX)

## Como rodar o projeto

1. Clone o repositório:
   ```bash
   git clone https://github.com/henrimagri/Ponto.git
   cd Ponto
   ```
2. Instale as dependências:
   ```bash
   composer install
   npm install
   ```
3. Configure o arquivo `.env` com as credenciais do banco de dados.
4. Execute as migrações:
   ```bash
   php artisan migrate --seed
   ```
5. Inicie o servidor:
   ```bash
   php artisan serve
   ```
6. Acesse `http://localhost:8000` no navegador.

## Usuários de Teste
- **Administrador:** admin@admin.com / 123456
- **Gestor:** gestor@teste.com / 123456
- **Funcionário:** funcionario@teste.com / 123456

## Licençaa
MIT
