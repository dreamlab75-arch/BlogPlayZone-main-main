<?php
// ============================================================
// HELPER — upload de imagem e resolução de paths
// ============================================================

/**
 * Resolve o caminho de uma imagem para uso em qualquer página.
 * Converte paths relativos à raiz (ex: "posts-index/posts_img/post_1.jpg")
 * em paths corretos considerando a profundidade da página atual.
 *
 * @param  string $imagem   Valor salvo no banco
 * @param  string $fallback Path do fallback se vazio
 * @return string  URL pronta para usar no src
 */
function img_url(string $imagem, string $fallback = ''): string
{
    if (empty($imagem)) {
        return $fallback;
    }

    // URL externa — retorna direto
    if (str_starts_with($imagem, 'http://') || str_starts_with($imagem, 'https://')) {
        return $imagem;
    }

    // Calcula quantos níveis acima da raiz está o arquivo atual
    // __DIR__ do arquivo que inclui este helper
    $raiz       = realpath(__DIR__ . '/..');          // raiz do projeto
    $atual_dir  = realpath(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file']);
    $atual_dir  = dirname($atual_dir);
    $profundidade = 0;
    $tmp = $atual_dir;
    while ($tmp !== $raiz && strlen($tmp) > strlen($raiz)) {
        $profundidade++;
        $tmp = dirname($tmp);
    }

    $prefixo = str_repeat('../', $profundidade);
    return $prefixo . ltrim($imagem, '/');
}

/**
 * Retorna um onerror seguro que não causa loop infinito.
 * Usa onload para verificar e só aplica fallback uma vez.
 */
function img_onerror(string $fallback = ''): string
{
    if (empty($fallback)) {
        return "onerror=\"this.style.display='none';this.onerror=null;\"";
    }
    return "onerror=\"this.onerror=null;this.src='" . htmlspecialchars($fallback, ENT_QUOTES) . "';\"";
}

/**
 * Salva um arquivo de imagem enviado via $_FILES.
 *
 * @param  string  $campo         Nome do campo no $_FILES
 * @param  string  $prefixo       Ex: 'post', 'noticia', 'avatar'
 * @param  int     $id            ID do registro (usado no nome do arquivo)
 * @param  string  $pasta         Caminho absoluto da pasta de destino
 * @param  string  $imagem_atual  Caminho atual (para deletar na troca)
 * @return string|null  Caminho relativo à raiz para salvar no banco, ou null se sem upload
 */
function salvar_imagem(string $campo, string $prefixo, int $id, string $pasta, string $imagem_atual = ''): ?string
{
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$campo];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro no upload da imagem (código ' . $file['error'] . ').');
    }

    $mime_permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $mime_permitidos)) {
        throw new RuntimeException('Formato não permitido. Use JPG, PNG, WEBP ou GIF.');
    }

    $extensoes    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $ext          = $extensoes[$mime];
    $nome_arquivo = "{$prefixo}_{$id}.{$ext}";
    $destino      = rtrim($pasta, '/') . '/' . $nome_arquivo;

    // Apaga versão anterior do mesmo registro (qualquer extensão)
    foreach ($extensoes as $e) {
        $antigo = rtrim($pasta, '/') . "/{$prefixo}_{$id}.{$e}";
        if (file_exists($antigo)) {
            @unlink($antigo);
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        throw new RuntimeException('Falha ao salvar o arquivo no servidor.');
    }

    // Retorna path relativo à raiz do projeto
    $raiz = realpath(__DIR__ . '/..');
    return ltrim(str_replace('\\', '/', str_replace($raiz, '', realpath($destino))), '/');
}