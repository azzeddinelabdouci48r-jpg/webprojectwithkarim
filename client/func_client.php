<?php

function getAllProducts($pdo) {
    $stmt = $pdo->query('SELECT * FROM PRODUIT WHERE stock > 0 ORDER BY produit_id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCommande($pdo, $client_id, $produit_id, $quantite, $prix_unitaire) {
    $stmt = $pdo->prepare('
        INSERT INTO COMMANDE (client_id, produit_id, quantite, prix_unitaire, date_commande, status)
        VALUES (:client_id, :produit_id, :quantite, :prix_unitaire, NOW(), "en_attente")
    ');
    $stmt->bindParam(':client_id',     $client_id);
    $stmt->bindParam(':produit_id',    $produit_id);
    $stmt->bindParam(':quantite',      $quantite);
    $stmt->bindParam(':prix_unitaire', $prix_unitaire);
    $stmt->execute();
}


function getCommandesByClient($pdo, $client_id) {
    $stmt = $pdo->prepare('
        SELECT C.*, P.nom AS produit_nom
        FROM COMMANDE C
        JOIN PRODUIT P ON C.produit_id = P.produit_id
        WHERE C.client_id = :client_id
        ORDER BY C.date_commande DESC
    ');
    $stmt->bindParam(':client_id', $client_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function annulerCommande($pdo, $commande_id, $client_id) {
    $stmt = $pdo->prepare('
        UPDATE COMMANDE
        SET status = "annule"
        WHERE commande_id = :commande_id
          AND client_id   = :client_id
          AND status      = "en_attente"
    ');
    $stmt->bindParam(':commande_id', $commande_id);
    $stmt->bindParam(':client_id',   $client_id);
    $stmt->execute();
}