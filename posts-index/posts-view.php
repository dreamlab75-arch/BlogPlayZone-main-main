<?php
require "posts-model.php";

$result_set_posts = buscar_posts_em_alta(3);
?>

<?php while ($linha_posts = $result_set_posts->fetch(PDO::FETCH_ASSOC)): ?>

    <?php $tags = explode(",", $linha_posts["tags"]); ?>

    <div class="post-card">

        <div class="post-author">
            <div class="post-avatar"><?= $linha_posts["avatar"] ?></div>
            <div class="post-author-info">
                <h6><?= $linha_posts["autor"] ?></h6>
            </div>
        </div>

        <div class="post-tags">
            <?php foreach ($tags as $tag): ?>
                <span class="post-tag"><?= trim($tag) ?></span>
            <?php endforeach; ?>
        </div>

        <h4 class="post-title"><?= $linha_posts["titulo"] ?></h4>
        <p class="post-excerpt"><?= mb_substr($linha_posts["conteudo"], 0, 150) ?>...</p>

        <div class="post-footer">
            <div class="post-stats">
                <span class="curtidas">❤️ <?= $linha_posts["curtidas"] ?> curtidas</span>
                <span class="comentarios">💬 <?= $linha_posts["comentarios"] ?> comentários</span>
                <span class="visualizacoes">👁️ <?= $linha_posts["visualizacoes"] ?> visualizações</span>
            </div>
            <a class="btn-ler-post" href="post.php?id=<?= $linha_posts["id"] ?>">Ler post →</a>
        </div>

    </div>

<?php endwhile; ?>

<div class="text-center mt-4">
    <a href="posts.html" class="btn-ver-mais">Ver todos os posts <i class="bi bi-arrow-right"></i></a>
</div>