<?php
require 'authorisation.php';
verificationRole('administrateur');
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];

    try {
        $sql = "DELETE FROM client WHERE id_client = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        echo "Client supprimé avec succès.";
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression du client : " . $e->getMessage();
    }
}
?>
