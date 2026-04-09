<?php
// Fichier : ajouter-offre.php

// 1. On appelle le fichier de connexion à la base de données
require 'db.php';

// 2. On vérifie si les données ont bien été envoyées par le formulaire (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 3. On récupère les données grâce aux attributs "name" du formulaire HTML
    $titre = $_POST['titre'];
    $entreprise = $_POST['entreprise'];
    $lieu = $_POST['lieu'];
    $duree = $_POST['duree'];
    $description = $_POST['description'];
    
    // Pour ton SQL, on ajoute des valeurs par défaut pour les champs manquants du formulaire
    $domaine = 'Informatique'; // Valeur par défaut

    // 4. On prépare la requête SQL (les "?" protègent contre les piratages)
    $sql = "INSERT INTO offers (title, description, company, location, domain, duration) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    
    // 5. On exécute la requête avec les données
    $stmt->execute([$titre, $description, $entreprise, $lieu, $domaine, $duree]);

    // 6. Une fois terminé, on redirige l'utilisateur vers la page HTML d'origine
    header("Location: ../Pages/Admin-Dashboard.html");
    exit();
} else {
    // Si quelqu'un essaie d'accéder à cette page sans passer par le formulaire
    echo "Accès refusé.";
}
?>