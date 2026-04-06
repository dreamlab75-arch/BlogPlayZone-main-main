<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/noticias-model.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: noticias-view.php'); exit; }

$noticia = buscar_noticia_por_id($id);
if (!$noticia) { header('Location: noticias-view.php'); exit; }

if (isset($_SESSION['usuario_id'])) {
    registrar_visualizacao_noticia($id, $_SESSION['usuario_id']);
}

// Relacionadas
$stmtRel = conectar_noticias()->prepare("
    SELECT id, titulo, imagem, categoria, data_publicacao
    FROM noticias WHERE categoria = :cat AND id != :id
    ORDER BY data_publicacao DESC LIMIT 3
");
$stmtRel->execute([':cat' => $noticia['categoria'], ':id' => $id]);
$relacionadas = $stmtRel->fetchAll(PDO::FETCH_ASSOC);

$cor_cat = categoria_para_cor($noticia['categoria']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($noticia['titulo']) ?> — PlayZone</title>
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

<div class="container noticia-single-wrap">

  <a href="noticias-view.php" class="btn-voltar">
    <i class="bi bi-arrow-left"></i> Voltar às notícias
  </a>

  <div class="row g-4">

    <!-- ARTIGO -->
    <div class="col-lg-8">
      <article class="noticia-artigo">

        <!-- BREADCRUMB CATEGORIA -->
        <div class="noticia-breadcrumb">
          <span class="noticia-breadcrumb-cat"
                style="color:<?= $cor_cat ?>;border-bottom:2px solid <?= $cor_cat ?>;">
            <?= strtoupper(htmlspecialchars($noticia['categoria'])) ?>
          </span>
          <span class="noticia-breadcrumb-sep">/</span>
          <span class="noticia-breadcrumb-tipo">NOTÍCIA</span>
        </div>

        <!-- TÍTULO GRANDE -->
        <h1 class="noticia-titulo-grande"><?= htmlspecialchars($noticia['titulo']) ?></h1>

        <!-- SUBTÍTULO / RESUMO -->
        <?php if (!empty($noticia['resumo'])): ?>
          <p class="noticia-resumo-destaque"><?= htmlspecialchars($noticia['resumo']) ?></p>
        <?php endif; ?>

        <hr class="noticia-divisor">

        <!-- DATA -->
        <div class="noticia-meta-data">
          <span>
            <i class="bi bi-calendar3 me-1"></i>
            <?= formatar_data_noticia($noticia['data_publicacao']) ?>
          </span>
          <span class="noticia-meta-sep">·</span>
          <span class="tempo-relativo" data-publicacao="<?= $noticia['data_publicacao'] ?>">
            <?= tempo_decorrido($noticia['data_publicacao']) ?>
          </span>
          <span class="noticia-meta-sep">·</span>
          <span><i class="bi bi-eye me-1"></i><?= $noticia['visualizacoes'] ?> views</span>
        </div>

        <hr class="noticia-divisor">

        <!-- AUTOR -->
        <div class="noticia-autor-bloco">
          <img src="<?= htmlspecialchars($noticia['autor_avatar'] ?? '../img/avatar-default.png') ?>"
               alt="<?= htmlspecialchars($noticia['autor_nome']) ?>"
               class="noticia-autor-avatar"
               onerror="this.src='../img/avatar-default.png'">
          <div>
            <div class="noticia-autor-nome"><?= htmlspecialchars($noticia['autor_nome']) ?></div>
            <a href="mailto:" class="noticia-autor-email">Enviar E-mail</a>
          </div>
        </div>

        <hr class="noticia-divisor">

        <!-- IMAGEM DESTAQUE -->
        <?php if (!empty($noticia['imagem'])): ?>
          <figure class="noticia-figura">
            <img src="<?= htmlspecialchars($noticia['imagem']) ?>"
                 alt="<?= htmlspecialchars($noticia['titulo']) ?>"
                 class="noticia-imagem-destaque"
                 onerror="this.style.display='none'">
          </figure>
        <?php endif; ?>

        <!-- CONTEÚDO -->
        <div class="noticia-conteudo">
          <?php
          $primeiro = true;
          foreach (explode("\n", trim($noticia['conteudo'])) as $p):
            $p = trim($p);
            if ($p === '') continue;
            if ($primeiro): ?>
              <p class="noticia-lead"><?= htmlspecialchars($p) ?></p>
              <?php $primeiro = false;
            else: ?>
              <p><?= htmlspecialchars($p) ?></p>
            <?php endif;
          endforeach; ?>
        </div>

        <!-- COMPARTILHAR -->
        <div class="noticia-compartilhar">
          <span class="noticia-compartilhar-label">Compartilhar:</span>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($noticia['titulo']) ?>&url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>"
             target="_blank" class="noticia-share-btn noticia-share-x" title="X">
            <i class="bi bi-twitter-x"></i>
          </a>
          <a href="https://wa.me/?text=<?= urlencode($noticia['titulo'].' - http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>"
             target="_blank" class="noticia-share-btn noticia-share-wa" title="WhatsApp">
            <i class="bi bi-whatsapp"></i>
          </a>
          <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>alert('Link copiado!'))"
                  class="noticia-share-btn noticia-share-copy" title="Copiar link">
            <i class="bi bi-link-45deg"></i>
          </button>
        </div>

      </article>

      <!-- RELACIONADAS -->
      <?php if (!empty($relacionadas)): ?>
      <section class="noticia-relacionadas">
        <h3 class="noticia-relacionadas-titulo">
          <span style="border-bottom:3px solid <?= $cor_cat ?>;padding-bottom:4px;">Veja também</span>
        </h3>
        <div class="row g-3 mt-1">
          <?php foreach ($relacionadas as $rel): ?>
          <div class="col-md-4">
            <a href="noticia.php?id=<?= $rel['id'] ?>" class="noticia-rel-card">
              <div class="noticia-rel-thumb"
                   style="<?= $rel['imagem']?'background-image:url('.htmlspecialchars($rel['imagem']).')':'' ?>">
                <?php if (!$rel['imagem']): ?><i class="bi bi-newspaper"></i><?php endif; ?>
              </div>
              <div class="noticia-rel-corpo">
                <span class="badge <?= categoria_para_badge($rel['categoria']) ?>" style="font-size:.65rem;">
                  <?= strtoupper($rel['categoria']) ?>
                </span>
                <p class="noticia-rel-titulo"><?= htmlspecialchars($rel['titulo']) ?></p>
                <span class="noticia-rel-data tempo-relativo" data-publicacao="<?= $rel['data_publicacao'] ?>">
                  <?= tempo_decorrido($rel['data_publicacao']) ?>
                </span>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- SIDEBAR -->
    <div class="col-lg-4">
      <div class="news-sidebar" style="top:90px;">
        <h4><i class="bi bi-fire me-2"></i>Mais Lidas</h4>
        <?php
        $mais_lidas = conectar_noticias()->query("
            SELECT n.id, n.titulo, n.categoria,
                   COUNT(DISTINCT vn.usuario_id) AS visualizacoes
            FROM noticias n
            LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
            WHERE n.id != $id
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

        <h4 class="mt-4"><i class="bi bi-tags me-2"></i>Categorias</h4>
        <div class="d-flex flex-wrap gap-2 mt-2">
          <?php foreach (buscar_categorias() as $cat): ?>
            <a href="noticias-view.php?categoria=<?= urlencode($cat) ?>"
               class="noticias-cat-pill <?= $noticia['categoria']===$cat?'ativa':'' ?>">
              <?= htmlspecialchars(ucfirst($cat)) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<template hx-get="../footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
<script src="../tempo-relativo.js"></script>
</body>
</html>