<?php
session_start();
require_once __DIR__ . '/posts-model.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: posts-view.php');
    exit;
}

$post = buscar_post_por_id($id);
if (!$post) {
    header('Location: posts-view.php');
    exit;
}

// ── Registra visualização (1 por usuário por post) ─────────────────────────
if (isset($_SESSION['usuario_id'])) {
    try {
        $pdo = conectar();
        $pdo->prepare("
            INSERT OR IGNORE INTO Visualiza_post (usuario_id, post_id)
            VALUES (:uid, :pid)
        ")->execute([':uid' => $_SESSION['usuario_id'], ':pid' => $id]);
    } catch (Exception $e) { /* silencia */ }
}

// ── Processa ação de curtir / descurtir (POST) ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    $uid = (int)$_SESSION['usuario_id'];
    $pdo = conectar();

    if ($_POST['acao'] === 'curtir') {
        // Upsert: se já existe, alterna ativo; senão insere
        $existe = $pdo->prepare("SELECT ativo FROM Curte_post WHERE usuario_id=:u AND post_id=:p");
        $existe->execute([':u' => $uid, ':p' => $id]);
        $row = $existe->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $novoAtivo = $row['ativo'] ? 0 : 1;
            $pdo->prepare("UPDATE Curte_post SET ativo=:a WHERE usuario_id=:u AND post_id=:p")
                ->execute([':a' => $novoAtivo, ':u' => $uid, ':p' => $id]);
        } else {
            $pdo->prepare("INSERT INTO Curte_post (usuario_id, post_id, ativo) VALUES (:u,:p,1)")
                ->execute([':u' => $uid, ':p' => $id]);
        }
    }

    if ($_POST['acao'] === 'comentar') {
        $comentario = trim($_POST['comentario'] ?? '');
        if (strlen($comentario) >= 2) {
            $pdo->prepare("
                INSERT INTO Comentarios_posts (comentario, post_id, usuario_id)
                VALUES (:c, :p, :u)
            ")->execute([':c' => $comentario, ':p' => $id, ':u' => $uid]);
        }
    }

    header("Location: post.php?id=$id", true, 303);
    exit;
}

$post = buscar_post_por_id($id);
$tags = $post['tags'] ? explode(',', $post['tags']) : [];

$pdo  = conectar();
$stmt = $pdo->prepare("
    SELECT c.comentario, c.data, u.nome, u.avatar
    FROM Comentarios_posts c
    JOIN usuarios u ON u.id = c.usuario_id
    WHERE c.post_id = :p
    ORDER BY c.data DESC
");
$stmt->execute([':p' => $id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuario_curtiu = false;
if (isset($_SESSION['usuario_id'])) {
    $ck = $pdo->prepare("SELECT ativo FROM Curte_post WHERE usuario_id=:u AND post_id=:p");
    $ck->execute([':u' => $_SESSION['usuario_id'], ':p' => $id]);
    $ckRow = $ck->fetch(PDO::FETCH_ASSOC);
    $usuario_curtiu = $ckRow && $ckRow['ativo'];
}

$usuario_logado = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($post['titulo']) ?> — PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php
$base = '../';
include __DIR__ . '/../header.php';
?>

<div class="container">
  <div class="post-single-wrap">

    <!-- VOLTAR -->
    <?php
    $voltar_url = 'posts-view.php';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $ref = $_SERVER['HTTP_REFERER'];
        if (strpos($ref, 'post.php') === false && (strpos($ref, 'posts') !== false || strpos($ref, 'index.php') !== false || strpos($ref, $_SERVER['HTTP_HOST']) !== false)) {
            $voltar_url = htmlspecialchars($ref);
        }
    }
    ?>
    <a href="<?= $voltar_url ?>" class="btn-voltar">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <!-- CARD PRINCIPAL -->
    <article class="post-single-card">

      <!-- Tags -->
      <?php if (!empty($tags)): ?>
      <div class="post-single-tags">
        <?php foreach ($tags as $tag): ?>
          <span class="post-single-tag"><?= htmlspecialchars(trim($tag)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Título -->
      <h1 class="post-single-titulo"><?= htmlspecialchars($post['titulo']) ?></h1>

      <!-- Autor + meta -->
      <div class="post-single-autor">
        <img src="<?= htmlspecialchars($post['avatar'] ?? '../img/avatar-default.png') ?>"
             alt="<?= htmlspecialchars($post['autor']) ?>"
             class="post-single-avatar"
             onerror="this.src='../img/avatar-default.png'">
        <div class="post-single-autor-info">
          <h6><?= htmlspecialchars($post['autor']) ?></h6>
          <small>
            <span>
              <i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>">
                <?= tempo_decorrido_posts($post['data_publicacao']) ?>
              </span>
            </span>
            <span><i class="bi bi-eye"></i> <?= $post['visualizacoes'] ?> visualizações</span>          </small>
        </div>
      </div>

      <!-- Imagem destaque (se houver) -->
      <?php if (!empty($post['imagem'])): ?>
        <img src="<?= htmlspecialchars($post['imagem']) ?>"
             alt="Imagem do post"
             class="post-single-imagem"
             onerror="this.style.display='none'">
      <?php endif; ?>

      <!-- Conteúdo -->
      <div class="post-single-body">
        <?php
        // Renderiza parágrafos respeitando quebras de linha do banco
        $paragrafos = explode("\n", trim($post['conteudo']));
        foreach ($paragrafos as $p) {
            $p = trim($p);
            if ($p !== '') {
                echo '<p>' . htmlspecialchars($p) . '</p>';
            }
        }
        ?>
      </div>

      <!-- AÇÕES: curtir + stats -->
      <div class="post-acoes">
        <?php if ($usuario_logado): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="acao" value="curtir">
            <button type="submit" class="btn-curtir <?= $usuario_curtiu ? 'curtido' : '' ?>">
              <i class="bi <?= $usuario_curtiu ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
              <?= $usuario_curtiu ? 'Curtido' : 'Curtir' ?>
              <strong><?= $post['curtidas'] ?></strong>
            </button>
          </form>
        <?php else: ?>
          <span class="btn-curtir" style="cursor:default;opacity:.7;">
            <i class="bi bi-heart"></i> <?= $post['curtidas'] ?> curtidas
          </span>
        <?php endif; ?>

        <span class="stat-pill">
          <i class="bi bi-chat-dots-fill"></i>
          <?= $post['comentarios'] ?> comentário<?= $post['comentarios'] != 1 ? 's' : '' ?>
        </span>

        <?php if (!$usuario_logado): ?>
          <span class="acoes-login-aviso">
            <a href="../auth/login.php">Faça login</a> para curtir e comentar
          </span>
        <?php endif; ?>
      </div>
    </article>

    <section class="comentarios-section">
      <div class="comentarios-titulo">
        <i class="bi bi-chat-square-dots-fill" style="color:#611DF2;"></i>
        Comentários
        <span><?= count($comentarios) ?></span>
      </div>

      <?php if ($usuario_logado): ?>
        <form method="POST" class="form-comentario">
          <input type="hidden" name="acao" value="comentar">
          <img src="<?= htmlspecialchars($_SESSION['usuario_avatar'] ?? '../img/avatar-default.png') ?>"
               alt="Você" class="avatar-mini"
               onerror="this.src='../img/avatar-default.png'">
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
            <img src="<?= htmlspecialchars($c['avatar'] ?? '../img/avatar-default.png') ?>"
                 alt="<?= htmlspecialchars($c['nome']) ?>"
                 class="avatar-mini"
                 onerror="this.src='../img/avatar-default.png'">
            <div class="comentario-corpo">
              <span class="comentario-autor"><?= htmlspecialchars($c['nome']) ?></span>
              <span class="comentario-tempo tempo-relativo" data-publicacao="<?= $c['data'] ?>">
                <?= tempo_decorrido_posts($c['data']) ?>
              </span>
              <p class="comentario-texto"><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

  </div>
</div>

<template hx-get="footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
<script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"
  integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz"
  crossorigin="anonymous"></script>
<script src="../tempo-relativo.js"></script>
</body>
</html>