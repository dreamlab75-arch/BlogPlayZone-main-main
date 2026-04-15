<?php
session_start();
require_once __DIR__ . "/posts-model.php";

$pagina      = isset($_GET['page'])  ? max(1, (int)$_GET['page']) : 1;
$busca       = $_GET['busca']  ?? '';
$tagsFiltro  = $_GET['tags']   ?? [];
$ordem       = $_GET['ordem']  ?? 'recentes';
$limite      = 10;

$result_set_posts = buscar_posts_paginados($pagina, $limite, $ordem, $busca, $tagsFiltro);
$total_posts      = contar_posts($busca, $tagsFiltro);
$total_paginas    = max(1, ceil($total_posts / $limite));
$tagsDisponiveis  = buscar_tags();

// Monta query string base (sem page) para paginação
function query_sem_page($busca, $ordem, $tagsFiltro) {
    $params = [];
    if ($busca)              $params[] = 'busca=' . urlencode($busca);
    if ($ordem !== 'recentes') $params[] = 'ordem=' . urlencode($ordem);
    foreach ($tagsFiltro as $t) $params[] = 'tags[]=' . urlencode($t);
    return $params ? implode('&', $params) . '&' : '';
}
$qs = query_sem_page($busca, $ordem, $tagsFiltro);

$usuario_logado = isset($_SESSION['usuario_id']);
$usuario_perfil = $_SESSION['usuario_perfil'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Posts — PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"
    integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz"
    crossorigin="anonymous"></script>
</head>
<body>
<?php
$base = '../'; // prefixo de caminho para a raiz, usado pelo header.php
include __DIR__ . '/../header.php';
?>

<!-- HERO -->
<div class="posts-page-hero">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <h1><i class="bi bi-controller me-2"></i>Posts da Comunidade</h1>
      <p>Opiniões, reviews e experiências dos gamers</p>
    </div>
    <?php if ($usuario_logado): ?>
      <button class="btn-criar-post" onclick="abrirModalCriar()">
        <i class="bi bi-plus-circle-fill"></i> Criar Post
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="max-width:960px; padding-bottom: 60px;">

  <a href="../index.php" class="btn-voltar">
    <i class="bi bi-arrow-left"></i> Voltar ao início
  </a>

  <!-- FILTRO BAR -->
  <form method="GET" id="formFiltro" class="filtro-bar">
    <div class="row g-2 align-items-center">

      <!-- Busca -->
      <div class="col-md-4">
        <div class="position-relative">
          <input type="text" name="busca" class="form-control" placeholder="Buscar por título..."
                 value="<?= htmlspecialchars($busca) ?>"
                 style="padding-left:38px;">
          <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:0.9rem;pointer-events:none;"></i>
        </div>
      </div>

      <!-- Ordenação -->
      <div class="col-md-3">
        <select name="ordem" class="form-select">
          <option value="recentes" <?= $ordem=='recentes'?'selected':'' ?>>🕒 Mais recentes</option>
          <option value="antigos"  <?= $ordem=='antigos' ?'selected':'' ?>>📅 Mais antigos</option>
          <option value="vistos"   <?= $ordem=='vistos'  ?'selected':'' ?>>👁️ Mais vistos</option>
        </select>
      </div>

      <!-- Tags dropdown -->
      <div class="col-md-3 tags-dropdown-wrap">
        <button type="button" class="btn-tags-toggle w-100 <?= !empty($tagsFiltro)?'ativo':'' ?>"
                onclick="toggleTagsDropdown()" id="btnTagsToggle">
          <i class="bi bi-tags me-1"></i>
          Tags<?= !empty($tagsFiltro) ? ' <span class="badge" style="background:#611DF2;border-radius:10px;font-size:.7rem;">'.count($tagsFiltro).'</span>' : '' ?>
        </button>
        <div class="dropdown-tags-box" id="dropdownTags">
          <?php foreach ($tagsDisponiveis as $tag): ?>
            <label class="tag-item">
              <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag['nome']) ?>"
                     <?= in_array($tag['nome'], $tagsFiltro) ? 'checked' : '' ?>>
              <?= htmlspecialchars($tag['nome']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Botão filtrar -->
      <div class="col-md-2">
        <button type="submit" class="btn-filtrar w-100">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
      </div>

    </div>

    <!-- Pills das tags selecionadas -->
    <?php if (!empty($tagsFiltro)): ?>
    <div class="tags-selecionadas-bar mt-3">
      <?php foreach ($tagsFiltro as $t): ?>
        <span class="tag-ativa-pill">
          <?= htmlspecialchars($t) ?>
          <button type="button" onclick="removerTag('<?= htmlspecialchars($t) ?>')" title="Remover">✕</button>
        </span>
      <?php endforeach; ?>
      <a href="posts-view.php" style="font-size:.8rem;color:#aaa;align-self:center;text-decoration:none;">
        Limpar tudo
      </a>
    </div>
    <?php endif; ?>
  </form>

  <!-- INFO RESULTADO -->
  <div class="resultado-info">
    <?php if ($busca || !empty($tagsFiltro)): ?>
      Mostrando <strong><?= $total_posts ?></strong> resultado<?= $total_posts != 1 ? 's' : '' ?>
      <?= $busca ? ' para "<strong>'.htmlspecialchars($busca).'</strong>"' : '' ?>
      · Página <strong><?= $pagina ?></strong> de <strong><?= $total_paginas ?></strong>
    <?php else: ?>
      <strong><?= $total_posts ?></strong> post<?= $total_posts != 1 ? 's' : '' ?> publicados
      · Página <strong><?= $pagina ?></strong> de <strong><?= $total_paginas ?></strong>
    <?php endif; ?>
  </div>

  <!-- LISTA DE POSTS -->
  <?php
  $posts_encontrados = false;
  while ($linha_posts = $result_set_posts->fetch(PDO::FETCH_ASSOC)):
    $posts_encontrados = true;
    $tags = $linha_posts['tags'] ? explode(',', $linha_posts['tags']) : [];
  ?>
    <div class="post-card">
      <div class="post-author">
        <div class="post-avatar">
          <img src="<?= htmlspecialchars($linha_posts['avatar'] ?? '../img/avatar-default.png') ?>"
               alt="<?= htmlspecialchars($linha_posts['autor']) ?>"
               onerror="this.src='../img/avatar-default.png'">
        </div>
        <div class="post-author-info">
          <h6><?= htmlspecialchars($linha_posts['autor']) ?></h6>
          <small>
            <i class="bi bi-clock me-1"></i>
            <span class="tempo-relativo" data-publicacao="<?= $linha_posts['data_publicacao'] ?>">
              <?= tempo_decorrido_posts($linha_posts['data_publicacao']) ?>
            </span>
          </small>
        </div>
      </div>

      <div class="post-tags">
        <?php foreach ($tags as $tag): ?>
          <span class="post-tag"><?= htmlspecialchars(trim($tag)) ?></span>
        <?php endforeach; ?>
      </div>

      <h4 class="post-title">
        <a href="post.php?id=<?= $linha_posts['id'] ?>">
          <?= htmlspecialchars($linha_posts['titulo']) ?>
        </a>
      </h4>

      <p class="post-excerpt">
        <?= htmlspecialchars(mb_substr($linha_posts['conteudo'], 0, 180)) ?>...
      </p>

      <div class="post-footer">
        <div class="post-stats">
          <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $linha_posts['curtidas'] ?></span>
          <span><i class="bi bi-chat-fill"></i> <?= $linha_posts['comentarios'] ?></span>
          <span><i class="bi bi-eye-fill"></i> <?= $linha_posts['visualizacoes'] ?></span>
        </div>
        <a class="btn-ler-post" href="post.php?id=<?= $linha_posts['id'] ?>">
          Ler post <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  <?php endwhile; ?>

  <?php if (!$posts_encontrados): ?>
    <div class="empty-state">
      <i class="bi bi-controller"></i>
      <p>Nenhum post encontrado<?= $busca ? ' para "<strong>'.htmlspecialchars($busca).'</strong>"' : '' ?>.</p>
    </div>
  <?php endif; ?>

  <!-- PAGINAÇÃO -->
  <?php if ($total_paginas > 1): ?>
  <nav class="paginacao">
    <?php if ($pagina > 1): ?>
      <a href="?<?= $qs ?>page=<?= $pagina-1 ?>" class="nav-pag">
        <i class="bi bi-chevron-left"></i> Anterior
      </a>
    <?php endif; ?>

    <?php
    // Lógica de páginas: mostra max 5 ao redor da atual
    $inicio = max(1, $pagina - 2);
    $fim    = min($total_paginas, $pagina + 2);
    if ($inicio > 1): ?>
      <a href="?<?= $qs ?>page=1">1</a>
      <?php if ($inicio > 2): ?><span class="reticencias">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $inicio; $i <= $fim; $i++): ?>
      <a href="?<?= $qs ?>page=<?= $i ?>" class="<?= $i == $pagina ? 'atual' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($fim < $total_paginas): ?>
      <?php if ($fim < $total_paginas - 1): ?><span class="reticencias">…</span><?php endif; ?>
      <a href="?<?= $qs ?>page=<?= $total_paginas ?>"><?= $total_paginas ?></a>
    <?php endif; ?>

    <?php if ($pagina < $total_paginas): ?>
      <a href="?<?= $qs ?>page=<?= $pagina+1 ?>" class="nav-pag">
        Próximo <i class="bi bi-chevron-right"></i>
      </a>
    <?php endif; ?>
  </nav>
  <?php endif; ?>

</div><!-- /container -->

<!-- ========== MODAL CRIAR POST ========== -->
<?php if ($usuario_logado): ?>
<div class="modal-criar-overlay" id="modalCriarOverlay" onclick="fecharModalFora(event)">
  <div class="modal-criar-box" id="modalCriarBox">
    <h3><i class="bi bi-pencil-square me-2"></i>Novo Post</h3>
    <p class="modal-subtitulo">Compartilhe sua opinião, review ou experiência com a comunidade</p>

    <form method="POST" action="ctrl-post.php" id="formCriarPost" enctype="multipart/form-data">
      <!-- Título -->
      <div class="mb-3">
        <label class="form-label">Título *</label>
        <input type="text" name="titulo" class="form-control"
               placeholder="Ex: Por que Elden Ring é uma obra-prima..." required maxlength="200">
      </div>

      <!-- Conteúdo -->
      <div class="mb-3">
        <label class="form-label">Conteúdo *</label>
        <textarea name="conteudo" class="form-control"
                  placeholder="Escreva seu post aqui..." required minlength="50"></textarea>
        <div class="form-text" style="font-size:.78rem;color:#aaa;">Mínimo 50 caracteres</div>
      </div>

      <!-- Imagem -->
      <div class="mb-3">
        <label class="form-label">Imagem <span style="font-weight:400;color:#aaa;">(opcional)</span></label>
        <input type="file" name="imagem" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>

      <div class="modal-divider"></div>

      <!-- Tags -->
      <div class="mb-4">
        <label class="form-label">Tags <span style="font-weight:400;color:#aaa;">(selecione até 5)</span></label>
        <div class="tags-modal-grid">
          <?php
          // Rebusca tags para o modal
          $tagsModal = buscar_tags();
          foreach ($tagsModal as $tag): ?>
            <div class="tag-check-pill">
              <input type="checkbox" name="tags_post[]"
                     id="tag_modal_<?= $tag['id'] ?>"
                     value="<?= $tag['id'] ?>">
              <label for="tag_modal_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Botões -->
      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn-modal-cancelar" onclick="fecharModal()">
          Cancelar
        </button>
        <button type="submit" class="btn-modal-publicar">
          <i class="bi bi-send me-1"></i> Publicar Post
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Mensagem de sucesso/erro vinda do controller -->
<?php if (isset($_GET['sucesso'])): ?>
<div style="position:fixed;bottom:24px;right:24px;z-index:99999;animation:slideUp .3s ease;">
  <div style="background:#22c55e;color:white;border-radius:12px;padding:14px 20px;
              box-shadow:0 8px 24px rgba(34,197,94,.3);font-weight:600;display:flex;align-items:center;gap:10px;">
    <i class="bi bi-check-circle-fill" style="font-size:1.2rem;"></i>
    Post publicado com sucesso!
  </div>
</div>
<script>setTimeout(()=>document.querySelector('[style*="bottom:24px"]')?.remove(), 4000);</script>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
<div style="position:fixed;bottom:24px;right:24px;z-index:99999;">
  <div style="background:#ef4444;color:white;border-radius:12px;padding:14px 20px;
              box-shadow:0 8px 24px rgba(239,68,68,.3);font-weight:600;display:flex;align-items:center;gap:10px;">
    <i class="bi bi-exclamation-circle-fill" style="font-size:1.2rem;"></i>
    <?= htmlspecialchars($_GET['erro']) ?>
  </div>
</div>
<script>setTimeout(()=>document.querySelector('[style*="bottom:24px"]')?.remove(), 5000);</script>
<?php endif; ?>

<script>
// ===== TAGS DROPDOWN FILTRO =====
function toggleTagsDropdown() {
  const box = document.getElementById('dropdownTags');
  const btn = document.getElementById('btnTagsToggle');
  const aberto = box.classList.toggle('aberto');
  btn.classList.toggle('ativo', aberto);
}
document.addEventListener('click', function(e) {
  const wrap = document.querySelector('.tags-dropdown-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('dropdownTags')?.classList.remove('aberto');
    document.getElementById('btnTagsToggle')?.classList.remove('ativo');
  }
});

function removerTag(nome) {
  const checks = document.querySelectorAll('input[name="tags[]"]');
  checks.forEach(c => { if (c.value === nome) c.checked = false; });
  document.getElementById('formFiltro').submit();
}

// ===== MODAL CRIAR POST =====
function abrirModalCriar() {
  document.getElementById('modalCriarOverlay').classList.add('aberto');
  document.body.style.overflow = 'hidden';
}
function fecharModal() {
  document.getElementById('modalCriarOverlay').classList.remove('aberto');
  document.body.style.overflow = '';
}
function fecharModalFora(e) {
  if (e.target === document.getElementById('modalCriarOverlay')) fecharModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });

// Limita seleção de tags a 5
document.querySelectorAll('input[name="tags_post[]"]').forEach(cb => {
  cb.addEventListener('change', function() {
    const selecionadas = document.querySelectorAll('input[name="tags_post[]"]:checked');
    if (selecionadas.length > 5) {
      this.checked = false;
    }
  });
});
</script>

<script src="../tempo-relativo.js"></script>

<template hx-get="/../footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
</body>
</html>