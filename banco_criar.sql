.open banco.db
.mode column

DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS noticias;

CREATE TABLE posts (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo           TEXT    NOT NULL,
    conteudo         TEXT    NOT NULL,
    autor            TEXT    NOT NULL,
    avatar           TEXT    NOT NULL,
    tags             TEXT    NOT NULL,
    curtidas         INTEGER DEFAULT 0,
    comentarios      INTEGER DEFAULT 0,
    visualizacoes    TEXT    DEFAULT '0'
);

CREATE TABLE noticias (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo           TEXT    NOT NULL,
    conteudo         TEXT    NOT NULL,
    badge_texto      TEXT    NOT NULL,
    badge_classe     TEXT    NOT NULL,
    imagem           TEXT    DEFAULT 'img/2014-08-20-tw3-gameplay.webp',
    visualizacoes    INTEGER DEFAULT 0,
    data_publicacao  TEXT    DEFAULT (datetime('now'))
);

INSERT INTO posts (titulo, conteudo, autor, avatar, tags, curtidas, comentarios, visualizacoes) VALUES
(
    'Por que Elden Ring definiu uma nova era para os RPGs',
    'Após mais de 200 horas de jogo, posso afirmar com segurança que Elden Ring não é apenas mais um souls-like. A FromSoftware conseguiu criar algo verdadeiramente único.

A liberdade de exploração é o que mais me surpreendeu. Diferente dos Souls anteriores, aqui você pode ir em qualquer direção — e vai morrer muito por isso.

O sistema de combate evoluiu sem perder a essência. As Ashes of War adicionam uma camada de customização que permite adaptar sua build ao seu estilo de jogo.

Se você nunca jogou um Souls-like, Elden Ring pode ser intimidador no início. Mas persista — a satisfação de derrotar um boss que te matou 30 vezes não tem comparação.',
    'João Martins', 'JM', 'RPG,Opinião', 234, 56, '1.2k'
),
(
    'Como melhorei meu rank em Valorant: 5 dicas práticas',
    'Depois de ficar travada no Prata por quase quatro meses, finalmente consegui subir para Diamante. Não foi sorte — foi mudança de mentalidade.

1. Comunicação antes de tudo. Callouts claros, sem tilt, sem toxicidade.
2. Jogue menos agentes, domine mais. Foquei em Jett e Reyna e minha consistência aumentou muito.
3. Revise seus próprios replays. 30 minutos revisando suas mortes te ensina mais do que 5 partidas novas.
4. Mira antes de movimentação. Posicione a mira onde o inimigo vai aparecer antes de avançar.
5. Saiba quando parar. Se perdeu 3 partidas seguidas, feche o jogo.',
    'Ana Silva', 'AS', 'FPS,E-Sports', 189, 42, '890'
),
(
    'Vale a pena montar um PC gamer em 2025? Minha experiência',
    'Gastei R$ 8.000 montando meu setup dos sonhos. Spoiler: valeu a pena, mas com algumas ressalvas.

A escolha dos componentes foi a parte mais trabalhosa. No final optei por uma RTX 4070 com um Ryzen 7 7700X.

A montagem foi mais tranquila do que eu esperava. Com tutoriais no YouTube e paciência, qualquer pessoa consegue.

O desempenho final superou minhas expectativas. Consigo rodar Cyberpunk 2077 em ultra com ray tracing a 60fps estáveis.',
    'Pedro Costa', 'PC', 'Hardware,Review', 312, 78, '2.1k'
),
(
    'Zelda: Tears of the Kingdom — um ano depois ainda impressiona',
    'Revisitei Hyrule após 12 meses e descobri que ainda havia segredos que não tinha encontrado.

O sistema Ultrahand continua sendo uma das mecânicas mais criativas da geração.

Os Templos são superiores aos Santuários do jogo anterior. Cada um tem identidade visual única e puzzles inteligentes.

A história, contada através das memórias, entrega uma conclusão emocionante.',
    'Lucas Cardoso', 'LC', 'Nintendo,Review', 421, 93, '3.4k'
),
(
    'Por que os jogos indie estão salvando a indústria',
    'Enquanto as grandes publishers apostam em remakes, os estúdios independentes entregam as experiências mais originais dos últimos anos.

Balatro redefiniu o que um jogo de cartas pode ser. Hades II continua evoluindo o roguelike de formas que nenhum estúdio grande ousaria tentar.

O modelo independente permite riscos criativos impossíveis dentro de grandes corporações.

A ascensão das plataformas digitais democratizou a distribuição.',
    'Marina Ferreira', 'MF', 'Indie,Opinião', 276, 64, '1.8k'
);

INSERT INTO noticias (titulo, conteudo, badge_texto, badge_classe, imagem, visualizacoes, data_publicacao) VALUES
(
    'Sony anuncia showcase com novidades do PS5',
    'A Sony confirmou um novo PlayStation Showcase para o próximo mês. O evento promete revelar os principais jogos exclusivos do segundo semestre.

Entre os títulos esperados estão sequências de franquias consagradas. Fontes indicam que pelo menos dois jogos inéditos serão revelados.

O showcase deve durar 90 minutos e será seguido por um State of Play focado em jogos indie.

Fãs aguardam notícias sobre God of War, Horizon e uma possível surpresa da franquia Twisted Metal.',
    'URGENTE', 'bg-danger text-white', 'img/2014-08-20-tw3-gameplay.webp', 15420, datetime('now', '-30 minutes')
),
(
    'Novo jogo da franquia Final Fantasy ganha data',
    'A Square Enix confirmou a data de lançamento do novo Final Fantasy em evento em Tóquio. O jogo chega ao PS5, Xbox Series X e PC simultaneamente.

O produtor revelou que a equipe trabalhou por mais de quatro anos no projeto.

A trilha sonora ficou a cargo de Nobuo Uematsu, que retorna após alguns títulos de ausência.

O jogo terá legendas em português do Brasil.',
    'LANÇAMENTO', 'bg-primary text-white', 'img/Prancheta 1.svg', 9870, datetime('now', '-1 hours')
),
(
    'Fortnite recebe novo mapa e modo de jogo',
    'A Epic Games lançou uma atualização massiva com novo mapa e modo inédito.

O novo mapa conta com biomas variados: área urbana destruída, floresta densa e região ártica.

O novo modo Zero Build Ranked oferece partidas competitivas sem construção.

A atualização também trouxe novos personagens colaborativos de franquias populares.',
    'ATUALIZAÇÃO', 'bg-success text-white', 'img/Prancheta 2.svg', 7340, datetime('now', '-2 hours')
),
(
    'Nintendo pode anunciar sucessor do Switch em breve',
    'Rumores consistentes apontam para um anúncio iminente do sucessor do Switch.

O novo hardware deve manter o conceito híbrido com melhorias significativas de desempenho.

A tela portátil deve receber upgrade com OLED maior e taxa de atualização de 120Hz.

Desenvolvedores third-party já estariam recebendo kits de desenvolvimento há alguns meses.',
    'RUMOR', 'bg-warning text-dark', 'img/Prancheta 1.svg', 4210, datetime('now', '-3 hours')
),
(
    'Como a IA está mudando o desenvolvimento de jogos',
    'A inteligência artificial está transformando o desenvolvimento de jogos, desde geração de assets até comportamento de NPCs.

Ferramentas de IA permitem que artistas criem variações de assets em minutos.

Modelos de linguagem estão sendo usados para criar personagens que respondem de forma mais natural.

O debate ético sobre o impacto da IA nos empregos da indústria continua intenso.',
    'ANÁLISE', 'bg-info text-white', 'img/Prancheta 2.svg', 2980, datetime('now', '-5 hours')
);

SELECT id, titulo FROM posts;
SELECT id, titulo, data_publicacao, visualizacoes FROM noticias ORDER BY visualizacoes DESC;