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
    - Edite o campo de senha do usuário "admin" na tabela de usuários

4. **Configuração dos arquivos PHP:**
    - Nos arquivos `acesso.php`, `verifica_login.php`. `login.php` e `index.php`, altere as
seguintes linhas conforme necessário para validar pelo IP a exibição do botão de login:
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
    - Aterar codigo recaptcha nas referidas linhas nos arquivos login.php e acesso.php, sendo a
chave privada em login.php e a publica em acesso.php

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
    - Altere o logo, na pagina principal:
        ```png
        logo.png
        ```
    - Altere o logo no footer do arquivo index.php, no cabeçalho da página gerapdf.php e no body da página acesso.php:
        ```php/html
        <img src="logoX,png" />
        ```
    - Altere os cabeçalhos e titulos das páginas para o nome do seu órgão/empresa.

7. **Mudança da Versão 0.7:**
    - Nesta nova versão foram feitas mudanças significativas no código relacionado as consultas ao banco de cados.
    - Há uma nova tabela de secretaria, que está relacionada ao campo "secretaria" da tabela lista, então é necessário
fazer ajustes ao banco de dados para poder utilizar esta versão.
    - Para os ajustes, você pode importar o arquivo SQL anexo a versão em um novo banco ou banco de testes, e migrar os
registros conforme necessário através da console do banco de dados ou usando uma ferramenta grafica como o phpmyadmin.
    - Caso queira ajustar manualmente, é necessário:
        - A. Incluir uma tabela chamada "secretarias", e nela as colunas "id_secretaria" do tipo int e "secretaria" do
tipo VARCHAR (100).
        - B. Cadastrar nesta tabela todas as secretarias necessárias.
        - C. Na tabela lista atualizar os registros, trocando estes da coluna "secretaria", pelo código (id_secretaria)
correspondente da tabela "secretarias".Isto pode ser feito usando o comando SQL abaixo:
            - ``` UPDATE `lista` SET `secretaria`='NOME_CADASTRADO' WHERE `secretaria`='NOVO_ID_DA_TABELA_SECRETARIAS'; ```
        - D. Adicionar o relacionamento das tabelas, relacionado o campo "secretarias" da tabela "lista", com o campo
"id_secretaria" da tabela "secretarias". Isto pode ser feito usando o comando SQL abaixo:
            - ``` ALTER TABLE `lista` ADD CONSTRAINT `fk_secretaria` FOREIGN KEY (`secretaria`) REFERENCES `secretarias`
(`id_secretaria`); ON DELETE RESTRICT ON UPDATE RESTRICT; ```

### ** Para efeito didático, abaixo segue explicação visual para a versão 0.7:**

### 1. Estrutura das Tabelas

**Tabela `lista`:**

| id_lista | nome | ramal | email | setor | secretaria |
|----------|------|-------|-------|-------|------------|
| 1        | Ana  | 1234  | ana@empresa.com | TI   | 2          |
| 2        | José | 5678  | jose@empresa.com | RH   | 1          |

**Tabela `secretarias`:**

| id_secretaria | secretaria     |
|---------------|----------------|
| 1             | Administração  |
| 2             | Tecnologia     |
| 3             | Finanças       |

### 2. Criação das Tabelas com Chave Estrangeira

Primeiro, você cria a tabela `secretarias`:

```sql
CREATE TABLE secretarias (
    id_secretaria INT PRIMARY KEY,
    secretaria VARCHAR(100) NOT NULL
);
```

Em seguida, você cria a tabela `lista`, com a chave estrangeira `secretaria`:

```sql
CREATE TABLE lista (
    id_lista INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ramal VARCHAR(10),
    email VARCHAR(100),
    setor VARCHAR(50),
    secretaria INT,
    FOREIGN KEY (secretaria) REFERENCES secretarias(id_secretaria)
);
```

### 3. Inserção de Dados nas Tabelas

Aqui está como você pode inserir dados em ambas as tabelas:

**Tabela `secretarias`:**

```sql
INSERT INTO secretarias (id_secretaria, secretaria) VALUES 
(1, 'Administração'),
(2, 'Tecnologia'),
(3, 'Finanças');
```

**Tabela `lista`:**

```sql
INSERT INTO lista (id_lista, nome, ramal, email, setor, secretaria) VALUES 
(1, 'Ana', '1234', 'ana@empresa.com', 'TI', 2),
(2, 'José', '5678', 'jose@empresa.com', 'RH', 1);
```

### 4. Consulta com `JOIN`

Para trazer as informações da tabela `secretarias` na consulta da tabela `lista`, você pode usar um `JOIN`. Aqui está um exemplo de consulta:

```sql
SELECT 
    l.id_lista,
    l.nome,
    l.ramal,
    l.email,
    l.setor,
    s.secretaria
FROM 
    lista l
JOIN 
    secretarias s ON l.secretaria = s.id_secretaria;
```

### 5. Resultado da Consulta

A consulta acima retornará algo como:

| id_lista | nome | ramal | email           | setor | secretaria   |
|----------|------|-------|-----------------|-------|--------------|
| 1        | Ana  | 1234  | ana@empresa.com | TI    | Tecnologia   |
| 2        | José | 5678  | jose@empresa.com | RH    | Administração|

### Explicação

- **Chave estrangeira (Foreign Key)**: No exemplo, o campo `secretaria` da tabela `lista` refere-se ao campo `id_secretaria` da tabela `secretarias`, criando uma relação entre as duas tabelas.
- **JOIN**: A consulta com `JOIN` permite que você combine dados das duas tabelas, unindo-as pela chave estrangeira.

## Uso

Depois de configurar a aplicação, você pode acessar a aplicação de lista telefônica através do
seu navegador apontando para o servidor onde a aplicação está hospedada.
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
