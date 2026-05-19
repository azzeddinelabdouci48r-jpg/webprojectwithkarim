<?php

// ============================================
// عدد كل المنتجات
// ============================================
function getNumberProducts($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM PRODUIT');
    return $stmt->fetchColumn();
}

// ============================================
// عدد المنتجات المتوفرة (stock > 0)
// ============================================
function getNumberInStock($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM PRODUIT WHERE stock > 0');
    return $stmt->fetchColumn();
}

// ============================================
// عدد المنتجات المنتهية (stock = 0)
// ============================================
function getNumberOutOfStock($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM PRODUIT WHERE stock = 0');
    return $stmt->fetchColumn();
}

// ============================================
// جلب كل المنتجات مع categorie و tags
// ============================================
function getAllProducts($pdo) {
    $stmt = $pdo->query('
        SELECT
            P.*,
            C.nom AS categorie_nom,
            GROUP_CONCAT(T.tag ORDER BY T.tag SEPARATOR ", ") AS tags
        FROM PRODUIT P
        LEFT JOIN CATEGORIE    C ON P.categorie_id = C.categorie_id
        LEFT JOIN PRODUIT_TAGS T ON P.produit_id   = T.produit_id
        GROUP BY P.produit_id
        ORDER BY P.produit_id DESC
    ');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// جلب أو إنشاء categorie
// ============================================
function getOrCreateCategorie($pdo, $nom_categorie) {
    $nom_categorie = trim($nom_categorie);

    $stmt = $pdo->prepare('SELECT categorie_id FROM CATEGORIE WHERE nom = :nom');
    $stmt->bindParam(':nom', $nom_categorie);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) return $row['categorie_id'];

    $stmt = $pdo->prepare('INSERT INTO CATEGORIE (nom) VALUES (:nom)');
    $stmt->bindParam(':nom', $nom_categorie);
    $stmt->execute();
    return $pdo->lastInsertId();
}

// ============================================
// حفظ tags للمنتج
// ============================================
function saveTags($pdo, $produit_id, $tags_array) {
    $stmt = $pdo->prepare('DELETE FROM PRODUIT_TAGS WHERE produit_id = :id');
    $stmt->bindParam(':id', $produit_id);
    $stmt->execute();

    $stmt = $pdo->prepare('INSERT INTO PRODUIT_TAGS (produit_id, tag) VALUES (:produit_id, :tag)');
    foreach ($tags_array as $tag) {
        $tag = trim($tag);
        if ($tag !== '') {
            $stmt->bindParam(':produit_id', $produit_id);
            $stmt->bindParam(':tag', $tag);
            $stmt->execute();
        }
    }
}

// ============================================
// إضافة منتج
// ============================================
function addProduct($pdo, $nom, $description, $prix, $categorie_nom, $stock, $tags_array) {
    $categorie_id = getOrCreateCategorie($pdo, $categorie_nom);

    $stmt = $pdo->prepare('
        INSERT INTO PRODUIT (nom, description, prix, categorie_id, stock)
        VALUES (:nom, :description, :prix, :categorie_id, :stock)
    ');
    $stmt->bindParam(':nom',          $nom);
    $stmt->bindParam(':description',  $description);
    $stmt->bindParam(':prix',         $prix);
    $stmt->bindParam(':categorie_id', $categorie_id);
    $stmt->bindParam(':stock',        $stock);
    $stmt->execute();

    saveTags($pdo, $pdo->lastInsertId(), $tags_array);
}

// ============================================
// تعديل منتج
// ============================================
function updateProduct($pdo, $id, $nom, $description, $prix, $categorie_nom, $stock, $tags_array) {
    $categorie_id = getOrCreateCategorie($pdo, $categorie_nom);

    $stmt = $pdo->prepare('
        UPDATE PRODUIT
        SET nom = :nom, description = :description, prix = :prix,
            categorie_id = :categorie_id, stock = :stock
        WHERE produit_id = :id
    ');
    $stmt->bindParam(':nom',          $nom);
    $stmt->bindParam(':description',  $description);
    $stmt->bindParam(':prix',         $prix);
    $stmt->bindParam(':categorie_id', $categorie_id);
    $stmt->bindParam(':stock',        $stock);
    $stmt->bindParam(':id',           $id);
    $stmt->execute();

    saveTags($pdo, $id, $tags_array);
}

// ============================================
// حذف منتج
// ============================================
function deleteProduct($pdo, $id) {
    $stmt = $pdo->prepare('DELETE FROM PRODUIT WHERE produit_id = :id');
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}