<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
$role = $_SESSION['role'] ?? 'normal';

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'fr';
}

$traductions = [
    'fr' => [
        'welcome_msg' => 'Gestion des Produits',
        'gestion_admins' => 'Gérer les administrateurs',
        'ajouter_produit' => '+ Ajouter un produit',
        'modifier_produit' => 'Modifier le produit',
        'supprimer' => 'Supprimer',
        'nom' => 'Nom',
        'prix' => 'Prix',
        'quantite' => 'Quantité',
        'categorie' => 'Catégorie',
        'image' => 'Image',
        'ajouter' => 'Ajouter',
        'fermer' => 'Fermer',
        'produit_ajoute' => 'Produit ajouté avec succès.',
        'erreur_ajout' => 'Erreur lors de l\'ajout.',
        'produit_modifie' => 'Produit modifié avec succès.',
        'produit_supprime' => 'Produit supprimé.',
        'confirmation_suppression' => 'Voulez-vous vraiment supprimer ce produit ?',
        'choose_file' => 'Choisir un fichier',
        'annuler' => 'Annuler',
        // Profil
        'profil_titre' => 'Profil Admin',
        'changer_mdp' => 'Changer le mot de passe',
        'ancien_mdp' => 'Ancien mot de passe',
        'nouveau_mdp' => 'Nouveau mot de passe',
        'confirmer_mdp' => 'Confirmer le nouveau mot de passe',
        'btn_changer_mdp' => 'Changer le mot de passe',
        'mdp_change_ok' => 'Mot de passe changé avec succès.',
        'mdp_erreur_ancien' => 'L\'ancien mot de passe est incorrect.',
        'mdp_erreur_conf' => 'La confirmation ne correspond pas au nouveau mot de passe.',
    ],
    'en' => [
        'welcome_msg' => 'Product Management',
        'gestion_admins' => 'Manage Administrators',
        'ajouter_produit' => '+ Add Product',
        'modifier_produit' => 'Edit Product',
        'supprimer' => 'Delete',
        'nom' => 'Name',
        'prix' => 'Price',
        'quantite' => 'Quantity',
        'categorie' => 'Category',
        'image' => 'Image',
        'ajouter' => 'Add',
        'fermer' => 'Close',
        'produit_ajoute' => 'Product successfully added.',
        'erreur_ajout' => 'Error while adding.',
        'produit_modifie' => 'Product successfully updated.',
        'produit_supprime' => 'Product deleted.',
        'confirmation_suppression' => 'Are you sure you want to delete this product?',
        'choose_file' => 'Choose File',
        'annuler' => 'Cancel',
        // Profil
        'profil_titre' => 'Admin Profile',
        'changer_mdp' => 'Change Password',
        'ancien_mdp' => 'Old Password',
        'nouveau_mdp' => 'New Password',
        'confirmer_mdp' => 'Confirm New Password',
        'btn_changer_mdp' => 'Change Password',
        'mdp_change_ok' => 'Password changed successfully.',
        'mdp_erreur_ancien' => 'Old password is incorrect.',
        'mdp_erreur_conf' => 'Password confirmation does not match new password.',
    ],
    'ar' => [
        'welcome_msg' => 'إدارة المنتجات',
        'gestion_admins' => 'إدارة المسؤولين',
        'ajouter_produit' => '+ إضافة منتج',
        'modifier_produit' => 'تعديل المنتج',
        'supprimer' => 'حذف',
        'nom' => 'الاسم',
        'prix' => 'السعر',
        'quantite' => 'الكمية',
        'categorie' => 'الفئة',
        'image' => 'صورة',
        'ajouter' => 'إضافة',
        'fermer' => 'إغلاق',
        'produit_ajoute' => 'تمت إضافة المنتج بنجاح.',
        'erreur_ajout' => 'حدث خطأ أثناء الإضافة.',
        'produit_modifie' => 'تم تعديل المنتج بنجاح.',
        'produit_supprime' => 'تم حذف المنتج.',
        'confirmation_suppression' => 'هل تريد فعلاً حذف هذا المنتج؟',
        'choose_file' => 'اختر ملفاً',
        'annuler' => 'إلغاء',
        // Profil
        'profil_titre' => 'ملف المسؤول',
        'changer_mdp' => 'تغيير كلمة المرور',
        'ancien_mdp' => 'كلمة المرور القديمة',
        'nouveau_mdp' => 'كلمة المرور الجديدة',
        'confirmer_mdp' => 'تأكيد كلمة المرور الجديدة',
        'btn_changer_mdp' => 'تغيير كلمة المرور',
        'mdp_change_ok' => 'تم تغيير كلمة المرور بنجاح.',
        'mdp_erreur_ancien' => 'كلمة المرور القديمة غير صحيحة.',
        'mdp_erreur_conf' => 'تأكيد كلمة المرور لا يطابق الجديدة.',
    ]
];

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// TRAITEMENT DU FORMULAIRE MOT DE PASSE + PRODUITS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Changer mot de passe
    if (isset($_POST['changer_mdp'])) {
        $ancien   = $_POST['old_password'] ?? '';
        $nouveau  = $_POST['new_password'] ?? '';
        $confirme = $_POST['confirm_password'] ?? '';

        $admin_id = $_SESSION['admin']['id'] ?? 0;

        $stmt = $connexion->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        if (!$row || !password_verify($ancien, $row['password'])) {
            $_SESSION['flash_message'] = $traductions[$lang]['mdp_erreur_ancien'];
            header("Location: admin_page.php?lang=$lang");
            exit;
        }
        if ($nouveau !== $confirme) {
            $_SESSION['flash_message'] = $traductions[$lang]['mdp_erreur_conf'];
            header("Location: admin_page.php?lang=$lang");
            exit;
        }

        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $stmt = $connexion->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $admin_id);
        $stmt->execute();

        $_SESSION['flash_message'] = $traductions[$lang]['mdp_change_ok'];
        header("Location: admin_page.php?lang=$lang");
        exit;
    }

    // Ajout produit
    if (isset($_POST['ajouter_produit'])) {
        $nom     = $_POST['nom'] ?? '';
        $prix    = floatval($_POST['prix'] ?? 0);
        $stock   = intval($_POST['quantite'] ?? 0);
        $id_type = intval($_POST['categorie'] ?? 0);
        $filename = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('prod_') . ".$ext";
            $dest     = "uploads/$filename";
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
        }
        $stmt = $connexion->prepare("INSERT INTO produits (nom, prix, stock, id_type, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiss", $nom, $prix, $stock, $id_type, $filename);
        $stmt->execute();
        $_SESSION['flash_message'] = $traductions[$lang]['produit_ajoute'];
        header("Location: admin_page.php?lang=$lang");
        exit;
    }

    // Modifier produit
    if (isset($_POST['modifier_produit'])) {
        $id      = $_POST['produit_id'] ?? 0;
        $nom     = $_POST['nom'] ?? '';
        $prix    = floatval($_POST['prix'] ?? 0);
        $stock   = intval($_POST['quantite'] ?? 0);
        $id_type = intval($_POST['categorie'] ?? 0);

        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('prod_') . ".$ext";
            $dest     = "uploads/$filename";
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
            $stmt = $connexion->prepare("UPDATE produits SET nom=?, prix=?, stock=?, id_type=?, image=? WHERE id_produit=?");
            $stmt->bind_param("sdissi", $nom, $prix, $stock, $id_type, $filename, $id);
        } else {
            $stmt = $connexion->prepare("UPDATE produits SET nom=?, prix=?, stock=?, id_type=? WHERE id_produit=?");
            $stmt->bind_param("sdisi", $nom, $prix, $stock, $id_type, $id);
        }
        $stmt->execute();
        $_SESSION['flash_message'] = $traductions[$lang]['produit_modifie'];
        header("Location: admin_page.php?lang=$lang");
        exit;
    }

    // Supprimer produit
    if (isset($_POST['supprimer_produit'])) {
        $id = $_POST['produit_id'] ?? 0;
        $stmt = $connexion->prepare("DELETE FROM produits WHERE id_produit=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['flash_message'] = $traductions[$lang]['produit_supprime'];
        header("Location: admin_page.php?lang=$lang");
        exit;
    }
}

$produits = $connexion->query("SELECT p.*, t.nom_type FROM produits p LEFT JOIN types_produits t ON p.id_type = t.id_type");
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Admin Page</title>
    <link rel="stylesheet" href="admin_page_style.css">
    <style>
    /* Ajustement position "Gérer les administrateurs" + icône profil */
    .second-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .second-left .green-text {
        font-weight: 600;
        color: #005500;
    }
    .second-right {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-left: auto;
    }
    .second-right .green-link {
        color: #005500;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .second-right .green-link:hover {
        text-decoration: underline;
    }
    .second-right .profile-icon-container {
        cursor: pointer;
        display: inline-block;
        line-height: 0;
    }
    .second-right .profile-icon-container img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }
    </style>
</head>
<body>
<div class="top-bar">
    <div class="top-bar-left"><img src="images/ocp_logo2.svg" alt="logo" class="logo"></div>
    <div class="top-bar-right">
        <a href="?lang=fr" class="lang-btn <?= $lang === 'fr' ? 'active' : '' ?>">FR</a>
        <a href="?lang=en" class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        <a href="?lang=ar" class="lang-btn <?= $lang === 'ar' ? 'active' : '' ?>">AR</a>
    </div>
</div>

<div class="second-bar">
    <div class="second-left">
        <span class="green-text"><?= $traductions[$lang]['welcome_msg'] ?></span>
    </div>
    <div class="second-right">
        <?php if ($role === 'principal'): ?>
            <a href="gere_admin.php" class="green-link no-underline"><?= $traductions[$lang]['gestion_admins'] ?></a>
        <?php endif; ?>
        <div class="profile-icon-container" onclick="ouvrirProfil()" title="Profil admin">
            <img src="images/profil_icon.jpg" alt="Profil">
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="message-container" id="flash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="cards-container">
    <?php while ($p = $produits->fetch_assoc()): ?>
        <div class="card produit-card" onclick='ouvrirModification(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="product-image" alt="">
            <div class="product-info">
                <div><?= htmlspecialchars($p['nom']) ?></div>
                <div><?= htmlspecialchars($p['prix']) ?> DH</div>
                <div>Quantité: <?= htmlspecialchars($p['stock']) ?></div>
                <div>Catégorie: <?= htmlspecialchars($p['nom_type']) ?></div>
            </div>
        </div>
    <?php endwhile; ?>
    <div class="card add-card" onclick="ouvrirAjout()">
        <div class="plus-sign">+</div>
    </div>
</div>

<!-- MODAL AJOUT / MODIF PRODUIT -->
<div class="modal" id="modal-form" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="fermerModal()">&times;</span>
        <h3 id="modal-title"><?= $traductions[$lang]['ajouter_produit'] ?></h3>
        <form method="POST" enctype="multipart/form-data" id="form-produit">
            <input type="hidden" name="produit_id" id="produit_id">
            <label><?= $traductions[$lang]['nom'] ?>:</label>
            <input type="text" name="nom" id="nom" required>
            <label><?= $traductions[$lang]['prix'] ?>:</label>
            <input type="number" name="prix" id="prix" step="0.01" required>
            <label><?= $traductions[$lang]['quantite'] ?>:</label>
            <input type="number" name="quantite" id="quantite" required>
            <label><?= $traductions[$lang]['categorie'] ?>:</label>
            <select name="categorie" id="categorie" required>
                <?php
                $types2 = $connexion->query("SELECT id_type, nom_type FROM types_produits");
                while ($type = $types2->fetch_assoc()):
                ?>
                    <option value="<?= $type['id_type'] ?>"><?= htmlspecialchars($type['nom_type']) ?></option>
                <?php endwhile; ?>
            </select>
            <label><?= $traductions[$lang]['image'] ?>:</label>
            <div class="custom-file-wrapper">
                <label for="image" class="custom-file-label" id="custom-file-label"><?= $traductions[$lang]['choose_file'] ?></label>
                <input type="file" name="image" id="image" class="custom-file-input" onchange="updateFileName()">
            </div>
           <div class="modal-actions">
    <!-- Bouton Ajouter -->
    <button type="submit" name="ajouter_produit" id="btn-ajouter" class="btn-large">
        <?= $traductions[$lang]['ajouter'] ?>
    </button>

    <!-- Modifier & Supprimer côte à côte -->
    <div class="double-buttons">
        <button type="submit" name="modifier_produit" id="btn-modifier" style="display: none;">
            <?= $traductions[$lang]['modifier_produit'] ?>
        </button>
        <button type="button" name="supprimer_produit" id="btn-supprimer" style="display: none;">
            <?= $traductions[$lang]['supprimer'] ?>
        </button>
    </div>
</div>



        </form>

        <div id="confirm-delete-box" style="display:none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.3); padding: 20px; z-index: 10; width: 90%; max-width: 320px; text-align: center;">
            <p style="font-size: 1.1rem; margin-bottom: 20px;"><?= $traductions[$lang]['confirmation_suppression'] ?></p>
            <button type="button" id="btn-confirm-delete" style="background-color: #b00020; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;"><?= $traductions[$lang]['supprimer'] ?></button>
            <button type="button" id="btn-cancel-delete" style="background-color: #777; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;"><?= $traductions[$lang]['annuler'] ?></button>
        </div>
    </div>
</div>

<!-- MODAL PROFIL -->
<div class="modal" id="modal-profil" style="display: none;">
    <div class="modal-content profil-modal-content" style="width: 350px; max-width: 90%;">
        <span class="close-btn" onclick="fermerProfil()">&times;</span>
        <h3><?= $traductions[$lang]['profil_titre'] ?></h3>

        <div class="info-profil">
    <p><strong>ID :</strong> <?= htmlspecialchars($_SESSION['admin']['id_admin'] ?? 'N/A') ?></p>
    <p><strong><?= $traductions[$lang]['nom'] ?> :</strong> <?= htmlspecialchars($_SESSION['admin']['nom'] ?? 'Admin') ?></p>
    <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['admin']['prenom'] ?? '') ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['admin']['email'] ?? 'email@example.com') ?></p>
</div>


        <form method="POST" id="form-mdp" style="display:flex; flex-direction: column;" onsubmit="return validerChangementMDP()">
            <label><?= $traductions[$lang]['ancien_mdp'] ?>:</label>
            <input type="password" name="old_password" required>
            <label><?= $traductions[$lang]['nouveau_mdp'] ?>:</label>
            <input type="password" name="new_password" required>
            <label><?= $traductions[$lang]['confirmer_mdp'] ?>:</label>
            <input type="password" name="confirm_password" required>
            <button type="submit" name="changer_mdp" style="margin-top: 15px; background-color: #005500; color: white; padding: 10px; border:none; border-radius:5px; cursor:pointer;">
                <?= $traductions[$lang]['btn_changer_mdp'] ?>
            </button>
        </form>
    </div>
</div>

<script>
function ouvrirProfil() {
    document.getElementById('modal-profil').style.display = 'block';
}
function fermerProfil() {
    document.getElementById('modal-profil').style.display = 'none';
}

function ouvrirAjout() {
    document.getElementById('modal-form').style.display = 'block';
    document.getElementById('modal-title').textContent = '<?= addslashes($traductions[$lang]['ajouter_produit']) ?>';
    document.getElementById('form-produit').reset();
    document.getElementById('produit_id').value = '';
    document.getElementById('btn-ajouter').style.display = 'inline-block';
    document.getElementById('btn-modifier').style.display = 'none';
    document.getElementById('btn-supprimer').style.display = 'none';
    hideConfirmDelete();
    updateFileLabel('<?= addslashes($traductions[$lang]['choose_file']) ?>');
}

function ouvrirModification(produit) {
    document.getElementById('modal-form').style.display = 'block';
    document.getElementById('modal-title').textContent = '<?= addslashes($traductions[$lang]['modifier_produit']) ?>';
    document.getElementById('produit_id').value = produit.id_produit;
    document.getElementById('nom').value = produit.nom;
    document.getElementById('prix').value = produit.prix;
    document.getElementById('quantite').value = produit.stock;
    document.getElementById('categorie').value = produit.id_type;
    document.getElementById('btn-ajouter').style.display = 'none';
    document.getElementById('btn-modifier').style.display = 'inline-block';
    document.getElementById('btn-supprimer').style.display = 'inline-block';
    hideConfirmDelete();
    updateFileLabel('<?= addslashes($traductions[$lang]['choose_file']) ?>');
}

function fermerModal() {
    document.getElementById('modal-form').style.display = 'none';
}

function updateFileLabel(text) {
    document.getElementById('custom-file-label').textContent = text;
}

function updateFileName() {
    const input = document.getElementById('image');
    if(input.files.length > 0) {
        updateFileLabel(input.files[0].name);
    } else {
        updateFileLabel('<?= addslashes($traductions[$lang]['choose_file']) ?>');
    }
}

document.getElementById('btn-supprimer').addEventListener('click', function() {
    showConfirmDelete();
});

document.getElementById('btn-cancel-delete').addEventListener('click', function() {
    hideConfirmDelete();
});

document.getElementById('btn-confirm-delete').addEventListener('click', function() {
    const form = document.getElementById('form-produit');
    let inputSupp = document.createElement('input');
    inputSupp.type = 'hidden';
    inputSupp.name = 'supprimer_produit';
    inputSupp.value = '1';
    form.appendChild(inputSupp);
    form.submit();
});

function showConfirmDelete() {
    document.getElementById('confirm-delete-box').style.display = 'block';
}
function hideConfirmDelete() {
    document.getElementById('confirm-delete-box').style.display = 'none';
}

function validerChangementMDP() {
    const form = document.getElementById('form-mdp');
    const newPassword = form.new_password.value.trim();
    const confirmPassword = form.confirm_password.value.trim();
    if(newPassword !== confirmPassword) {
        alert('<?= addslashes($traductions[$lang]['mdp_erreur_conf']) ?>');
        return false;
    }
    return true;
}

// Message flash disparition automatique après 3s
window.onload = function() {
    const flash = document.getElementById('flash-message');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = "opacity 0.8s";
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 800);
        }, 3000);
    }
}
</script>

</body>
</html>
