<?php
require 'db.php';
try{
    $sql_total="SELECT COUNT(*) AS total_factures FROM facture";
    $stmt_total=$pdo->prepare($sql_total);
    $stmt_total->execute();
    $total_factures=$stmt_total->fetch(PDO::FETCH_ASSOC)['total_factures'];

    $sql_impayees="SELECT COUNT(*) AS factures_impayees FROM facture WHERE etat='impayée' ";
    $stmt_impayees=$pdo->prepare($sql_impayees);
    $stmt_impayees->execute();
    $factures_impayees=$stmt_impayees->fetch(PDO::FETCH_ASSOC)['factures_impayees'];

    $sql_payes="SELECT COUNT(*) AS factures_payes FROM facture WHERE etat='payé'";
    $stmt_payes=$pdo->prepare($sql_payes);
    $stmt_payes->execute();
    $facture_payes=$stmt_payes->fetch(PDO::FETCH_ASSOC)['factures_payes'];

    $sql_montant="SELECT SUM(montant) AS montant_total FROM facture";
    $stmt_montant=$pdo->prepare($sql_montant);
    $stmt_montant->execute();
    $montant=$stmt_montant->fetch(PDO::FETCH_ASSOC)['montant_total'];

    $resultat=[
        'total_factures'=>$total_factures,
        'factures_impayees'=>$factures_impayees,
        'factures_payes'=>$facture_payes,
        'montant_total'=>$montant ?? 0
    ];
    header ('Content-Type:application/json');
    echo json_encode($resultat);

}catch(PDOException $e){
    echo json_encode(['error'=>'Erreur:'.$e->getMessage()]);
}
?>