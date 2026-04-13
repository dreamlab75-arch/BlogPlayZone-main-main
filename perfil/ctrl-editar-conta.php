<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=Faça login para acessar seu painel');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel-usuario.php?aba=conta');
    exit;
}

require_once __DIR__ . '/../posts-index/posts-model.php';

$pdo        = conectar();
$usuario_id = (int)$_SESSION['usuario_id'];

$nome   = trim($_POST['nome']   ?? '');
$email  = trim($_POST['email']  ?? '');
$avatar = trim($_POST['avatar'] ?? '');
$bio    = trim($_POST['bio']    ?? '');


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('E-mail inválido.'));
    exit;
}

$stmtEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
$stmtEmail->execute([':email' => $email, ':id' => $usuario_id]);
if ($stmtEmail->fetch()) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('Este e-mail já está em uso por outro usuário.'));
    exit;
}

$campos  = "nome = :nome, email = :email, avatar = :avatar, bio = :bio";
$params  = [
    ':nome'       => $nome,
    ':email'      => $email,
    ':avatar'     => $avatar ?: null,
    ':bio'        => $bio    ?: null,
    ':id'         => $usuario_id,
];

try {
    $pdo->prepare("UPDATE usuarios SET $campos WHERE id = :id")->execute($params);

    $_SESSION['usuario_nome']   = $nome;
    $_SESSION['usuario_avatar'] = $avatar ?: $_SESSION['usuario_avatar'];

    header('Location: painel-usuario.php?aba=conta&sucesso=' . urlencode('Conta atualizada com sucesso!'), true, 303);
} catch (Exception $e) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('Erro ao salvar as alterações. Tente novamente.'));
}
exit;