<?php
// ============================================
// AI Auto-Categorizer & SEO Tag Generator
// Powered by Groq API (Free Tier - Fast!)
// ============================================

header('Content-Type: application/json; charset=utf-8');

// Gestion CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ── Lecture des données JSON ──
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !is_array($data)) {
    echo json_encode(['error' => 'Données JSON invalides ou vides'], JSON_UNESCAPED_UNICODE);
    exit();
}

$nom  = trim($data['nom']  ?? '');
$desc = trim($data['desc'] ?? '');

if (empty($nom)) {
    echo json_encode(['error' => 'Veuillez remplir le nom du produit'], JSON_UNESCAPED_UNICODE);
    exit();
}

$text = $nom . ' ' . $desc;

// ── Groq API Configuration ──
// 1. Allez sur: https://console.groq.com
// 2. Créez un compte (email ou Google)
// 3. Générez une API key
// 4. Remplacez ci-dessous:
$GROQ_API_KEY = 'gsk_VOTRE_CLE_GROQ_ICI'; // ← METTEZ VOTRE CLÉ ICI

$apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

$systemPrompt = 'Tu es un expert en e-commerce. Tu réponds UNIQUEMENT en JSON valide.';

$userPrompt = 'Classifie ce produit dans UNE SEULE catégorie parmi: Téléphones, Ordinateurs, Accessoires Informatique, Écrans & TV, Audio, Jeux Vidéo, Vêtements, Chaussures, Électroménager, Meuble & Déco, Alimentation, Sport & Fitness, Beauté & Santé, Jouets & Enfants, Général.

Produit: "' . $nom . '"
Description: "' . $desc . '"

Réponds JSON: {"categorie":"...","tags":["tag1","tag2","tag3","tag4","tag5"],"confidence":85}';

$payload = [
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt]
    ],
    'temperature' => 0.3,
    'max_tokens' => 200
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $GROQ_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ── Gestion des erreurs ──
if ($curlError) {
    echo json_encode([
        'error' => 'Erreur de connexion: ' . $curlError,
        'fallback' => true,
        'categorie' => detectCategorieFallback($text),
        'tags' => generateTagsFallback($text),
        'source' => 'fallback'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($httpCode === 429) {
    echo json_encode([
        'error' => 'Limite Groq atteinte (429). Attendez 1 minute.',
        'fallback' => true,
        'categorie' => detectCategorieFallback($text),
        'tags' => generateTagsFallback($text),
        'source' => 'fallback'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($httpCode !== 200) {
    echo json_encode([
        'error' => "Erreur API Groq (HTTP $httpCode).",
        'fallback' => true,
        'categorie' => detectCategorieFallback($text),
        'tags' => generateTagsFallback($text),
        'source' => 'fallback'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$result = json_decode($response, true);
$content = $result['choices'][0]['message']['content'] ?? '';

// Extraire le JSON
preg_match('/\{.*\}/s', $content, $matches);
$aiResult = json_decode($matches[0] ?? '{}', true);

if (empty($aiResult['categorie'])) {
    echo json_encode([
        'error' => 'Réponse AI invalide',
        'fallback' => true,
        'categorie' => detectCategorieFallback($text),
        'tags' => generateTagsFallback($text),
        'source' => 'fallback'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// ── Retourner le résultat ──
echo json_encode([
    'categorie'  => $aiResult['categorie'],
    'tags'       => $aiResult['tags'] ?? generateTagsFallback($text),
    'confidence' => ($aiResult['confidence'] ?? 80) . '%',
    'source'     => 'groq'
], JSON_UNESCAPED_UNICODE);


// ============================================
// Fonctions Fallback
// ============================================

function detectCategorieFallback($text) {
    $text = strtolower($text);
    $rules = [
        'Téléphones' => ['phone','smartphone','iphone','samsung','android','mobile','gsm','4g','5g','sim','téléphone'],
        'Ordinateurs' => ['laptop','pc','ordinateur','macbook','dell','hp','lenovo','ram','ssd','processeur'],
        'Accessoires Informatique' => ['clavier','souris','keyboard','mouse','webcam','usb','casque','câble','hdmi'],
        'Écrans & TV' => ['écran','moniteur','tv','télévision','4k','oled','qled','led','display'],
        'Audio' => ['écouteur','casque','enceinte','speaker','bluetooth','airpod','audio','son','musique'],
        'Jeux Vidéo' => ['gaming','gamer','playstation','xbox','nintendo','manette','console','jeu'],
        'Vêtements' => ['tshirt','chemise','pantalon','veste','pull','robe','jupe','jean','vêtement'],
        'Chaussures' => ['chaussure','basket','sneaker','boot','sandale','nike','adidas','puma'],
        'Électroménager' => ['frigo','machine à laver','four','micro-onde','aspirateur','climatiseur','cafetière'],
        'Meuble & Déco' => ['meuble','table','chaise','canapé','bureau','armoire','lampe','déco'],
        'Alimentation' => ['alimentaire','nourriture','café','thé','biscuit','chocolat','boisson','jus'],
        'Sport & Fitness' => ['sport','fitness','gym','musculation','yoga','vélo','haltère','course'],
        'Beauté & Santé' => ['crème','parfum','shampoing','maquillage','soin','beauté','santé','vitamine'],
        'Jouets & Enfants' => ['jouet','enfant','bébé','poupée','puzzle','lego','peluche'],
    ];

    $bestCat = 'Général'; $bestScore = 0;
    foreach ($rules as $cat => $keywords) {
        $score = 0;
        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false) $score++;
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestCat = $cat;
        }
    }
    return $bestCat;
}

function generateTagsFallback($text) {
    $text = strtolower($text);
    $words = preg_split('/[\s\-_]+/', $text);
    $tags = [];
    foreach ($words as $word) {
        $word = preg_replace('/[^a-zàâçéèêëîïôûùüÿñœæ]/i', '', $word);
        if (strlen($word) > 3 && !in_array(ucfirst($word), $tags)) {
            $tags[] = ucfirst($word);
        }
    }
    return array_slice($tags, 0, 8);
}
?>