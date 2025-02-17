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
$num_facture = $_GET['num_facture'];
$query = "SELECT c.nom, c.prenom, c.adresse, c.telephone, 
                 f.num_facture, f.date_creation, f.date_echeance, f.methode_paiement 
          FROM facture f 
          JOIN client c ON f.id_client = c.id_client
          WHERE f.num_facture = :num_facture";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':num_facture', $num_facture, PDO::PARAM_INT);
$stmt->execute();
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    die("Facture introuvable.");
}

$pdf->Text(8, 38, 'Nom et Prenom du client: ' . mb_convert_encoding($facture['nom'] . ' ' . $facture['prenom'], 'ISO-8859-1', 'UTF-8'));
$pdf->Text(8, 43, 'Adresse: ' . mb_convert_encoding($facture['adresse'], 'ISO-8859-1', 'UTF-8'));
$pdf->Text(8, 48, 'Telephone: ' . $facture['telephone']);
$pdf->Text(120, 48, 'Numero de facture: ' . $facture['num_facture']);
$pdf->Text(120, 53, 'Date de creation: ' . $facture['date_creation']);
$pdf->Text(120, 58, 'Date echeance: ' . $facture['date_echeance']);
$pdf->Text(120, 63, 'Methode de paiement: ' . mb_convert_encoding($facture['methode_paiement'], 'ISO-8859-1', 'UTF-8'));

$position_entete = 78;
function entete_table($position_entete) {
    global $pdf;
    $pdf->SetDrawColor(0); 
    $pdf->SetFillColor(221); 
    $pdf->SetTextColor(0);
    $pdf->SetY($position_entete);
    $pdf->SetX(8);
    $w1 = 112; 
    $w2 = 24;  
    $w3 = 24;  
    $w4 = 24;  
    $pdf->Cell($w1, 8, 'Nom du produit', 1, 0, 'L', 1);
    $pdf->Cell($w2, 8, 'Quantite', 1, 0, 'C', 1);
    $pdf->Cell($w3, 8, 'Prix unitaire', 1, 0, 'C', 1);
    $pdf->Cell($w4, 8, 'Total', 1, 1, 'C', 1);
}
entete_table($position_entete);

$query_produits = "SELECT p.nom, fp.quantite, fp.prix_unitaire 
                   FROM facture_produit fp 
                   JOIN produits p ON fp.id_produit = p.id_produit
                   WHERE fp.num_facture = :num_facture";
$stmt = $pdo->prepare($query_produits);
$stmt->bindParam(':num_facture', $num_facture, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($produits)) {
    die("Aucun produit trouve pour cette facture.");
}

$hauteur_ligne = 8;
$total_lignes = count($produits) + 1; 
$hauteur_totale = $total_lignes * $hauteur_ligne;
$pdf->SetFont('Arial', '', 12);
$pdf->SetY($position_entete + 8);
$pdf->SetX(8);

$total_facture = 0;
foreach ($produits as $produit) {
    $total_produit = $produit['quantite'] * $produit['prix_unitaire'];
    $total_facture += $total_produit;
    $y_position=$pdf->GetY();
    $pdf->SetX(8);
    $pdf->MultiCell(112,8,mb_convert_encoding($produit['nom'],'ISO-8859-1','UTF-8'),1,'L');
    $ligne_height=$pdf->GetY() -$y_position;
    $pdf->SetY($y_position);
    $pdf->SetX(120); 
    $pdf->Cell(24, $ligne_height, $produit['quantite'], 1, 0, 'C');  
    $pdf->Cell(24, $ligne_height, number_format($produit['prix_unitaire'], 2) . 'dh', 1, 0, 'C');   
    $pdf->Cell(24, $ligne_height, number_format($total_produit, 2) . 'dh', 1, 1, 'C');  
}
$pdf->Rect(8, $position_entete, 184, $pdf->GetY() -$position_entete);
$y_position_total = $pdf->GetY();
$ligne_height_total = 8; 
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetX(8);
$pdf->Cell(112 + 24 + 24, $ligne_height_total, 'Total Facture', 1, 0, 'R', 1);
$pdf->Cell(24, $ligne_height_total, number_format($total_facture, 2) . 'dh', 1, 1, 'C', 1);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="facture_' . $num_facture . '.pdf"');
$pdf->Output('D'); 
?>