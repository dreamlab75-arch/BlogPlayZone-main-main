<?php

require "noticias-index/noticias-model.php";

$destaques = buscar_destaques_semana(3);
$slides = $destaques->fetchAll(PDO::FETCH_ASSOC);

if (empty($slides)) {
    $slides = buscar_noticias_recentes(3)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<section id="home" class="carousel-section">
  <div class="container">
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">

      <div class="carousel-indicators">
        <?php foreach ($slides as $i => $slide): ?>
          <button type="button"
            data-bs-target="#mainCarousel"
            data-bs-slide-to="<?= $i ?>"
            <?= $i === 0 ? 'class="active"' : '' ?>>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="carousel-inner">
        <?php foreach ($slides as $i => $slide): ?>
          <a href="noticias-index/noticia.php?id=<?= $slide['id'] ?>"
             class="carousel-item <?= $i === 0 ? 'active' : '' ?>"
             style="background-image: url('<?= htmlspecialchars(normalizar_imagem_noticia($slide['imagem'], '')) ?>');">
            <div class="carousel-caption">
              <span class="badge <?= categoria_para_badge($slide['categoria']) ?> mb-2">
                <?= strtoupper($slide['categoria']) ?>
              </span>
              <h3><?= htmlspecialchars($slide['titulo']) ?></h3>
              <p><?= htmlspecialchars(mb_substr($slide['resumo'] ?: $slide['conteudo'], 0, 120)) ?>...</p>
              <span class="btn-ler-post mt-2" style="display:inline-block;">
                Ler notícia <i class="bi bi-arrow-right ms-1"></i>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev"
              onclick="event.preventDefault();event.stopPropagation();">
        <div class="carousel-btn"><i class="bi bi-chevron-left"></i></div>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next"
              onclick="event.preventDefault();event.stopPropagation();">
        <div class="carousel-btn"><i class="bi bi-chevron-right"></i></div>
      </button>

    </div>
  </div>
</section>