<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel-usuario.php');
    exit;
}

require_once __DIR__ . '/../posts-index/posts-model.php';

$pdo        = conectar();
$usuario_id = (int)$_SESSION['usuario_id'];
$post_id    = (int)($_POST['post_id'] ?? 0);
$titulo     = trim($_POST['titulo']   ?? '');
$conteudo   = trim($_POST['conteudo'] ?? '');
$imagem     = trim($_POST['imagem']   ?? '');
$tags_ids   = array_slice(array_map('intval', $_POST['tags_post'] ?? []), 0, 5);

if (!$post_id || strlen($titulo) < 5 || strlen($conteudo) < 50) {
    header('Location: painel-usuario.php?erro=' . urlencode('Dados inválidos. Verifique título e conteúdo.'));
    exit;
}

$stmt = $pdo->prepare("SELECT usuario_id FROM posts WHERE id = :id");
$stmt->execute([':id' => $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

$eh_adm = ($_SESSION['usuario_perfil'] ?? '') === 'adm';
if (!$post || ((int)$post['usuario_id'] !== $usuario_id && !$eh_adm)) {
    header('Location: painel-usuario.php?erro=' . urlencode('Sem permissão para editar este post.'));
    exit;
}

$pdo->beginTransaction();
try {
    // Atualiza o post
    $pdo->prepare("
        UPDATE posts SET titulo=:titulo, conteudo=:conteudo, imagem=:imagem
        WHERE id=:id
    ")->execute([
        ':titulo'   => $titulo,
        ':conteudo' => $conteudo,
        ':imagem'   => $imagem ?: null,
        ':id'       => $post_id,
    ]);

    // Recria as tags
    $pdo->prepare("DELETE FROM post_tag WHERE post_id = :id")->execute([':id' => $post_id]);
    if (!empty($tags_ids)) {
        $stmtTag = $pdo->prepare("INSERT OR IGNORE INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)");
        foreach ($tags_ids as $tag_id) {
            $stmtTag->execute([':post_id' => $post_id, ':tag_id' => $tag_id]);
        }
    }

    $pdo->commit();
    header('Location: painel-usuario.php?sucesso=' . urlencode('Post atualizado com sucesso!'));
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: painel-usuario.php?erro=' . urlencode('Erro ao salvar o post.'));
}
exit;