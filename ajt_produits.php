<?php
require 'db.php'; // Connexion à la base de données

header("Content-Type: application/json");

try {
    // 🔹 Lire les données envoyées en JSON (depuis Postman)
    $data = json_decode(file_get_contents("php://input"), true);

    // 🔹 Vérifier les données
    if (!isset($data["num_facture"]) || !isset($data["produits"])) {
        echo json_encode(["status" => "error", "message" => "Données incomplètes"]);
        exit;
    }

    $num_facture = $data["num_facture"];
    $produits = $data["produits"]; // Liste des produits envoyée en JSON

    // 🔹 Vérifier si la facture existe
    $query_check_facture = "SELECT num_facture FROM facture WHERE num_facture = :num_facture";
    $stmt_check_facture = $pdo->prepare($query_check_facture);
    $stmt_check_facture->bindParam(":num_facture", $num_facture, PDO::PARAM_INT);
    $stmt_check_facture->execute();

    if ($stmt_check_facture->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Facture introuvable"]);
        exit;
    }

    // 🔹 Insérer chaque produit dans la table `facture_produit`
    $query_insert = "INSERT INTO facture_produit (num_facture, id_produit, quantite, prix_unitaire) 
                     VALUES (:num_facture, :id_produit, :quantite, :prix_unitaire)";
    $stmt_insert = $pdo->prepare($query_insert);

    foreach ($produits as $produit) {
        // Vérifier si le produit existe et récupérer son prix
        $query_produit = "SELECT prix_unitaire FROM produits WHERE id_produit = :id_produit";
        $stmt_produit = $pdo->prepare($query_produit);
        $stmt_produit->bindParam(":id_produit", $produit["id_produit"], PDO::PARAM_INT);
        $stmt_produit->execute();
        $produit_info = $stmt_produit->fetch(PDO::FETCH_ASSOC);

        if (!$produit_info) {
            echo json_encode(["status" => "error", "message" => "Produit ID " . $produit["id_produit"] . " introuvable"]);
            exit;
        }

        $prix_unitaire = $produit_info["prix_unitaire"];
        $quantite = $produit["quantite"];

        // 🔹 Insérer dans `facture_produit`
        $stmt_insert->bindParam(":num_facture", $num_facture, PDO::PARAM_INT);
        $stmt_insert->bindParam(":id_produit", $produit["id_produit"], PDO::PARAM_INT);
        $stmt_insert->bindParam(":quantite", $quantite, PDO::PARAM_INT);
        $stmt_insert->bindParam(":prix_unitaire", $prix_unitaire, PDO::PARAM_STR);
        $stmt_insert->execute();
    }

    echo json_encode(["status" => "success", "message" => "Produits ajoutés à la facture avec succès"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
