<?php
// ============================================================
// MODEL — conexão e consultas à tabela de noticias
// ============================================================
 
function conectar_noticias() {
    $pdo = new PDO("sqlite:banco.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
 
// Retorna todas as notícias
function buscar_noticias() {
    $pdo = conectar_noticias();
    return $pdo->query("SELECT * FROM noticias");
}
 
// Retorna apenas as N primeiras (para o sidebar)
function buscar_noticias_recentes($limite = 5) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("SELECT * FROM noticias LIMIT :limite");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}
 
// Retorna uma notícia pelo ID
function buscar_noticia_por_id($id) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>