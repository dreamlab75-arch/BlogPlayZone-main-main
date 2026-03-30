<?php
// ============================================================
// MODEL — conexão e consultas à tabela de noticias
// ============================================================

function conectar_noticias() {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../banco.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function buscar_noticias() {
    $pdo = conectar_noticias();
    return $pdo->query("
        SELECT
            n.*,
            COUNT(DISTINCT vn.usuario_id) AS visualizacoes
        FROM noticias n
        LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
        GROUP BY n.id
        ORDER BY n.data_publicacao DESC
    ");
}

function buscar_noticias_recentes($limite = 5) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT
            n.*,
            COUNT(DISTINCT vn.usuario_id) AS visualizacoes
        FROM noticias n
        LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
        GROUP BY n.id
        ORDER BY n.data_publicacao DESC
        LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function buscar_destaques_semana($limite = 3) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT
            n.*,
            COUNT(DISTINCT vn.usuario_id) AS visualizacoes
        FROM noticias n
        LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
        WHERE n.data_publicacao >= datetime('now', '-7 days')
        GROUP BY n.id
        ORDER BY visualizacoes DESC
        LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

function buscar_noticia_por_id($id) {
    $pdo = conectar_noticias();
    $stmt = $pdo->prepare("
        SELECT
            n.*,
            COUNT(DISTINCT vn.usuario_id) AS visualizacoes
        FROM noticias n
        LEFT JOIN Visualiza_noticia vn ON vn.noticia_id = n.id
        WHERE n.id = :id
        GROUP BY n.id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tempo_decorrido($data_publicacao) {
    $agora     = new DateTime('now', new DateTimeZone('UTC'));
    $publicado = new DateTime($data_publicacao, new DateTimeZone('UTC'));
    $diff      = $agora->diff($publicado);
    $minutos   = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;

    if ($diff->days >= 365) return 'Há ' . floor($diff->days / 365) . 'a';
    if ($diff->days >= 30)  return 'Há ' . $diff->m . ($diff->m > 1 ? ' meses' : ' mês');
    if ($diff->days >= 1)   return 'Há ' . $diff->days . 'd';
    if ($minutos >= 60)     return 'Há ' . $diff->h . 'h';
    if ($minutos >= 1)      return 'Há ' . $minutos . 'min';
    return 'Agora mesmo';
}

function categoria_para_badge($categoria) {
    $mapa = [
        'lançamento' => 'bg-primary text-white',
        'rumor'      => 'bg-warning text-dark',
        'análise'    => 'bg-info text-white',
        'urgente'    => 'bg-danger text-white',
        'review'     => 'bg-success text-white',
        'prévia'     => 'bg-purple text-white',
        'atualização'=> 'bg-secondary text-white',
        'evento'     => 'bg-dark text-white',
        'hardware'   => 'bg-warning text-dark',
        'negócios'   => 'bg-danger text-white',
        'curiosidade'=> 'bg-info text-white',
        'lista'      => 'bg-primary text-white',
    ];
    return $mapa[mb_strtolower(trim($categoria))] ?? 'bg-secondary text-white';
}
?>