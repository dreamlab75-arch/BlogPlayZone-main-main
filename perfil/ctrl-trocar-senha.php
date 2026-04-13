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

$senha_atual    = $_POST['senha_atual']    ?? '';
$senha_nova     = $_POST['senha_nova']     ?? '';
$senha_confirma = $_POST['senha_confirma'] ?? '';

$redir = 'painel-usuario.php?aba=conta';


$stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: $redir&erro=" . urlencode('Usuário não encontrado.'), true, 303);
    exit;
}


if (hash('sha256', $senha_atual) !== $usuario['senha']) {
    header("Location: $redir&erro=" . urlencode('Senha atual incorreta.'), true, 303);
    exit;
}

if (strlen($senha_nova) < 6) {
    header("Location: $redir&erro=" . urlencode('A nova senha deve ter no mínimo 6 caracteres.'), true, 303);
    exit;
}

if ($senha_nova !== $senha_confirma) {
    header("Location: $redir&erro=" . urlencode('As senhas não coincidem.'), true, 303);
    exit;
}

try {
    $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id")
        ->execute([':senha' => hash('sha256', $senha_nova), ':id' => $usuario_id]);

    header("Location: $redir&sucesso=" . urlencode('Senha atualizada com sucesso!'), true, 303);
} catch (Exception $e) {
    header("Location: $redir&erro=" . urlencode('Erro ao atualizar a senha. Tente novamente.'), true, 303);
}
exit;