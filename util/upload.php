<?php

/**
 * Converte um path de imagem salvo no banco em URL absoluta para o browser.
 * 
 * Estratégia simples e confiável:
 * - URL externa (http/https): retorna direto
 * - Path relativo (img/foto.webp, posts-index/posts_img/post_1.jpg):
 *   adiciona '/' na frente, tornando absoluto a partir da raiz do site
 *
 * @param string $imagem   Valor salvo no banco
 * @param string $fallback URL de fallback se vazio
 * @return string URL pronta para usar no src
 */
function img_url(string $imagem, string $fallback = ''): string
{
    if (empty(trim($imagem))) {
        return $fallback;
    }

    if (str_starts_with($imagem, 'http://') || str_starts_with($imagem, 'https://')) {
        return $imagem;
    }

    return '/' . ltrim(str_replace('\\', '/', $imagem), '/');
}

/**
 * Retorna um atributo onerror seguro que não causa loop infinito.
 * Usa this.onerror=null antes de setar o fallback.
 *
 * @param string $fallback URL do fallback (deixe vazio para só esconder)
 * @return string Atributo HTML onerror completo
 */
function img_onerror(string $fallback = ''): string
{
    if (empty($fallback)) {
        return "onerror=\"this.onerror=null;this.style.display='none';\"";
    }

    $fb = img_url($fallback, '');
    return "onerror=\"this.onerror=null;this.src='" . htmlspecialchars($fb, ENT_QUOTES) . "';\"";
}

/**
 * Salva um arquivo de imagem enviado via $_FILES.
 *
 * @param  string  $campo         Nome do campo no $_FILES
 * @param  string  $prefixo       Ex: 'post', 'noticia', 'avatar'
 * @param  int     $id            ID do registro (usado no nome do arquivo)
 * @param  string  $pasta         Caminho absoluto da pasta de destino
 * @param  string  $imagem_atual  Path atual no banco (para deletar ao trocar)
 * @return string|null
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

    foreach ($extensoes as $e) {
        $antigo = rtrim($pasta, '/') . "/{$prefixo}_{$id}.{$e}";
        if (file_exists($antigo)) {
            @unlink($antigo);
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        throw new RuntimeException('Falha ao salvar o arquivo no servidor.');
    }

    $raiz = realpath(__DIR__ . '/..');
    $path = str_replace('\\', '/', $destino);
    $raiz = str_replace('\\', '/', $raiz);
    return ltrim(str_replace($raiz . '/', '', $path), '/');
}