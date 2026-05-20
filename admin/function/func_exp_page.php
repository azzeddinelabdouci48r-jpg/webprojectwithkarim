<?php

// ============================================
// جلب كل expéditions مع تفاصيل commande + client + produit
// ============================================
function getAllExpeditions($pdo) {
    $stmt = $pdo->query("
        SELECT
            E.expedition_id,
            E.date_expedition,
            E.adresse_livraison,
            E.status_livraison,
            E.transporteur,
            E.numero_suivi,
            E.date_livraison,
            C.commande_id,
            C.date_commande,
            C.status        AS commande_status,
            C.quantite,
            C.prix_unitaire,
            (C.quantite * C.prix_unitaire) AS total,
            CL.nom          AS client_nom,
            CL.email        AS client_email,
            CL.adresse      AS client_adresse,
            P.nom           AS produit_nom
        FROM EXPEDITION E
        JOIN COMMANDE C  ON E.commande_id = C.commande_id
        JOIN CLIENT   CL ON C.client_id   = CL.client_id
        JOIN PRODUIT  P  ON C.produit_id  = P.produit_id
        ORDER BY E.expedition_id DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// جلب commandes التي ليس لها expédition بعد
// (لإنشاء expédition جديدة)
// ============================================
function getCommandesSansExpedition($pdo) {
    $stmt = $pdo->query("
        SELECT
            C.commande_id,
            C.date_commande,
            C.quantite,
            C.prix_unitaire,
            (C.quantite * C.prix_unitaire) AS total,
            CL.nom     AS client_nom,
            CL.adresse AS client_adresse,
            P.nom      AS produit_nom
        FROM COMMANDE C
        JOIN CLIENT  CL ON C.client_id  = CL.client_id
        JOIN PRODUIT P  ON C.produit_id = P.produit_id
        WHERE C.commande_id NOT IN (
            SELECT commande_id FROM EXPEDITION
        )
        AND C.status != 'annule'
        ORDER BY C.date_commande DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// إنشاء expédition جديدة
// ============================================
function addExpedition($pdo, $commande_id, $adresse_livraison, $transporteur, $numero_suivi) {
    $stmt = $pdo->prepare("
        INSERT INTO EXPEDITION (commande_id, adresse_livraison, transporteur, numero_suivi, status_livraison, date_expedition)
        VALUES (:commande_id, :adresse, :transporteur, :numero_suivi, 'en_preparation', NOW())
    ");
    $stmt->bindParam(':commande_id',  $commande_id);
    $stmt->bindParam(':adresse',      $adresse_livraison);
    $stmt->bindParam(':transporteur', $transporteur);
    $stmt->bindParam(':numero_suivi', $numero_suivi);
    $stmt->execute();

    // Mettre à jour le statut de la commande
    $stmt2 = $pdo->prepare("UPDATE COMMANDE SET status = 'confirme' WHERE commande_id = :id");
    $stmt2->bindParam(':id', $commande_id);
    $stmt2->execute();
}

// ============================================
// Modifier le statut de l'expédition
// ============================================
function updateExpedition($pdo, $expedition_id, $status_livraison, $transporteur, $numero_suivi, $date_livraison) {

    // Si livré et pas de date → mettre la date actuelle
    $date_liv = ($status_livraison === 'livre' && empty($date_livraison))
                ? date('Y-m-d H:i:s')
                : ($date_livraison ?: null);

    // ── 1. Mettre à jour EXPEDITION ──────────────────
    $stmt = $pdo->prepare("
        UPDATE EXPEDITION
        SET status_livraison = :status,
            transporteur     = :transporteur,
            numero_suivi     = :numero_suivi,
            date_livraison   = :date_livraison
        WHERE expedition_id  = :id
    ");
    $stmt->bindParam(':status',        $status_livraison);
    $stmt->bindParam(':transporteur',  $transporteur);
    $stmt->bindParam(':numero_suivi',  $numero_suivi);
    $stmt->bindParam(':date_livraison',$date_liv);
    $stmt->bindParam(':id',            $expedition_id);
    $stmt->execute();

    // ── 2. Récupérer le commande_id lié ──────────────
    $stmt2 = $pdo->prepare("SELECT commande_id FROM EXPEDITION WHERE expedition_id = :id");
    $stmt2->bindParam(':id', $expedition_id);
    $stmt2->execute();
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$row) return;
    $commande_id = $row['commande_id'];

    // ── 3. Synchroniser statut COMMANDE ──────────────
    // Correspondance : status_livraison → status commande
    $map = [
        'en_preparation' => 'confirme',
        'expedie'        => 'confirme',
        'en_transit'     => 'confirme',
        'livre'          => 'livre',
        'echec'          => 'en_attente',
    ];
    $statusCommande = $map[$status_livraison] ?? 'confirme';

    $stmt3 = $pdo->prepare("UPDATE COMMANDE SET status = :status WHERE commande_id = :id");
    $stmt3->bindParam(':status', $statusCommande);
    $stmt3->bindParam(':id',     $commande_id);
    $stmt3->execute();
}

// ============================================
// Supprimer une expédition
// ============================================
function deleteExpedition($pdo, $expedition_id) {

    // 1. Récupérer commande_id avant suppression
    $stmt = $pdo->prepare("SELECT commande_id FROM EXPEDITION WHERE expedition_id = :id");
    $stmt->bindParam(':id', $expedition_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Remettre commande en en_attente
    if ($row) {
        $stmt2 = $pdo->prepare("UPDATE COMMANDE SET status = 'en_attente' WHERE commande_id = :id");
        $stmt2->bindParam(':id', $row['commande_id']);
        $stmt2->execute();
    }

    // 3. Supprimer l'expédition
    $stmt3 = $pdo->prepare("DELETE FROM EXPEDITION WHERE expedition_id = :id");
    $stmt3->bindParam(':id', $expedition_id);
    $stmt3->execute();
}

// ============================================
// Stats expéditions
// ============================================
function getExpeditionsParStatus($pdo, $status) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM EXPEDITION WHERE status_livraison = :status");
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getExpeditionsAujourdhui($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM EXPEDITION WHERE DATE(date_expedition) = CURDATE()");
    return $stmt->fetchColumn();
}