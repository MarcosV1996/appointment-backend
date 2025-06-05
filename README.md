# Backend - Sistema de Agendamento (Laravel) - Guia com Docker


Este é o backend do sistema de agendamento para um albergue, desenvolvido com Laravel.

## Requisitos

- PHP 8.0 ou superior
- Banco de dados (pode ser configurado na máquina local)
- Docker e Docker Compose instalados (Como instalar no Android/Redmi)
- Git (opcional, se for clonar o repositório)
  
## Instalação

### 1. Clonar o Repositório

Primeiro, clone o repositório do back-end para sua máquina local.

```bash
git clone https://github.com/MarcosV1996/appointment-backend.git
cd backend

#### Passo 2: Configurar o Ambiente com Docker
Crie/copie o arquivo .env (baseado no .env.example):

bash
cp .env.example .env

#### passo 3: Edite o .env (use um editor como nano ou vim):

nano .env

#### passo 4: Configure as variáveis do banco de dados:

DB_CONNECTION=sqlite
DB_HOST=db  # Nome do serviço no docker-compose.yml
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=senha_segura

#### passo 5: Fazer a biuld dos Containers
docker-compose build frontend backend

###Passo 6: Iniciar os Containers
docker-compose up -d

###Passo 7: Instalar Dependências e Configurar
Acesse o container do Laravel:

docker-compose exec app bash

### Passo 8: Dentro do container:

composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

Passo 9: Acessar o Sistema
http:localhost:8080/login
