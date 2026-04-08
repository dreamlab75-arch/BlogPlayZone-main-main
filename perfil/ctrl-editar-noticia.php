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

$pdo        = conectar_noticias();
$usuario_id = (int)$_SESSION['usuario_id'];
$noticia_id = (int)($_POST['noticia_id'] ?? 0);

if (!$noticia_id) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Notícia inválida.'));
    exit;
}

// Verifica se a notícia pertence ao usuário (ou se é adm)
$stmt = $pdo->prepare("SELECT usuario_id FROM noticias WHERE id = :id");
$stmt->execute([':id' => $noticia_id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

$perfil_id = (int)($_SESSION['usuario_perfil_id'] ?? 0);
if (!$noticia || ($noticia['usuario_id'] !== $usuario_id && $perfil_id !== 1)) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Sem permissão para editar esta notícia.'));
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

if (strlen($titulo) < 5) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Título muito curto.'));
    exit;
}
if (!in_array($categoria, $categorias_validas)) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Categoria inválida.'));
    exit;
}

try {
    $pdo->prepare("
        UPDATE noticias
        SET titulo = :titulo, resumo = :resumo, conteudo = :conteudo,
            imagem = :imagem, categoria = :categoria
        WHERE id = :id
    ")->execute([
        ':titulo'    => $titulo,
        ':resumo'    => $resumo,
        ':conteudo'  => $conteudo,
        ':imagem'    => $imagem,
        ':categoria' => $categoria,
        ':id'        => $noticia_id,
    ]);

    header('Location: painel-usuario.php?aba=noticias&sucesso=' . urlencode('Notícia atualizada com sucesso.'));
} catch (Exception $e) {
    header('Location: painel-usuario.php?aba=noticias&erro=' . urlencode('Erro ao atualizar a notícia.'));
}
exit;