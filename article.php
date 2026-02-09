<?php
// Paramètres de connexion à la base de données
$host = "localhost";
$dbname = "gestion de stock cofat";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<script>Swal.fire('Erreur', 'Erreur de connexion : " . $e->getMessage() . "', 'error');</script>";
    exit;
}

// Initialisation de la variable de message pour SweetAlert
$message = null;
$type = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajouter un article
    
    // Vérifie si le bouton 'ajouter' a été soumis 
    if (isset($_POST['ajouter'])) {
        $nom = $_POST['nom'];
        $quantite = $_POST['quantite'];
        $prix = $_POST['prix'];
        $id_categorie = $_POST['id_categorie'];
          // Prépare une requête SQL pour insérer un nouvel article dans la table "articles"

        $stmtArticles = $conn->prepare("INSERT INTO articles (nom, quantite, prix, id_categorie) VALUES (?, ?, ?, ?)");
        if ($stmtArticles->execute([$nom, $quantite, $prix, $id_categorie])) {
              // Récupère l'ID de l'article qui vient d'être inséré
            $id_article = $conn->lastInsertId();
            
            // Vérifier les colonnes disponibles dans la table stock
            try {
                $stmtCheckStock = $conn->prepare("SELECT * FROM stock WHERE id_article = ?");
                $stmtCheckStock->execute([$id_article]);
                $existingStock = $stmtCheckStock->fetch();
                
                if ($existingStock) {
                    // Si le stock existe, met à jour la quantité en stock
                    // Utiliser quantite_stock au lieu de quantite
                    $stmtUpdateStock = $conn->prepare("UPDATE stock SET quantite_stock = quantite_stock + ? WHERE id_article = ?");
                    if ($stmtUpdateStock->execute([$quantite, $id_article])) {
                        $message = "Article ajouté et stock mis à jour avec succès!";
                        $type = "success";
                    } else {
                        $message = "Une erreur s'est produite lors de la mise à jour du stock.";
                        $type = "error";
                    }
                } else {
                    // Si le stock n'existe pas, crée une nouvelle entrée dans la table "stock"
                    // Utiliser quantite_stock au lieu de quantite
                    $stmtStock = $conn->prepare("INSERT INTO stock (id_article, quantite_stock) VALUES (?, ?)");
                    if ($stmtStock->execute([$id_article, $quantite])) {
                        $message = "Article et stock ajoutés avec succès!";
                        $type = "success";
                    } else {
                        $message = "Une erreur s'est produite lors de l'ajout du stock.";
                        $type = "error";
                    }
                }
            } catch (PDOException $e) {
                // Si erreur avec la table stock, on continue quand même
                $message = "Article ajouté avec succès! (Erreur stock: " . $e->getMessage() . ")";
                $type = "warning";
            }
        } else {
            // Si l'insertion de l'article a échoué
            $message = "Une erreur s'est produite lors de l'ajout de l'article.";
            $type = "error";
        }
    }

    // Modifier un article
    if (isset($_POST['modifier'])) {
        $id_article = $_POST['id_article'];
        $nom = $_POST['nom'];
        $quantite = $_POST['quantite'];
        $prix = $_POST['prix'];
        $id_categorie = $_POST['id_categorie'];
         // Prépare une requête SQL pour modifier les informations de l'article
        $stmt = $conn->prepare("UPDATE articles SET nom = ?, quantite = ?, prix = ?, id_categorie = ? WHERE id_article = ?");
         // Exécute la requête avec les nouvelles données
        if ($stmt->execute([$nom, $quantite, $prix, $id_categorie, $id_article])) {
            // Si l'article est modifié avec succès, met à jour aussi le stock correspondant
            try {
                // Utiliser quantite_stock au lieu de quantite
                $stmtStock = $conn->prepare("UPDATE stock SET quantite_stock = ? WHERE id_article = ?");
                if ($stmtStock->execute([$quantite, $id_article])) {
                    $message = "Article et stock modifiés avec succès!";
                    $type = "success";
                } else {
                    $message = "Article modifié avec succès! (Stock non mis à jour)";
                    $type = "success";
                }
            } catch (PDOException $e) {
                $message = "Article modifié avec succès! (Erreur stock: " . $e->getMessage() . ")";
                $type = "warning";
            }
        } else {
            $message = "Une erreur s'est produite lors de la modification de l'article.";
            $type = "error";
        }
    }
}

// Suppression d'un article
if (isset($_GET['supprimer'])) {
    // Vérifie si un paramètre "supprimer" est présent dans l'URL (via GET)
    $id_article = $_GET['supprimer'];
     // Prépare une requête pour supprimer l'article correspondant

    $stmt = $conn->prepare("DELETE FROM articles WHERE id_article = ?");
    if ($stmt->execute([$id_article])) {
        $message = "Article supprimé avec succès!";
        $type = "success";
    } else {
        $message = "Une erreur s'est produite lors de la suppression.";
        $type = "error";
    }
}

// Récupération des articles
$stmt = $conn->query("SELECT articles.*, categories.nom AS categorie_nom FROM articles 
                      INNER JOIN categories ON articles.id_categorie = categories.id_categorie 
                      ORDER BY id_article DESC");
 // Stocke les résultats dans un tableau associatif
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des catégories
$stmtCategories = $conn->query("SELECT * FROM categories");

$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Style global pour la page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;

        }

        /* Titre principal */
        h1 {
            font-size: 6.5rem;
            margin-bottom: 100px;
            text-align: center;
            color: #343a40;
            width: 150%;
            right: 400px;
            position: absolute;


        }

        .swal-popup {
            font-size: 50px;
            /* Modifier la taille de la police */
            padding: 45px;
            /* Modifier les espacements */
        }

        h2 {
            font-size: 6.5rem;
            margin-bottom: 200px;
            text-align: center;
            color: #343a40;
            width: 150%;
            padding-top: 127%;






        }



        /* Formulaire d'ajout d'article */
        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            /* Position absolue */
            top: 300px;
            /* Vous pouvez ajuster la position verticale */
            left: 600px;
            /* Place le formulaire à 20px du bord droit */
            width: 48%;
        }

        form .form-label {
            font-weight: bold;
            font-size: 4.5rem;


        }

        /* Bouton Ajouter */
        button[type="submit"] {
            width: 100%;
            padding: 30px;
            font-size: 4.2rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }

      
        input.form-control {
            font-size: 3.5rem;
          
            padding: 15px;
          
            border-radius: 6px;
           
            width: 100%;
           
        }

      
        select.form-select {
            font-size: 3.5rem;
            padding: 12px;
            border-radius: 6px;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;


        }

        
        table {
            font-size: 4rem;
           
            padding: 30px;
           
            width: 90%;
            max-width: 90%;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            position: absolute;
            top: 1900px;
            right: 0.7px;
           


        }

      
        table th,
        table td {
            padding: 30px;
           
            text-align: center;
            border-bottom: 2px solid #ddd;
           
        }

       
        table th {
            background-color: #007bff;
            color: white;
            font-size: 4.5rem;
          

        }

       
        a.btn.btn-warning {
            margin-right: 10px;
            padding: 30px 30px;
            font-size: 50px;
            border-radius: 8px;
            color: white;  

            text-decoration: none;
            display: inline-block;
            
            text-align: center;
           


        }

        a.btn.btn-danger {
            margin-right: 10px;
            padding: 30px 30px;
            font-size: 50px;
            border-radius: 8px;
            /* Coins arrondis pour un style plus moderne */
            color: white;
            /* Couleur du texte */
            background-color: red;
            /* Couleur de fond */
            text-decoration: none;
            /* Supprime le soulignement */
            display: inline-block;
            /* S'assure que l'élément est affiché comme un bouton */
            text-align: center;
            /* Centrage du texte */
        }

        a.btn.btn-danger:hover {
            color: #0D0D0E;


        }


        .mb-3 {
            padding: 20px;
            /* Augmenter l'espacement autour de l'élément */
            font-size: 60px;
            /* Augmenter la taille de la police */
        }






        button.btn btn-primary {

            font-size: 1.5rem;
        }


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

        h1 {
            font-size: 6.5rem;
            margin-bottom: 200px;
            text-align: center;
            color: #343a40;
            width: 150%;
        }

        .swal-popup {
            font-size: 50px;
            /* Modifier la taille de la police */
            padding: 45px;
            /* Modifier les espacements */
        }

        /* Styles globaux pour les modals */
        .modal-content {
            background-color: #f8f9fa;
            /* Couleur de fond douce */
            border-radius: 8px;
            /* Coins arrondis */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            /* Ombre douce */
            border: none;
            /* Retirer la bordure par défaut */
            width: 2500px;
            max-width: 2500px;
        }

        .modal-header {
            background-color: #007bff;
            /* Couleur primaire pour le header */
            color: white;
            /* Texte blanc */
            border-bottom: none;
            /* Retirer la bordure inférieure */
            padding: 15px 20px;
            /* Espacement interne */
            border-top-left-radius: 8px;
            /* Coins arrondis en haut à gauche */
            border-top-right-radius: 8px;
            /* Coins arrondis en haut à droite */
        }

        .modal-title {
            font-size: 60px;
            font-weight: 600;
        }

        .btn-close {
            background: none;
            /* Retirer le fond par défaut */
            border: none;
            /* Retirer la bordure */
            color: white;
            /* Icone blanche */
            font-size: 60px;
            opacity: 0.8;
        }

        .btn-close:hover {
            opacity: 1;
            /* Pleine opacité au survol */
        }

        .modal-body {
            padding: 20px;
            /* Espacement interne */
        }

        .form-label {
            font-size: 60px;
            color: #495057;
            /* Couleur neutre pour les labels */
        }

        .form-control {
            border-radius: 5px;
            /* Coins légèrement arrondis */
            border: 1px solid #ced4da;
            /* Bordure douce */
            padding: 10px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            /* Couleur primaire au focus */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
            /* Légère ombre bleue */
        }

        .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px;
            transition: border-color 0.3s;
        }

        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        }

        .modal-footer {
            background-color: #f1f1f1;
            /* Couleur de fond douce */
            padding: 15px 20px;
            /* Espacement interne */
            border-top: none;
            /* Retirer la bordure supérieure */
            border-bottom-left-radius: 8px;
            /* Coins arrondis en bas à gauche */
            border-bottom-right-radius: 8px;
            /* Coins arrondis en bas à droite */
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
            font-size: 60px;
            padding: 10px 15px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            /* Couleur plus sombre au survol */
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 5px;
            font-size: 60px;
            padding: 10px 15px;
            transition: background-color 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            /* Couleur plus sombre au survol */
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
    <h1>Gestion des Articles</h1>

    <!-- Formulaire d'ajout d'article -->
    <form method="POST" action="">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom de l'article</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>
        <div class="mb-3">
            <label for="quantite" class="form-label">Quantité</label>
            <input type="number" class="form-control" id="quantite" name="quantite" required>
        </div>
        <div class="mb-3">
            <label for="prix" class="form-label">Prix</label>
            <input type="number" class="form-control" id="prix" name="prix" required>
        </div>
        <div class="mb-3">
            <label for="id_categorie" class="form-label">Catégorie</label>
            <select class="form-select" id="id_categorie" name="id_categorie" required>
                <?php foreach ($categories as $categorie) { ?>
                    <option value="<?php echo htmlspecialchars($categorie['id_categorie'] ?? ''); ?>"><?php echo htmlspecialchars($categorie['nom'] ?? ''); ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="ajouter">Ajouter l'article</button>
    </form>

    <!-- Table des articles -->
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Quantité</th>
                <th>Prix</th>
                <th>Catégorie</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article) { ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($article['nom'] ?? ''); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($article['quantite'] ?? ''); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($article['prix'] ?? ''); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($article['categorie_nom'] ?? ''); ?>
                    </td>
                    <td>
                        <a href="#" class="btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                            onclick="populateEditModal(<?php echo htmlspecialchars($article['id_article'] ?? ''); ?>, '<?php echo htmlspecialchars($article['nom'] ?? ''); ?>', <?php echo htmlspecialchars($article['quantite'] ?? ''); ?>, <?php echo htmlspecialchars($article['prix'] ?? ''); ?>, <?php echo htmlspecialchars($article['id_categorie'] ?? ''); ?>)">
                            Modifier
                        </a>
                        <a href="?supprimer=<?php echo htmlspecialchars($article['id_article'] ?? ''); ?>" class="btn-danger">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Modal de modification -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Modifier l'article</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_article" name="id_article">
                        <div class="mb-3">
                            <label for="edit_nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="edit_nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_quantite" class="form-label">Quantité</label>
                            <input type="number" class="form-control" id="edit_quantite" name="quantite" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_prix" class="form-label">Prix</label>
                            <input type="number" class="form-control" id="edit_prix" name="prix" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_id_categorie" class="form-label">Catégorie</label>
                            <select class="form-select" id="edit_id_categorie" name="id_categorie" required>
                                <?php foreach ($categories as $categorie) { ?>
                                    <option value="<?php echo htmlspecialchars($categorie['id_categorie'] ?? ''); ?>"><?php echo htmlspecialchars($categorie['nom'] ?? ''); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="modifier">Enregistrer les
                            modifications</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Remplir le modal avec les données de l'article
        function populateEditModal(id_article, nom, quantite, prix, id_categorie) {
            document.getElementById('edit_id_article').value = id_article;
            document.getElementById('edit_nom').value = nom;
            document.getElementById('edit_quantite').value = quantite;
            document.getElementById('edit_prix').value = prix;
            document.getElementById('edit_id_categorie').value = id_categorie;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<!-- Affichage des messages SweetAlert -->
<?php if ($message): ?>
    <script>
        Swal.fire({
            icon: '<?= $type ?>',
            title: '<?= $message ?>',
            showConfirmButton: false,
            timer: 2000,
            customClass: {
                popup: 'swal-popup'
            }
        });

    </script>
<?php endif; ?>