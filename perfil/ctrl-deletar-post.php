<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../posts-index/posts-model.php';

$pdo        = conectar();
$usuario_id = (int)$_SESSION['usuario_id'];
$post_id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header('Location: painel-usuario.php?erro=' . urlencode('Post inválido.'));
    exit;
}

$stmt = $pdo->prepare("SELECT usuario_id FROM posts WHERE id = :id");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: painel-usuario.php?erro=' . urlencode('Post não encontrado.'));
    exit;
}

$eh_adm = ($_SESSION['usuario_perfil'] ?? '') === 'adm';
if ((int)$post['usuario_id'] !== $usuario_id && !$eh_adm) {
    header('Location: painel-usuario.php?erro=' . urlencode('Você não tem permissão para deletar este post.'));
    exit;
}

$pdo->beginTransaction();
try {
    $pdo->prepare("DELETE FROM post_tag           WHERE post_id = :id")->execute([':id' => $post_id]);
    $pdo->prepare("DELETE FROM Curte_post         WHERE post_id = :id")->execute([':id' => $post_id]);
    $pdo->prepare("DELETE FROM Comentarios_posts  WHERE post_id = :id")->execute([':id' => $post_id]);
    $pdo->prepare("DELETE FROM Visualiza_post     WHERE post_id = :id")->execute([':id' => $post_id]);
    $pdo->prepare("DELETE FROM posts              WHERE id      = :id")->execute([':id' => $post_id]);
    $pdo->commit();
    header('Location: painel-usuario.php?sucesso=' . urlencode('Post deletado com sucesso.'));
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: painel-usuario.php?erro=' . urlencode('Erro ao deletar o post.'));
}
exit;