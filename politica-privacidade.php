<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$base = '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Política de Privacidade — PlayZone</title>
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
<div class="sobre-section" style="margin-top:72px;">
  <div class="container text-center py-2">
    <h1 class="section-title--light fw-bold mb-2" style="color:#fff;font-size:2.4rem;">
      <i class="bi bi-shield-check me-2"></i>Política de Privacidade
    </h1>
    <p style="color:rgba(255,255,255,.8);font-size:1.05rem;max-width:560px;margin:0 auto;">
      Última atualização: Janeiro de 2025
    </p>
  </div>
</div>

<div class="container" style="max-width:860px;padding-top:48px;padding-bottom:80px;">

  <a href="index.php" class="btn-voltar">
    <i class="bi bi-arrow-left"></i> Voltar ao início
  </a>

  <!-- INTRO -->
  <div class="noticia-artigo mb-4">
    <div class="noticia-conteudo">
      <p class="noticia-lead">
        O PlayZone respeita a sua privacidade. Esta política explica de forma clara quais dados coletamos,
        como os utilizamos e quais são os seus direitos como usuário da plataforma.
      </p>
      <p>
        Ao criar uma conta ou utilizar nosso site, você concorda com as práticas descritas neste documento.
        Recomendamos a leitura completa antes de se cadastrar.
      </p>
    </div>
  </div>

  <!-- SEÇÃO 1 -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;">1</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Quais dados coletamos</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p><strong style="color:#611DF2;">Dados de cadastro:</strong> ao criar sua conta, coletamos seu nome, endereço de e-mail e senha (armazenada de forma criptografada). Também pode incluir uma URL de avatar e uma bio, ambos opcionais.</p>
      <p><strong style="color:#611DF2;">Dados de uso:</strong> registramos quais posts e notícias você visualizou, curtiu ou comentou. Esses dados são usados para gerar estatísticas de engajamento e personalizar destaques da plataforma.</p>
      <p><strong style="color:#611DF2;">Conteúdo publicado:</strong> posts e comentários enviados por você ficam armazenados em nosso banco de dados e são públicos para todos os visitantes do blog.</p>
      <p><strong style="color:#611DF2;">Dados técnicos:</strong> como qualquer servidor web, nossos logs podem registrar o endereço IP de acesso e o user-agent do navegador para fins de segurança e diagnóstico de erros.</p>
    </div>
  </div>

  <!-- SEÇÃO 2 -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;">2</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Como usamos seus dados</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p>Utilizamos seus dados exclusivamente para:</p>
      <p><strong style="color:#611DF2;">Operar a plataforma</strong> — manter sua conta ativa, exibir seu nome e avatar nas publicações e garantir que suas curtidas e comentários sejam salvos corretamente.</p>
      <p><strong style="color:#611DF2;">Gerar estatísticas</strong> — contagens de visualizações, curtidas e comentários são agregadas e exibidas publicamente nos posts e notícias.</p>
      <p><strong style="color:#611DF2;">Segurança</strong> — detectar acessos não autorizados, prevenir spam e proteger a integridade da plataforma.</p>
      <p>Não vendemos, alugamos ou compartilhamos seus dados pessoais com terceiros para fins comerciais ou publicitários.</p>
    </div>
  </div>

  <!-- SEÇÃO 3 -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;">3</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Armazenamento e segurança</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p>Seus dados são armazenados em banco de dados local, protegido por controle de acesso ao servidor. Senhas são armazenadas como hash SHA-256 — nunca em texto puro.</p>
      <p>Apesar de adotarmos medidas razoáveis de segurança, nenhum sistema conectado à internet é 100% inviolável. Em caso de incidente de segurança que afete seus dados, faremos o possível para notificá-lo em tempo hábil.</p>
      <p>Não utilizamos cookies de rastreamento de terceiros, redes de anúncios ou qualquer ferramenta de analytics externo que colete dados de comportamento sem seu conhecimento.</p>
    </div>
  </div>

  <!-- SEÇÃO 4 -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;">4</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Seus direitos (LGPD)</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p>Em conformidade com a <strong>Lei Geral de Proteção de Dados (Lei nº 13.709/2018)</strong>, você tem direito a:</p>
      <p><strong style="color:#611DF2;">Acesso</strong> — solicitar uma cópia de todos os dados que temos sobre você.</p>
      <p><strong style="color:#611DF2;">Correção</strong> — atualizar ou corrigir seus dados a qualquer momento pelo painel da conta.</p>
      <p><strong style="color:#611DF2;">Exclusão</strong> — solicitar a remoção da sua conta e de todos os seus dados pessoais. Posts e comentários públicos podem ser anonimizados em vez de excluídos, para preservar a coerência das discussões.</p>
      <p><strong style="color:#611DF2;">Portabilidade</strong> — solicitar seus dados em formato legível por máquina.</p>
      <p><strong style="color:#611DF2;">Revogação de consentimento</strong> — você pode encerrar sua conta a qualquer momento, sem necessidade de justificativa.</p>
      <p>Para exercer qualquer um desses direitos, entre em contato pelo e-mail
        <a href="mailto:dream.lab75@gmail.com" style="color:#611DF2;font-weight:600;text-decoration:none;">dream.lab75@gmail.com</a>.
      </p>
    </div>
  </div>

  <!-- SEÇÃO 5 -->
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;">5</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Conteúdo de terceiros</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p>O PlayZone pode conter links para sites externos (redes sociais, fontes de notícias, etc.). Não nos responsabilizamos pelas práticas de privacidade desses sites. Recomendamos que você leia as políticas de cada plataforma antes de interagir com elas.</p>
      <p>Imagens exibidas em posts são de responsabilidade de quem as publica. Caso identifique conteúdo indevido, entre em contato para que possamos avaliar e remover se necessário.</p>
    </div>
  </div>

  <!-- SEÇÃO 6 -->
  <div class="noticia-artigo">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;">6</span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;">Alterações nesta política</h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo">
      <p>Podemos atualizar esta política periodicamente para refletir mudanças na plataforma ou na legislação. A data de "última atualização" no topo desta página sempre indicará quando foi a revisão mais recente.</p>
      <p>Mudanças significativas serão comunicadas na página inicial do blog. O uso continuado da plataforma após uma alteração constitui aceite das novas condições.</p>
      <p>Dúvidas sobre esta política? Fale conosco:
        <a href="mailto:dream.lab75@gmail.com" style="color:#611DF2;font-weight:600;text-decoration:none;">dream.lab75@gmail.com</a>
      </p>
    </div>
  </div>

</div>

<template hx-get="footer.html" hx-target="#footer" hx-trigger="load"></template>
<div id="footer"></div>
</body>
</html>