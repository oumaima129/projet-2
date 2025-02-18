<?php
require 'db.php'; 
header("Content-Type: application/json");
try {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data["num_facture"]) || !isset($data["produits"])) {
        echo json_encode(["status" => "error", "message" => "Données incomplètes"]);
        exit;
    }

    $num_facture = $data["num_facture"];
    $produits = $data["produits"];
    $sql_facture = "SELECT num_facture FROM facture WHERE num_facture = :num_facture";
    $stmt_facture = $pdo->prepare($sql_facture);
    $stmt_facture->bindParam(":num_facture", $num_facture, PDO::PARAM_INT);
    $stmt_facture->execute();

    if ($stmt_facture->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Facture introuvable"]);
        exit;
    }

    $sql_insert = "INSERT INTO facture_produit (num_facture, id_produit, quantite, prix_unitaire) 
                     VALUES (:num_facture, :id_produit, :quantite, :prix_unitaire)";
    $stmt_insert = $pdo->prepare($sql_insert);

    foreach ($produits as $produit) {
        $sql_produit = "SELECT prix_unitaire FROM produits WHERE id_produit = :id_produit";
        $stmt_produit = $pdo->prepare($sql_produit);
        $stmt_produit->bindParam(":id_produit", $produit["id_produit"], PDO::PARAM_INT);
        $stmt_produit->execute();
        $produit_info = $stmt_produit->fetch(PDO::FETCH_ASSOC);

        if (!$produit_info) {
            echo json_encode(["status" => "error", "message" => "Produit ID " . $produit["id_produit"] . " introuvable"]);
            exit;
        }

        $prix_unitaire = $produit_info["prix_unitaire"];
        $quantite = $produit["quantite"];
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
