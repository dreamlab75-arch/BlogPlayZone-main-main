fetch('posts-index/post-template.html')
  .then(res => res.text())
  .then(templateHTML => {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = templateHTML;
    const template = tempDiv.querySelector('#post-template');

    fetch('posts-index/posts.json')
      .then(res => res.json())
      .then(posts => {
        const container = document.getElementById('posts-container');
        const postsEmAlta = posts.slice(0, 3); // pega apenas os 3 primeiros

        postsEmAlta.forEach(post => {
          const clone = template.content.cloneNode(true);

          // Preenche as infos
          clone.querySelector('.post-avatar').textContent = post.avatar;
          clone.querySelector('.post-author-info h6').textContent = post.autor;
          clone.querySelector('.post-author-info small').textContent = post.tempo;

          const tagsDiv = clone.querySelector('.post-tags');
          tagsDiv.innerHTML = post.tags.map(tag => `<span class="post-tag">${tag}</span>`).join('');

          clone.querySelector('.post-title').textContent = post.titulo;
          clone.querySelector('.post-excerpt').textContent = post.resumo;

          clone.querySelector('.post-stats .curtidas').innerHTML = `<i class="bi bi-heart-fill"></i> ${post.curtidas} curtidas`;
          clone.querySelector('.post-stats .comentarios').innerHTML = `<i class="bi bi-chat-fill"></i> ${post.comentarios} comentários`;
          clone.querySelector('.post-stats .visualizacoes').innerHTML = `<i class="bi bi-eye-fill"></i> ${post.visualizacoes} visualizações`;

          container.appendChild(clone);
        });
      });
  });