```markdown
# Aplicação de Lista Telefônica

Bem-vindo ao repositório da aplicação de lista telefônica desenvolvida para um órgão público.
Esta aplicação foi construída utilizando PHP, HTML, CSS, JavaScript e MariaDB.
O objetivo desta aplicação é fornecer uma interface intuitiva para gerenciar contatos telefônicos.

## Índice

- [Requisitos de Software](#requisitos-de-software)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
- [Contribuição](#contribuição)

## Requisitos de Software

Para executar esta aplicação, é necessário ter os seguintes softwares instalados:

- PHP 8.2
- MariaDB 10.6 (ou MySQL)
- Apache 2.4 (ou equivalente)

## Instalação

Siga os passos abaixo para instalar a aplicação:

1. Clone o repositório para o seu ambiente local:
    ```bash
    git clone https://github.com/seu-usuario/seu-repositorio.git
    ```

2. Navegue até o diretório do projeto:
    ```bash
    cd seu-repositorio
    ```

3. Certifique-se de ter os requisitos de software instalados e configurados.

## Configuração

Para configurar a aplicação, siga os passos abaixo:

1. **Criação de usuário e senha do banco de dados:**
    - Acesse o MariaDB (ou MySQL) e crie um novo usuário e senha:
        ```sql
        CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'senha';
        ```

2. **Criação da base de dados:**
    - Crie uma base de dados chamada `agenda`:
        ```sql
        CREATE DATABASE agenda;
        ```

3. **Importar o arquivo SQL:**
    - Importe o arquivo SQL incluído na release (ou crie as tabelas conforme exigido nos arquivos):
        ```bash
        mysql -u usuario -p agenda < caminho/para/o/arquivo.sql
        ```

4. **Configuração dos arquivos PHP:**
    - Nos arquivos `acesso.php`, `verifica_login.php` e `index.php`, altere as seguintes linhas conforme necessário para validar pelo IP a exibição do botão de login:
        ```php
        $ip = $_SERVER['HTTP_X_REAL_IP'];
        //$ipaddress = "172.16.0.10";
        $ipaddress = strstr($ip, ',', true);
        ```
    - Caso não queira validar pelo IP, descomente a linha:
        ```php
        //$ipaddress = "172.16.0.10";
        ```
      e comente as linhas:
        ```php
        $ip = $_SERVER['HTTP_X_REAL_IP'];
        $ipaddress = strstr($ip, ',', true);
        ```

5. **Edição do arquivo config.php:**
    - Edite o arquivo `config.php` com o usuário e senha do banco de dados:
        ```php
        define('DB_USERNAME', 'usuario');
        define('DB_PASSWORD', 'senha');
        ```

## Uso

Depois de configurar a aplicação, você pode acessar a aplicação de lista telefônica através do seu navegador apontando para o servidor onde a aplicação está hospedada.

## Contribuição

Se você deseja contribuir com este projeto, siga as diretrizes abaixo:

1. Fork este repositório.
2. Crie uma branch para a sua feature ou correção de bug (`git checkout -b minha-feature`).
3. Commit suas alterações (`git commit -am 'Adicionar nova feature'`).
4. Push para a branch (`git push origin minha-feature`).
5. Crie um novo Pull Request.

## Referências Usadas

fpdf.org
getbootstrap.com
datatables.net
jquery.org
fontawesome.com
bulma.io
