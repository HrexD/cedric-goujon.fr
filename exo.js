document.getElementById('search_button').addEventListener('click', async () => {
  const username = document.getElementById("git_username").value.trim();
  const resultDiv = document.getElementById("git_result");

  if (!username) {
    resultDiv.innerHTML = `<p style="color:orange;">⚠️ Entrez un pseudo</p>`;
    return;
  }

  resultDiv.innerHTML = `<p>⏳ Chargement...</p>`;

  try {
    const response = await fetch(`https://api.github.com/users/${username}`);
    if (!response.ok) throw new Error('Utilisateur non trouvé');

    const user = await response.json();

    resultDiv.innerHTML = `
      <img id="git_avatar" src="${user.avatar_url}" alt="${user.login}" width="100">
      <h2>${user.name || user.login}</h2>
      <!--<p>${user.bio || 'Aucune bio disponible'}</p>-->
      <p>Repos publics: ${user.public_repos}</p>
      <p>Followers: ${user.followers}</p>
      <p>Following: ${user.following}</p>
      <p>Compte créé le : ${new Date(user.created_at).toLocaleDateString()}</p>
      <a href="${user.html_url}" target="_blank">Voir le profil GitHub</a>
    `;

    // Charger les 5 derniers repos
    const reposResponse = await fetch(`https://api.github.com/users/${username}/repos?sort=created&per_page=5`);
    const repos = await reposResponse.json();

    if (repos.length > 0) {
      const reposList = repos.map(repo => `
        <li>
          <a href="${repo.html_url}" target="_blank">${repo.name}</a>
          ⭐ ${repo.stargazers_count}
        </li>
      `).join("");

      resultDiv.innerHTML += `
        <h3>Derniers repos :</h3>
        <ul>${reposList}</ul>
      `;
    }

  } catch (error) {
    resultDiv.innerHTML = `<p style="color:red;">❌ ${error.message}</p>`;
  }
});
