<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php'; // Connexion à la BDD

// --- Récupérer les catégories ---
$categories = [];
$sql = "SELECT id_type, nom_type FROM types_produits ORDER BY nom_type ASC";
$result = $connexion->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// --- Gestion langue ---
$allowedLangs = ['fr','ar','en'];
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'fr';
$dir  = ($lang === 'ar') ? 'rtl' : 'ltr';

$isClient = isset($_SESSION['client']);

// --- Gestion filtres ---
$searchTerm = $_GET['q'] ?? '';
$searchTermEscaped = $connexion->real_escape_string($searchTerm);

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- Construire la requête dynamique selon filtres ---
$sqlProd = "SELECT p.id_produit, p.nom, p.prix, p.image, t.nom_type
            FROM produits p
            LEFT JOIN types_produits t ON p.id_type = t.id_type";

$whereClauses = [];
if ($categoryId > 0) {
    $whereClauses[] = "p.id_type = $categoryId";
}
if ($searchTerm !== '') {
    $whereClauses[] = "p.nom LIKE '%$searchTermEscaped%'";
}

if (count($whereClauses) > 0) {
    $sqlProd .= " WHERE " . implode(" AND ", $whereClauses);
}

$sqlProd .= " ORDER BY p.date_ajout DESC";

$resProd = $connexion->query($sqlProd);
$produits = [];
if ($resProd) {
    while ($row = $resProd->fetch_assoc()) {
        $produits[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES); ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>OCP Boutique - Accueil</title>
    <link rel="stylesheet" href="home_page_style.css" />
</head>
<body>

<!-- Barre blanche -->
<header class="top-green">
    <div class="header-left">
        <span class="header-title">OCP PRODUCTS</span>
    </div>
    <div class="header-right">
        <nav class="lang-switch" aria-label="Changer de langue">
            <a href="?lang=fr" class="lang-btn <?php echo $lang==='fr'?'active':''; ?>">FR</a>
            <a href="?lang=ar" class="lang-btn <?php echo $lang==='ar'?'active':''; ?>">AR</a>
            <a href="?lang=en" class="lang-btn <?php echo $lang==='en'?'active':''; ?>">EN</a>
        </nav>

        <nav class="auth-nav" aria-label="Authentification">
            <?php if ($isClient): ?>
                <a href="logout.php" class="auth-btn">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="auth-btn">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="transparent-bar">
    <a href="home_page.php" class="header-logo">
        <img src="images/ocp_logo2.svg" alt="OCP" />
    </a>

    <form action="home_page.php" method="GET" class="search-bar" role="search" aria-label="Barre de recherche">
        <input type="text" name="q" placeholder="Rechercher un produit..." value="<?php echo htmlspecialchars($searchTerm); ?>" />
        <?php if ($categoryId > 0): ?>
            <input type="hidden" name="id" value="<?php echo $categoryId; ?>" />
        <?php endif; ?>
        <button type="submit" class="search-button" aria-label="Rechercher">
            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 24 24">
                <path fill="white" d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.7.7l.27.28v.79l5 5 1.5-1.5-5-5zm-6 0a4.5 4.5 0 110-9 4.5 4.5 0 010 9z"/>
            </svg>
        </button>
        <!-- Garder la langue -->
        <?php if($lang): ?>
            <input type="hidden" name="lang" value="<?php echo htmlspecialchars($lang); ?>" />
        <?php endif; ?>
    </form>

    <div class="category-links">
        <?php foreach($categories as $cat): ?>
            <a href="home_page.php?id=<?php echo (int)$cat['id_type']; ?><?php echo $searchTerm !== '' ? '&q=' . urlencode($searchTerm) : ''; ?>" class="category-link<?php echo ($categoryId === (int)$cat['id_type']) ? ' active' : ''; ?>">
                <?php echo htmlspecialchars($cat['nom_type']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="cart-icon">
        <a href="panier.php" class="cart-link" aria-label="Voir le panier">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 24 24">
                <path d="M7 18c-1.104 0-1.99.896-1.99 2S5.896 22 7 22s2-.896 2-2-.896-2-2-2zm10 0c-1.104 0-1.99.896-1.99 2s.886 2 1.99 2 2-.896 2-2-.896-2-2-2zM7.334 6l.938 5h8.164l.75-4H7.334zM21 4H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.45C4.16 15.84 5.48 18 7.29 18H19v-2H7.42c-.14 0-.25-.11-.25-.25L7.7 13h9.55c.75 0 1.41-.41 1.75-1.03l3.58-6.49L21 4z"/>
            </svg>
            <?php
            $totalArticles = $_SESSION['panier_total'] ?? 0;
            if ($totalArticles > 0): ?>
                <span class="cart-count"><?php echo $totalArticles; ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Contenu des produits -->
<main class="page-content">
    <section class="product-grid">
        <?php if (empty($produits)): ?>
            <p>Aucun produit disponible<?php
                if ($searchTerm !== '') echo ' pour "' . htmlspecialchars($searchTerm) . '"';
                if ($categoryId > 0) {
                    $catNom = '';
                    foreach ($categories as $c) {
                        if ((int)$c['id_type'] === $categoryId) {
                            $catNom = $c['nom_type'];
                            break;
                        }
                    }
                    echo ($searchTerm !== '' ? ' et dans la catégorie ' : ' dans la catégorie ') . htmlspecialchars($catNom);
                }
            ?>.</p>
        <?php else: ?>
            <?php foreach ($produits as $prod): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="uploads/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['nom']); ?>" />
                    </div>
                    <h3 class="product-title"><?php echo htmlspecialchars($prod['nom']); ?></h3>
                    <p class="price"><?php echo number_format($prod['prix'], 2); ?> DH</p>
                    <form action="ajouter_panier.php" method="POST">
                        <input type="hidden" name="id_produit" value="<?php echo (int)$prod['id_produit']; ?>" />
                        <button type="submit" class="btn-add-cart">Ajouter au panier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

</body>
<script>
let lastScrollTop = 0;
const topBar = document.querySelector('.top-green');
const transparentBar = document.querySelector('.transparent-bar');

window.addEventListener('scroll', function() {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > lastScrollTop) {
        // Scroll vers le bas => cacher les barres
        topBar.style.transform = 'translateY(-100%)';
        transparentBar.style.transform = 'translateY(-100%)';
    } else {
        // Scroll vers le haut => afficher les barres
        topBar.style.transform = 'translateY(0)';
        transparentBar.style.transform = 'translateY(0)';
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // éviter valeur négative
});
</script>

</html>
