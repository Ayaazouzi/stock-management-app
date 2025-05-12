<?php
session_start();
//Démarre une session PHP pour stocker des variables d’utilisateur une fois connecté 

// Informations de connexion à la base de données
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "gestion de stock cofat";

// Création de la connexion
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Vérifie si la connexion échoue et affiche une erreur le cas échéant.
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Définir l'encodage pour éviter les problèmes de caractères
$conn->set_charset("utf8");

// Initialiser les messages pour SweetAlert
$message = '';
$message_success = '';

// Traitement du formulaire lorsque la méthode POST est utilisée
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['nom']) && !empty($_POST['mot_de_passe'])) {
        $username = $_POST['nom'];
        $password = $_POST['mot_de_passe'];

        $stmt = $conn->prepare("SELECT mot_de_passe FROM admin WHERE username = ?");
        if (!$stmt) {
            die('Échec de la préparation de la requête : ' . $conn->error);
        }
        //On lie le nom d’utilisateur ($username) à la requête et on exécute.
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $username;
                $message_success = "Connexion réussie ! Bienvenue, $username.";
                echo "<script>
                        setTimeout(() => {
                            window.location.href = 'dashboard.php';
                        }, 2000);
                      </script>";
            } else {
                $message = "Mot de passe incorrect!";
            }
        } else {
            $message = "Nom introuvable!";
        }
        $stmt->close();
    } else {
        $message = "Tous les champs doivent être remplis.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - COFAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-image: url('bg.png');
            /* Remplacez par le chemin de votre image */
            background-size: cover;
            /* Couvre toute la zone */
            background-position: center;
            /* Centre l'image */
            background-attachment: fixed;
            /* L'image reste fixe pendant le défilement */
            background-repeat: no-repeat;
            /* Empêche la répétition de l'image */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            color: #333;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            filter: blur(8px);
            /* Applique un flou de 8px à l'image de fond */
            z-index: -1;
            /* L'image floue est derrière le contenu */
            animation: bgFlow 10s infinite linear;
            /* Applique l'animation */
        }

        /* Animation de flux de fond */
        @keyframes bgFlow {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 100% 100%;
            }
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.8);
            /* Fond semi-transparent (0.8 pour opacité) */
            border-radius: 20px;
            /* Coins plus arrondis pour un design moderne */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            /* Ombre plus marquée */
            padding: 300px;
            /* Augmenter le padding pour agrandir l'espace interne */
            width: 1300px;
            /* Conteneur plus large */
            text-align: center;
            backdrop-filter: blur(10px);
            /* Applique un flou de fond à l'arrière-plan visible à travers le conteneur */
        }

        .login-container img {
            width: 600px;
            /* Image agrandie */
            margin-bottom: 30px;
            /* Espacement accru sous l'image */
        }

        .login-container h2 {
            margin-bottom: 40px;
            color: #164A9E;
            font-size: 6rem;
            /* Texte du titre plus grand */
        }

        .form-control {

            margin-bottom: 25px;
            height: 60px;
            /* Champs plus hauts */
            font-size: 2.5rem;
            /* Texte des champs plus grand */
            width: 130%;
            /* Réduction pour ajouter du vide autour */
            border-radius: 10px;
            /* Coins légèrement arrondis */
            padding: 60px;
            /* Espace interne accru */
            margin-left: -15%;
        }

        .btn-primary {
            background-color: #164A9E;
            border: none;
            height: 80px;
            /* Augmenté pour plus de visibilité */
            font-size: 2.5rem;
            /* Taille du texte */
            width: 100%;
            /* Largeur maximale */
            border-radius: 10px;
            /* Coins arrondis */
            display: flex;
            /* Flexbox pour centrer */
            justify-content: center;
            /* Centrer horizontalement */
            align-items: center;
            /* Centrer verticalement */
            padding: 8%;
            /* Retirer tout padding supplémentaire */
            color: white;
            /* Texte en blanc pour un meilleur contraste */
            cursor: pointer;
            margin-top: 20%;
            /* Ajout du curseur pour un effet interactif */
        }

        .btn-primary:hover {
            background-color: #0d3b82;
        }

        .help-text {
            margin-top: 200px;
            /* Espacement supplémentaire sous le bouton */
            font-size: 2.2rem;
            /* Texte de l'aide agrandi */
            color: #555;
        }

        .help-text a {
            color: #164A9E;
            text-decoration: none;
            font-weight: bold;
            /* Texte plus visible */

        }

        .help-text a:hover {
            text-decoration: underline;

        }

        footer {
            margin-top: 40px;
            /* Espace supplémentaire sous l'aide */
            font-size: 2.5rem;
            /* Texte de pied de page agrandi */
            color: #666;
        }

        .swal-popup {
            font-size: 50px;
            /* Modifier la taille de la police */
            padding: 45px;
            /* Modifier les espacements */
            color: #164A9E;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="logo.png" alt="Logo COFAT">
        <h2>S'authentifier Admin</h2>
        <form action="" method="POST">
            <input type="text" name="nom" class="form-control" placeholder="Nom d'utilisateur" required>
            <input type="password" name="mot_de_passe" class="form-control" placeholder="Mot de passe" required>
            <button type="submit" class="btn btn-primary">Connexion</button>
        </form>
        <div class="help-text">
            Besoin d'aide ? <a href="#">Contactez le service IT</a><br>
            Pour demander un accès, contactez le service RH.
        </div>
        <footer>
            L'utilisation de ce système est soumise aux politiques de sécurité de COFAT.
        </footer>
    </div>

    <script>
        <?php if ($message): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: '<?= htmlspecialchars($message) ?>',
                confirmButtonColor: '#164A9E',
                customClass: {
                    popup: 'swal-popup'
                }
            });
        <?php endif; ?>

        <?php if ($message_success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: '<?= htmlspecialchars($message_success) ?>',
                confirmButtonColor: '#164A9E',
                customClass: {
                    popup: 'swal-popup'
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>