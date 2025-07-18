<?php
class Session {
    private array $messages;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 86400,
                'path' => '/',
                //'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']), // true se for HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $this->regenerateCSRFToken();
        }

        $this->messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);
    }

    public function regenerateCSRFToken(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        // Debug - remova depois de testar
        error_log('CSRF Token regenerado: '.$_SESSION['csrf_token']);
    }

    public function isLoggedIn() : bool {
      return isset($_SESSION['id']);    
    }

    public function logout() {
      session_destroy();
    }

    public function getId() : ?int {
      return isset($_SESSION['id']) ? $_SESSION['id'] : null;    
    }

    public function getName() : ?string {
      return isset($_SESSION['name']) ? $_SESSION['name'] : null;
    }

    public function getRole() : ?string {
      return isset($_SESSION['role']) ? $_SESSION['role'] : null;
    }

    public function setId(int $id) {
      $_SESSION['id'] = $id;
    }

    public function setName(string $name) {
      $_SESSION['name'] = $name;
    }
    public function setRole(string $role) {
      $_SESSION['role'] = $role;
    }

    public function addMessage(string $type, string $text) {
      $_SESSION['messages'][] = array('type' => $type, 'text' => $text);
    }

    public function getMessages() {
      return $this->messages;
    }

      public function getCSRFToken(): string {
          return $_SESSION['csrf_token'];
      }

      public function validateCSRFToken(?string $token): bool {
          if (empty($token) || empty($_SESSION['csrf_token'])) {
              error_log('Token ausente: '.($token ?? 'null').' | Sessão: '.($_SESSION['csrf_token'] ?? 'null'));
              return false;
          }
          return hash_equals($_SESSION['csrf_token'], $token);
      }
  }
?>