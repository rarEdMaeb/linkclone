<?php
// Execute no terminal: php criar_admin.php
// Este arquivo cria um administrador com senha armazenada em hash.

if (PHP_SAPI !== 'cli') {
    exit("Execute este arquivo pelo terminal.\n");
}

$host = 'localhost';
$banco = 'linktree';
$usuarioBanco = 'root';
$senhaBanco = ''; // Ajuste conforme seu MySQL.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuarioBanco,
        $senhaBanco,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    exit("Erro ao conectar ao banco. Confira as configuracoes.\n");
}

$usuario = trim(readline('Usuario administrador: '));
$senha = readline('Senha: ');

if ($usuario === '' || $senha === '') {
    exit("Usuario e senha sao obrigatorios.\n");
}

try {
    $consulta = $pdo->prepare('INSERT INTO administradores (usuario, senha_hash) VALUES (?, ?)');
    $consulta->execute([$usuario, password_hash($senha, PASSWORD_DEFAULT)]);
    echo "Administrador criado com sucesso.\n";
} catch (PDOException $e) {
    echo "Nao foi possivel criar o administrador. Verifique se o usuario ja existe.\n";
}
