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
