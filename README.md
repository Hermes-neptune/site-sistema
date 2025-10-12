# Site Sistema - Sistema de Gestão Institucional

<div align="center">
  <p><em>Sistema completo de gestão para instituições de ensino com controle de presença, gamificação e interação social</em></p>

  <img alt="last-commit" src="https://img.shields.io/github/last-commit/Hermes-neptune/site-sistema?style=flat&logo=git&logoColor=white&color=0080ff">
  <img alt="repo-top-language" src="https://img.shields.io/github/languages/top/Hermes-neptune/site-sistema?style=flat&color=0080ff">
  <img alt="repo-language-count" src="https://img.shields.io/github/languages/count/Hermes-neptune/site-sistema?style=flat&color=0080ff">
  
  <p><em>Desenvolvido com as tecnologias:</em></p>
  <img alt="PHP" src="https://img.shields.io/badge/PHP-777BB4.svg?style=flat&logo=PHP&logoColor=white">
  <img alt="PostgreSQL" src="https://img.shields.io/badge/PostgreSQL-336791.svg?style=flat&logo=PostgreSQL&logoColor=white">
  <img alt="JavaScript" src="https://img.shields.io/badge/JavaScript-F7DF1E.svg?style=flat&logo=JavaScript&logoColor=black">
  <img alt="Composer" src="https://img.shields.io/badge/Composer-885630.svg?style=flat&logo=Composer&logoColor=white">
  <img alt="JSON" src="https://img.shields.io/badge/JSON-000000.svg?style=flat&logo=JSON&logoColor=white">
</div>

## 📑 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Começando](#começando)
  - [Pré-requisitos](#pré-requisitos)
  - [Instalação](#instalação)
  - [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [API](#api)
- [Segurança](#segurança)
- [Uso](#uso)
- [Contribuindo](#contribuindo)
- [Licença](#licença)
- [Autor](#autor)

## 🎯 Sobre o Projeto

O **Site Sistema** é uma plataforma web completa desenvolvida para gestão institucional, focada em instituições de ensino. O sistema oferece uma solução integrada que combina controle de presença via QR Code, sistema de créditos/pontos, interação social entre usuários, gamificação e muito mais.

**Por que usar o Site Sistema?**

- ✅ **Gestão Completa**: Controle total de usuários, presença e atividades
- ✅ **Gamificação**: Sistema de pontos e medalhas para engajamento
- ✅ **Social**: Chat privado, grupos e sistema de amizades
- ✅ **Seguro**: Implementação robusta de segurança e criptografia
- ✅ **Moderno**: Interface responsiva com tema claro/escuro
- ✅ **Escalável**: Arquitetura preparada para crescimento

## ⚡ Funcionalidades

### 👤 Autenticação e Gestão de Usuários
- Sistema de login seguro para usuários e administradores
- Registro de novos usuários com validação de dados
- Recuperação de senha via email
- Gestão de perfil com foto personalizada
- Sistema de permissões (usuário comum e administrador)
- Proteção contra ataques de força bruta e SQL injection

### 📊 Controle de Presença
- Geração dinâmica de QR Codes para eventos
- Scanner de QR Code via câmera
- Registro automático de presença
- Histórico completo de presenças
- Validação de presença única por dia
- Relatórios administrativos de presença

### 🎮 Sistema de Gamificação
- **Sistema de Créditos/Pontos**: Acumule pontos participando de atividades
- **Medalhas**: Sistema de conquistas e medalhas personalizadas
- **Ranking**: Visualização de posição entre usuários
- **Recompensas**: Troca de pontos por benefícios
- Histórico de conquistas e atividades

### 💬 Sistema de Mensagens e Social
- **Chat Privado**: Conversas individuais em tempo real
- **Grupos**: Criação e gestão de grupos de conversa
  - Grupos públicos e privados
  - Sistema de convites
  - Papéis de usuário (admin, membro)
  - Controle de mensagens lidas
- **Sistema de Amizades**: 
  - Enviar/aceitar solicitações de amizade
  - Lista de amigos
  - Perfis de usuários
- **Notificações**: Sistema de notificações em tempo real

### 📰 Gestão de Conteúdo
- Feed de notícias e avisos
- Sistema de pendências
- Visualização de histórico de atividades
- Área administrativa para gestão de conteúdo

### 🎨 Interface e Experiência
- Design responsivo para todos os dispositivos
- Tema claro/escuro alternável
- Navegação intuitiva
- Feedback visual para ações do usuário
- Acessibilidade conforme WCAG
- Animações suaves e modernas

### 🔐 Recursos de Segurança
- Criptografia de senhas com SHA-256 e salt
- Proteção contra XSS, CSRF e SQL Injection
- Sessões seguras com timeout automático
- Validação de entrada em todas as requisições
- Armazenamento seguro de dados sensíveis
- Log de atividades suspeitas
- Comunicação via HTTPS

## 🛠️ Tecnologias Utilizadas

### Backend
- **PHP 7.4+**: Linguagem principal do servidor
- **PostgreSQL**: Banco de dados relacional
- **Composer**: Gerenciador de dependências PHP

### Frontend
- **HTML5/CSS3**: Estrutura e estilização
- **JavaScript**: Interatividade do cliente
- **Font Awesome**: Ícones

### Bibliotecas e Dependências
- **chillerlan/php-qrcode** (^5.0): Geração de QR Codes
- **phpmailer/phpmailer** (^6.10): Envio de emails
- **vlucas/phpdotenv** (^5.6): Gestão de variáveis de ambiente
- **firebase/php-jwt** (^6.11): Autenticação JWT

### Infraestrutura
- Servidor Web (Apache/Nginx)
- SSL/TLS para HTTPS
- Supabase para armazenamento de arquivos

## 🚀 Começando

### Pré-requisitos

Antes de iniciar, certifique-se de ter instalado:

- **Servidor Web**: Apache 2.4+ ou Nginx 1.18+
- **PHP**: Versão 7.4 ou superior
  - Extensões necessárias: PDO, pdo_pgsql, mbstring, openssl, json
- **PostgreSQL**: Versão 12 ou superior
- **Composer**: Gerenciador de dependências PHP
- **Git**: Para clonar o repositório

### Instalação

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/Hermes-neptune/site-sistema.git
   cd site-sistema
   ```

2. **Instale as dependências do PHP:**
   ```bash
   composer install
   ```

3. **Configure o banco de dados:**
   
   Crie um banco de dados PostgreSQL:
   ```sql
   CREATE DATABASE site_sistema;
   ```

### Configuração

1. **Configure as variáveis de ambiente:**

   Crie um arquivo `.env` na raiz do projeto:
   ```env
   # Database Configuration
   DB_ENDPOINT_ID=seu_endpoint_id
   DB_HOST=seu_host_postgresql
   DB_PORT=5432
   DB_NAME=site_sistema
   DB_USER=seu_usuario
   DB_PASSWORD=sua_senha

   # Encryption
   ENCRYPTION_KEY=sua_chave_de_criptografia_segura

   # Email Configuration (PHPMailer)
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=seu_email@gmail.com
   SMTP_PASS=sua_senha_app
   SMTP_FROM=seu_email@gmail.com
   SMTP_FROM_NAME=Site Sistema

   # Application
   APP_URL=http://localhost:8000
   ```

2. **Importe o esquema do banco de dados:**

   Execute os scripts SQL necessários para criar as tabelas:
   ```bash
   # Você precisará ter os scripts de migração ou criar as tabelas manualmente
   # As principais tabelas incluem: users, presencas, creditos, medalhas, 
   # amizades, grupos, grupo_membros, mensagens, etc.
   ```

3. **Configure permissões de arquivos:**
   ```bash
   chmod -R 755 /caminho/do/projeto
   chmod -R 777 /caminho/do/projeto/uploads  # Se existir
   ```

4. **Configure o servidor web:**

   Para Apache, crie um VirtualHost ou use `.htaccess`
   
   Para Nginx, configure o server block adequadamente

### Iniciando a Aplicação

#### Desenvolvimento Local

Use o servidor embutido do PHP:
```bash
php -S localhost:8000
```

Acesse no navegador: `http://localhost:8000`

#### Produção

Configure seu servidor web (Apache/Nginx) para servir a aplicação com HTTPS habilitado.

## 📁 Estrutura do Projeto

```
site-sistema/
├── api/                      # APIs RESTful
│   ├── adm/                  # Endpoints administrativos
│   │   ├── login.php         # Login de administrador
│   │   ├── add_credits.php   # Adicionar créditos
│   │   ├── get_user_by_rm.php # Buscar usuário por RM
│   │   └── ...
│   ├── user/                 # Endpoints de usuário
│   │   ├── login.php         # Login de usuário
│   │   ├── create_group.php  # Criar grupo
│   │   ├── send_message.php  # Enviar mensagem
│   │   ├── friend_request.php # Solicitações de amizade
│   │   ├── get_medalhas.php  # Obter medalhas
│   │   └── ...
│   ├── wemos/               # Integração com dispositivos IoT
│   └── config_api.php       # Configuração da API
├── css/                     # Arquivos de estilo
│   ├── protected.css        # Estilos de páginas protegidas
│   ├── chat.css            # Estilos do chat
│   ├── thema.css           # Temas claro/escuro
│   └── ...
├── js/                      # Scripts JavaScript
├── img/                     # Imagens e recursos visuais
├── process/                 # Processamento de dados
│   ├── db_connect.php      # Conexão com banco de dados
│   ├── authenticate.php    # Autenticação
│   ├── register_process.php # Processo de registro
│   ├── mensagens.php       # Processamento de mensagens
│   ├── creditos.php        # Gestão de créditos
│   └── ...
├── vendor/                  # Dependências do Composer
├── index.php               # Página principal
├── login.php               # Página de login
├── register.php            # Página de registro
├── chat.php                # Interface de chat
├── friends.php             # Página de amigos
├── checkin.php             # Check-in de presença
├── cracha.php              # Crachá digital
├── config.php              # Configurações da conta
├── composer.json           # Dependências PHP
├── .env                    # Variáveis de ambiente (não versionado)
└── README.md              # Este arquivo
```

## 🔌 API

O sistema possui uma API RESTful completa dividida em duas categorias:

### Endpoints de Usuário (`/api/user/`)

- **Autenticação**
  - `POST /api/user/login.php` - Login de usuário
  - `POST /api/user/forgot_password.php` - Recuperação de senha

- **Perfil e Social**
  - `POST /api/user/search_user.php` - Buscar usuários
  - `POST /api/user/friend_request.php` - Gerenciar amizades
  - `GET /api/user/friends.php` - Listar amigos
  - `POST /api/user/get_medalhas.php` - Obter medalhas

- **Mensagens**
  - `POST /api/user/send_message.php` - Enviar mensagem privada
  - `POST /api/user/fetch_messages.php` - Buscar mensagens
  - `GET /api/user/get_conversations.php` - Listar conversas

- **Grupos**
  - `POST /api/user/create_group.php` - Criar grupo
  - `POST /api/user/group.php` - Listar grupos
  - `POST /api/user/invite_group.php` - Convidar para grupo
  - `POST /api/user/send_messages_group.php` - Enviar mensagem no grupo
  - `POST /api/user/exit_group.php` - Sair do grupo

- **Outros**
  - `GET /api/user/notices.php` - Obter notícias
  - `GET /api/user/popular_games.php` - Jogos populares

### Endpoints Administrativos (`/api/adm/`)

- `POST /api/adm/login.php` - Login administrativo
- `POST /api/adm/add_credits.php` - Adicionar créditos a usuários
- `POST /api/adm/get_user_by_rm.php` - Buscar usuário por RM
- `GET /api/adm/get_history.php` - Histórico de atividades
- `POST /api/adm/update_profile.php` - Atualizar perfil de usuário

### Formato de Resposta

Todas as APIs retornam JSON no formato:
```json
{
  "success": true,
  "message": "Mensagem de sucesso",
  "data": {}
}
```

Em caso de erro:
```json
{
  "success": false,
  "message": "Mensagem de erro"
}
```

## 🔒 Segurança

A segurança é uma prioridade fundamental neste sistema, com múltiplas camadas de proteção:

### Autenticação e Autorização
- Senhas criptografadas com SHA-256 e salt único por usuário
- Sistema de sessões seguro com timeout automático
- Validação de permissões em todas as operações sensíveis
- Proteção contra acesso não autorizado a recursos

### Proteção contra Ataques
- **SQL Injection**: Uso exclusivo de prepared statements
- **XSS (Cross-Site Scripting)**: Sanitização de todas as entradas e saídas
- **CSRF (Cross-Site Request Forgery)**: Tokens de validação
- **Session Hijacking**: Regeneração de IDs de sessão
- **Brute Force**: Limitação de tentativas de login

### Criptografia e Privacidade
- Chave de criptografia para dados sensíveis (via `ENCRYPTION_KEY`)
- Comunicação HTTPS obrigatória em produção
- Armazenamento seguro de dados pessoais
- Logs de auditoria para ações críticas

### Boas Práticas
- Validação rigorosa de todos os inputs
- Escape de outputs para evitar XSS
- Princípio do menor privilégio
- Separação de responsabilidades (usuário/admin)
- Headers de segurança configurados

## 💻 Uso

### Acesso ao Sistema

1. **Registro de Novo Usuário**
   - Acesse `/register.php`
   - Preencha os dados solicitados (nome, email, RM, senha)
   - Confirme seu email se necessário

2. **Login**
   - Acesse `/login.php`
   - Insira seu RM e senha
   - Você será redirecionado para a página principal

3. **Recuperação de Senha**
   - Na página de login, clique em "Esqueci minha senha"
   - Informe seu email cadastrado
   - Siga as instruções recebidas por email

### Funcionalidades Principais

#### Dashboard (`/index.php`)
- Visualize seu perfil e créditos
- Veja notícias e avisos recentes
- Acesse pendências
- Verifique suas mensagens

#### Check-in de Presença (`/checkin.php`)
- Escaneie o QR Code apresentado
- Registre sua presença automaticamente
- Visualize histórico de presenças

#### Chat e Mensagens (`/chat.php`)
- Converse com amigos em tempo real
- Crie e participe de grupos
- Envie mensagens, emojis e arquivos
- Receba notificações de novas mensagens

#### Sistema de Amizades (`/friends.php`)
- Busque outros usuários
- Envie solicitações de amizade
- Aceite ou recuse solicitações
- Visualize perfis de amigos

#### Configurações (`/config.php`)
- Atualize suas informações pessoais
- Altere sua foto de perfil
- Configure preferências de notificação
- Gerencie privacidade da conta
- Altere sua senha

#### Crachá Digital (`/cracha.php`)
- Gere seu crachá digital
- Compartilhe ou imprima
- Visualize suas informações principais

### Para Administradores

Administradores têm acesso a funcionalidades adicionais:
- Adicionar créditos a usuários
- Visualizar histórico completo de atividades
- Gerenciar usuários
- Acessar relatórios e estatísticas
- Moderar conteúdo e grupos

## 🧪 Testes

O sistema inclui testes automatizados para garantir qualidade e estabilidade:

```bash
vendor/bin/phpunit
```

Os testes cobrem:
- Autenticação e autorização
- Manipulação de dados
- Funcionalidades da interface
- Integração com banco de dados
- Performance sob carga
- APIs e endpoints

## 🤝 Contribuindo

Contribuições são bem-vindas! Siga os passos abaixo:

1. **Fork o projeto**
   ```bash
   # Clique em "Fork" no GitHub
   ```

2. **Clone seu fork**
   ```bash
   git clone https://github.com/seu-usuario/site-sistema.git
   cd site-sistema
   ```

3. **Crie uma branch para sua feature**
   ```bash
   git checkout -b feature/minha-nova-feature
   ```

4. **Faça suas alterações e commit**
   ```bash
   git add .
   git commit -m 'Adiciona nova funcionalidade X'
   ```

5. **Push para o GitHub**
   ```bash
   git push origin feature/minha-nova-feature
   ```

6. **Abra um Pull Request**
   - Acesse seu fork no GitHub
   - Clique em "New Pull Request"
   - Descreva suas alterações detalhadamente

### Diretrizes para Contribuição

- Siga os padrões de código existentes
- Comente código complexo
- Adicione testes para novas funcionalidades
- Atualize a documentação quando necessário
- Mantenha commits claros e concisos
- Teste localmente antes de enviar PR

## 📝 Licença

Este projeto está licenciado sob a **MIT License**. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

```
MIT License

Copyright (c) 2025 Hermes

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

## 👨‍💻 Autor

<div align="center">
  <p><strong>Desenvolvido como parte de um Trabalho de Conclusão de Curso (TCC)</strong></p>
  <p>Por <strong>MTSmalow</strong></p>
  <p>© 2025 Todos os direitos reservados</p>
  
  <br>
  
  <p>Se este projeto foi útil para você, considere dar uma ⭐️!</p>
</div>

---

<div align="center">
  <p>
    <a href="https://github.com/Hermes-neptune/site-sistema/issues">Reportar Bug</a>
    ·
    <a href="https://github.com/Hermes-neptune/site-sistema/issues">Solicitar Feature</a>
    ·
    <a href="https://github.com/Hermes-neptune/site-sistema">Documentação</a>
  </p>
</div>
