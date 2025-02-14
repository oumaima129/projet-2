<?php
require 'db.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "my_key";

header("Content-Type: application/json");

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = trim(str_replace('Bearer', '', $authHeader));

if (!$token) {
    die(json_encode(["error" => "Token manquant ou mal formé"]));
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_utilisateur = $decoded->id_utilisateur; 

    if (!$id_utilisateur) {
        die(json_encode(["error" => "Utilisateur non valide dans le token"]));
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id_client = $data['id_client'];
    $produits = $data['produits'];
    $etat = $data['etat'] ?? 'en attente';
    $methode_paiement=$data['methode_paiement'] ?? 'espèces';
    $date_echeance = $data['date_echeance'];

    echo "etat" . $etat;
    
    $montant_total = 0;
    foreach ($produits as $produit) {
        $stmt = $pdo->prepare("SELECT prix_unitaire FROM produits WHERE id_produit = ?");
        $stmt->execute([$produit['id_produit']]);
        $produit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produit_data) {
            $montant_total += $produit_data['prix_unitaire'] * $produit['quantité'];
        }
    }

    $stmt = $pdo->prepare("INSERT INTO facture (id_client, id_utilisateur, montant, etat, methode_paiement, date_echeance) 
                            VALUES (:idClient, :idUser, :montant, :etat, :methodPaiement, :date_echeance) ");

$stmt->bindParam(":idClient", $id_client, PDO::PARAM_INT);
$stmt->bindParam(":idUser", $id_utilisateur, PDO::PARAM_INT);
$stmt->bindParam(":montant", $montant_total, PDO::PARAM_STR);
$stmt->bindParam(":etat", $etat, PDO::PARAM_STR);
$stmt->bindParam(":methodPaiement", $methode_paiement, PDO::PARAM_STR);
$stmt->bindParam(":date_echeance", $date_echeance, PDO::PARAM_STR);
    if ($stmt->execute()) {
        $last_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM facture WHERE num_facture= ?");
        $stmt->execute([$last_id]);
        $facture = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            "message" => "Facture créée",
            "facture" => $facture
        ]);
    }else{
        echo json_encode(["error"=>"erreur lors de l'insertion des données","details"=>$stmt->errorInfo()]);
    } ;
} catch (Exception $e) {
    die(json_encode(["error" => "Token invalide", "details" => $e->getMessage()]));
}
?>
