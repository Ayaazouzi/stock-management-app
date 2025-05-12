<?php
session_start();

// Informations de connexion à la base de données
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "gestion de stock cofat";

// Création de la connexion
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Vérification de la connexion
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
    // Vérification que les champs sont remplis
    if (!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['mot_de_passe'])) {
        // Récupérer les valeurs envoyées par le formulaire
        $username = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $password = $_POST['mot_de_passe'];

        // Requête préparée pour éviter les injections SQL
        $stmt = $conn->prepare("SELECT id_user, password, role FROM user WHERE nom = ? AND prenom = ?");
        if (!$stmt) {
            die('Échec de la préparation de la requête : ' . $conn->error);
        }
        $stmt->bind_param("ss", $username, $prenom);
        $stmt->execute();
        $stmt->store_result();

        // Vérifier si un utilisateur correspondant existe
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id_user, $hashed_password, $role);
            $stmt->fetch();

            // Vérification du mot de passe
            if (password_verify($password, $hashed_password)) {
                // Enregistrer l'utilisateur dans la session
                $_SESSION['user_id'] = $id_user;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_name'] = $username;
                $_SESSION['user_prenom'] = $prenom;

                $message_success = "Connexion réussie ! Bienvenue, $prenom $username.";
                echo "<script>
                        setTimeout(() => {
                            window.location.href = 'dashboard user.php';
                        }, 2000);
                      </script>";
            } else {
                $message = "Mot de passe incorrect!";
            }
        } else {
            $message = "Nom d'utilisateur ou prénom introuvable!";
        }
        $stmt->close();
    } else {
        $message = "Tous les champs doivent être remplis.";
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User</title>
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
    filter: blur(8px); /* Applique un flou de 8px à l'image de fond */
    z-index: -1; /* L'image floue est derrière le contenu */
    animation: bgFlow 10s infinite linear; /* Applique l'animation */
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


.form-container {
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 20px; /* Coins plus arrondis pour un design moderne */
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Ombre plus marquée */
    padding: 300px; /* Augmenter le padding pour agrandir l'espace interne */
    width: 1300px; /* Conteneur plus large */
    text-align: center;
}

.form-container img {
    width: 400px;
    margin-bottom: 30px;
}

.form-container h2 {
    margin-bottom: 40px;
    color: #164A9E;
    font-size: 5rem; /* Ajuster la taille du titre */
}

.form-container input {
    margin-bottom: 25px;
    height: 60px;
    font-size: 2.5rem;
    width: 100%;
    border-radius: 10px;
    padding: 60px;
    border: 1px solid #ddd;
}

.form-container input:focus {
    outline: none;
    border-color: #164A9E;
}

.form-container button {
    background-color: #164A9E;
    border: none;
    height: 80px; /* Augmenté pour plus de visibilité */
    font-size: 2.5rem; /* Taille du texte */
    width: 100%; /* Largeur maximale */
    border-radius: 10px; /* Coins arrondis */
    display: flex; /* Flexbox pour centrer */
    justify-content: center; /* Centrer horizontalement */
    align-items: center; /* Centrer verticalement */
    padding: 8%; /* Retirer tout padding supplémentaire */
    color: white; /* Texte en blanc pour un meilleur contraste */
    cursor: pointer;
}

.form-container button:hover {
    background-color: #0d3b82;
}
.help-text {
    margin-top: 40px; /* Espacement supplémentaire sous le bouton */
    font-size: 2.2rem; /* Texte de l'aide agrandi */
    color: #555;
}

.help-text a {
    color: #164A9E;
    text-decoration: none;
    font-weight: bold; /* Texte plus visible */
    
}

.help-text a:hover {
    text-decoration: underline;
    
}

footer {
    margin-top: 40px; /* Espace supplémentaire sous l'aide */
    font-size: 2.5rem; /* Texte de pied de page agrandi */
    color: #666;
}

.swal-popup {
    font-size: 50px;
    padding: 30px;
    color: #164A9E;
}

        </style>
</head>

<body>
    <div class="form-container">
    <img src="logo.png">
       <h2>LOGIN Employé</h2>
        <form action="" method="POST">
            <input type="text" name="nom" placeholder="Nom d'utilisateur" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
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
                confirmButtonColor: '#164A9E',
                text: '<?= htmlspecialchars($message) ?>',
                customClass: {
                    popup: 'swal-popup'
                }
            });
        <?php endif; ?>

        <?php if ($message_success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                confirmButtonColor: '#164A9E', // Couleur du bouton OK

                text: '<?= htmlspecialchars($message_success) ?>',
                customClass: {
                    popup: 'swal-popup'
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>