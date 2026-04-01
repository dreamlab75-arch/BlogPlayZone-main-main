<?php
session_start();

// Só usuários logados podem criar posts
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../posts-index/posts-view.php');
    exit;
}

require_once __DIR__ . '/posts-model.php';

$titulo   = trim($_POST['titulo']   ?? '');
$conteudo = trim($_POST['conteudo'] ?? '');
$imagem   = trim($_POST['imagem']   ?? '');
$tags_ids = $_POST['tags_post']     ?? [];

// Validações básicas
if (strlen($titulo) < 5) {
    header('Location: posts-view.php?erro=' . urlencode('Título muito curto (mínimo 5 caracteres).'));
    exit;
}
if (strlen($conteudo) < 50) {
    header('Location: posts-view.php?erro=' . urlencode('Conteúdo muito curto (mínimo 50 caracteres).'));
    exit;
}
// Máximo 5 tags
$tags_ids = array_slice(array_map('intval', $tags_ids), 0, 5);

$usuario_id = (int)$_SESSION['usuario_id'];

$pdo = conectar();
$pdo->beginTransaction();

try {
    // Insere o post
    $stmt = $pdo->prepare("
        INSERT INTO posts (titulo, conteudo, imagem, usuario_id)
        VALUES (:titulo, :conteudo, :imagem, :usuario_id)
    ");
    $stmt->execute([
        ':titulo'     => $titulo,
        ':conteudo'   => $conteudo,
        ':imagem'     => $imagem ?: null,
        ':usuario_id' => $usuario_id,
    ]);
    $post_id = $pdo->lastInsertId();

    // Insere as tags
    if (!empty($tags_ids)) {
        $stmtTag = $pdo->prepare("
            INSERT OR IGNORE INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)
        ");
        foreach ($tags_ids as $tag_id) {
            $stmtTag->execute([':post_id' => $post_id, ':tag_id' => $tag_id]);
        }
    }

    $pdo->commit();
    header('Location: posts-view.php?sucesso=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: posts-view.php?erro=' . urlencode('Erro ao publicar o post. Tente novamente.'));
    exit;
}