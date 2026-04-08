<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php?erro=' . urlencode('Faça login para publicar notícias.'));
    exit;
}

require_once __DIR__ . '/noticias-model.php';

$pdo  = conectar_noticias();
$stmt = $pdo->prepare("SELECT nome, perfil_id, avatar FROM usuarios WHERE id = :id");
$stmt->execute([':id' => (int)$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !in_array((int)$usuario['perfil_id'], [1, 3])) {
    header('Location: noticias-view.php?erro=' . urlencode('Apenas jornalistas podem publicar notícias.'));
    exit;
}

$categorias = [
    'lançamento','rumor','análise','urgente','review',
    'prévia','atualização','evento','hardware','negócios',
    'curiosidade','lista'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Escrever Notícia — PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="painel-body">
<?php $base = '../'; include __DIR__ . '/../header.php'; ?>

<div class="container" style="max-width:780px; padding: 40px 16px 80px;">

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="fw-bold mb-1" style="color:#611DF2;">
        <i class="bi bi-newspaper me-2"></i>Escrever Notícia
      </h2>
      <p class="text-muted mb-0" style="font-size:.9rem;">
        Publicando como <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
        <span class="badge bg-purple text-white ms-1" style="font-size:.7rem;">
          <i class="bi bi-newspaper me-1"></i>Jornalista
        </span>
      </p>
    </div>
    <a href="noticias-view.php" class="btn-voltar" style="margin:0;">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>

  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger" id="alerta-erro">
      <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['erro']) ?>
    </div>
  <?php endif; ?>

  <div class="adm-card">
    <form action="ctrl-escrever-noticia.php" method="POST" id="formNoticia">

      <div class="mb-3">
        <label class="form-label fw-semibold">Título <span style="color:#ef4444;">*</span></label>
        <input type="text" name="titulo" id="inputTitulo"
               class="form-control adm-form-input"
               placeholder="Título da notícia..."
               required minlength="5" maxlength="200"
               value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
        <div class="form-text" id="contagemTitulo">0 / 200</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Resumo <span style="color:#ef4444;">*</span>
          <span class="text-muted fw-normal" style="font-size:.82rem;"></span>
        </label>
        <textarea name="resumo" id="inputResumo"
                  class="form-control adm-form-input"
                  placeholder="Um parágrafo curto descrevendo a notícia..."
                  required minlength="10" maxlength="300"
                  rows="2"><?= htmlspecialchars($_POST['resumo'] ?? '') ?></textarea>
        <div class="form-text" id="contagemResumo">0 / 300</div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Categoria <span style="color:#ef4444;">*</span></label>
          <select name="categoria" id="selectCategoria"
                  class="form-select adm-form-input" required>
            <option value="" disabled <?= empty($_POST['categoria']) ? 'selected' : '' ?>>Selecione...</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat ?>" <?= (($_POST['categoria'] ?? '') === $cat) ? 'selected' : '' ?>>
                <?= ucfirst($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-7">
          <label class="form-label fw-semibold">
            Imagem <span class="text-muted fw-normal">(URL, opcional)</span>
          </label>
          <input type="url" name="imagem" id="inputImagem"
                 class="form-control adm-form-input"
                 placeholder="https://..."
                 value="<?= htmlspecialchars($_POST['imagem'] ?? '') ?>"
                 oninput="atualizarPreviewImagem(this.value)">
        </div>
      </div>

      <div id="previewImagemWrap" class="mb-3" style="display:none;">
        <div style="background:#0e0e1a;border-radius:12px;overflow:hidden;max-height:260px;">
          <img id="previewImagem" src="" alt="Preview"
               style="width:100%;max-height:260px;object-fit:cover;display:block;"
               onerror="document.getElementById('previewImagemWrap').style.display='none'">
        </div>
        <div class="form-text"><i class="bi bi-check-circle text-success me-1"></i>Preview da imagem</div>
      </div>

      <div class="mb-3" id="badgePreviewWrap" style="display:none;">
        <span class="badge" id="badgePreview" style="font-size:.8rem;"></span>
        <span class="form-text ms-2">Preview da categoria</span>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Conteúdo <span style="color:#ef4444;">*</span>
          <span class="text-muted fw-normal" style="font-size:.82rem;">(cada parágrafo em uma linha separada)</span>
        </label>
        <textarea name="conteudo" id="inputConteudo"
                  class="form-control adm-form-input"
                  placeholder="Escreva o conteúdo completo da notícia aqui..."
                  required minlength="50"
                  rows="12"
                  style="resize:vertical;"><?= htmlspecialchars($_POST['conteudo'] ?? '') ?></textarea>
        <div class="form-text" id="contagemConteudo">0 caracteres (mínimo 50)</div>
      </div>

      <div class="d-flex gap-3 justify-content-end flex-wrap">
        <a href="noticias-view.php" class="btn-modal-cancelar" style="text-decoration:none;display:inline-flex;align-items:center;">
          Cancelar
        </a>
        <button type="submit" class="btn-modal-publicar" id="btnPublicar">
          <i class="bi bi-send-fill me-1"></i> Publicar Notícia
        </button>
      </div>

    </form>
  </div>

</div>

<script>
function contador(inputId, labelId, max) {
  const el = document.getElementById(inputId);
  const lb = document.getElementById(labelId);
  if (!el || !lb) return;
  const atualizar = () => {
    const n = el.value.length;
    lb.textContent = max ? `${n} / ${max}` : `${n} caracteres (mínimo 50)`;
    lb.style.color = (max && n > max * 0.9) ? '#ef4444' : '';
  };
  el.addEventListener('input', atualizar);
  atualizar();
}
contador('inputTitulo',   'contagemTitulo',   200);
contador('inputResumo',   'contagemResumo',   300);
contador('inputConteudo', 'contagemConteudo',  0);

function atualizarPreviewImagem(url) {
  const wrap = document.getElementById('previewImagemWrap');
  const img  = document.getElementById('previewImagem');
  if (url && url.startsWith('http')) {
    img.src = url;
    wrap.style.display = 'block';
  } else {
    wrap.style.display = 'none';
  }
}
const imgVal = document.getElementById('inputImagem').value;
if (imgVal) atualizarPreviewImagem(imgVal);

const badgeMap = {
  'lançamento':'bg-primary text-white','rumor':'bg-warning text-dark',
  'análise':'bg-info text-white','urgente':'bg-danger text-white',
  'review':'bg-success text-white','prévia':'bg-purple text-white',
  'atualização':'bg-secondary text-white','evento':'bg-dark text-white',
  'hardware':'bg-warning text-dark','negócios':'bg-danger text-white',
  'curiosidade':'bg-info text-white','lista':'bg-primary text-white',
};
document.getElementById('selectCategoria').addEventListener('change', function() {
  const wrap  = document.getElementById('badgePreviewWrap');
  const badge = document.getElementById('badgePreview');
  if (this.value) {
    badge.className = 'badge ' + (badgeMap[this.value] || 'bg-secondary text-white');
    badge.textContent = this.value.toUpperCase();
    wrap.style.display = 'block';
  } else {
    wrap.style.display = 'none';
  }
});
document.getElementById('selectCategoria').dispatchEvent(new Event('change'));

document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 4000);
});

document.getElementById('formNoticia').addEventListener('submit', function() {
  const btn = document.getElementById('btnPublicar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publicando...';
});
</script>

<template hx-get="../footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
<script src="../tempo-relativo.js"></script>
</body>
</html>