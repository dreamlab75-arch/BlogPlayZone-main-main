<?php
require "noticias-model.php";
 
$result_set_noticias = buscar_noticias_recentes(5);
?>
 
<?php while ($linha = $result_set_noticias->fetch(PDO::FETCH_ASSOC)): ?>
 
<div class="noticia-card">
    <span class="news-badge <?= $linha["badge_classe"] ?>"><?= $linha["badge_texto"] ?></span>
    <div class="news-item-title"><?= $linha["titulo"] ?></div>
    <div class="news-item-footer">
        <!-- data-publicacao permite o JS recalcular sem recarregar a página -->
        <div class="news-item-date">
            <i class="bi bi-clock"></i>
            <span class="tempo-relativo" data-publicacao="<?= $linha["data_publicacao"] ?>">
                <?= tempo_decorrido($linha["data_publicacao"]) ?>
            </span>
        </div>
        <a class="btn-ler-noticia" href="noticia.php?id=<?= $linha["id"] ?>">Ver mais</a>
    </div>
</div>
 
<?php endwhile; ?>