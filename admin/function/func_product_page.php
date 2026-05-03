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
// جلب كل المنتجات
// ============================================
function getAllProducts($pdo) {
    $stmt = $pdo->query('SELECT * FROM PRODUIT ORDER BY produit_id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// إضافة منتج
// ============================================
function addProduct($pdo, $nom, $description, $prix, $categorie, $stock) {
    $stmt = $pdo->prepare('INSERT INTO PRODUIT (nom, description, prix, categorie, stock)
                           VALUES (:nom, :description, :prix, :categorie, :stock)');
    $stmt->bindParam(':nom',         $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':prix',        $prix);
    $stmt->bindParam(':categorie',   $categorie);
    $stmt->bindParam(':stock',       $stock);
    $stmt->execute();
}

// ============================================
// تعديل منتج
// ============================================
function updateProduct($pdo, $id, $nom, $description, $prix, $categorie, $stock) {
    $stmt = $pdo->prepare('UPDATE PRODUIT SET nom = :nom, description = :description,
                           prix = :prix, categorie = :categorie, stock = :stock
                           WHERE produit_id = :id');
    $stmt->bindParam(':nom',         $nom);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':prix',        $prix);
    $stmt->bindParam(':categorie',   $categorie);
    $stmt->bindParam(':stock',       $stock);
    $stmt->bindParam(':id',          $id);
    $stmt->execute();
}

// ============================================
// حذف منتج
// ============================================
function deleteProduct($pdo, $id) {
    $stmt = $pdo->prepare('DELETE FROM PRODUIT WHERE produit_id = :id');
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}