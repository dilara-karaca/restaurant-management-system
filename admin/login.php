<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Test: PHP çalışıyor mı?
echo '<!-- PHP is working -->';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /Restaurant-Management-System/admin/dashboard.php');
    exit;
}

// Login kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // (username: admin, password: 12345)
    if ($username === 'admin' && $password === '12345') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: /Restaurant-Management-System/admin/dashboard.php');
        exit;
    } else {
        $error_message = 'Kullanıcı adı veya şifre yanlış!';
    }
}

$extraJs = ["/Restaurant-Management-System/assets/js/admin.js"];
$bodyClass = "page-auth";

include __DIR__ . '/../includes/layout/top.php';
?>

<main class="auth">
  <div class="auth__bg" aria-hidden="true"></div>

  <section class="auth__card">
    <header class="auth__header">
      <h1 class="title">Hoşgeldiniz</h1>
      <p class="auth__subtitle">Restoranızla ilgili detaylar için giriş yapın.</p>
    </header>

    <form class="form" method="post" action="/Restaurant-Management-System/admin/login.php" autocomplete="on">

      <?php if (isset($error_message)): ?>
        <div style="color: #ef4444; background: rgba(239, 68, 68, .1); padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 8px;">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <!-- KULLANICI ADI -->
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

      <!-- ŞİFRE -->
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

      <div class="auth__row">
        <label class="check">
          <input type="checkbox" name="remember">
          <span>Beni hatırla</span>
        </label>

        <a class="link" href="#" onclick="return false;">Şifremi unuttum</a>
      </div>

      <button class="btn btn--primary btn--block" type="submit">
        Giriş Yap
      </button>

    </form>
  </section>
</main>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
