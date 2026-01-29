# Aplicação de Lista Telefônica

Bem-vindo ao repositório da aplicação de lista telefônica desenvolvida para órgãos públicos. Esta ferramenta fornece uma interface intuitiva, responsiva e segura para gerenciar contatos e ramais internos.

**Destaque da Versão 0.13+:** Interface modernizada com Bootstrap 5, suporte nativo a Temas (Claro/Escuro) e verificação automática de atualizações.
**Destaque da Versão 0.15+:** Alterado captcha do Google recaptcha para o Cloudflare Turnstile, Implementado rate limite no login para segurança adicional, Implementado importação automática do banco de dados.

## Índice

* [Requisitos de Software](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#requisitos-de-software)
* [Instalação](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#instala%C3%A7%C3%A3o)
* [Configuração](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#configura%C3%A7%C3%A3o)
* [Uso](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#uso)
* [Interface e Temas](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#interface-e-temas)
* [Contribuição](https://github.com/adrianolerner/lista-telefonica?tab=readme-ov-file#contribui%C3%A7%C3%A3o)

## Requisitos de Software

Para executar esta aplicação, é necessário:

* **PHP 8.2+** (com extensões `php-mysqli` e `php-curl` habilitadas)
* **MariaDB 10.6+** ou MySQL 8.0+
* **Apache 2.4+** (com módulo `mod_env` e `mod_rewrite` habilitados)

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/adrianolerner/lista-telefonica.git

```


2. Navegue até o diretório:
```bash
cd lista-telefonica

```



## Configuração

### 1. Banco de Dados

Acesse seu gerenciador de banco de dados e execute:

```sql
CREATE DATABASE agenda;
CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'sua_senha_forte';
GRANT ALL PRIVILEGES ON agenda.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;

```

### 2. Importação das Tabelas

Na versão 0.15, o sistema importa as tabelas automaticamente, desde que o usuário tenha os privilégios corretos, caso contrário importe manualamente conforme a seguir.

Importe o arquivo `setup.sql` localizado na raiz do projeto:

```bash
mysql -u usuario -p agenda < setup.sql

```
** ATUALIZAÇÃO PARA A VERSÃO 0.14 **

OBSERVAÇÃO: PARA ATUALIZAR PARA A VERSÃO 0.14 SERÁ NECESSÁRIO IMPORTAR O NOVO ARQUIVO DE BANCO QUE CONTEM A TABELA NOVA DE STATS E NOVAS COLUNAS PARA OS NOVOS DADOS, OU FAZER OS AJUSTES MANUAIS EM SEU BANCO COM BASE NO ARQUIVO SQL DE EXEMPLO.

### 3. Variáveis de Ambiente (Segurança)

Para proteger suas credenciais, a aplicação utiliza variáveis de ambiente. No Ubuntu/Debian com Apache, edite seu VirtualHost:

```bash
sudo nano /etc/apache2/sites-available/000-default.conf

```

Adicione dentro de `<VirtualHost *:80>`:

```apache
SetEnv DB_SERVER localhost
SetEnv DB_USERNAME usuario
SetEnv DB_PASSWORD sua_senha_forte
SetEnv DB_NAME agenda

```

Reinicie o serviço: `sudo systemctl reload apache2`

### 4. Validação por IP e Órgão

No arquivo `index.php`, ajuste o nome da sua entidade:

```php
$orgao = "PREFEITURA DE SUA CIDADE";

```

A aplicação possui um filtro de segurança para exibir o botão de Login apenas em redes internas. No `index.php`, `acesso.php` e `verifica_login.php`, configure a faixa de IP permitida:

```php
if (!fnmatch("172.16.0.*", $ipaddress))

```

*Dica: Para desativar a trava de IP e permitir acesso externo, descomente a linha fixa de IP no topo desses arquivos conforme as instruções nos comentários do código.*

## Uso

* **Acesso Inicial:** O usuário padrão é `admin` com a senha `admin`. **Altere-a imediatamente após o primeiro login.**
* **Manutenção de Senhas:** Caso perca o acesso, utilize o arquivo auxiliar `trocahash.php`. Insira a nova senha no campo indicado e execute via terminal: `php trocahash.php`. O script gerará o hash seguro para inserção manual no banco de dados.
* **Relatórios:** O sistema gera listas em PDF formatadas através da biblioteca FPDF. As variáveis de cabeçalho do PDF podem ser alteradas diretamente no topo do arquivo `gerapdf.php`.

## Interface e Temas

A aplicação utiliza o **Bootstrap 5.3** e oferece suporte a Temas:

* **Dark Mode / Light Mode:** O usuário pode alternar o tema através do botão na barra de navegação. A preferência é salva automaticamente no navegador.
* **Responsividade:** A tabela de contatos utiliza o plugin *Responsive* do DataTables, adaptando-se a celulares, tablets e desktops.

## Contribuição

1. Faça um **Fork** do projeto.
2. Crie uma **Branch** para sua modificação (`git checkout -b feature/nova-funcao`).
3. Faça o **Commit** (`git commit -am 'Adiciona nova função'`).
4. **Push** para a branch (`git push origin feature/nova-funcao`).
5. Abra um **Pull Request**.

## Referências

* [FPDF.org](http://fpdf.org) - Geração de PDFs
* [DataTables.net](https://datatables.net) - Gestão de tabelas dinâmicas
* [FontAwesome](https://fontawesome.com) - Ícones da interface

---

*Mantenha os créditos do autor original: Adriano Lerner Biesek.*

---
