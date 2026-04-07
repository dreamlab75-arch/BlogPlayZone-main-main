<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/noticias-model.php';

$pagina    = max(1, (int)($_GET['page']      ?? 1));
$categoria = $_GET['categoria'] ?? '';
$busca     = $_GET['busca']     ?? '';
$limite    = 10;

$resultado     = buscar_noticias_paginadas($pagina, $limite, $categoria, $busca);
$total         = contar_noticias($categoria, $busca);
$total_paginas = max(1, ceil($total / $limite));
$categorias    = buscar_categorias();

// Verifica se o usuário logado pode escrever notícias (adm=1 ou jornalista=3)
$pode_escrever = isset($_SESSION['usuario_id']) &&
                 in_array((int)($_SESSION['usuario_perfil_id'] ?? 0), [1, 3]);

function qs_noticias($categoria, $busca) {
    $p = [];
    if ($categoria) $p[] = 'categoria=' . urlencode($categoria);
    if ($busca)     $p[] = 'busca='     . urlencode($busca);
    return $p ? implode('&', $p) . '&' : '';
}
$qs = qs_noticias($categoria, $busca);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notícias — PlayZone</title>
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
$base = '../';
include __DIR__ . '/../header.php';
?>

<!-- HERO -->
<div class="noticias-hero">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <h1><i class="bi bi-newspaper me-2"></i>Notícias</h1>
      <p class="mb-0">Fique por dentro de tudo que acontece no mundo dos games</p>
    </div>
    <?php if ($pode_escrever): ?>
      <a href="escrever-noticia.php" class="btn-criar-noticia">
        <i class="bi bi-plus-circle-fill"></i> Escrever Notícia
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="container noticias-page-wrap">
  <div class="row g-4">

    <!-- COLUNA PRINCIPAL -->
    <div class="col-lg-8">

      <!-- FILTROS -->
      <form method="GET" class="filtro-bar">
        <div class="row g-2 align-items-center">
          <div class="col-md-6">
            <div class="position-relative">
              <input type="text" name="busca" class="form-control"
                     placeholder="Buscar notícias..."
                     value="<?= htmlspecialchars($busca) ?>"
                     style="padding-left:38px;">
              <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.9rem;pointer-events:none;"></i>
            </div>
          </div>
          <div class="col-md-4">
            <select name="categoria" class="form-select">
              <option value="">Todas as categorias</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria===$cat?'selected':'' ?>>
                  <?= htmlspecialchars(ucfirst($cat)) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn-filtrar w-100">
              <i class="bi bi-funnel me-1"></i>Filtrar
            </button>
          </div>
        </div>
        <?php if ($categoria || $busca): ?>
        <div class="tags-selecionadas-bar mt-3">
          <?php if ($categoria): ?>
            <span class="tag-ativa-pill">
              <?= htmlspecialchars(ucfirst($categoria)) ?>
              <button type="button" onclick="limparFiltro('categoria')">✕</button>
            </span>
          <?php endif; ?>
          <?php if ($busca): ?>
            <span class="tag-ativa-pill">
              "<?= htmlspecialchars($busca) ?>"
              <button type="button" onclick="limparFiltro('busca')">✕</button>
            </span>
          <?php endif; ?>
          <a href="noticias-view.php" style="font-size:.8rem;color:#aaa;align-self:center;text-decoration:none;">Limpar tudo</a>
        </div>
        <?php endif; ?>
      </form>

      <!-- RESULTADO INFO -->
      <div class="resultado-info">
        <strong><?= $total ?></strong> notícia<?= $total!=1?'s':'' ?>
        <?= $categoria ? ' em <strong>'.htmlspecialchars(ucfirst($categoria)).'</strong>' : '' ?>
        · Página <strong><?= $pagina ?></strong> de <strong><?= $total_paginas ?></strong>
      </div>

      <!-- LISTA -->
      <?php
      $encontrou = false;
      while ($n = $resultado->fetch(PDO::FETCH_ASSOC)):
        $encontrou = true;
      ?>
      <a href="noticia.php?id=<?= $n['id'] ?>" class="noticias-card-link">
        <article class="noticias-lista-card">
          <?php if ($n['imagem']): ?>
          <div class="noticias-lista-thumb"
               style="background-image:url('<?= htmlspecialchars($n['imagem']) ?>')"></div>
          <?php endif; ?>
          <div class="noticias-lista-corpo">
            <div class="mb-1">
              <span class="badge <?= categoria_para_badge($n['categoria']) ?>">
                <?= strtoupper($n['categoria']) ?>
              </span>
            </div>
            <h2 class="noticias-lista-titulo"><?= htmlspecialchars($n['titulo']) ?></h2>
            <p class="noticias-lista-resumo"><?= htmlspecialchars($n['resumo']) ?></p>
            <div class="noticias-lista-meta">
              <img src="<?= htmlspecialchars($n['autor_avatar'] ?? '../img/avatar-default.png') ?>"
                   class="noticias-meta-avatar"
                   onerror="this.src='../img/avatar-default.png'"
                   alt="<?= htmlspecialchars($n['autor_nome']) ?>">
              <span class="noticias-meta-autor"><?= htmlspecialchars($n['autor_nome']) ?></span>
              <span class="noticias-meta-sep">·</span>
              <span class="tempo-relativo" data-publicacao="<?= $n['data_publicacao'] ?>">
                <?= tempo_decorrido($n['data_publicacao']) ?>
              </span>
              <span class="noticias-meta-sep">·</span>
              <span><i class="bi bi-eye"></i> <?= $n['visualizacoes'] ?></span>
            </div>
          </div>
        </article>
      </a>
      <?php endwhile; ?>

      <?php if (!$encontrou): ?>
        <div class="empty-state">
          <i class="bi bi-newspaper"></i>
          <p>Nenhuma notícia encontrada.</p>
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
        $ini = max(1, $pagina-2); $fim = min($total_paginas, $pagina+2);
        if ($ini > 1): ?><a href="?<?= $qs ?>page=1">1</a><?php
          if ($ini > 2): ?><span class="reticencias">…</span><?php endif;
        endif;
        for ($i=$ini; $i<=$fim; $i++):
        ?><a href="?<?= $qs ?>page=<?= $i ?>" class="<?= $i==$pagina?'atual':'' ?>"><?= $i ?></a><?php
        endfor;
        if ($fim < $total_paginas):
          if ($fim < $total_paginas-1): ?><span class="reticencias">…</span><?php endif;
          ?><a href="?<?= $qs ?>page=<?= $total_paginas ?>"><?= $total_paginas ?></a><?php
        endif; ?>
        <?php if ($pagina < $total_paginas): ?>
          <a href="?<?= $qs ?>page=<?= $pagina+1 ?>" class="nav-pag">
            Próximo <i class="bi bi-chevron-right"></i>
          </a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- SIDEBAR -->
    <div class="col-lg-4">
      <div class="news-sidebar" style="top:90px;">
        <h4><i class="bi bi-fire me-2"></i>Mais Lidas</h4>
        <?php
        $mais_lidas = conectar_noticias()->query("
            SELECT n.id, n.titulo, n.categoria, n.data_publicacao,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes
            FROM noticias n
            LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
            GROUP BY n.id ORDER BY visualizacoes DESC LIMIT 5
        ");
        $rank = 1;
        while ($ml = $mais_lidas->fetch(PDO::FETCH_ASSOC)):
        ?>
        <div class="noticia-card">
          <div style="display:flex;gap:12px;align-items:flex-start;">
            <span class="noticias-rank"><?= $rank++ ?></span>
            <div>
              <span class="badge <?= categoria_para_badge($ml['categoria']) ?>" style="font-size:.65rem;margin-bottom:4px;">
                <?= strtoupper($ml['categoria']) ?>
              </span>
              <div class="news-item-title" style="font-size:.88rem;">
                <a href="noticia.php?id=<?= $ml['id'] ?>" style="color:#333;text-decoration:none;">
                  <?= htmlspecialchars($ml['titulo']) ?>
                </a>
              </div>
              <div class="news-item-date mt-1">
                <i class="bi bi-eye"></i> <?= $ml['visualizacoes'] ?> views
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>

  </div>
</div>

<template hx-get="../footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>

<script>
function limparFiltro(campo) {
  const input = document.querySelector('[name="' + campo + '"]');
  if (input) { input.value = campo === 'categoria' ? '' : ''; }
  document.querySelector('form').submit();
}
</script>
<script src="../tempo-relativo.js"></script>
</body>
</html>