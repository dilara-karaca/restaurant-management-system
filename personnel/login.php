<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../includes/cruds.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['personnel_logged_in']) && $_SESSION['personnel_logged_in'] === true) {
    header('Location: /Restaurant-Management-System/personnel/orders.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error_message = 'Kullanıcı adı ve şifre zorunludur.';
    } else {
        try {
            $crud = new CRUD();
            $result = $crud->customQuery(
                "SELECT u.user_id, u.username, u.password, r.role_name, p.personnel_id, p.first_name, p.last_name, p.position
                 FROM Users u
                 JOIN Roles r ON u.role_id = r.role_id
                 JOIN Personnel p ON u.user_id = p.user_id
                 WHERE u.username = :username
                 LIMIT 1",
                [':username' => $username]
            );

            if (!$result || count($result) === 0) {
                $error_message = 'Kullanıcı bulunamadı.';
            } else {
                $user = $result[0];
                $allowedRoles = ['Waiter', 'Manager'];

                if (!in_array($user['role_name'], $allowedRoles, true)) {
                    $error_message = 'Bu hesap personel yetkisine sahip değil.';
                } elseif (!verifyPassword($password, $user['password'])) {
                    $error_message = 'Kullanıcı adı veya şifre yanlış!';
                } else {
                    $_SESSION['personnel_logged_in'] = true;
                    $_SESSION['personnel_user_id'] = (int) $user['user_id'];
                    $_SESSION['personnel_id'] = (int) $user['personnel_id'];
                    $_SESSION['personnel_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                    $_SESSION['personnel_role'] = $user['role_name'];
                    $_SESSION['personnel_username'] = $user['username'];

                    header('Location: /Restaurant-Management-System/personnel/orders.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            $error_message = 'Sunucu hatası: ' . $e->getMessage();
        }
    }
}

$extraJs = ["/Restaurant-Management-System/assets/js/admin.js"];
$bodyClass = "page-auth";
$title = "Personel Girişi";

include __DIR__ . '/../includes/layout/top.php';
?>

<main class="auth">
  <div class="auth__bg" aria-hidden="true"></div>

  <section class="auth__card">
    <header class="auth__header">
      <h1 class="title">Personel Girişi</h1>
      <p class="auth__subtitle">Kendi siparişlerinizi yönetmek için giriş yapın.</p>
    </header>

    <form class="form" method="post" action="/Restaurant-Management-System/personnel/login.php" autocomplete="on">

      <?php if (isset($error_message)): ?>
        <div style="color: #ef4444; background: rgba(239, 68, 68, .1); padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 8px;">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <label class="field">
        <span class="field__label">Kullanıcı Adı</span>
        <div class="field__control">
          <input
            class="input"
            type="text"
            name="username"
            placeholder="kullanıcı adınız"
            required
          >
        </div>
      </label>

      <label class="field">
        <span class="field__label">Şifre</span>
        <div class="field__control">
          <input
            class="input"
            type="password"
            id="password"
            name="password"
            placeholder="şifreniz"
            required
          >

          <button
            type="button"
            id="pwToggle"
            class="field__icon btn-icon"
            aria-label="Basılı tutarak şifreyi göster"
          >
            <svg viewBox="0 0 24 24" class="pw-icon" aria-hidden="true">
              <path d="M12 5c5.5 0 9.7 4.2 11 7-1.3 2.8-5.5 7-11 7S2.3 14.8 1 12c1.3-2.8 5.5-7 11-7Zm0 2C7.8 7 4.3 10.1 3.1 12 4.3 13.9 7.8 17 12 17s7.7-3.1 8.9-5C19.7 10.1 16.2 7 12 7Zm0 2.5A2.5 2.5 0 1 1 9.5 12 2.5 2.5 0 0 1 12 9.5Z"></path>
            </svg>
          </button>
        </div>
      </label>

      <button class="btn btn--primary btn--block" type="submit">
        Giriş Yap
      </button>

    </form>
  </section>
</main>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>
