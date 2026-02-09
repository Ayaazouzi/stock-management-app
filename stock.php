<?php
// Connexion à la base de données
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = "gestion de stock cofat";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CORRECTION: Requête SQL améliorée pour récupérer les articles avec leurs stocks
$articlesStockQuery = "
    SELECT 
        a.id_article, 
        a.nom AS article_nom, 
        a.prix, 
        a.quantite AS article_quantite,
        COALESCE(s.id_stock, '') AS id_stock,
        COALESCE(s.quantite_stock, 0) AS quantite_stock, 
        COALESCE(s.nom_emplacement, 'Non défini') AS nom_emplacement
    FROM articles a
    LEFT JOIN stock s ON a.id_article = s.id_article
    ORDER BY a.id_article
";

$articlesStock = $conn->query($articlesStockQuery);

// Traitement de la suppression d'un article
if (isset($_POST['delete_article_id'])) {
    $delete_article_id = intval($_POST['delete_article_id']);
    
    $conn->begin_transaction();

    try {
        // Supprimer l'article de la table stock
        $deleteStockQuery = "DELETE FROM stock WHERE id_article = ?";
        $stmt = $conn->prepare($deleteStockQuery);
        $stmt->bind_param("i", $delete_article_id);
        $stmt->execute();
        
        // Supprimer l'article de la table articles
        $deleteArticleQuery = "DELETE FROM articles WHERE id_article = ?";
        $stmt = $conn->prepare($deleteArticleQuery);
        $stmt->bind_param("i", $delete_article_id);
        $stmt->execute();
        
        $conn->commit();
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// CORRECTION: Traitement de la mise à jour amélioré
if (isset($_POST['edit_article_id'])) {
    $edit_article_id = intval($_POST['edit_article_id']);
    $new_quantity = intval($_POST['new_quantity']);
    $new_location = trim($_POST['new_location']);

    // Vérifier si un enregistrement existe déjà dans stock
    $checkQuery = "SELECT id_stock FROM stock WHERE id_article = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $edit_article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // UPDATE si l'enregistrement existe
        $updateStockQuery = "UPDATE stock SET quantite_stock = ?, nom_emplacement = ? WHERE id_article = ?";
        $stmt = $conn->prepare($updateStockQuery);
        $stmt->bind_param("isi", $new_quantity, $new_location, $edit_article_id);
    } else {
        // INSERT si l'enregistrement n'existe pas
        $updateStockQuery = "INSERT INTO stock (id_article, quantite_stock, nom_emplacement) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($updateStockQuery);
        $stmt->bind_param("iis", $edit_article_id, $new_quantity, $new_location);
    }
    
    $stmt->execute();
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stocks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background-color: #343a40;
            color: white;
            padding: 10px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 10%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 10px;
        }

        .sidebar h2 {
            color: white;
            font-size: 70px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            display: block;
            margin: 15px 0;
            padding: 50px;
            border-radius: 8px;
            font-size: 60px;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .main-content {
            padding: 30px;
        }

        .tables-container {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        table {
            font-size: 4rem;
            padding: 30px;
            width: 90%;
            max-width: 90%;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            position: absolute;
            top: 900px;
            margin-left: -1700px;
        }

        table th,
        table td {
            padding: 30px;
            text-align: center;
            border-bottom: 2px solid #ddd;
            font-size: 4rem;
        }

        table th {
            background-color: #007bff !important;
            font-weight: bold;
            color: white;
        }

        h1 {
            font-size: 6.5rem;
            margin-bottom: 200px;
            text-align: center;
            color: #343a40;
            width: 150%;
        }

        h3 {
            font-size: 5rem;
            text-align: center;
            margin-bottom: 20px;
            margin-left: -3000px;
        }

        button.editCategory,
        button.copyCategory,
        button.deleteCategory {
            padding: 10px 20px;
            width: auto;
            margin: 5px;
        }

        button.editCategory {
            background-color: #ffc107;
            color: white;
        }

        button.copyCategory {
            background-color: #17a2b8;
            color: white;
        }

        button.deleteCategory {
            background-color: #dc3545;
            color: white;
        }

        button.editCategory:hover {
            background-color: #e0a800;
        }

        button.copyCategory:hover {
            background-color: #138496;
        }

        button.deleteCategory:hover {
            background-color: #c82333;
        }

        button.editCategory,
        button.copyCategory,
        button.deleteCategory {
            padding: 15px 30px;
            width: auto;
            margin: 10px;
            font-size: 4.5rem;
        }

        button.btn-danger,
        button.btn-info,
        button.btn-warning {
            padding: 15px 30px;
            width: auto;
            margin: 20px;
            font-size: 2.5rem;
        }

        .swal2-popup {
            font-size: 3rem;
            max-width: 2000px;
            padding: 2rem;
        }

        .swal2-title {
            font-size: 5rem;
            font-weight: bold;
        }

        .swal2-content {
            font-size: 3rem;
        }

        .swal2-confirm {
            font-size: 2rem;
            padding: 0.8rem 1.5rem;
        }

        .swal2-cancel {
            font-size: 2rem;
            padding: 0.8rem 1.5rem;
        }

        /* CORRECTION: Ajout de styles pour les inputs SweetAlert */
        .swal2-input {
            font-size: 2.5rem !important;
            padding: 1rem !important;
            margin: 1rem 0 !important;
        }
    </style>
</head>

<body>
    <div class="col-md-3 sidebar">
        <img src="logo.png">
        <a href="dashboard.php">Dashboard</a>
        <a href="article.php">Articles</a>
        <a href="user.php">Employés</a>
        <a href="fournisseur.php">Fournisseurs</a>
        <a href="stock.php">Stock</a>
        <a href="categorie.php">Catégorie</a>
        <a href="page home.html">se Deconnecter</a>
    </div>

    <div class="container my-4">
        <h1 class="text-center">Gestion des Stocks par Catégorie</h1>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Article</th>
                    <th>Nom de l'Article</th>
                    <th>Prix</th>
                    <th>Quantité Article</th>
                    <th>Quantité en Stock</th>
                    <th>Emplacement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // CORRECTION: Vérification si des résultats existent
                if ($articlesStock->num_rows > 0) {
                    while ($articleStock = $articlesStock->fetch_assoc()) { 
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($articleStock['id_article']); ?></td>
                        <td><?php echo htmlspecialchars($articleStock['article_nom']); ?></td>
                        <td><?php echo htmlspecialchars($articleStock['prix']); ?> TND</td>
                        <td><?php echo htmlspecialchars($articleStock['article_quantite']); ?></td>
                        <td><?php echo htmlspecialchars($articleStock['quantite_stock']); ?></td>
                        <td><?php echo htmlspecialchars($articleStock['nom_emplacement']); ?></td>
                        <td>
                            <button class="btn btn-warning editArticle" 
                                data-id="<?php echo $articleStock['id_article']; ?>"
                                data-quantity="<?php echo $articleStock['quantite_stock']; ?>"
                                data-location="<?php echo htmlspecialchars($articleStock['nom_emplacement']); ?>">
                                Éditer
                            </button>
                            <button class="btn btn-danger deleteArticle"
                                data-id="<?php echo $articleStock['id_article']; ?>">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='7'>Aucun article trouvé</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CORRECTION: Code JavaScript amélioré
        document.querySelectorAll('.editArticle').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const quantity = button.getAttribute('data-quantity');
                const location = button.getAttribute('data-location');

                Swal.fire({
                    title: 'Éditez l\'article',
                    html: `
                        <input type="number" id="newQuantity" class="swal2-input" value="${quantity}" placeholder="Nouvelle quantité" style="font-size: 2.5rem; padding: 1rem; margin: 1rem 0;">
                        <input type="text" id="newLocation" class="swal2-input" value="${location}" placeholder="Nouvel emplacement" style="font-size: 2.5rem; padding: 1rem; margin: 1rem 0;">
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Enregistrer',
                    cancelButtonText: 'Annuler',
                    preConfirm: () => {
                        const newQuantity = document.getElementById('newQuantity').value;
                        const newLocation = document.getElementById('newLocation').value;
                        
                        if (!newQuantity || !newLocation) {
                            Swal.showValidationMessage('Veuillez remplir tous les champs');
                            return false;
                        }
                        
                        return { newQuantity, newLocation };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { newQuantity, newLocation } = result.value;
                        
                        // Création du formulaire
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';

                        // Ajout des champs
                        const fields = {
                            'edit_article_id': id,
                            'new_quantity': newQuantity,
                            'new_location': newLocation
                        };

                        for (const [name, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.deleteArticle').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cet article sera définitivement supprimé.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer!',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';

                        const idField = document.createElement('input');
                        idField.type = 'hidden';
                        idField.name = 'delete_article_id';
                        idField.value = id;
                        form.appendChild(idField);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>

</body>

</html>