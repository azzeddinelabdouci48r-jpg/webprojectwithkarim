<?php
// ============================================
// function/func_rapport_page.php
// ============================================

/**
 * إجمالي المبيعات حسب الفئة لفترة معينة
 */
function getVentesParCategorie($pdo, $date_debut, $date_fin) {
    $sql = "
        SELECT
            cat.nom AS categorie_nom,
            COUNT(DISTINCT co.commande_id)      AS nb_commandes,
            SUM(co.quantite)                    AS total_quantite,
            SUM(co.quantite * co.prix_unitaire) AS total_ventes
        FROM commande co
        JOIN produit p    ON co.produit_id   = p.produit_id
        LEFT JOIN categorie cat ON p.categorie_id = cat.categorie_id
        WHERE co.status != 'annule'
          AND DATE(co.date_commande) BETWEEN :debut AND :fin
        GROUP BY cat.categorie_id, cat.nom
        ORDER BY total_ventes DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':debut' => $date_debut, ':fin' => $date_fin]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Commandes en attente de livraison (en_attente OU confirme)
 */
function getCommandesEnAttenteLivraison($pdo) {
    $sql = "
        SELECT
            co.commande_id,
            co.quantite,
            co.prix_unitaire,
            (co.quantite * co.prix_unitaire) AS total,
            co.date_commande,
            co.status,
            cl.nom   AS client_nom,
            cl.email AS client_email,
            p.nom    AS produit_nom
        FROM commande co
        JOIN client  cl ON co.client_id  = cl.client_id
        JOIN produit p  ON co.produit_id = p.produit_id
        WHERE co.status IN ('en_attente', 'confirme')
        ORDER BY co.date_commande ASC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}