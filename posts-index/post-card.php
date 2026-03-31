<div class="post-card">

    <div class="post-author">
        <div class="post-avatar">
            <img src="<?= htmlspecialchars($linha_posts["avatar"]) ?>" alt="<?= htmlspecialchars($linha_posts["autor"]) ?>">
        </div> 
        <div class="post-author-info">
            <h6><?= htmlspecialchars($linha_posts["autor"]) ?></h6>
            <small>
                <i class="bi bi-clock me-1"></i>
                <span class="tempo-relativo" data-publicacao="<?= $linha_posts["data_publicacao"] ?>">
                    <?= tempo_decorrido_posts($linha_posts["data_publicacao"]) ?>
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
        <a href="posts-index/post.php?id=<?= $linha_posts["id"] ?>">
            <?= htmlspecialchars($linha_posts["titulo"]) ?>
        </a>
    </h4>

    <p class="post-excerpt">
        <?= htmlspecialchars(mb_substr($linha_posts["conteudo"], 0, 150)) ?>...
    </p>

    <div class="post-footer">
        <div class="post-stats">
            <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $linha_posts["curtidas"] ?></span>
            <span><i class="bi bi-chat-fill"></i> <?= $linha_posts["comentarios"] ?></span>
            <span><i class="bi bi-eye-fill"></i> <?= $linha_posts["visualizacoes"] ?></span>
        </div>

        <a class="btn-ler-post" href="posts-index/post.php?id=<?= $linha_posts["id"] ?>">
            Ler post <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

</div>