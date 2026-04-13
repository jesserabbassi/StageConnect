<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données envoyées par le JavaScript
    $titre = $_POST['titre'];
    $entreprise = $_POST['entreprise'];
    $lieu = $_POST['lieu'];
    $duree = $_POST['duree'];
    $description = $_POST['description'];
    $domaine = 'Informatique'; // Valeur par défaut

    // Insertion dans la base
    $sql = "INSERT INTO offers (title, description, company, location, domain, duration) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$titre, $description, $entreprise, $lieu, $domaine, $duree])) {
        // Au lieu de rediriger la page, on envoie un simple texte au JavaScript
        echo "success";
    } else {
        echo "error";
    }
}
?>