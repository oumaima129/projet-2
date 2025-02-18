<?php
require 'authorisation.php';
require 'db.php';
require 'vendor/autoload.php';
connexion();

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
    $id_utilisateur = $decoded->id_utilisateur ?? null;
    $role=$decoded->role ?? null;

    if (!$id_utilisateur || !$role) {
        die(json_encode(["error" => "Utilisateur non valide dans le token"]));
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $data = json_decode(file_get_contents("php://input"), true);

    if ($method === 'POST') {
        $id_client = $data['id_client'];
        $produits = $data['produits'];
        $etat = $data['etat'] ?? 'en attente';
        $methode_paiement = $data['methode_paiement'] ?? 'espèces';
        $date_echeance = $data['date_echeance'];

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
                                VALUES (:id_client, :id_utilisateur, : montant, :etat, :methode_paiement, :date_echeance)");
        if ($stmt->execute([$id_client, $id_utilisateur, $montant_total, $etat, $methode_paiement, $date_echeance])) {
            $last_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM facture WHERE num_facture= ?");
            $stmt->execute([$last_id]);
            $facture = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(["message" => "Facture créée", "facture" => $facture]);
        } else {
            echo json_encode(["error" => "Erreur lors de l'insertion", "details" => $stmt->errorInfo()]);
        }

    } elseif ($method === 'PUT') {
        $stmt=$pdo->prepare("SELECT id_utilisateur FROM facture WHERE num_facture= ?");
        $stmt->execute([$data['num_facture']]);
        $facture=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$facture){
            die(json_encode(["error"=>"facture introuvable"]));
        }
        if($role!=='administrateur' && $facture['id_utilisateur']!==$id_utilisateur){
            die(json_encode(["error"=>"Accès refusé:vous ne pouvez modifier que vos propres factures"]));
        }

        $stmt = $pdo->prepare("UPDATE facture SET etat=?, methode_paiement=?, date_echeance=? WHERE num_facture=?");
        $stmt->execute([$data['etat'], $data['methode_paiement'], $data['date_echeance'], $data['num_facture']]);
        echo json_encode(["message" => "Facture mise à jour"]);

    } elseif ($method === 'DELETE') {
        if (!isset($data['num_facture'])) {
            die(json_encode(["error" => "Numéro de facture manquant"]));
        }
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM facture WHERE num_facture= ?");
        $stmt->execute([$data['num_facture']]);
        $facture = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$facture) {
            die(json_encode(["error" => "Facture introuvable"]));
        }
        if($role!=='administrateur' && $facture['id_utilisateur']!==$id_utilisateur){
            die(json_encode(["error"=>"Accès refusé:vous ne pouvez supprimer  que vos propres factures"]));
        }
        $stmt = $pdo->prepare("DELETE FROM facture WHERE num_facture=?");
        $stmt->execute([$data['num_facture']]);
        echo json_encode(["message" => "Facture supprimée"]);

    } else {
        echo json_encode(["error" => "Méthode non autorisée"]);
    }

} catch (Exception $e) {
    die(json_encode(["error" => "Token invalide", "details" => $e->getMessage()]));
}
?>
