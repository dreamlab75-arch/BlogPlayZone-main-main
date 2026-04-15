<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=Faça login para acessar seu painel');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel-usuario.php?aba=noticias');
    exit;
}

require_once __DIR__ . '/../noticias-index/noticias-model.php';
require_once __DIR__ . '/../util/upload.php';

$pdo        = conectar_noticias();
$usuario_id = (int)$_SESSION['usuario_id'];
$noticia_id = (int)($_POST['noticia_id'] ?? 0);

if (!$noticia_id) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Notícia inválida.'));
    exit;
}

$stmt = $pdo->prepare("SELECT usuario_id, imagem FROM noticias WHERE id = :id");
$stmt->execute([':id' => $noticia_id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

$perfil_id = (int)($_SESSION['usuario_perfil_id'] ?? 0);
if (!$noticia || ($noticia['usuario_id'] !== $usuario_id && $perfil_id !== 1)) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Sem permissão.'));
    exit;
}

$titulo    = trim($_POST['titulo']    ?? '');
$resumo    = trim($_POST['resumo']    ?? '');
$conteudo  = trim($_POST['conteudo']  ?? '');
$categoria = trim($_POST['categoria'] ?? '');

$categorias_validas = ['lançamento','rumor','análise','urgente','review','prévia','atualização','evento','hardware','negócios','curiosidade','lista'];

if (strlen($titulo) < 5 || !in_array($categoria, $categorias_validas)) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Dados inválidos.'));
    exit;
}

try {
    // Upload nova imagem (se enviada)
    $pasta  = __DIR__ . '/../noticias-index/noticias_img';
    $imagem = salvar_imagem('imagem', 'noticia', $noticia_id, $pasta, $noticia['imagem'] ?? '');

    $imagem_final = $imagem ?? $noticia['imagem'];

    $pdo->prepare("
        UPDATE noticias SET titulo=:titulo, resumo=:resumo, conteudo=:conteudo, imagem=:imagem, categoria=:categoria
        WHERE id=:id
    ")->execute([
        ':titulo'    => $titulo,
        ':resumo'    => $resumo,
        ':conteudo'  => $conteudo,
        ':imagem'    => $imagem_final,
        ':categoria' => $categoria,
        ':id'        => $noticia_id,
    ]);

    header('Location: painel-usuario.php?aba=noticias&sucesso=' . urlencode('Notícia atualizada com sucesso.'), true, 303);
} catch (RuntimeException $e) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode($e->getMessage()));
} catch (Exception $e) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Erro ao atualizar a notícia.'));
}
exit;