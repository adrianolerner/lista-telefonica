## Aplicação de Lista Telefônica

Bem-vindo ao repositório da aplicação de lista telefônica desenvolvida para um órgão público.
Esta aplicação foi construída utilizando PHP, HTML, CSS, JavaScript e MariaDB.
O objetivo desta aplicação é fornecer uma interface intuitiva para gerenciar contatos
telefônicos do órgão.

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
    git clone https://github.com/adrianolerner/lista-telefonica.git
    ```

2. Navegue até o diretório do projeto:
    ```bash
    cd lista-telefonica
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
    - Importe o arquivo SQL incluído na release (ou crie as tabelas conforme exigido nos
arquivos):
        ```bash
        mysql -u usuario -p agenda < caminho/para/o/arquivo.sql
        ```
    - Edite o campo de senha do usuário "admin" na tabela de usuários (a senha padrão do arquivo é admin)

4. **Configuração dos arquivos PHP:**
    - Nos arquivos `acesso.php`, `verifica_login.php` e `index.php`, altere as
seguintes linhas conforme necessário para validar pelo IP a exibição do botão de login:
        ```php
        $ip = $_SERVER['HTTP_X_REAL_IP'];
        //$ipaddress = "172.16.0.10";
        $ipaddress = strstr($ip, ',', true);
        ```
         - No arquivo `index.php` alterar a variável abaixo com o nome do seu órgão ou entidade:
      	```php
         $orgao = "DA PRAFEITURA DE XXXX";
       	```
         - Nos arquivos `acesso.php` e `index.php`, altere o IP  das
seguintes linhas conforme necessário para validar pelo IP.
        ```php
        if (!fnmatch("172.16.0.\*", $ipaddress))
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
    - Caso queira usar o recaptcha para validação de login, mude para "false" o valor da variável $recaptcha_verified na linha 12. Caso não queira utilizar, basta manter como "true".
      ```php
      $recaptcha_verified = true;
      ```
    - Criar no google recaptcha v3 um novo site conforme seu dominio e copiar as chaves privada e pública. (Caso queira usar)
    - Aterar codigo recaptcha nas referidas linhas nos arquivos login.php e acesso.php, sendo a
chave privada em login.php e a publica em acesso.php.
    - Caso a variavel acima esteja como "false" e não seja indicado o código recaptcha nas paginas indicadas, a tela de login não funcionará.

5. **Inclusão de usuário e senha do banco de dados (mudança da versão 0.8 - passos obrigatórios para atualização)**
Para configurar variáveis de ambiente no Ubuntu Server com Apache 2 e usá-las no seu código PHP, você pode seguir estas etapas:

    1. **Criar as Variáveis de Ambiente no Sistema**
    As variáveis de ambiente podem ser definidas no sistema operacional e acessadas pelo Apache e PHP.

    2. **Editar o Arquivo de Configuração do Apache**:
       - Abra o arquivo de configuração do Apache para o site em questão. Geralmente, o arquivo está localizado em `/etc/apache2/sites-available/000-default.conf` (ou em outro arquivo de configuração se estiver usando hosts virtuais específicos).
       - Adicione as variáveis de ambiente usando a diretiva `SetEnv` dentro do bloco `<VirtualHost>`.

       ```bash
       sudo nano /etc/apache2/sites-available/000-default.conf
       ```

       **Exemplo de configuração:**

       ```apache
       <VirtualHost *:80>
       ServerAdmin webmaster@localhost
       DocumentRoot /var/www/html

       # Definir variáveis de ambiente
       SetEnv DB_SERVER localhost
       SetEnv DB_USERNAME USUARIO-DB
       SetEnv DB_PASSWORD SENHA-DB
       SetEnv DB_NAME agenda

       ErrorLog ${APACHE_LOG_DIR}/error.log
       CustomLog ${APACHE_LOG_DIR}/access.log combined
       </VirtualHost>
       ```

    3. **Recarregar o Apache**:
       - Após editar o arquivo de configuração, recarregue o Apache para aplicar as mudanças.

       ```bash
       sudo systemctl reload apache2
       ```

	**Alternativa: Usar `.htaccess` para Definir Variáveis de Ambiente**

	Se preferir, as variáveis de ambiente também podem ser definidas em um arquivo `.htaccess` na raiz do seu projeto.

    1. **Criar ou Editar o Arquivo `.htaccess`**:
       - Se o arquivo `.htaccess` não existir, crie um na raiz do diretório do seu projeto web (`/var/www/html` ou o diretório correspondente).

       ```bash
       sudo nano /var/www/html/.htaccess
       ```

    2. **Adicionar as Variáveis de Ambiente**:
       - Insira as diretivas `SetEnv` no arquivo `.htaccess`.

       **Exemplo:**

       ```apache
       SetEnv DB_SERVER localhost
       SetEnv DB_USERNAME USUARIO-DB
       SetEnv DB_PASSWORD SENHA-DB
       SetEnv DB_NAME agenda
       ```

    3. **Reiniciar o Apache**:
       - Certifique-se de que o módulo `mod_env` do Apache está habilitado e reinicie o Apache para aplicar as mudanças.

       ```bash
       sudo a2enmod env
       sudo systemctl restart apache2
       ```

    4. **Segurança Adicional**

	Para aumentar a segurança, especialmente ao definir variáveis de ambiente com credenciais sensíveis, considere:

    - **Restringir o acesso ao arquivo `.htaccess`**: Apenas o proprietário e o servidor web devem ter permissões de leitura.
  
  	```bash
	sudo chmod 640 /var/www/html/.htaccess
 	 ```

    - **Habilitar HTTPS**: Isso garante que as credenciais transmitidas entre o cliente e o servidor estejam criptografadas.

6. **Alterar imagens/Titulos:**
    - Altere os logos.
    - Os logos ficam na pasta "img" e podem ser trocados somente substituindo por seus logos e mantendo o nome. Cada arquivo represente o Logo em um local.
    - O logo.png, é o brasão exibido no cabeçalho da lista em PDF.
    - O logo2.png, é o logo/brasão do órgão a ser exibido na pagina principal.

7. **Caso seja necessário a atualização de versão anterior, por conta das mudaças no código e banco de dados, recomendamos que seja feito backup dos arquivos do projeto e do banco de dados, e seja criado novo banco, importando do exemplo disponibilizado, e importados os registros no sistema já atualizado, para evitar conflitos.**

8. **Para Verificar novas versões desta ferramenta, acesse a página sobre da aplicação (a partir da versão 0.12). Em caso de nova versão, será exibido alerta com link para download.**

## Uso

Depois de configurar a aplicação, você pode acessar a aplicação de lista telefônica através do
seu navegador apontando para o servidor onde a aplicação está hospedada.
O usuário pré-cadastrado é "admin" e senha "admin".
Em caso de necessidade, é possível gerar o hash de senha para inserção diretamente no banco de dos, editando o arquivo trocahash.php, inserindo a senha desejada no campo `INSIRA_A_SENHA_AQUI` e depois executando-o no terminal em sua pasta com o PHP com o comando `php trocahash.php`.

Por gentileza mantenha os créditos do criador.

## Contribuição

Se você deseja contribuir com este projeto, siga as diretrizes abaixo:

1. Fork este repositório.
2. Crie uma branch para a sua feature ou correção de bug (`git checkout -b minha-feature`).
3. Commit suas alterações (`git commit -am 'Adicionar nova feature'`).
4. Push para a branch (`git push origin minha-feature`).
5. Crie um novo Pull Request.

## Referências Usadas

- [fpdf.org](http://fpdf.org)
- [getbootstrap.com](https://getbootstrap.com)
- [datatables.net](https://datatables.net)
- [jquery.org](https://jquery.org)
- [fontawesome.com](https://fontawesome.com)
- [bulma.io](https://bulma.io)
