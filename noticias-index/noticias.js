fetch('noticias-index/noticias-template.html')
  .then(res => res.text())
  .then(templateHTML => {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = templateHTML;
    const template = tempDiv.querySelector('#noticia-template');

    fetch('noticias-index/noticias.json')
      .then(res => res.json())
      .then(noticias => {
        const container = document.getElementById('noticias-container');

        noticias.forEach(noticia => {
          const clone = template.content.cloneNode(true);

          const badge = clone.querySelector('.news-badge');
          badge.textContent = noticia.badgeTexto;
          badge.className = `news-badge ${noticia.badgeClasse}`;

          clone.querySelector('.news-item-title').textContent = noticia.titulo;
          clone.querySelector('.news-tempo').textContent = noticia.tempo;

          container.appendChild(clone);
        });
      });
  });