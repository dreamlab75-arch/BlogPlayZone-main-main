<?php
session_start();

// Só usuários logados podem criar notícias
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=' . urlencode('Faça login para publicar notícias.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: noticias-view.php');
    exit;
}

require_once __DIR__ . '/noticias-model.php';

// Verifica se o usuário tem permissão (adm=1 ou jornalista=3)
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
$imagem    = trim($_POST['imagem']    ?? '');
$categoria = trim($_POST['categoria'] ?? '');

$categorias_validas = [
    'lançamento','rumor','análise','urgente','review',
    'prévia','atualização','evento','hardware','negócios',
    'curiosidade','lista'
];

// Validações
if (strlen($titulo) < 5) {
    header('Location: escrever-noticia.php?erro=' . urlencode('Título muito curto (mínimo 5 caracteres).'));
    exit;
}
if (strlen($resumo) < 10) {
    header('Location: escrever-noticia.php?erro=' . urlencode('Resumo muito curto (mínimo 10 caracteres).'));
    exit;
}
if (strlen($conteudo) < 50) {
    header('Location: escrever-noticia.php?erro=' . urlencode('Conteúdo muito curto (mínimo 50 caracteres).'));
    exit;
}
if (!in_array($categoria, $categorias_validas)) {
    header('Location: escrever-noticia.php?erro=' . urlencode('Categoria inválida.'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO noticias (titulo, resumo, conteudo, imagem, categoria, usuario_id)
        VALUES (:titulo, :resumo, :conteudo, :imagem, :categoria, :usuario_id)
    ");
    $stmt->execute([
        ':titulo'     => $titulo,
        ':resumo'     => $resumo,
        ':conteudo'   => $conteudo,
        ':imagem'     => $imagem ?: '',
        ':categoria'  => $categoria,
        ':usuario_id' => (int)$_SESSION['usuario_id'],
    ]);

    $nova_id = $pdo->lastInsertId();
    header('Location: noticia.php?id=' . $nova_id . '&sucesso=1');
    exit;

} catch (Exception $e) {
    header('Location: escrever-noticia.php?erro=' . urlencode('Erro ao publicar a notícia. Tente novamente.'));
    exit;
}