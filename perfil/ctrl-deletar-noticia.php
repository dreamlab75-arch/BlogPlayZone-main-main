<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=Faça login para acessar seu painel');
    exit;
}

require_once __DIR__ . '/../noticias-index/noticias-model.php';

$pdo        = conectar_noticias();
$usuario_id = (int)$_SESSION['usuario_id'];
$noticia_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$noticia_id) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Notícia inválida.'));
    exit;
}

$stmt = $pdo->prepare("SELECT usuario_id FROM noticias WHERE id = :id");
$stmt->execute([':id' => $noticia_id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

$perfil_id = (int)($_SESSION['usuario_perfil_id'] ?? 0);
if (!$noticia || ($noticia['usuario_id'] !== $usuario_id && $perfil_id !== 1)) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Sem permissão para deletar esta notícia.'));
    exit;
}

try {
    $pdo->prepare("DELETE FROM Comentarios_noticias WHERE noticia_id = :id")->execute([':id' => $noticia_id]);
    $pdo->prepare("DELETE FROM Curte_noticia WHERE noticia_id = :id")->execute([':id' => $noticia_id]);
    $pdo->prepare("DELETE FROM Visualiza_noticia WHERE noticia_id = :id")->execute([':id' => $noticia_id]);
    $pdo->prepare("DELETE FROM noticias WHERE id = :id")->execute([':id' => $noticia_id]);

    header('Location: painel-usuario.php?aba=noticias&sucesso=' . urlencode('Notícia deletada com sucesso.'));
} catch (Exception $e) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Erro ao deletar a notícia.'));
}
exit;