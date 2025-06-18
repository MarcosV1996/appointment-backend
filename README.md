# Sistema de Gerenciamento de Vagas em Albergues (Backend - Laravel)

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![SQLite](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)](https://www.sqlite.org/index.html)

Este é o **backend do sistema de agendamento de vagas para pessoas em situação de vulnerabilidade social**, desenvolvido em **Laravel**. Ele atua como a API RESTful que suporta o frontend (desenvolvido em Angular 17), sendo uma parte crucial do Trabalho de Conclusão de Curso em Tecnologia em Sistemas para Internet pela UTFPR.

---

## Contexto do Projeto: Otimizando a Gestão de Albergues

Este projeto foi concebido para **substituir o sistema manual, baseado em planilhas Excel**, utilizado pelo Albergue Noturno de Guarapuava. O backend é o coração da modernização, visando:

* **Centralizar e gerenciar** todas as informações de agendamentos e acolhidos.
* **Fornecer uma API robusta** para o frontend e futuras integrações.
* **Automatizar a geração de relatórios** e a gestão de dados.
* **Garantir a segurança e integridade** das informações do sistema.

---

## Stack Tecnológica

#### Backend:

* **Laravel 11**: O framework PHP robusto para construir a API RESTful.
* **PHP 8.2+**: A linguagem de programação que sustenta o Laravel.
* **SQLite**: Banco de dados leve  para desenvolvimento e produção, facilitando a portabilidade.
* **Laravel Sanctum**: Para autenticação e autorização via API Tokens.

#### Frontend:

* **Angular 17**: Interface de usuário que consome esta API.

#### Infraestrutura:

* **Docker**: Containerização das aplicações, para  ambientes isolados e consistentes.
* **Docker Compose**: Orquestração dos containers para facilitar o deploy e gerenciamento do ambiente de desenvolvimento.

