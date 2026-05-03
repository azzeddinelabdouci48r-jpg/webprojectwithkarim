<?php
function getNumberClient($pdo){
    $stmt = $pdo->query("SELECT COUNT(*) FROM client");
    return $stmt->fetchColumn();
}
function getAllClients($pdo){
    $stmt = $pdo->query("SELECT * FROM client");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function deleteClient($pdo, $id){
    $stmt = $pdo->prepare("DELETE FROM client WHERE client_id = ?");
    return $stmt->execute([$id]);
}
function updateClient($pdo, $id, $nom, $adresse, $email){
    $stmt = $pdo->prepare("UPDATE client SET nom = ?, adresse = ?, email = ? WHERE client_id = ?");
    return $stmt->execute([$nom, $adresse, $email, $id]);
}