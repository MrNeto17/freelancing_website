<?php function output_header(Session $session, array $styles = []) { ?>
  <!DOCTYPE html>
  <html>
    <head>
        <title>Freelancer Services</title>    
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/base.css">
        <?php 
        foreach ($styles as $style) {
          echo '<link rel="stylesheet" href="' . $style . '">';
        }
        ?>
        <script src="../javascript/script.js" defer></script>
    </head>
    <body class="<?php echo $session->isLoggedIn() ? 'logged-in' : 'not-logged-in'; ?>">
      <header class="main-header">
        <div class="header-left">
          <img src="/img/Lancer-Icon.png">
          <h1><a href="/">Lancer for Hire</a></h1>
        </div>
        <div class="login-container">
          <?php 
            if ($session->isLoggedIn()){
              drawLogoutForm($session);
            }else{
              drawLoginForm($session->getCSRFToken());
            }
          ?>
        </div>
      </header>
      <nav id="main-nav">
        <input type="checkbox" id="hamburger" aria-label="Mobile menu toggle">
        <label class="hamburger" for="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </label>
        <ul class="nav-list">
          <?php if ($session->isLoggedIn()) { ?>            
            <li><a href="../pages/profile.php?user_id=<?php echo $session->getId();?>">Profile</a></li>
            <li><a href="../pages/contacts_page.php">Contact</a></li>
            <li><a href="../pages/search_page.php">Search</a></li>
          <?php } ?>
          <?php if ($session->isLoggedIn() && $session->getRole() === 'admin') { ?>
              <li><a href="../pages/admin_dashboard_page.php" class="admin-link">Dashboard</a></li>
          <?php } ?>
        </ul>
      </nav>
      <section id="messages">
      <?php foreach ($session->getMessages() as $messsage) { ?>
        <article class="<?=$messsage['type']?>">
          <?=$messsage['text']?>
        </article>
      <?php } ?>
      </section>
      <main>
<?php } ?>

<?php function output_footer() { ?>
      
      <footer>
          <p>2025 Freelancer Services. All rights reserved.</p>
      </footer>
    </main>
</body>
</html>
<?php } ?>


<?php function drawLoginForm($csrftoken) { ?>
  <div class="login-form-wrapper"> <!-- New wrapper -->
    <form action="../actions/action_login.php" method="post" class="login-form">
      <input type="hidden" name="csrf_token" value="<?= $csrftoken ?>">
      <div class="input-group">
        <input type="email" name="email" placeholder="Email" required>
        <div class="password-wrapper">
          <input type="password" name="password" placeholder="Password" required>
        </div>
      </div>
      <div class="button-group">
        <button type="submit" class="login-btn">Login</button>
        <a href="../pages/register.php" class="register-btn">Register</a>
      </div>
    </form>
  </div>
<?php } ?>

<?php function drawLogoutForm(Session $session) { ?>
  <div class="logout-wrapper">
    <div class="user-logout-section">
      <a href="../pages/profile.php?user_id=<?= $session->getId() ?>" class="username-link">
        <?= $session->getName() ?>
      </a>
      <form action="../actions/action_logout.php" method="post" class="logout-form">
        <button type="submit" class="text-logout-btn">Logout</button>
      </form>
    </div>
  </div>
<?php } ?>