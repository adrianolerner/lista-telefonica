# Aplicação de Lista Telefônica (v0.16)

Bem-vindo ao repositório da aplicação de lista telefônica desenvolvida para órgãos públicos. Esta ferramenta fornece uma interface intuitiva, responsiva e segura para gerenciar contatos e ramais internos.

**Destaque da Versão 0.16:**

* 🚀 **Instalação Automática:** O sistema cria o banco e importa as tabelas automaticamente no primeiro acesso.
* 🛡️ **Segurança Reforçada:** Implementação de Rate Limit (proteção contra força bruta) e Cloudflare Turnstile (Captcha).
* ⚙️ **Configuração Centralizada:** Todas as regras de acesso (IP) e chaves de segurança geridas em um único arquivo.
* ⭐ **Favoritos:** Possibilidade de favoritar contatos (salvos localmente no navegador).
* 📊 **Dashboard:** Gráficos de estatísticas de acesso e distribuição de ramais.

## Índice

* [Requisitos de Software](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#requisitos-de-software)
* [Instalação](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#instala%C3%A7%C3%A3o)
* [Configuração Centralizada](https://github.com/adrianolerner/lista-telefonica%3Ftab%3Dreadme-ov-file%23configura%25C3%25A7%25C3%25A3o-centralizada)
* [Segurança e Captcha](https://github.com/adrianolerner/lista-telefonica%3Ftab%3Dreadme-ov-file%23seguran%25C3%25A7a-e-captcha)
* [Uso](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#uso)
* [Contribuição](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#contribui%C3%A7%C3%A3o)

## Requisitos de Software

Para executar esta aplicação, é necessário:

* **PHP 8.1+** (com extensões `php-mysqli` e `php-curl` habilitadas).
* **MariaDB 10.6+** ou MySQL 8.0+.
* **Apache 2.4+** (ou Nginx/IIS).
* Permissão de escrita na pasta raiz (para o processo de auto-instalação apagar o arquivo SQL temporário).
* Permissão correta do usuário do banco de dados para criação do banco e tabelas.

## Instalação

A versão 0.16 introduziu o conceito de **Auto-Instalação**. Não é mais necessário importar SQL manualmente.

1. Clone o repositório ou baixe os arquivos para seu servidor web (`/var/www/html` ou similar).
2. Certifique-se de que o arquivo `setup.sql` esteja na raiz do projeto junto com o `config.php`.
3. Acesse a aplicação pelo navegador (ex: `http://localhost/lista`).
4. O sistema detectará a ausência do banco de dados, fará a criação, importação das tabelas e apagará o arquivo `setup.sql` automaticamente.

## Configuração Centralizada

Esqueça a edição de múltiplos arquivos. Agora, tudo é controlado via **Variáveis de Ambiente** ou editando apenas o arquivo **`config.php`**.

### 1. Banco de Dados

Você tem duas opções:

**Opção A: Variáveis de Ambiente (Recomendado para Docker/Linux)**
Configure no seu VirtualHost ou arquivo `.env` do servidor:

```bash
SetEnv DB_SERVER "localhost"
SetEnv DB_USERNAME "seu_usuario"
SetEnv DB_PASSWORD "sua_senha"
SetEnv DB_NAME "agenda"

```

**Opção B: Edição Direta**
Edite o arquivo `config.php` caso não possa usar variáveis de ambiente:

```php
$DB_SERVER   = 'localhost';
$DB_USERNAME = 'root'; // Altere aqui
$DB_PASSWORD = '';     // Altere aqui
$DB_NAME     = 'agenda';

```

### 2. Controle de Acesso (IP e Rede)

Para restringir o acesso ao Painel Administrativo apenas para a rede interna (Intranet), ajuste as constantes no início do `config.php`.

```php
// true = Bloqueia acesso externo ao admin / false = Libera geral
define('RESTRITO_POR_IP', true);

// Define a faixa de IP permitida (aceita curinga *)
define('FAIXA_IP_PERMITIDA', '172.16.0.*'); 

```

*Se o IP do usuário não corresponder à faixa, o botão de login será ocultado e o acesso direto às páginas administrativas será bloqueado.*

## Segurança e Captcha

O sistema possui proteção contra ataques de força bruta (bloqueia o IP após 5 tentativas falhas) e integração opcional com **Cloudflare Turnstile**.

### Configurando o Captcha

Para ativar o Captcha no login, defina as chaves no `config.php` (ou via variáveis de ambiente `CF_SITE_KEY` e `CF_SECRET_KEY`):

```php
// No config.php:
$cf_site_key   = 'SUA_CHAVE_SITE_CLOUDFLARE';
$cf_secret_key = 'SUA_CHAVE_SECRETA_CLOUDFLARE';

```
### Nome do órgão

Para alterar o nome do órgão que aparece na página principal, defina o valor da variável de ambiente NOME_ORGAO ou diretamente no valor da variável (NOME DA PREFEITURA) no arquivo `config.php` como abaixo:

```php
// No config.php:
$orgao = getenv('NOME_ORGAO') ?: 'NOME DA PREFEITURA';

```

* **Ativação Automática:** Se as chaves estiverem preenchidas, o Captcha aparece.
* **Desativação Automática:** Se as variáveis estiverem vazias (`''`), o Captcha é desabilitado automaticamente.
* **Modo Desenvolvedor:** Se você acessar via `localhost` ou IPs de desenvolvimento definidos no código, o Captcha é ignorado automaticamente para facilitar seus testes.

## Uso

* **Acesso Padrão:**
* Usuário: `admin`
* Senha: `admin`
* *Nota: O sistema solicitará a troca da senha ou você deve alterá-la imediatamente no menu de usuários.*


* **Dashboard:** Acesse `/dashboard.php` para visualizar gráficos de acessos diários e estatísticas de uso.
* **Favoritos:** Clique na estrela (⭐) ao lado de um contato para fixá-lo no topo da sua lista. Essa preferência é salva no seu navegador.
* **Relatórios:** O sistema gera listas em PDF através da biblioteca FPDF (`gerapdf.php`).

## Interface e Temas

A aplicação utiliza o **Bootstrap 5.3** e oferece:

* **Dark Mode / Light Mode:** Alternância de tema com persistência local.
* **Responsividade:** Tabela adaptável para dispositivos móveis.

## Contribuição

1. Faça um **Fork** do projeto.
2. Crie uma **Branch** (`git checkout -b feature/melhoria-x`).
3. Faça o **Commit** (`git commit -am 'Adiciona melhoria X'`).
4. **Push** para a branch (`git push origin feature/melhoria-x`).
5. Abra um **Pull Request**.

## Referências

* [FPDF.org](http://fpdf.org) - Geração de PDFs
* [DataTables.net](https://datatables.net) - Gestão de tabelas
* [Chart.js](https://www.chartjs.org/) - Gráficos do Dashboard
* [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/) - Proteção anti-robô

---

*Desenvolvido para agilizar a comunicação no serviço público.*