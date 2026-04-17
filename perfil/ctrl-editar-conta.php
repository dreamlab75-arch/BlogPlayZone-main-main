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
require_once __DIR__ . '/../util/upload.php';

$pdo        = conectar();
$usuario_id = (int)$_SESSION['usuario_id'];

$nome  = trim($_POST['nome']  ?? '');
$email = trim($_POST['email'] ?? '');
$bio   = trim($_POST['bio']   ?? '');

if (strlen($nome) < 2) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('Nome muito curto.'));
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('E-mail inválido.'));
    exit;
}

$stmtEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
$stmtEmail->execute([':email' => $email, ':id' => $usuario_id]);
if ($stmtEmail->fetch()) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('Este e-mail já está em uso.'));
    exit;
}

$stmtAtual = $pdo->prepare("SELECT avatar FROM usuarios WHERE id = :id");
$stmtAtual->execute([':id' => $usuario_id]);
$atual = $stmtAtual->fetch(PDO::FETCH_ASSOC);

try {
    $pasta  = __DIR__ . '/avatares';
    $avatar = salvar_imagem('avatar', 'avatar', $usuario_id, $pasta, $atual['avatar'] ?? '');

    $avatar_final = $avatar ?? $atual['avatar'];

    $pdo->prepare("UPDATE usuarios SET nome=:nome, email=:email, avatar=:avatar, bio=:bio WHERE id=:id")
        ->execute([
            ':nome'   => $nome,
            ':email'  => $email,
            ':avatar' => $avatar_final,
            ':bio'    => $bio ?: null,
            ':id'     => $usuario_id,
        ]);

    $_SESSION['usuario_nome']   = $nome;
    $_SESSION['usuario_avatar'] = $avatar_final;

    header('Location: painel-usuario.php?aba=conta&sucesso=' . urlencode('Conta atualizada com sucesso!'), true, 303);
} catch (RuntimeException $e) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode($e->getMessage()));
} catch (Exception $e) {
    header('Location: painel-usuario.php?aba=conta&erro=' . urlencode('Erro ao salvar as alterações.'));
}
exit;