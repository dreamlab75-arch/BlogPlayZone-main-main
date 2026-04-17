<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: posts-view.php');
    exit;
}

require_once __DIR__ . '/posts-model.php';
require_once __DIR__ . '/../util/upload.php';

$titulo   = trim($_POST['titulo']   ?? '');
$conteudo = trim($_POST['conteudo'] ?? '');
$tags_ids = array_slice(array_map('intval', $_POST['tags_post'] ?? []), 0, 5);

if (strlen($titulo) < 5) {
    header('Location: posts-view.php?erro=' . urlencode('Título muito curto (mínimo 5 caracteres).'));
    exit;
}
if (strlen($conteudo) < 50) {
    header('Location: posts-view.php?erro=' . urlencode('Conteúdo muito curto (mínimo 50 caracteres).'));
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];
$pdo = conectar();
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare("
        INSERT INTO posts (titulo, conteudo, imagem, usuario_id)
        VALUES (:titulo, :conteudo, :imagem, :usuario_id)
    ");
    $stmt->execute([
        ':titulo'     => $titulo,
        ':conteudo'   => $conteudo,
        ':imagem'     => null,
        ':usuario_id' => $usuario_id,
    ]);
    $post_id = (int)$pdo->lastInsertId();

    $pasta  = __DIR__ . '/posts_img';
    $imagem = salvar_imagem('imagem', 'post', $post_id, $pasta);

    if ($imagem) {
        $pdo->prepare("UPDATE posts SET imagem = :imagem WHERE id = :id")
            ->execute([':imagem' => $imagem, ':id' => $post_id]);
    }

    // Tags
    if (!empty($tags_ids)) {
        $stmtTag = $pdo->prepare("INSERT OR IGNORE INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)");
        foreach ($tags_ids as $tag_id) {
            $stmtTag->execute([':post_id' => $post_id, ':tag_id' => $tag_id]);
        }
    }

    $pdo->commit();
    header('Location: posts-view.php?sucesso=1', true, 303);
    exit;

} catch (RuntimeException $e) {
    $pdo->rollBack();
    header('Location: posts-view.php?erro=' . urlencode($e->getMessage()));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: posts-view.php?erro=' . urlencode('Erro ao publicar o post. Tente novamente.'));
    exit;
}