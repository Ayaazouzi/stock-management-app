<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion de stock cofat");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Ajouter une catégorie

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $nom = $conn->real_escape_string($_POST['nom']);
    $insertQuery = "INSERT INTO categories (nom) VALUES ('$nom')";
    if ($conn->query($insertQuery)) {
        // Redirection pour éviter une nouvelle soumission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "<script>Swal.fire('Erreur', 'Erreur lors de l\'ajout de la catégorie', 'error');</script>";
    }
}


// Modifier une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_categorie']) && isset($_POST['edit_nom'])) {
    $id = intval($_POST['id_categorie']);
    $nom = $conn->real_escape_string($_POST['edit_nom']);
    $updateQuery = "UPDATE categories SET nom = '$nom' WHERE id_categorie = $id";
    if ($conn->query($updateQuery)) {
        echo "<script>Swal.fire('Succès', 'Catégorie modifiée avec succès', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Erreur', 'Erreur lors de la modification de la catégorie', 'error');</script>";
    }
}

// Supprimer une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $deleteQuery = "DELETE FROM categories WHERE id_categorie = $id";
    if ($conn->query($deleteQuery)) {
        echo "<script>Swal.fire('Succès', 'Catégorie supprimée avec succès', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Erreur', 'Erreur lors de la suppression de la catégorie', 'error');</script>";
    }
}

// Récupérer toutes les catégories
$categories = $conn->query("SELECT * FROM categories ORDER BY id_categorie DESC");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
            background-color: #ffffff;
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
        button.deleteCategory {
            padding: 10px 20px;
            width: auto;
            margin: 5px;
        }

        button.editCategory {
            background-color: #ffc107;
            color: white;
        }



        button.deleteCategory {
            background-color: #dc3545;
            color: white;
        }

        button.editCategory:hover {
            background-color: #e0a800;
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

        input.form-control {

            /* Augmenter la taille du texte */
            padding: 60px;
            /* Augmenter l'espace interne du champ */
            border-radius: 6px;
            /* Ajouter un rayon de bordure pour adoucir les coins */
            width: 200%;
            /* Assurer que le champ prend toute la largeur disponible */
            font-size: 3rem;
        }

        .form-label {
            font-size: 5.5rem;

        }

        button.btn-primary {
            font-size: 4.5rem;
            margin: 3%;
            margin-left: 0.9%;
            background-color: #AAB6C2;
            border-color: #AAB6C2;


        }

        button.btn-primary:hover {
            font-size: 4.5rem;
            margin: 3%;
            margin-left: 0.9%;
            background-color: #AAB6C2;
            border-color: #AAB6C2;


        }


        /* Style pour le formulaire */
        #editCategoryForm {
            max-width: 2500px;
            /* Largeur maximale extrêmement large */
            width: 2500px;
            margin: 0 auto;
            padding: 40px;
            /* Garde un espacement suffisant autour du contenu */
            border: 1px solid #ddd;
            border-radius: 12px;
            /* Coins arrondis */
            background-color: #f9f9f9;
            box-shadow: 0 8px 10px rgba(0, 0, 0, 0.15);
            /* Ombre plus marquée */
            height: 1500px;
            /* Hauteur fixe pour un espace ample */
            box-sizing: border-box;
        }

        /* Style pour le label */
        #editCategoryForm .editNom {
            display: block;
            font-weight: bold;
            margin-bottom: 16px;
            font-size: 60px;
            color: #333;
        }

        /* Style pour les inputs */
        #editCategoryForm .text {
            width: 100%;
            padding: 40px;
            margin-bottom: 25px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 60px;
            box-sizing: border-box;

        }

        /* Effet focus sur les inputs */
        #editCategoryForm .text:focus {
            border-color: #007bff;
            outline: none;

        }

        /* Style pour le bouton */
        #editCategoryForm .submit {
            display: inline-block;
            padding: 20px 30px;
            font-size: 60px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;

        }

        /* Effet hover pour le bouton */
        #editCategoryForm .submit:hover {
            background-color: #0056b3;
        }



        #editCategoryForm .text {
            font-size: 60px;
            /* Ajuste la taille des champs de saisie */
        }

        #editCategoryForm .submit {
            font-size: 60px;
            /* Réduit la taille du texte du bouton */
            padding: 15px 25px;
            /* Réduit l'espacement du bouton */
        }

        /* Style pour le titre du modal */
        .modal-title {
            font-size: 3.5rem !important;
            /* Taille du texte */

        }

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
            font-size: 3rem;
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
        <a href="dashboard.php">Dashboard</a>
        <a href="article.php">Articles</a>
        <a href="user.php">Employés</a>
        <a href="fournisseur.php">Fournisseurs</a>
        <a href="stock.php">Stock</a>
        <a href="categorie.php">Catégorie</a>
        <a href="page home.html">se Deconnecter</a>
    </div>
    <div class="container mt-5">
        <h1 class="text-center">Gestion des Catégories</h1>
        <form id="addCategoryForm" method="POST">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de la catégorie</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>

        </form>

        <table class="table table-bordered mt-5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?= $row['id_categorie'] ?>
                        </td>
                        <td>
                            <?= $row['nom'] ?>
                        </td>
                        <td>
                            <button class="btn btn-warning editCategory"
                                data-id="<?= $row['id_categorie'] ?>">Éditer</button>
                            <button class="btn btn-danger deleteCategory"
                                data-id="<?= $row['id_categorie'] ?>">Supprimer</button>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal d'édition -->
    <div class="modal" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm" method="POST">
                        <input type="hidden" name="id_categorie" id="editId">
                        <div class="mb-3">
                            <label for="editNom" class="editNom">Nom</label>
                            <input type="text" class="text" id="editNom" name="edit_nom" required>
                            <button type="submit" class="submit">Sauvegarder</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.editCategory').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const row = button.closest('tr');
                const name = row.querySelector('td:nth-child(2)').innerText;
                document.getElementById('editId').value = id;
                document.getElementById('editNom').value = name;
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
        });

        document.querySelectorAll('.deleteCategory').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action supprimera la catégorie définitivement.",
                    icon: 'warning',
                    showCancelButton: true,
    confirmButtonColor: '#164A9E', // Couleur personnalisée pour le bouton OK
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Oui, supprimer !'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('delete_id', id);
                        fetch('categorie.php', { method: 'POST', body: formData })
                            .then(response => response.text())
                            .then(() => Swal.fire('Supprimé !', 'Catégorie supprimée.', 'success')
                                .then(() => location.reload()));
                    }
                });
            });
        });


    </script>
</body>

</html>