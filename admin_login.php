<?php
session_start();
include 'db_connect.php';

$message = "";

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'fr';

$traductions = [
    'fr' => [
        'titre' => 'Connexion Admin',
        'email' => 'Email',
        'mot_de_passe' => 'Mot de passe',
        'se_connecter' => 'Se connecter',
        'erreur_mdp' => 'Mot de passe incorrect.',
        'aucun_admin' => 'Aucun administrateur trouvé.'
    ],
    'en' => [
        'titre' => 'Admin Login',
        'email' => 'Email',
        'mot_de_passe' => 'Password',
        'se_connecter' => 'Log in',
        'erreur_mdp' => 'Incorrect password.',
        'aucun_admin' => 'No administrator found.'
    ],
    'ar' => [
        'titre' => 'تسجيل دخول المسؤول',
        'email' => 'البريد الإلكتروني',
        'mot_de_passe' => 'كلمة المرور',
        'se_connecter' => 'تسجيل الدخول',
        'erreur_mdp' => 'كلمة المرور غير صحيحة.',
        'aucun_admin' => 'لم يتم العثور على مسؤول.'
    ]
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $mot_de_passe = $_POST["mot_de_passe"] ?? '';

    $stmt = $connexion->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if ($mot_de_passe === $row['mot_de_passe']) {
            $_SESSION['admin'] = $row;
            $_SESSION['role']  = $row['role'];
            if (!empty($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $upd = $connexion->prepare("UPDATE admins SET remember_token = ? WHERE id_admin = ?");
                $upd->bind_param("si", $token, $row['id_admin']);
                $upd->execute();
                setcookie('remember_token', $token, time() + 30*24*3600, "/", "", isset($_SERVER['HTTPS']), true);
            }
            header("Location: admin_page.php?lang=$lang");
            exit();
        } else {
            $message = $traductions[$lang]['erreur_mdp'];
        }
    } else {
        $message = $traductions[$lang]['aucun_admin'];
    }
}

require_once __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('380613820885-kqt99qtubmkjlav1j43kjmotv5nvuuli.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/ocp_project/admin_login.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $oauth = new Google_Service_Oauth2($client);
        $userInfo = $oauth->userinfo->get();

        $_SESSION['admin_email'] = $userInfo->email;
        $_SESSION['admin_name'] = $userInfo->name;

        header('Location: admin_page.php');
        exit();
    } else {
        $message = "Erreur Google OAuth.";
    }
}

$googleLoginUrl = $client->createAuthUrl();

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $traductions[$lang]['titre'] ?></title>
    <link rel="stylesheet" href="admin_login_style.css">
</head>
<body>
<div class="logo-container">
    <img src="images/ocp_logo.png" alt="Logo">
</div>

<div class="lang-switch">
    <button onclick="window.location.href='?lang=fr'">FR</button>
    <button onclick="window.location.href='?lang=en'">EN</button>
    <button onclick="window.location.href='?lang=ar'">AR</button>
</div>

<div class="login-box">
    <h2><?= $traductions[$lang]['titre'] ?></h2>
    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label><?= $traductions[$lang]['email'] ?></label>
        <input type="email" name="email" required>

        <label><?= $traductions[$lang]['mot_de_passe'] ?></label>
        <input type="password" name="mot_de_passe" required>

        <div class="remember-me">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Se souvenir de moi</label>
        </div>

        <button type="submit"><?= $traductions[$lang]['se_connecter'] ?></button>

        <div class="separator"></div>

        <div class="google-login">
            <a href="<?= htmlspecialchars($googleLoginUrl) ?>" class="google-btn">
                <img src="images/google_logo.webp" alt="Google" class="google-icon">
                Continuer avec Google
            </a>
        </div>
    </form>
</div>
</body>
</html>
