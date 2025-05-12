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

// Récupérer les catégories et les articles avec leurs stocks
$categoriesQuery = "SELECT * FROM categories";
$articlesStockQuery = "
    SELECT a.id_article, a.nom AS article_nom, a.prix, a.quantite AS article_quantite, a.nom, s.quantite_stock, s.nom_emplacement
    FROM articles a
    LEFT JOIN stock s ON a.id_article = s.id_article
";

$categories = $conn->query($categoriesQuery);
$articlesStock = $conn->query($articlesStockQuery);

// Traitement de la suppression d'un article
if (isset($_POST['delete_article_id'])) {
    $delete_article_id = $_POST['delete_article_id'];
    
    // Commencer une transaction pour garantir la cohérence de la base de données
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
        
        // Commit de la transaction
        $conn->commit();
        
        // Redirection après suppression
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollback();
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Traitement de la mise à jour d'un article (modification de la quantité et emplacement)
if (isset($_POST['edit_article_id'])) {
    $edit_article_id = $_POST['edit_article_id'];
    $new_quantity = $_POST['new_quantity'];
    $new_location = $_POST['new_location'];

    // Mise à jour de la quantité en stock et de l'emplacement
    $updateStockQuery = "UPDATE stock SET quantite_stock = ?, nom_emplacement = ? WHERE id_article = ?";
    $stmt = $conn->prepare($updateStockQuery);
    $stmt->bind_param("isi", $new_quantity, $new_location, $edit_article_id);
    $stmt->execute();
    
    // Redirection après mise à jour
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
            /* Réduit le padding global pour rapprocher le contenu du haut */
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 10%;
            display: flex;
            flex-direction: column;
            /* Aligner les éléments en colonne */
            align-items: flex-start;
            /* Alignement à gauche */
            justify-content: flex-start;
            /* Alignement en haut */
            gap: 10px;
            /* Espacement minimal entre les éléments */
        }

        .sidebar h2 {
            color: white;
            font-size: 70px;
            font-weight: bold;
            margin: 0;
            /* Supprime la marge par défaut */
            padding: 0;
            /* Supprime le padding éventuel */
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
            /* Espacement entre les tables */
            justify-content: space-between;
            /* Espacement égal entre les tables */
        }

        table {
            font-size: 4rem;
            /* Augmenter la taille de la police */
            padding: 30px;
            /* Ajouter plus d'espace autour de la table */
            width: 90%;
            /* Utilisation d'une largeur plus large et plus fluide */
            max-width: 90%;
            /* Utilisation de toute la largeur disponible */
            margin: 0 auto;
            /* Centrer la table */

            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            /* Permet un défilement horizontal si nécessaire */
            position: absolute;
            /* Position absolue */
            top: 900px;
            /* Vous pouvez ajuster la position verticale */
            margin-left: -1700px;
            /* Place le formulaire à 20px du bord droit */


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
            color:white;
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
            /* Augmentation du padding pour les boutons */
            width: auto;
            margin: 10px;
            font-size: 4.5rem;
            /* Augmentation de la taille des boutons */
        }

        button.btn-danger,
        button.btn-info,
        button.btn-warning {
            padding: 15px 30px;
            /* Augmentation du padding pour les boutons */
            width: auto;
            margin: 20px;
            font-size: 2.5rem;
            /* Augmentation de la taille des boutons */
        }


        /* Classes personnalisées pour SweetAlert */
        .swal2-popup {
            font-size: 3rem;
            /* Augmente la taille de la police dans la popup */
            max-width: 2000px;
            /* Définit une largeur plus grande */
            padding: 2rem;
            /* Ajoute un espacement interne plus grand */
        }

        .swal2-title {
            font-size: 5rem;
            /* Augmente la taille du titre */
            font-weight: bold;
            /* Rend le titre plus visible */
        }

        .swal2-content {
            font-size: 3rem;
            /* Ajuste la taille du contenu */
        }

        .swal2-confirm {
            font-size: 2rem;
            /* Augmente la taille du bouton de confirmation */
            padding: 0.8rem 1.5rem;
            /* Ajuste l'espacement interne du bouton */
        }

        .swal2-cancel {
            font-size: 2rem;
            /* Augmente la taille du bouton d'annulation */
            padding: 0.8rem 1.5rem;
            /* Ajuste l'espacement interne du bouton */
        }
    </style>
</head>

<body>
    <div class="col-md-3 sidebar">
        <img src="logo.png">
        <a href="dashboard user.php">Dashboard</a>
        <a href="article user.php">Articles</a>
        <a href="stock user.php">Stock</a>
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
                <?php while ($articleStock = $articlesStock->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $articleStock['id_article']; ?></td>
                        <td><?php echo $articleStock['article_nom']; ?></td>
                        <td><?php echo $articleStock['prix']; ?> TND</td>
                        <td><?php echo $articleStock['article_quantite']; ?></td>
                        <td><?php echo $articleStock['quantite_stock']; ?></td>
                        <td><?php echo $articleStock['nom_emplacement']; ?></td>
                        <td>
                            <button class="btn btn-warning editArticle" data-id="<?php echo $articleStock['id_article']; ?>"
                                data-quantity="<?php echo $articleStock['quantite_stock']; ?>"
                                data-location="<?php echo $articleStock['nom_emplacement']; ?>">Éditer</button>
                            <button class="btn btn-danger deleteArticle"
                                data-id="<?php echo $articleStock['id_article']; ?>">Supprimer</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.editArticle').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const quantity = button.getAttribute('data-quantity');
                const location = button.getAttribute('data-location');

                Swal.fire({
                    title: 'Éditez l\'article',
                    html: `
                        <input type="number" id="newQuantity" class="swal2-input" value="${quantity}" placeholder="Nouvelle quantité">
                        <input type="text" id="newLocation" class="swal2-input" value="${location}" placeholder="Nouvel emplacement">
                    `,
                    focusConfirm: false,
                    preConfirm: () => {
                        const newQuantity = document.getElementById('newQuantity').value;
                        const newLocation = document.getElementById('newLocation').value;

                        if (newQuantity && newLocation) {
                            return { newQuantity, newLocation };
                        } else {
                            Swal.showValidationMessage('Veuillez remplir tous les champs');
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { newQuantity, newLocation } = result.value;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';

                        const idField = document.createElement('input');
                        idField.type = 'hidden';
                        idField.name = 'edit_article_id';
                        idField.value = id;
                        form.appendChild(idField);

                        const quantityField = document.createElement('input');
                        quantityField.type = 'hidden';
                        quantityField.name = 'new_quantity';
                        quantityField.value = newQuantity;
                        form.appendChild(quantityField);

                        const locationField = document.createElement('input');
                        locationField.type = 'hidden';
                        locationField.name = 'new_location';
                        locationField.value = newLocation;
                        form.appendChild(locationField);

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
