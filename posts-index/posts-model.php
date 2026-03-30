<?php
// ============================================================
// MODEL — responsável pela conexão e consultas ao banco
// ============================================================

function conectar() {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../banco.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Retorna todos os posts com autor, avatar, tags e contagens
function buscar_posts() {
    $pdo = conectar();
    return $pdo->query("
        SELECT
            p.id,
            p.titulo,
            p.conteudo,
            p.imagem,
            p.data_publicacao,
            u.nome                          AS autor,
            u.avatar                        AS avatar,
            GROUP_CONCAT(t.nome, ',')       AS tags,
            COUNT(DISTINCT cp.usuario_id)   AS curtidas,
            COUNT(DISTINCT co.id)           AS comentarios,
            COUNT(DISTINCT vp.usuario_id)   AS visualizacoes
        FROM posts p
        JOIN usuarios u         ON u.id = p.usuario_id
        LEFT JOIN post_tag pt   ON pt.post_id = p.id
        LEFT JOIN tags t        ON t.id = pt.tag_id
        LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
        LEFT JOIN Comentarios_posts co ON co.post_id = p.id
        LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
        GROUP BY p.id
        ORDER BY p.data_publicacao DESC
    ");
}

// Retorna apenas os N primeiros posts (para o índice)
function buscar_posts_em_alta($limite = 3) {
    $pdo = conectar();
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.titulo,
            p.conteudo,
            p.imagem,
            p.data_publicacao,
            u.nome                          AS autor,
            u.avatar                        AS avatar,
            GROUP_CONCAT(t.nome, ',')       AS tags,
            COUNT(DISTINCT cp.usuario_id)   AS curtidas,
            COUNT(DISTINCT co.id)           AS comentarios,
            COUNT(DISTINCT vp.usuario_id)   AS visualizacoes
        FROM posts p
        JOIN usuarios u         ON u.id = p.usuario_id
        LEFT JOIN post_tag pt   ON pt.post_id = p.id
        LEFT JOIN tags t        ON t.id = pt.tag_id
        LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
        LEFT JOIN Comentarios_posts co ON co.post_id = p.id
        LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
        GROUP BY p.id
        ORDER BY p.data_publicacao DESC
        LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

// Retorna um post pelo ID
function buscar_post_por_id($id) {
    $pdo = conectar();
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.titulo,
            p.conteudo,
            p.imagem,
            p.data_publicacao,
            u.nome                          AS autor,
            u.avatar                        AS avatar,
            GROUP_CONCAT(t.nome, ',')       AS tags,
            COUNT(DISTINCT cp.usuario_id)   AS curtidas,
            COUNT(DISTINCT co.id)           AS comentarios,
            COUNT(DISTINCT vp.usuario_id)   AS visualizacoes
        FROM posts p
        JOIN usuarios u         ON u.id = p.usuario_id
        LEFT JOIN post_tag pt   ON pt.post_id = p.id
        LEFT JOIN tags t        ON t.id = pt.tag_id
        LEFT JOIN Curte_post cp ON cp.post_id = p.id AND cp.ativo = 1
        LEFT JOIN Comentarios_posts co ON co.post_id = p.id
        LEFT JOIN Visualiza_post vp ON vp.post_id = p.id
        WHERE p.id = :id
        GROUP BY p.id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>