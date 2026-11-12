LINKTREE SIMPLES - PHP PROCEDURAL + MYSQL

ARQUIVOS
- index.php: aplicacao inteira (perfil publico, login, cadastro, listagem, alteracao e exclusao).
- banco.sql: cria o banco, as tabelas e alguns links de exemplo.
- criar_admin.php: cria o usuario administrador pelo terminal.

INSTALACAO NO XAMPP
1. Copie a pasta linktree-simples para C:\xampp\htdocs e renomeie para lkt.
2. Inicie Apache e MySQL no XAMPP.
3. Abra http://localhost/phpmyadmin e importe o arquivo banco.sql.
4. Abra index.php e confira as credenciais do MySQL no inicio do arquivo.
5. No terminal, entre na pasta C:\xampp\htdocs\lkt e execute:
   php criar_admin.php
6. Informe o nome de usuario e a senha do administrador.
7. Acesse a administracao em:
   http://localhost/lkt/?pagina=admin

URL PUBLICA DO PERFIL
http://localhost/lkt/?a=fulano

COMO FUNCIONA A CRIACAO DO PERFIL
Nao existe uma tabela separada de perfis. O perfil e identificado pelo campo apelido
na tabela links. Ao cadastrar links com o mesmo apelido, eles aparecem juntos na
pagina publica daquele apelido. Exemplo: todos os registros com apelido "fulano"
aparecem em http://localhost/lkt/?a=fulano.

FUNCIONALIDADES
Parte 1: banco de dados, listagem publica por apelido e inclusao de links.
Parte 2: alteracao e exclusao de links.
Parte 3: login administrativo e protecao das operacoes administrativas por sessao.

OBSERVACOES
- Projeto procedural: nao utiliza classes nem arquitetura MVC.
- O acesso ao MySQL utiliza PDO com consultas preparadas.
- A senha administrativa e salva com password_hash e validada com password_verify.
- A URL e chave primaria, conforme o enunciado; por isso nao pode se repetir.
- O layout usa Pico CSS por CDN; e necessaria conexao com a internet para carregar o estilo.
- O projeto nao implementa token CSRF, conforme a simplificacao solicitada.
- Para producao, configure HTTPS e remova ou proteja o script criar_admin.php depois de criar o administrador.
