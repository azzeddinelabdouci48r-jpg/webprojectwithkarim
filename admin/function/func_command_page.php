<?php

// ============================================
// عدد commandes اليوم
// ============================================
function getCommandesAujourdhui($pdo) {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM COMMANDE
        WHERE DATE(date_commande) = CURDATE()
    ");
    return $stmt->fetchColumn();
}

// ============================================
// عدد commandes آخر 7 أيام
// ============================================
function getCommandesSemaine($pdo) {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM COMMANDE
        WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    return $stmt->fetchColumn();
}

// ============================================
// عدد commandes آخر 30 يوم
// ============================================
function getCommandesMois($pdo) {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM COMMANDE
        WHERE date_commande >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    return $stmt->fetchColumn();
}

// ============================================
// عدد commandes حسب الحالة
// ============================================
function getCommandesParStatus($pdo, $status) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM COMMANDE WHERE status = :status
    ");
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// ============================================
// جلب كل commandes مع تفاصيل client و produit
// ============================================
function getAllCommandes($pdo) {
    $stmt = $pdo->query("
        SELECT
            C.commande_id,
            C.quantite,
            C.prix_unitaire,
            C.date_commande,
            C.status,
            CL.nom   AS client_nom,
            CL.email AS client_email,
            P.nom    AS produit_nom,
            (C.quantite * C.prix_unitaire) AS total
        FROM COMMANDE C
        JOIN CLIENT  CL ON C.client_id  = CL.client_id
        JOIN PRODUIT P  ON C.produit_id = P.produit_id
        ORDER BY C.date_commande DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// ============================================
// changer le statut d'une commande
// ============================================
function updateStatutCommande($pdo, $commande_id, $status) {
    $stmt = $pdo->prepare("
        UPDATE COMMANDE SET status = :status WHERE commande_id = :id
    ");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id',     $commande_id);
    $stmt->execute();
}