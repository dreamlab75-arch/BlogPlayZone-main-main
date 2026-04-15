<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=' . urlencode('Faça login para publicar notícias.'));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: noticias-view.php');
    exit;
}

require_once __DIR__ . '/noticias-model.php';
require_once __DIR__ . '/../util/upload.php';

$pdo  = conectar_noticias();
$stmt = $pdo->prepare("SELECT perfil_id FROM usuarios WHERE id = :id");
$stmt->execute([':id' => (int)$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !in_array((int)$usuario['perfil_id'], [1, 3])) {
    header('Location: noticias-view.php?erro=' . urlencode('Apenas jornalistas podem publicar notícias.'));
    exit;
}

$titulo    = trim($_POST['titulo']    ?? '');
$resumo    = trim($_POST['resumo']    ?? '');
$conteudo  = trim($_POST['conteudo']  ?? '');
$categoria = trim($_POST['categoria'] ?? '');

$categorias_validas = ['lançamento','rumor','análise','urgente','review','prévia','atualização','evento','hardware','negócios','curiosidade','lista'];

if (strlen($titulo) < 5)  { header('Location: escrever-noticia.php?erro=' . urlencode('Título muito curto.')); exit; }
if (strlen($resumo) < 10) { header('Location: escrever-noticia.php?erro=' . urlencode('Resumo muito curto.')); exit; }
if (strlen($conteudo) < 50) { header('Location: escrever-noticia.php?erro=' . urlencode('Conteúdo muito curto.')); exit; }
if (!in_array($categoria, $categorias_validas)) { header('Location: escrever-noticia.php?erro=' . urlencode('Categoria inválida.')); exit; }

try {
    // Insere sem imagem primeiro
    $stmt = $pdo->prepare("
        INSERT INTO noticias (titulo, resumo, conteudo, imagem, categoria, usuario_id)
        VALUES (:titulo, :resumo, :conteudo, :imagem, :categoria, :usuario_id)
    ");
    $stmt->execute([
        ':titulo'     => $titulo,
        ':resumo'     => $resumo,
        ':conteudo'   => $conteudo,
        ':imagem'     => '',
        ':categoria'  => $categoria,
        ':usuario_id' => (int)$_SESSION['usuario_id'],
    ]);
    $noticia_id = (int)$pdo->lastInsertId();

    // Upload da imagem com o ID gerado
    $pasta  = __DIR__ . '/noticias_img';
    $imagem = salvar_imagem('imagem', 'noticia', $noticia_id, $pasta);

    if ($imagem) {
        $pdo->prepare("UPDATE noticias SET imagem = :imagem WHERE id = :id")
            ->execute([':imagem' => $imagem, ':id' => $noticia_id]);
    }

    header('Location: noticia.php?id=' . $noticia_id . '&sucesso=1', true, 303);
    exit;

} catch (RuntimeException $e) {
    header('Location: escrever-noticia.php?erro=' . urlencode($e->getMessage()));
    exit;
} catch (Exception $e) {
    header('Location: escrever-noticia.php?erro=' . urlencode('Erro ao publicar a notícia. Tente novamente.'));
    exit;
}