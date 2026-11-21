# Larachurch — Sistema de Gestão Administrativa, Cadastral e Financeira

Sistema web multiusuário desenvolvido em **PHP / Laravel** para centralizar a gestão cadastral de membros, rotinas administrativas, emissão de documentos em lote, conciliação financeira via Pix e disparos automatizados de comunicados para múltiplas comunidades.

A solução foi arquitetada para substituir controles descentralizados em planilhas e processos manuais de secretaria, garantindo rastreabilidade operacional, padronização de documentos e ganho de escala na administração.

---

## Principais Funcionalidades

* **Controle de Acesso Multiusuário e Multiunidade:** Segmentação de permissões e visibilidade de dados por comunidade/paróquia e perfil operacional.
* **Gestão Cadastral de Membros:** Cadastro estruturado com validação de documentos, endereços, contatos, histórico de vínculos e filtros administrativos.
* **Geração de Cartas e PDFs em Lote (DomPDF + ZIP):** Emissão automatizada de cartas de contribuição e informativos personalizados em PDF, com empacotamento automático em arquivos `.zip` para impressão ou envio em massa.
* **Integração Pix e Rastreamento Bancário:** Geração dinâmica de QR Codes e códigos Pix com identificadores exclusivos vinculados ao lançamento, facilitando a conciliação com extratos bancários.
* **Disparo Automatizado de E-mails via Filas:** Envio assíncrono de comunicados e anexos em PDF utilizando **Laravel Queues**, evitando timeouts e gargalos durante disparos em lote.
* **Painel Administrativo:** Interface responsiva para gestão diária, emissão de listagens e acompanhamento operacional.

---

## Stack Tecnológica

* **Backend:** PHP 8+, Laravel
* **Frontend:** Blade, Livewire, AdminLTE / Bootstrap
* **Banco de Dados:** MySQL
* **Ambiente e Infraestrutura:** Docker, Laravel Sail
* **Processamento e Integrações:** DomPDF (geração de PDFs), Laravel Queues (processamento assíncrono), geração de payload/QR Code Pix e exportação em lote (`ZipArchive`)

---

## Estrutura do Projeto

```text
├── app/
│   ├── Helpers/          # Funções auxiliares de formatação (CPF, CNPJ, CEP, telefone) e regras de apoio
│   ├── Http/             # Controllers, Requests de validação e Middlewares de acesso
│   ├── Jobs/             # Tarefas assíncronas para envio de e-mails e geração em lote
│   ├── Livewire/         # Componentes reativos da interface administrativa
│   └── Models/           # Entidades Eloquent e relacionamentos (comunidades, membros, lançamentos)
├── database/
│   ├── migrations/       # Estrutura relacional do banco de dados
│   └── seeders/          # Dados iniciais e fictícios para ambiente de desenvolvimento
├── resources/
│   └── views/            # Templates Blade do painel administrativo e layouts de cartas em PDF
└── routes/
    └── web.php           # Definição de rotas autenticadas e administrativas
```

---

## Como Executar Localmente (Docker / Laravel Sail)

1. **Clone o repositório:**
   ```bash
   git clone [https://github.com/amroberto/NOME-DO-REPOSITORIO.git](https://github.com/amroberto/NOME-DO-REPOSITORIO.git)
   cd NOME-DO-REPOSITORIO
   ```

2. **Configure as variáveis de ambiente:**
   ```bash
   cp .env.example .env
   ```
   *(Ajuste no `.env` as credenciais locais de banco de dados, servidor SMTP e parâmetros da aplicação.)*

3. **Instale as dependências e suba os containers:**
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php83-composer:latest \
       composer install --ignore-platform-reqs

   ./vendor/bin/sail up -d
   ```

4. **Gere a chave da aplicação e execute as migrations:**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```

5. **Inicie o worker de filas (para envios de e-mail em segundo plano):**
   ```bash
   ./vendor/bin/sail artisan queue:work
   ```

A aplicação ficará disponível em `http://localhost` (ou no domínio local configurado no `.env`).

---

## Segurança e Privacidade (LGPD)

Todas as credenciais de acesso, chaves de integração, dados bancários e registros reais são isolados via variáveis de ambiente (`.env`) e excluídos do controle de versão via `.gitignore`. O repositório contém exclusivamente a arquitetura da aplicação e dados fictícios de exemplo.

---

## Direitos Autorais

© 2026 Alexandre Martins Roberto. Todos os direitos reservados.  
Código disponibilizado exclusivamente para fins de portfólio e avaliação técnica.