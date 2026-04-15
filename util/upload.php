<?php
// ============================================================
// HELPER — upload de imagem centralizado
// ============================================================

/**
 * Salva um arquivo de imagem enviado via $_FILES.
 *
 * @param  string  $campo      Nome do campo no $_FILES
 * @param  string  $prefixo    Ex: 'post', 'noticia', 'avatar'
 * @param  int     $id         ID do post/noticia/usuario (usado no nome do arquivo)
 * @param  string  $pasta      Caminho absoluto da pasta de destino
 * @param  string  $imagem_atual  Caminho atual salvo no banco (para deletar na troca)
 * @return string|null  Caminho relativo para salvar no banco, ou null se não houve upload
 */
function salvar_imagem(string $campo, string $prefixo, int $id, string $pasta, string $imagem_atual = ''): ?string
{
    // Sem arquivo enviado ou campo vazio
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$campo];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro no upload da imagem (código ' . $file['error'] . ').');
    }

    // Verifica se é imagem real pelo conteúdo (não só pela extensão)
    $mime_permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $mime_permitidos)) {
        throw new RuntimeException('Formato não permitido. Use JPG, PNG, WEBP ou GIF.');
    }

    // Extensão a partir do MIME real (não do nome enviado pelo usuário)
    $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $ext = $extensoes[$mime];

    // Nome final: prefixo_id.ext — ex: post_12.jpg, avatar_3.png
    $nome_arquivo = "{$prefixo}_{$id}.{$ext}";
    $destino      = rtrim($pasta, '/') . '/' . $nome_arquivo;

    // Deleta arquivo anterior do mesmo prefixo/id (qualquer extensão)
    foreach ($extensoes as $e) {
        $antigo = rtrim($pasta, '/') . "/{$prefixo}_{$id}.{$e}";
        if (file_exists($antigo)) {
            @unlink($antigo);
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        throw new RuntimeException('Falha ao salvar o arquivo no servidor.');
    }

    // Retorna caminho relativo a partir da raiz do projeto
    // Ex: posts-index/posts_img/post_12.jpg
    $raiz = dirname(__DIR__);
    return ltrim(str_replace($raiz, '', $destino), '/\\');
}