<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlayZone - Blog de Videogames</title>
  <link href="style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"
    integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz"
    crossorigin="anonymous"></script>
</head>
<body>

  <template hx-get="header.php" hx-target="#header" hx-trigger="load"></template>
  <div id="header"></div>

  <template hx-get="carrosel-noticias.php" hx-target="#carrosel-noticias" hx-trigger="load"></template>
  <div id="carrosel-noticias"></div>

  <div class="container posts-container">
    <div class="row g-4">

      <div class="col-lg-8">
      <?php
require "posts-index/posts-model.php";

$result_set_posts = buscar_posts_em_alta(3);
?>

<?php while ($linha_posts = $result_set_posts->fetch(PDO::FETCH_ASSOC)): ?>

    <?php 
    $tags = $linha_posts["tags"] ? explode(",", $linha_posts["tags"]) : [];
    include "posts-index/post-card.php"; 
    ?>

<?php endwhile; ?>

<div class="text-center mt-4">
    <a href="posts-index/posts-view.php" class="btn-ver-mais">
        Ver todos os posts <i class="bi bi-arrow-right"></i>
    </a>
</div>
      </div>

      <div class="col-lg-4">
        <div class="news-sidebar" id="noticias">
          <h4><i class="bi bi-newspaper me-2"></i>Últimas Notícias</h4>
          <?php require "noticias-index/sidebar-noticias.php"; ?>
          <div class="text-center mt-4">
            <a href="noticias-index/noticias-view.php" class="btn-ver-mais">
              Ver todas as notícias <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <template hx-get="sobre.html" hx-target="#sobre" hx-trigger="load"></template>
  <div id="sobre"></div>

  <template hx-get="footer.html" hx-target="#footer" hx-trigger="load"></template>
  <div id="footer"></div>

  <script src="tempo-relativo.js"></script>
  
</body>
</html>