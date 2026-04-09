<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$base = '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quem Somos — PlayZone</title>
  <link href="style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"
    integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz"
    crossorigin="anonymous"></script>
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<!-- HERO -->
<div class="sobre-section" style="margin: top 8px;">
  <div class="container text-center py-2">
    <h1 class="section-title--light fw-bold mb-2" style="color:#fff;font-size:2.4rem;">
      <i class="bi bi-controller me-2"></i>Quem Somos
    </h1>
    <p style="color:rgba(255,255,255,.8);font-size:1.05rem;max-width:560px;margin:0 auto;">
      Conheça a história, missão e as pessoas por trás do PlayZone.
    </p>
  </div>
</div>

<div class="container" style="max-width:860px;padding-top:48px;padding-bottom:80px;">

  <a href="index.php" class="btn-voltar">
    <i class="bi bi-arrow-left"></i> Voltar ao início
  </a>

  <!-- O QUE É -->
  <div class="noticia-artigo mb-4">

    <div class="d-flex align-items-center gap-3 mb-3">
      <span style="font-size:2.2rem;">🎮</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">O que é o PlayZone</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p class="noticia-lead">
        O PlayZone nasceu da vontade de criar um espaço genuíno para quem vive e respira videogames.
        Não somos uma grande redação corporativa — somos gamers que decidimos colocar nossas opiniões,
        análises e descobertas em um só lugar.
      </p>
      <p>
        Aqui você não encontra apenas notícias frias — encontra contexto, debate e a perspectiva
        de quem realmente joga. Cada post é escrito por alguém que viveu aquela experiência em
        primeira mão.
      </p>
    </div>
  </div>

  <!-- MISSÃO E VALORES -->
  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="sobre-card">
        <div class="sobre-icon">🎯</div>
        <h5>Nossa Missão</h5>
        <p>Informar e entreter a comunidade gamer com conteúdo honesto, aprofundado e acessível.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sobre-card">
        <div class="sobre-icon">🤝</div>
        <h5>Nossa Comunidade</h5>
        <p>Qualquer pessoa pode criar uma conta e publicar posts. Acreditamos que as melhores opiniões vêm de quem joga.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sobre-card">
        <div class="sobre-icon">📰</div>
        <h5>Nosso Jornalismo</h5>
        <p>As notícias são escritas por jornalistas verificados. Buscamos sempre confirmar informações antes de publicar, diferenciando rumores de fatos.</p>
      </div>
    </div>
  </div>

  <!-- O QUE VOCÊ ENCONTRA -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span style="font-size:2.2rem;">📰</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">O que você encontra aqui</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p class="noticia-lead">
        O PlayZone é dividido em duas grandes áreas: <strong>Posts</strong> e <strong>Notícias</strong>.
      </p>
      <p>
        <strong style="color:#611DF2;">Posts</strong> são escritos por qualquer membro da comunidade.
        Reviews pessoais, dicas de gameplay, listas de favoritos, opiniões controversas — tudo é bem-vindo,
        desde que respeite as regras da plataforma.
      </p>
      <p>
        <strong style="color:#611DF2;">Notícias</strong> são produzidas exclusivamente por
        jornalistas credenciados. Cobrindo lançamentos, eventos, rumores verificados, análises de
        mercado e tudo mais que movimenta a indústria dos games.
      </p>
      <p>
        Cobrimos RPGs, FPS, aventura, indie, hardware, e-sports, mobile e muito mais.
        Se existe no universo gamer, você vai encontrar aqui.
      </p>
    </div>
  </div>

  <!-- COMO PARTICIPAR -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span style="font-size:2.2rem;">✍️</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Como participar</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p class="noticia-lead">
        Participar do PlayZone é simples e gratuito.
      </p>
      <p>
        Basta criar uma conta, verificar seu e-mail e você já pode publicar seus próprios posts,
        comentar nas notícias e interagir com a comunidade curtindo o conteúdo que mais gostou.
      </p>
      <p>
        Quer fazer parte da equipe de jornalistas? Entre em contato pelo e-mail
        <a href="mailto:dream.lab75@gmail.com" style="color:#611DF2;font-weight:600;">dream.lab75@gmail.com</a>
        com uma apresentação sua e exemplos de textos anteriores. Avaliamos todos os candidatos.
      </p>
    </div>
    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="auth/cadastro.php" class="btn-modal-publicar" style="text-decoration:none;">
        <i class="bi bi-person-plus-fill me-1"></i> Criar minha conta
      </a>
      <a href="mailto:dream.lab75@gmail.com" class="btn-modal-cancelar" style="text-decoration:none;display:inline-flex;align-items:center;">
        <i class="bi bi-envelope me-1"></i> Quero ser jornalista
      </a>
    </div>
  </div>

  <!-- CONTATO -->
  <div class="noticia-artigo">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span style="font-size:2.2rem;">📬</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Fale conosco</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p class="noticia-lead">Tem alguma dúvida, sugestão ou quer nos mandar um alô? Adoramos ouvir a comunidade.</p>
    </div>
    <div class="d-flex flex-wrap gap-4 mt-2">
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="bi bi-envelope-fill" style="color:#611DF2;font-size:1.3rem;"></i>
        <a href="mailto:dream.lab75@gmail.com" style="color:#611DF2;font-weight:600;text-decoration:none;">
          dream.lab75@gmail.com
        </a>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="bi bi-telephone-fill" style="color:#611DF2;font-size:1.3rem;"></i>
        <span style="color:#555;">+55 51 94598-5489</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="bi bi-instagram" style="color:#611DF2;font-size:1.3rem;"></i>
        <a href="https://www.instagram.com/senactech/" target="_blank" style="color:#611DF2;font-weight:600;text-decoration:none;">
          @senactech
        </a>
      </div>
    </div>
  </div>

</div>

<template hx-get="footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
</body>
</html>