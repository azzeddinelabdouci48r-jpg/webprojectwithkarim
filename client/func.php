<?php

// ============================================
// جلب كل المنتجات المتوفرة (stock > 0)
// ============================================
function getAllProducts($pdo) {
    $stmt = $pdo->query('SELECT * FROM PRODUIT WHERE stock > 0 ORDER BY produit_id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// إضافة طلب واحد في جدول COMMANDE
// ============================================
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