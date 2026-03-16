<?php
// ============================================================
// MODEL — responsável pela conexão e consultas ao banco
// ============================================================
 
function conectar() {
    $pdo = new PDO("sqlite:banco.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
 
// Retorna todos os posts
function buscar_posts() {
    $pdo = conectar();
    $result_set_posts = $pdo->query("SELECT * FROM posts");
    return $result_set_posts;
}
 
// Retorna apenas os N primeiros posts (para o índice)
function buscar_posts_em_alta($limite = 3) {
    $pdo = conectar();
    $result_set_posts = $pdo->prepare("SELECT * FROM posts LIMIT :limite");
    $result_set_posts->bindValue(':limite', $limite, PDO::PARAM_INT);
    $result_set_posts->execute();
    return $result_set_posts;
}
 
// Retorna um post pelo ID
function buscar_post_por_id($id) {
    $pdo = conectar();
    $result_set_posts = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $result_set_posts->bindValue(':id', $id, PDO::PARAM_INT);
    $result_set_posts->execute();
    return $result_set_posts->fetch(PDO::FETCH_ASSOC);
}
?>