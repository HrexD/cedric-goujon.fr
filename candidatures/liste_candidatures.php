<?php
// Redirection vers le nouveau système
header('Location: index.php');
exit();
?>
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Candidatures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .search-input {
            max-width: 300px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Candidatures enregistrées</h2>
        <a href="ajouter_candidature.php" class="btn btn-success">+ Nouvelle</a>
    </div>

    <?php if (empty($candidatures)): ?>
        <div class="alert alert-info">Aucune candidature enregistrée.</div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <input type="text" class="form-control search-input" id="searchInput" placeholder="🔍 Rechercher...">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white" id="candidaturesTable">
                <thead class="table-light">
                    <tr>
                        <th onclick="sortTable(0)">📅 Date</th>
                        <th onclick="sortTable(1)">💼 Poste</th>
                        <th onclick="sortTable(2)">🏢 Entreprise</th>
                        <th onclick="sortTable(3)">📍 Lieu</th>
                        <th onclick="sortTable(4)">🌐 Site</th>
                        <th>🔗 Lien</th>
                        <th onclick="sortTable(6)">💰 Rémunération</th>
                        <th>⚙️ Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php foreach ($candidatures as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date_candidature']) ?></td>
                            <td><?= htmlspecialchars($row['poste']) ?></td>
                            <td><?= htmlspecialchars($row['entreprise']) ?></td>
                            <td><?= htmlspecialchars($row['lieu']) ?></td>
                            <td><?= htmlspecialchars($row['site_annonce']) ?></td>
                            <td>
                                <?php if (!empty($row['url_annonce'])): ?>
                                    <a href="<?= htmlspecialchars($row['url_annonce']) ?>" target="_blank">Voir</a>
                                <?php else: ?>
                                    <em>—</em>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['remuneration'] ? htmlspecialchars($row['remuneration']) : '<em>—</em>' ?></td>
                            <td>
                                <a href="modifier_candidature.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning">✏️</a>
                                <a href="supprimer_candidature.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette candidature ?');">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center mt-4" id="pagination"></ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Scripts JS pour tri, recherche, pagination -->
<script>
    const rowsPerPage = 25;
    let currentPage = 1;

    function paginateTable() {
        const table = document.getElementById("candidaturesTable");
        const tbody = document.getElementById("tableBody");
        const rows = Array.from(tbody.rows);
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        tbody.innerHTML = '';
        rows.slice(start, end).forEach(row => tbody.appendChild(row));

        const pagination = document.getElementById("pagination");
        pagination.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            pagination.innerHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        }
    }

    function changePage(page) {
        currentPage = page;
        paginateTable();
    }

    function sortTable(colIndex) {
        const tbody = document.getElementById("tableBody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        const isAsc = tbody.getAttribute("data-sort-dir") !== "asc";
        tbody.setAttribute("data-sort-dir", isAsc ? "asc" : "desc");

        rows.sort((a, b) => {
            const valA = a.cells[colIndex].textContent.trim().toLowerCase();
            const valB = b.cells[colIndex].textContent.trim().toLowerCase();
            return isAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });

        rows.forEach(row => tbody.appendChild(row));
        paginateTable();
    }

    document.getElementById("searchInput").addEventListener("input", function () {
        const term = this.value.toLowerCase();
        const rows = document.querySelectorAll("#tableBody tr");
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? "" : "none";
        });
    });

    // Initial pagination
    paginateTable();
</script>
</body>
</html>
