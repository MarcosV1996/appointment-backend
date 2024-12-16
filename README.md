# Backend - Sistema de Agendamento (Laravel)

Este é o backend do sistema de agendamento para um albergue, desenvolvido com Laravel.

## Requisitos

- PHP 8.0 ou superior
- Banco de dados (pode ser configurado na máquina local)
  
## Instalação

### 1. Clonar o Repositório

Primeiro, clone o repositório do back-end para sua máquina local.

```bash
git clone https://github.com/MarcosV1996/appointment-backend.git
cd backend

#### 2. **Aplique as migrações**

```bash
php artisan migrate

###### 3. Problemas Comuns no Backend
Problema com migrações: Se ao rodar as migrações você encontrar erros, tente limpar o cache de configurações do Laravel:

php artisan config:cache

###### 4. Iniciar servidor no terminal 

php artisan serve
