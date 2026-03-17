<?php
// ============================================================
// Carrossel — fragmento PHP puro, carregado via HTMX
// Exibe as 3 notícias mais vistas da última semana
// ============================================================
 
require "noticias-index/noticias-model.php";
 
$destaques = buscar_destaques_semana(3);
$slides = $destaques->fetchAll(PDO::FETCH_ASSOC);
?>
 
<section id="home" class="carousel-section">
  <div class="container">
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
 
      <!-- INDICADORES -->
      <div class="carousel-indicators">
        <?php foreach ($slides as $i => $slide): ?>
          <button type="button"
            data-bs-target="#mainCarousel"
            data-bs-slide-to="<?= $i ?>"
            <?= $i === 0 ? 'class="active"' : '' ?>>
          </button>
        <?php endforeach; ?>
      </div>
 
      <!-- SLIDES -->
      <div class="carousel-inner">
        <?php foreach ($slides as $i => $slide): ?>
          <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>"
               style="background-image: url('<?= $slide['imagem'] ?>');">
            <div class="carousel-caption">
              <span class="badge <?= $slide['badge_classe'] ?> mb-2">
                <?= $slide['badge_texto'] ?>
              </span>
              <h3><?= $slide['titulo'] ?></h3>
              <p><?= mb_substr($slide['conteudo'], 0, 120) ?>...</p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
 
      <!-- BOTÕES -->
      <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
        <div class="carousel-btn"><i class="bi bi-chevron-left"></i></div>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
        <div class="carousel-btn"><i class="bi bi-chevron-right"></i></div>
      </button>
 
    </div>
  </div>
</section>