<?php
session_start();

// CONFIGURACAO DO BANCO DE DADOS
$host = 'localhost';
$banco = 'linktree';
$usuario = 'root';
$senha = ''; // Altere conforme a configuracao do seu MySQL.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados. Confira as configuracoes no inicio do index.php.');
}

function proteger($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar($url) {
    header("Location: $url");
    exit;
}

$pagina = $_GET['pagina'] ?? '';
$acao = $_GET['acao'] ?? '';

// =====================================================
// AREA ADMINISTRATIVA
// =====================================================
if ($pagina === 'admin') {

    // LOGIN
    if ($acao === '' || $acao === 'login') {
        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioLogin = trim($_POST['usuario'] ?? '');
            $senhaLogin = $_POST['senha'] ?? '';

            $consulta = $pdo->prepare('SELECT id, usuario, senha_hash FROM administradores WHERE usuario = ?');
            $consulta->execute([$usuarioLogin]);
            $admin = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($senhaLogin, $admin['senha_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                redirecionar('?pagina=admin&acao=listar');
            } else {
                $erro = 'Usuário ou senha incorretos.';
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Login administrativo</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        </head>
        <body class="container">
            <main style="max-width: 430px; margin: 60px auto;">
                <h1>Área administrativa</h1>
                <?php if ($erro): ?><p style="color: red;"><?= proteger($erro) ?></p><?php endif; ?>
                <form method="post" action="?pagina=admin&acao=login">
                    <label>Usuário<input type="text" name="usuario" required></label>
                    <label>Senha<input type="password" name="senha" required></label>
                    <button type="submit">Entrar</button>
                </form>
                <a href="index.php">Voltar ao início</a>
            </main>
        </body>
        </html>
        <?php
        exit;
    }

    // TODAS AS OUTRAS ACOES EXIGEM LOGIN
    if (empty($_SESSION['admin_id'])) {
        redirecionar('?pagina=admin');
    }

    // SAIR
    if ($acao === 'sair') {
        $_SESSION = [];
        session_destroy();
        redirecionar('?pagina=admin');
    }

    // EXCLUIR LINK
    if ($acao === 'excluir' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $urlExcluir = $_POST['url'] ?? '';
        $consulta = $pdo->prepare('DELETE FROM links WHERE url = ?');
        $consulta->execute([$urlExcluir]);
        redirecionar('?pagina=admin&acao=listar');
    }

    // SALVAR NOVO LINK OU ALTERACAO
    if ($acao === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $urlOriginal = $_POST['url_original'] ?? '';
        $url = trim($_POST['url'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $apelido = trim($_POST['apelido'] ?? '');

        $urlValida = filter_var($url, FILTER_VALIDATE_URL) &&
            in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);

        if (!$urlValida || $nome === '' || $apelido === '' ||
            strlen($url) > 255 || mb_strlen($nome) > 50 || mb_strlen($apelido) > 50) {
            $_SESSION['mensagem'] = 'Preencha os campos corretamente. Use uma URL http ou https.';
            redirecionar('?pagina=admin&acao=' . ($urlOriginal !== '' ? 'editar&url=' . rawurlencode($urlOriginal) : 'novo'));
        }

        try {
            if ($urlOriginal !== '') {
                $consulta = $pdo->prepare('UPDATE links SET url = ?, nome = ?, apelido = ? WHERE url = ?');
                $consulta->execute([$url, $nome, $apelido, $urlOriginal]);
            } else {
                $consulta = $pdo->prepare('INSERT INTO links (url, nome, apelido) VALUES (?, ?, ?)');
                $consulta->execute([$url, $nome, $apelido]);
            }
            $_SESSION['mensagem'] = 'Link salvo com sucesso.';
            redirecionar('?pagina=admin&acao=listar');
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Não foi possível salvar. Verifique se a URL já está cadastrada.';
            redirecionar('?pagina=admin&acao=listar');
        }
    }

    // FORMULARIO DE NOVO LINK OU EDICAO
    if ($acao === 'novo' || $acao === 'editar') {
        $registro = ['url' => '', 'nome' => '', 'apelido' => ''];

        if ($acao === 'editar') {
            $consulta = $pdo->prepare('SELECT url, nome, apelido FROM links WHERE url = ?');
            $consulta->execute([$_GET['url'] ?? '']);
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            if (!$resultado) {
                die('Link não encontrado.');
            }
            $registro = $resultado;
        }
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= $acao === 'novo' ? 'Novo link' : 'Editar link' ?></title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        </head>
        <body class="container">
            <main style="max-width: 600px; margin: 40px auto;">
                <h1><?= $acao === 'novo' ? 'Cadastrar link' : 'Alterar link' ?></h1>
                <?php if (!empty($_SESSION['mensagem'])): ?>
                    <p><?= proteger($_SESSION['mensagem']) ?></p>
                    <?php unset($_SESSION['mensagem']); ?>
                <?php endif; ?>
                <form method="post" action="?pagina=admin&acao=salvar">
                    <input type="hidden" name="url_original" value="<?= proteger($registro['url']) ?>">
                    <label>URL<input type="url" name="url" maxlength="255" required value="<?= proteger($registro['url']) ?>" placeholder="https://exemplo.com"></label>
                    <label>Nome<input type="text" name="nome" maxlength="50" required value="<?= proteger($registro['nome']) ?>"></label>
                    <label>Apelido do perfil<input type="text" name="apelido" maxlength="50" required value="<?= proteger($registro['apelido']) ?>" placeholder="fulano"></label>
                    <button type="submit">Salvar</button>
                </form>
                <a href="?pagina=admin&acao=listar">Voltar</a>
            </main>
        </body>
        </html>
        <?php
        exit;
    }

    // LISTAGEM ADMINISTRATIVA
    if ($acao === 'listar') {
        $consulta = $pdo->query('SELECT url, nome, apelido FROM links ORDER BY apelido, nome');
        $links = $consulta->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Gerenciar links</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        </head>
        <body class="container">
            <main>
                <h1>Gerenciar links</h1>
                <p>Administrador: <?= proteger($_SESSION['admin_usuario']) ?> | <a href="?pagina=admin&acao=sair">Sair</a></p>
                <?php if (!empty($_SESSION['mensagem'])): ?>
                    <p><?= proteger($_SESSION['mensagem']) ?></p>
                    <?php unset($_SESSION['mensagem']); ?>
                <?php endif; ?>
                <p><a href="?pagina=admin&acao=novo" role="button">Cadastrar novo link</a></p>
                <figure>
                    <table>
                        <thead><tr><th>Nome</th><th>Apelido</th><th>URL</th><th>Ações</th></tr></thead>
                        <tbody>
                        <?php foreach ($links as $link): ?>
                            <tr>
                                <td><?= proteger($link['nome']) ?></td>
                                <td><?= proteger($link['apelido']) ?></td>
                                <td><a href="<?= proteger($link['url']) ?>" target="_blank" rel="noopener noreferrer">Abrir</a></td>
                                <td>
                                    <a href="?pagina=admin&acao=editar&url=<?= rawurlencode($link['url']) ?>">Editar</a>
                                    <form method="post" action="?pagina=admin&acao=excluir" style="display:inline" onsubmit="return confirm('Deseja excluir este link?')">
                                        <input type="hidden" name="url" value="<?= proteger($link['url']) ?>">
                                        <button type="submit" class="secondary">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </figure>
            </main>
        </body>
        </html>
        <?php
        exit;
    }

    redirecionar('?pagina=admin&acao=listar');
}

// =====================================================
// PAGINA PUBLICA DO PERFIL: ?a=apelido
// Cada apelido agrupa os links de um perfil.
// =====================================================
$apelido = trim($_GET['a'] ?? '');

if ($apelido !== '') {
    $consulta = $pdo->prepare('SELECT nome, url FROM links WHERE apelido = ? ORDER BY nome');
    $consulta->execute([$apelido]);
    $linksPerfil = $consulta->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Links de <?= proteger($apelido) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        <style>
            body { background: #f3f4f6; }
            .perfil { max-width: 520px; margin: 50px auto; text-align: center; padding: 25px; }
            .link-perfil { display: block; margin: 14px 0; }
        </style>
    </head>
    <body class="container">
        <main class="perfil">
            <h1><?= proteger($apelido) ?></h1>
            <p>Meus links</p>
            <?php if (count($linksPerfil) === 0): ?>
                <p>Este perfil ainda não possui links cadastrados.</p>
            <?php else: ?>
                <?php foreach ($linksPerfil as $link): ?>
                    <a class="link-perfil" role="button" href="<?= proteger($link['url']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= proteger($link['nome']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// PAGINA INICIAL
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meu Linktree</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body class="container">
    <main style="max-width: 600px; margin: 60px auto; text-align: center;">
        <h1>Meu Linktree</h1>
        <p>Uma página para reunir todos os seus links.</p>
        <a href="?pagina=admin" role="button">Área administrativa</a>
        <p>Para acessar um perfil, utilize <code>?a=apelido</code>.</p>
    </main>
</body>
</html>
