<?php 
// Connexion à la base de données
session_start();
$host = 'localhost';
$dbname = "gestion de stock cofat"; // Correction du nom de la base de données
$username = "root";
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Ajouter ou modifier un fournisseur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $contact = $_POST['contact'];
    $adresse = $_POST['adresse'];

    if (isset($_POST['add_fournisseur'])) {
        $stmt = $pdo->prepare("INSERT INTO fournisseurs (nom, contact, adresse) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $contact, $adresse]);
        $_SESSION['message'] = "Fournisseur ajouté avec succès !";
    } elseif (isset($_POST['edit_fournisseur'])) {
        $id_fournisseur = $_POST['id_fournisseur'];
        $stmt = $pdo->prepare("UPDATE fournisseurs SET nom = ?, contact = ?, adresse = ? WHERE id_fournisseur = ?");
        $stmt->execute([$nom, $contact, $adresse, $id_fournisseur]);
        $_SESSION['message'] = "Fournisseur modifié avec succès !";
    }

    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Supprimer un fournisseur
if (isset($_GET['delete'])) {
    $id_fournisseur = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM fournisseurs WHERE id_fournisseur = ?");
    $stmt->execute([$id_fournisseur]);
    $_SESSION['message'] = "Fournisseur supprimé avec succès !";

    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Récupérer les fournisseurs
$stmt = $pdo->query("SELECT * FROM fournisseurs");
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Éditer un fournisseur
$editFournisseur = null;
if (isset($_GET['edit'])) {
    $id_fournisseur = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM fournisseurs WHERE id_fournisseur = ?");
    $stmt->execute([$id_fournisseur]);
    $editFournisseur = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fournisseurs</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            font-size: 4rem;
            padding: 30px;
            width: 85%;
            max-width: 85%;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            position: absolute;
            top: 500px;
            right: 1px;
        }
        table th, table td {
            padding: 30px;
            text-align: center;
            border-bottom: 2px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-size: 4.5rem;
        }
        .swal-popup {
            font-size: 50px; /* Modifier la taille de la police */
            padding: 45px;  /* Modifier les espacements */
        }
        form {
            margin-bottom: 20px;
            margin: 200px auto;
           width:84.4%;
           overflow-x: auto;
           position: absolute;
           top: 100px;
           right: 1px;
        }
        form input, form select, form button {
            padding: 10px;
            margin: 0 auto;
            
           
            

            
        }
        .actions a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        button[type="submit"] {
            background-color: #666666;
            color: white;
            border: none;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #666666;
        }
        h1 {
            font-size: 5.5rem;
            
            font-size: 5.5rem;
            position: absolute;
           top: 10px;
           right: 4000px;
            
        
        }
        input, select, button.a {
            font-size: 3.5rem;
            padding: 15px;
            border-radius: 6px;
            width: 10%;
        }
        button.a:hover {
            background-color: #666666;
        }
        .sidebar {
    background-color: #343a40;
    color: white;
    padding: 10px; /* Réduit le padding global pour rapprocher le contenu du haut */
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    width: 10%;
    display: flex;
    flex-direction: column; /* Aligner les éléments en colonne */
    align-items: flex-start; /* Alignement à gauche */
    justify-content: flex-start; /* Alignement en haut */
    gap: 10px; /* Espacement minimal entre les éléments */
}

.sidebar h2 {
    color: white;
    font-size: 70px;
    font-weight: bold;
    margin: 0; /* Supprime la marge par défaut */
    padding: 0; /* Supprime le padding éventuel */
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
        h2.m{
            margin-left:1px;
            padding-right: 120%;
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
<?php if (isset($_SESSION['message'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: <?= json_encode($_SESSION['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
            confirmButtonText: 'OK',
            customClass: {
                    popup: 'swal-popup'
                }
        });
    </script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<h1>Gestion des Fournisseurs</h1>

<!-- Formulaire d'ajout ou d'édition -->
<form method="POST" action="">
    <input type="text" name="nom" placeholder="Nom" value="<?= isset($editFournisseur) ? htmlspecialchars($editFournisseur['nom']) : '' ?>" required>
    <input type="text" name="contact" placeholder="Contact" value="<?= isset($editFournisseur) ? htmlspecialchars($editFournisseur['contact']) : '' ?>" required>
    <input type="text" name="adresse" placeholder="Adresse" value="<?= isset($editFournisseur) ? htmlspecialchars($editFournisseur['adresse']) : '' ?>" required>
    <input type="hidden" name="id_fournisseur" value="<?= isset($editFournisseur) ? $editFournisseur['id_fournisseur'] : '' ?>">
    <button  class="a"type="submit" name="<?= isset($editFournisseur) ? 'edit_fournisseur' : 'add_fournisseur' ?>">
        <?= isset($editFournisseur) ? 'Modifier' : 'Ajouter' ?>
    </button>
</form>

<!-- Liste des fournisseurs -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Contact</th>
            <th>Adresse</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fournisseurs as $fournisseur): ?>
            <tr>
                <td><?= htmlspecialchars($fournisseur['id_fournisseur']) ?></td>
                <td><?= htmlspecialchars($fournisseur['nom']) ?></td>
                <td><?= htmlspecialchars($fournisseur['contact']) ?></td>
                <td><?= htmlspecialchars($fournisseur['adresse']) ?></td>
                <td>
                    <a href="?edit=<?= $fournisseur['id_fournisseur'] ?>">Modifier</a>
                    <a href="?delete=<?= $fournisseur['id_fournisseur'] ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce fournisseur ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
