<?php
session_start();
include 'db_connect.php';
mysqli_report(MYSQLI_REPORT_OFF);

if (isset($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];
$lang = $_SESSION['lang'] ?? 'fr';

$tr = [
  'fr'=>[
    'title'=>'Gérer les administrateurs',
    'id'=>'ID','nom'=>'Nom','prenom'=>'Prénom','email'=>'Email','mdp'=>'Mot de passe',
    'ajouter'=>'Ajouter un admin','modifier'=>'Modifier','supprimer'=>'Supprimer',
    'form_ajout'=>'Ajouter nouvel admin','edit_title'=>'Modifier Admin',
    'message_succes_ajout'=>'Admin ajouté avec succès',
    'message_succes_modif'=>'Admin modifié avec succès',
    'message_succes_supp'=>'Admin supprimé avec succès',
    'message_vide'=>'Tous les champs sont requis',
    'message_existe'=>'Erreur : email existant',
    'confirm_delete'=>'Voulez-vous vraiment supprimer cet admin ?',
    'annuler'=>'Annuler'
  ],
  'en'=>[
    'title'=>'Manage Administrators',
    'id'=>'ID','nom'=>'Last Name','prenom'=>'First Name','email'=>'Email','mdp'=>'Password',
    'ajouter'=>'Add admin','modifier'=>'Edit','supprimer'=>'Delete',
    'form_ajout'=>'Add New Admin','edit_title'=>'Edit Admin',
    'message_succes_ajout'=>'Admin added successfully',
    'message_succes_modif'=>'Admin updated successfully',
    'message_succes_supp'=>'Admin deleted successfully',
    'message_vide'=>'All fields required',
    'message_existe'=>'Error: email exists',
    'confirm_delete'=>'Are you sure you want to delete this admin?',
    'annuler'=>'Cancel'
  ],
  'ar'=>[
    'title'=>'إدارة المسؤولين',
    'id'=>'المعرف','nom'=>'الاسم','prenom'=>'الاسم الأول','email'=>'البريد الإلكتروني','mdp'=>'كلمة المرور',
    'ajouter'=>'إضافة مسؤول','modifier'=>'تعديل','supprimer'=>'حذف',
    'form_ajout'=>'إضافة مسؤول جديد','edit_title'=>'تعديل المسؤول',
    'message_succes_ajout'=>'تمت إضافة المسؤول بنجاح',
    'message_succes_modif'=>'تم تعديل المسؤول بنجاح',
    'message_succes_supp'=>'تم حذف المسؤول بنجاح',
    'message_vide'=>'جميع الحقول مطلوبة',
    'message_existe'=>'خطأ: البريد موجود بالفعل',
    'confirm_delete'=>'هل أنت متأكد من حذف هذا المسؤول؟',
    'annuler'=>'إلغاء'
  ],
];
$t = $tr[$lang];

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['ajouter_admin'])) {
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mdp    = trim($_POST['mdp'] ?? '');

    if ($nom && $prenom && $email && $mdp) {
      $stmt = $connexion->prepare("INSERT INTO admins (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $nom, $prenom, $email, $mdp);
      if ($stmt->execute()) {
        $_SESSION['msg'] = $t['message_succes_ajout'];
      } else {
        $_SESSION['msg'] = ($connexion->errno === 1062) ? $t['message_existe'] : ('Erreur: '.$connexion->error);
      }
    } else {
      $_SESSION['msg'] = $t['message_vide'];
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
  }

  if (isset($_POST['modifier_admin'])) {
    $id     = intval($_POST['id_admin'] ?? 0);
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mdp    = trim($_POST['mdp'] ?? '');

    if ($id && $nom && $prenom && $email) {
      if ($mdp !== '') {
        $stmt = $connexion->prepare("UPDATE admins SET nom=?, prenom=?, email=?, mot_de_passe=? WHERE id_admin=?");
        $stmt->bind_param("ssssi", $nom, $prenom, $email, $mdp, $id);
      } else {
        $stmt = $connexion->prepare("UPDATE admins SET nom=?, prenom=?, email=? WHERE id_admin=?");
        $stmt->bind_param("sssi", $nom, $prenom, $email, $id);
      }
      if ($stmt->execute()) {
        $_SESSION['msg'] = $t['message_succes_modif'];
      } else {
        $_SESSION['msg'] = ($connexion->errno === 1062) ? $t['message_existe'] : ('Erreur: '.$connexion->error);
      }
    } else {
      $_SESSION['msg'] = $t['message_vide'];
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
  }

  if (isset($_POST['supprimer_admin'])) {
    $id = intval($_POST['id_admin'] ?? 0);
    if ($id) {
      $stmt = $connexion->prepare("DELETE FROM admins WHERE id_admin=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $_SESSION['msg'] = $t['message_succes_supp'];
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
  }
}

$res = $connexion->query("SELECT * FROM admins ORDER BY id_admin ASC");
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($t['title']) ?></title>
  <link rel="stylesheet" href="gere_admin_style.css" />
</head>
<body>

<div class="top-bar">
  <div class="top-bar-left"><img src="images/ocp_logo2.svg" alt="Logo OCP" class="logo"></div>
  <div class="top-bar-center"><?= htmlspecialchars($t['title']) ?></div>
  <div class="top-bar-right">
    <a href="?lang=fr" class="lang-btn<?= $lang=='fr'?' active':'' ?>">FR</a>
    <a href="?lang=en" class="lang-btn<?= $lang=='en'?' active':'' ?>">EN</a>
    <a href="?lang=ar" class="lang-btn<?= $lang=='ar'?' active':'' ?>">AR</a>
  </div>
</div>

<div class="container">

  <?php if ($msg): ?>
    <div id="flash-message" class="flash-message"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="table-actions">
    <button id="btnAddAdmin" class="btn-add"><?= htmlspecialchars($t['ajouter']) ?></button>
  </div>

  <table>
    <thead>
      <tr>
        <th><?= $t['id'] ?></th>
        <th><?= $t['nom'] ?></th>
        <th><?= $t['prenom'] ?></th>
        <th><?= $t['email'] ?></th>
        <th><?= $t['mdp'] ?></th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if($res && $res->num_rows>0): while ($a = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $a['id_admin'] ?></td>
        <td><?= htmlspecialchars($a['nom']) ?></td>
        <td><?= htmlspecialchars($a['prenom']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['mot_de_passe']) ?></td>
        <td>
          <button class="btn-edit"
            data-id="<?= $a['id_admin'] ?>"
            data-nom="<?= htmlspecialchars($a['nom']) ?>"
            data-prenom="<?= htmlspecialchars($a['prenom']) ?>"
            data-email="<?= htmlspecialchars($a['email']) ?>"
            data-mdp="<?= htmlspecialchars($a['mot_de_passe']) ?>">
            <?= $t['modifier'] ?>
          </button>
          <form method="POST" class="delete-form" onsubmit="return false;" style="display:inline-block; margin-left:8px;">
            <input type="hidden" name="id_admin" value="<?= $a['id_admin'] ?>" />
            <button type="button" class="btn-delete btn-confirm-delete"><?= $t['supprimer'] ?></button>
          </form>
        </td>
      </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<div id="modalAdmin" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" style="cursor:pointer;">&times;</span>
    <h2 id="modalTitle"></h2>
    <form method="POST" id="adminForm">
      <input type="hidden" name="id_admin" id="id_admin" />
      <input type="text"  name="nom"    id="nom"    placeholder="<?= htmlspecialchars($t['nom']) ?>"    required />
      <input type="text"  name="prenom" id="prenom" placeholder="<?= htmlspecialchars($t['prenom']) ?>" required />
      <input type="email" name="email"  id="email"  placeholder="<?= htmlspecialchars($t['email']) ?>"  required />
      <input type="text"  name="mdp"    id="mdp"    placeholder="<?= htmlspecialchars($t['mdp']) ?>"    />
      <button type="submit" id="submitBtn" name="ajouter_admin">
        <?= htmlspecialchars($t['ajouter']) ?>
      </button>
    </form>
  </div>
</div>

<div id="confirmDeleteModal" class="modal" style="display:none;">
  <div class="modal-content">
    <p><?= htmlspecialchars($t['confirm_delete']) ?></p>
    <button id="confirmYes"><?= htmlspecialchars($t['supprimer']) ?></button>
    <button id="confirmNo"><?= htmlspecialchars($t['annuler']) ?></button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal        = document.getElementById('modalAdmin');
  const confirmModal = document.getElementById('confirmDeleteModal');
  let formToDelete   = null;

  const titleEl    = document.getElementById('modalTitle');
  const idField    = document.getElementById('id_admin');
  const nomField   = document.getElementById('nom');
  const prenField  = document.getElementById('prenom');
  const emailField = document.getElementById('email');
  const mdpField   = document.getElementById('mdp');
  const submitBtn  = document.getElementById('submitBtn');
  const form       = document.getElementById('adminForm');

  document.getElementById('btnAddAdmin').addEventListener('click', () => {
    titleEl.textContent   = '<?= addslashes($t['form_ajout']) ?>';
    submitBtn.name        = 'ajouter_admin';
    submitBtn.textContent = '<?= addslashes($t['ajouter']) ?>';
    form.reset();
    idField.value = '';
    modal.style.display = 'flex';
  });

  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      titleEl.textContent   = '<?= addslashes($t['edit_title']) ?>';
      submitBtn.name        = 'modifier_admin';
      submitBtn.textContent = '<?= addslashes($t['modifier']) ?>';
      idField.value         = btn.dataset.id;
      nomField.value        = btn.dataset.nom;
      prenField.value       = btn.dataset.prenom;
      emailField.value      = btn.dataset.email;
      mdpField.value        = '';
      modal.style.display   = 'flex';
    });
  });

  document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      formToDelete = btn.closest('form');
      confirmModal.style.display = 'flex';
    });
  });

  document.getElementById('confirmYes').addEventListener('click', () => {
    if (formToDelete) {
      let input = formToDelete.querySelector('input[name="supprimer_admin"]');
      if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'supprimer_admin';
        input.value = '1';
        formToDelete.appendChild(input);
      }
      formToDelete.submit();
      confirmModal.style.display = 'none';
    }
  });

  document.getElementById('confirmNo').addEventListener('click', () => {
    formToDelete = null;
    confirmModal.style.display = 'none';
  });

  window.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
    if (e.target === confirmModal) {
      formToDelete = null;
      confirmModal.style.display = 'none';
    }
  });

  document.querySelector('#modalAdmin .close').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  const msg = document.getElementById('flash-message');
  if (msg) {
    msg.classList.add('show');
    setTimeout(() => {
      msg.classList.remove('show');
      msg.classList.add('hide');
    }, 2000);
    setTimeout(() => msg.remove(), 2400);
  }
});
</script>

</body>
</html>
