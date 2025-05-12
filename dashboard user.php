<?php 
// Paramètres de connexion
$host = "localhost";
$dbname = "gestion de stock cofat";
$username = "root";
$password = "";

try {
    // Connexion à la base de données
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
   
    // Récupérer le nombre total de fournisseurs
    $stmtFournisseurs = $conn->query("SELECT COUNT(*) AS total FROM fournisseurs");
    $totalFournisseurs = $stmtFournisseurs->fetch(PDO::FETCH_ASSOC)['total'];

    // Récupérer le nombre total de catégories
    $stmtCategories = $conn->query("SELECT COUNT(*) AS total FROM categories");
    $totalCategories = $stmtCategories->fetch(PDO::FETCH_ASSOC)['total'];

    // Calculer le budget total
    $stmtBudget = $conn->query("SELECT SUM(prix * quantite) AS total FROM articles");
    $totalBudget = $stmtBudget->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

    // Récupérer le nombre total d'articles
    $stmtArticles = $conn->query("SELECT COUNT(*) AS total FROM articles");
    $totalArticles = $stmtArticles->fetch(PDO::FETCH_ASSOC)['total'];

    // Récupérer les données pour le graphique
    $stmtGraphique = $conn->query("SELECT nom, quantite FROM articles");
    $dataGraphique = $stmtGraphique->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion de Stock Cofat</title>
    <!-- Liens Bootstrap et Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding: 20px;
            width: 10%;
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
        .sidebar h2 {
            color: white;
            font-size: 70px;
            font-weight: bold;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            padding: 30px;
        }
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            padding: 50px;
            text-align: center;
        }
        .stat-card h3 {
            margin-bottom: 15px;
            font-size: 50px;
        }
        .stat-card p {
            font-size: 50px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }
        img.c,img.a,img.f,img.b {
            width:20%;
        }
        .export-btn {
    font-size: 4rem; /* Augmente la taille de la police */
    padding: 40px 40px; /* Augmente l'espacement interne du bouton */
    position: absolute; /* Positionne le bouton de manière absolue */
    top: 20px; /* Distance par rapport au haut de la page */
    right: 20px; /* Distance par rapport au côté droit */
    background-color: #164A9E; /* Couleur de fond verte (style bootstrap) */
    color: white; /* Couleur du texte */
    border: none; /* Retire les bordures supplémentaires */
    border-radius: 8px; /* Ajoute des coins arrondis */
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2); /* Ajoute une ombre pour le style */
    text-align: center; /* Centre le texte */
    text-decoration: none; /* Retire la sous-ligne */
}

.export-btn:hover {
    background-color: #164A9E; /* Change la couleur au survol */
    box-shadow: 0px 6px 8px rgba(0, 0, 0, 0.3); /* Augmente l'ombre au survol */
}


    </style>
</head>
<body>

<a href="export_excel.php" class=" export-btn">Exporter en Excel</a>

<div class="container-fluid">
    <div class="row">
        <!-- Barre latérale -->
        <div class="col-md-3 sidebar">
        <img src="logo.png">
            <a href="dashboard.php">Dashboard</a>
            <a href="article.php">Articles</a>
            <a href="stock.php">Stock</a>
            <a href="page home.html">se Deconnecter</a>
        </div>
        <!-- Contenu principal -->
        <div class="col-md-9 main-content">
            <div class="row">
                <div class="col-12 mb-4">
                    <h1 style="font-size: 60px;">Dashboard</h1>
                </div>
            </div>
            <div class="row">
                <!-- Cartes statistiques sur une seule ligne -->
                <div class="col-md-3">
                    <div class="stat-card">
                        <img class="f"src="f" alt="Fournisseur">
                        <h3>Fournisseurs</h3>
                        <p><?= $totalFournisseurs ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <img class="c"src="c" alt="Catégorie">
                        <h3>Catégories</h3>
                        <p><?= $totalCategories ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <img class="a" src="a" alt="Article">
                        <h3>Articles</h3>
                        <p><?= $totalArticles ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <img class="b" src="b" alt="Budget">
                        <h3>Budget Total</h3>
                        <p><?= number_format($totalBudget, 2) ?> DNT</p>
                    </div>
                </div>
            </div>
            <!-- Graphique -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="card-title" style="font-size: 60px;">Graphique des Stocks COFAT</h1>
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const labels = <?= json_encode(array_column($dataGraphique, 'nom')) ?>;
    const data = <?= json_encode(array_column($dataGraphique, 'quantite')) ?>;
    const ctx = document.getElementById('stockChart').getContext('2d');

    const stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantité en Stock',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            devicePixelRatio: 2,
            
            plugins: {
                legend: {
                    position: 'top',
                    
                },
            },
            scales: {
                y: {
                    beginAtZero: true
                    
                }
            }
        }
    });

    
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
