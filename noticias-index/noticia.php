<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/noticias-model.php';
require_once __DIR__ . '/../util/upload.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: noticias-view.php'); exit; }

$noticia = buscar_noticia_por_id($id);
if (!$noticia) { header('Location: noticias-view.php'); exit; }

// ── Registra visualização (1 por usuário por notícia) ─────────────────────
if (isset($_SESSION['usuario_id'])) {
    registrar_visualizacao_noticia($id, $_SESSION['usuario_id']);
}

// ── Processa ação de curtir / comentar (POST) ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    $uid = (int)$_SESSION['usuario_id'];
    $pdo = conectar_noticias();

    if ($_POST['acao'] === 'curtir') {
        $existe = $pdo->prepare("SELECT ativo FROM Curte_noticia WHERE usuario_id=:u AND noticia_id=:n");
        $existe->execute([':u' => $uid, ':n' => $id]);
        $row = $existe->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $novoAtivo = $row['ativo'] ? 0 : 1;
            $pdo->prepare("UPDATE Curte_noticia SET ativo=:a WHERE usuario_id=:u AND noticia_id=:n")
                ->execute([':a' => $novoAtivo, ':u' => $uid, ':n' => $id]);
        } else {
            $pdo->prepare("INSERT INTO Curte_noticia (usuario_id, noticia_id, ativo) VALUES (:u,:n,1)")
                ->execute([':u' => $uid, ':n' => $id]);
        }
    }

    if ($_POST['acao'] === 'comentar') {
        $comentario = trim($_POST['comentario'] ?? '');
        if (strlen($comentario) >= 2) {
            $pdo->prepare("
                INSERT INTO Comentarios_noticias (comentario, noticia_id, usuario_id)
                VALUES (:c, :n, :u)
            ")->execute([':c' => $comentario, ':n' => $id, ':u' => $uid]);
        }
    }

    header("Location: noticia.php?id=$id", true, 303);
    exit;
}

// ── Recarrega dados atualizados ───────────────────────────────────────────
$noticia = buscar_noticia_por_id($id);

// ── Busca comentários ─────────────────────────────────────────────────────
$pdo  = conectar_noticias();
$stmt = $pdo->prepare("
    SELECT c.comentario, c.data, u.nome, u.avatar
    FROM Comentarios_noticias c
    JOIN usuarios u ON u.id = c.usuario_id
    WHERE c.noticia_id = :n
    ORDER BY c.data DESC
");
$stmt->execute([':n' => $id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Verifica se o usuário logado já curtiu ────────────────────────────────
$usuario_curtiu = false;
if (isset($_SESSION['usuario_id'])) {
    $ck = $pdo->prepare("SELECT ativo FROM Curte_noticia WHERE usuario_id=:u AND noticia_id=:n");
    $ck->execute([':u' => $_SESSION['usuario_id'], ':n' => $id]);
    $ckRow = $ck->fetch(PDO::FETCH_ASSOC);
    $usuario_curtiu = $ckRow && $ckRow['ativo'];
}

$usuario_logado = isset($_SESSION['usuario_id']);

// Relacionadas
$stmtRel = $pdo->prepare("
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

  <?php
  // Botão de voltar inteligente: preserva a página/filtro de onde o usuário veio
  $voltar_url = 'noticias-view.php';
  if (!empty($_SERVER['HTTP_REFERER'])) {
      $ref = $_SERVER['HTTP_REFERER'];
      // Aceita qualquer URL do próprio site que não seja a própria noticia.php
      if (strpos($ref, 'noticia.php') === false && (strpos($ref, 'noticias') !== false || strpos($ref, 'index.php') !== false || strpos($ref, $_SERVER['HTTP_HOST']) !== false)) {
          $voltar_url = htmlspecialchars($ref);
      }
  }
  ?>
  <a href="<?= $voltar_url ?>" class="btn-voltar">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>

  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success mt-3">
      <i class="bi bi-check-circle me-2"></i>Notícia publicada com sucesso!
    </div>
  <?php endif; ?>

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

        <!-- META: DATA + STATS -->
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
          <span class="noticia-meta-sep">·</span>
          <span><i class="bi bi-heart me-1"></i><?= $noticia['curtidas'] ?> curtidas</span>
          <span class="noticia-meta-sep">·</span>
          <span><i class="bi bi-chat-dots me-1"></i><?= $noticia['comentarios'] ?> comentários</span>
        </div>

        <hr class="noticia-divisor">

        <!-- AUTOR -->
        <div class="noticia-autor-bloco">
          <img src="<?= htmlspecialchars(img_url($noticia['autor_avatar'] ?? '', '../img/avatar-default.png')) ?>"
               alt="<?= htmlspecialchars($noticia['autor_nome']) ?>"
               class="noticia-autor-avatar"
               <?= img_onerror('../img/avatar-default.png') ?>>
          <div>
            <div class="noticia-autor-nome"><?= htmlspecialchars($noticia['autor_nome']) ?></div>
            <a href="mailto:" class="noticia-autor-email">Enviar E-mail</a>
          </div>
        </div>

        <hr class="noticia-divisor">

        <!-- IMAGEM DESTAQUE -->
        <?php if (!empty($noticia['imagem'])): ?>
          <figure class="noticia-figura">
            <img src="<?= htmlspecialchars(img_url($noticia['imagem'])) ?>"
                 alt="<?= htmlspecialchars($noticia['titulo']) ?>"
                 class="noticia-imagem-destaque"
                 onerror="this.onerror=null;this.style.display='none';">
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

        <!-- AÇÕES: curtir + stats -->
        <div class="post-acoes">
          <?php if ($usuario_logado): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="acao" value="curtir">
              <button type="submit" class="btn-curtir <?= $usuario_curtiu ? 'curtido' : '' ?>">
                <i class="bi <?= $usuario_curtiu ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                <?= $usuario_curtiu ? 'Curtido' : 'Curtir' ?>
                <strong><?= $noticia['curtidas'] ?></strong>
              </button>
            </form>
          <?php else: ?>
            <span class="btn-curtir" style="cursor:default;opacity:.7;">
              <i class="bi bi-heart"></i> <?= $noticia['curtidas'] ?> curtidas
            </span>
          <?php endif; ?>

          <span class="stat-pill">
            <i class="bi bi-chat-dots-fill"></i>
            <?= $noticia['comentarios'] ?> comentário<?= $noticia['comentarios'] != 1 ? 's' : '' ?>
          </span>

          <span class="stat-pill">
            <i class="bi bi-eye-fill"></i>
            <?= $noticia['visualizacoes'] ?> visualização<?= $noticia['visualizacoes'] != 1 ? 'ões' : '' ?>
          </span>

          <?php if (!$usuario_logado): ?>
            <span class="acoes-login-aviso">
              <a href="../auth/login.php">Faça login</a> para curtir e comentar
            </span>
          <?php endif; ?>
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

      <!-- ── COMENTÁRIOS ── -->
      <section class="comentarios-section">
        <div class="comentarios-titulo">
          <i class="bi bi-chat-square-dots-fill" style="color:#611DF2;"></i>
          Comentários
          <span><?= count($comentarios) ?></span>
        </div>

        <?php if ($usuario_logado): ?>
          <form method="POST" class="form-comentario">
            <input type="hidden" name="acao" value="comentar">
            <img src="<?= htmlspecialchars(img_url($_SESSION['usuario_avatar'] ?? '', '../img/avatar-default.png')) ?>"
                 alt="Você" class="avatar-mini"
                 <?= img_onerror('../img/avatar-default.png') ?>>
            <textarea name="comentario"
                      placeholder="Escreva um comentário..."
                      required minlength="2"
                      onkeydown="if(event.ctrlKey&&event.key==='Enter')this.form.submit()"></textarea>
            <button type="submit" class="btn-enviar-comentario">
              <i class="bi bi-send-fill"></i>
            </button>
          </form>
        <?php else: ?>
          <div class="aviso-login-comentar">
            <a href="../auth/login.php">Faça login</a> para deixar um comentário
          </div>
        <?php endif; ?>

        <?php if (empty($comentarios)): ?>
          <div class="sem-comentarios">
            <i class="bi bi-chat-square"></i>
            Seja o primeiro a comentar!
          </div>
        <?php else: ?>
          <?php foreach ($comentarios as $c): ?>
            <div class="comentario-item">
              <img src="<?= htmlspecialchars(img_url($c['avatar'] ?? '', '../img/avatar-default.png')) ?>"
                   alt="<?= htmlspecialchars($c['nome']) ?>"
                   class="avatar-mini"
                   <?= img_onerror('../img/avatar-default.png') ?>>
              <div class="comentario-corpo">
                <span class="comentario-autor"><?= htmlspecialchars($c['nome']) ?></span>
                <span class="comentario-tempo tempo-relativo" data-publicacao="<?= $c['data'] ?>">
                  <?= tempo_decorrido($c['data']) ?>
                </span>
                <p class="comentario-texto"><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

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
        $mais_lidas = $pdo->query("
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

      </div>
    </div>

  </div>
</div>

<template hx-get="../footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
<script src="../tempo-relativo.js"></script>
</body>
</html>