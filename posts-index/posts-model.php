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
            GROUP_CONCAT(DISTINCT t.nome)   AS tags,
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
            GROUP_CONCAT(DISTINCT t.nome)   AS tags,
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
            GROUP_CONCAT(DISTINCT t.nome)   AS tags,
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

// ── NOVO: paginação com filtros ──────────────────────────────────────────────
function buscar_posts_paginados($pagina = 1, $limite = 10, $ordem = 'recentes', $busca = '', $tags = []) {
    $pdo    = conectar();
    $offset = ($pagina - 1) * $limite;

    $orderBy = "p.data_publicacao DESC";
    if ($ordem === 'antigos') $orderBy = "p.data_publicacao ASC";
    if ($ordem === 'vistos')  $orderBy = "visualizacoes DESC";

    [$whereSQL, $params, $tagValues] = _montar_where($busca, $tags);

    $sql = "
        SELECT
            p.id,
            p.titulo,
            p.conteudo,
            p.imagem,
            p.data_publicacao,
            u.nome                          AS autor,
            u.avatar                        AS avatar,
            GROUP_CONCAT(DISTINCT t.nome)   AS tags,
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
        $whereSQL
        GROUP BY p.id
        ORDER BY $orderBy
        LIMIT :limite OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    foreach ($tagValues as $i => $v) $stmt->bindValue($i + 1, $v); // posicionais para tags
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

// ── NOVO: conta total de posts (para paginação) ──────────────────────────────
function contar_posts($busca = '', $tags = []) {
    $pdo = conectar();

    [$whereSQL, $params, $tagValues] = _montar_where($busca, $tags);

    // Subconsulta para contar posts distintos com os filtros
    $sql = "
        SELECT COUNT(DISTINCT p.id) AS total
        FROM posts p
        JOIN usuarios u         ON u.id = p.usuario_id
        LEFT JOIN post_tag pt   ON pt.post_id = p.id
        LEFT JOIN tags t        ON t.id = pt.tag_id
        $whereSQL
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    foreach ($tagValues as $i => $v) $stmt->bindValue($i + 1, $v);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

// ── Helper interno: monta WHERE reutilizável ────────────────────────────────
function _montar_where($busca, $tags) {
    $where      = [];
    $params     = [];
    $tagValues  = [];

    if ($busca) {
        $where[]         = "p.titulo LIKE :busca";
        $params[':busca'] = "%$busca%";
    }

    if (!empty($tags)) {
        $placeholders = implode(',', array_fill(0, count($tags), '?'));
        $where[]      = "t.nome IN ($placeholders)";
        $tagValues    = array_values($tags);
    }

    $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";
    return [$whereSQL, $params, $tagValues];
}

function buscar_tags() {
    $pdo = conectar();
    return $pdo->query("SELECT id, nome FROM tags ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
}

// ── Tempo decorrido (posts) ─────────────────────────────────────────────────
function tempo_decorrido_posts($data_publicacao) {
    $agora     = new DateTime('now',  new DateTimeZone('UTC'));
    $publicado = new DateTime($data_publicacao, new DateTimeZone('UTC'));
    $diff      = $agora->diff($publicado);
    $minutos   = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;

    if ($diff->days >= 365) {
        $anos = floor($diff->days / 365);
        return 'Há ' . $anos . ' ano' . ($anos > 1 ? 's' : '');
    }
    if ($diff->days >= 30) {
        return 'Há ' . $diff->m . ($diff->m > 1 ? ' meses' : ' mês');
    }
    if ($diff->days >= 7) {
        $semanas = floor($diff->days / 7);
        return 'Há ' . $semanas . ' semana' . ($semanas > 1 ? 's' : '');
    }
    if ($diff->days >= 1) {
        return 'Há ' . $diff->days . ' dia' . ($diff->days > 1 ? 's' : '');
    }
    if ($minutos >= 60) {
        return 'Há ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
    }
    if ($minutos >= 1) {
        return 'Há ' . $minutos . ' minuto' . ($minutos > 1 ? 's' : '');
    }
    return 'Agora mesmo';
}