<?php
require 'db.php'; 
require 'vendor/autoload.php'; 
use setasign\Fpdi\Fpdi;

class PDF extends Fpdi {
    function Header() {
        $this->SetFont('Arial', 'B', 16); 
        $this->Cell(190, 10, 'FACTURE', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 10);
        $this->Cell(195, 5, 'Galaxy Solutions, Bv Zerktouni Mohamedia, 0523312057, contact@galaxysolutions.ma', 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0);

if (!isset($_GET['num_facture']) || empty($_GET['num_facture'])) {
    die("num de facture invalide.");
}
$id_facture = $_GET['num_facture'];
$query = "SELECT c.nom, c.prenom, c.adresse, c.telephone, 
                 f.numero, f.date_creation, f.date_echeance, f.methode_paiement 
          FROM facture f 
          JOIN clients c ON f.id_client = c.id 
          WHERE f.num_facture= :num_facture";
$stmt = $db->prepare($query);
$stmt->bindParam(':num_facture', $num_facture, PDO::PARAM_INT);
$stmt->execute();
$facture = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$facture) {
    die("Facture introuvable.");
}

$pdf->Text(8, 38, 'Nom et Prénom du client: ' . utf8_decode($facture['nom'] . ' ' . $facture['prenom']));
$pdf->Text(8, 43, 'Adresse: ' . utf8_decode($facture['adresse']));
$pdf->Text(8, 48, 'Téléphone: ' . $facture['telephone']);


$pdf->Text(120, 48, 'Numéro de facture: ' . $facture['numero']);
$pdf->Text(120, 53, 'Date de création: ' . $facture['date_creation']);
$pdf->Text(120, 58, 'Date échéance: ' . $facture['date_echeance']);
$pdf->Text(120, 63, 'Méthode de paiement: ' . utf8_decode($facture['methode_paiement']));

$position_entete = 78;
function entete_table($position_entete) {
    global $pdf;
    $pdf->SetDrawColor(0); 
    $pdf->SetFillColor(221); 
    $pdf->SetTextColor(0);
    $pdf->SetY($position_entete);
    $pdf->SetX(8);

    $pdf->Cell(112, 8, 'Nom du produit', 1, 0, 'L', 1);
    $pdf->Cell(24, 8, 'Quantité', 1, 0, 'C', 1);
    $pdf->Cell(24, 8, 'Prix unitaire', 1, 0, 'C', 1);
    $pdf->Cell(24, 8, 'Total', 1, 1, 'C', 1);
}
entete_table($position_entete);
$query_produits = "SELECT p.nom, fp.quantite, fp.prix_unitaire 
                   FROM produits p 
                   JOIN produits p ON fp.id_produit = p.id 
                   WHERE p.num__facture = :num_facture";
$stmt = $db->prepare($query_produits);
$stmt->bindParam(':id_facture', $id_facture, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hauteur_ligne = 8;
$total_lignes = count($produits) + 1; 
$hauteur_totale = $total_lignes * $hauteur_ligne;

$pdf->Rect(8, $position_entete, 184, $hauteur_totale);

$pdf->SetFont('Arial', '', 12);
$pdf->SetY($position_entete + 8);
$pdf->SetX(8);

$total_facture = 0;
foreach ($produits as $produit) {
    $total_produit = $produit['quantite'] * $produit['prix_unitaire'];
    $total_facture += $total_produit;

    $pdf->Cell(112, 8, utf8_decode($produit['nom']), 1, 0, 'L');  
    $pdf->Cell(24, 8, $produit['quantite'], 1, 0, 'C');  
    $pdf->Cell(24, 8, number_format($produit['prix_unitaire'], 2) . '€', 1, 0, 'C');   
    $pdf->Cell(24, 8, number_format($total_produit, 2) . '€', 1, 1, 'C');  
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(160, 8, 'Total Facture', 1, 0, 'R', 1);
$pdf->Cell(24, 8, number_format($total_facture, 2) . '€', 1, 1, 'C', 1);

$pdf->Output('F', 'facture_test.pdf'); 
echo "PDF généré avec succès : facture_test.pdf";

?>
