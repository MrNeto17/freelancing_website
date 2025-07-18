<?php
declare(strict_types=1);

class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private string $password;
    private string $name;
    private ?string $phone; // Pode ser null
    private string $role; // 'client', 'freelancer', ou 'admin'
    private ?string $description; // Apenas para freelancers, pode ser null
    private ?float $rating; // Valor entre 0.00 e 5.00
    private ?string $created_at; // Timestamp no formato string


    public function __construct(
        ?int    $id,
        string  $username,
        string  $email,
        string  $password,
        string  $name,
        ?string $phone,
        string  $role,
        ?string $description = null,  // Make sure this parameter exists
        ?float $rating = null
    )
    {
        $username = trim($username);
        if (empty($username)) {
            throw new InvalidArgumentException("Username cannot be empty.");
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) { // Exemplo: 3-50 caracteres alfanuméricos, underscore, ponto, hífen
            throw new InvalidArgumentException("Username contains invalid characters or is too short/long.");
        }

        // Validação de Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        if ($phone !== null && !preg_match('/^[0-9\s\-\(\)\+]{5,20}$/', $phone)) { // Exemplo de regex para telefone
            throw new InvalidArgumentException("Invalid phone number format.");
        }
        $validRoles = ['client', 'freelancer', 'admin'];
        if (!in_array($role, $validRoles)) {
            throw new InvalidArgumentException("Invalid user role: " . htmlspecialchars($role));
        }
        if ($description !== null && strlen($description) > 1000) { // Exemplo: max 1000 caracteres
            throw new InvalidArgumentException("Description is too long (max 1000 chars).");
        }
        if ($rating !== null && ($rating < 0.0 || $rating > 5.0)) {
            throw new InvalidArgumentException("Rating must be between 0.00 and 5.00.");
        }


        $this->email = $email;
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->name = $this->validateName($name, "name");
        $this->phone = $phone;
        $this->role = $role;
        $this->description = $description;
        $this->rating = $rating;
    }

    private function validateName(string $name, string $fieldName): string {
        $name = trim($name);
        if (empty($name)) {
            throw new InvalidArgumentException("$fieldName cannot be empty");
        }
        if (!preg_match('/^[\p{L}\s\'-]+$/u', $name)) {
            throw new InvalidArgumentException("$fieldName contains invalid characters");
        }
        if (strlen($name) > 100) {
            throw new InvalidArgumentException("$fieldName is too long (max 100 chars)");
        }
        return $name;
    }

    public function validatePassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT, ['cost' >= 12]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return htmlspecialchars($this->username, ENT_QUOTES, 'UTF-8');
    }

    public function getEmail(): string
    {
        return filter_var($this->email, FILTER_SANITIZE_EMAIL);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
    }

    public function getPhone(): ?string
    {
        return $this->phone ? htmlspecialchars($this->phone, ENT_QUOTES, 'UTF-8') : null;
    }

    public function getRole(): string
    {
        return htmlspecialchars($this->role, ENT_QUOTES, 'UTF-8');
    }

    // Add this to your User.class.php
    public function getDescription(): ?string {
        return $this->description ? htmlspecialchars($this->description, ENT_QUOTES, 'UTF-8') : null;
    }
    public function getRating(): ?float {
        return $this->rating;
    }
    public static function getUserByID(PDO $db, int $id): ?User
    {
        $stmt = $db->prepare("SELECT * FROM USER WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user == false)
            return null;
        return new User($user["id"], $user["username"], $user["email"], $user["password"], $user["name"], $user["phone"], $user["role"], $user["description"], $user["rating"]);
    }

    public static function getUserByUsername(PDO $db, string $username): ?User
    {
        $stmt = $db->prepare("SELECT * FROM USER WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user == false)
            return null;
        return new User($user["id"], $user["username"], $user["email"], $user["password"], $user["name"], $user["phone"], $user["role"], $user["description"], $user["rating"]);
    }

    public static function getUserByEmail(PDO $db, string $email): ?User
    {
        $stmt = $db->prepare("SELECT * FROM USER WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user == false) {
            return null;
        }
        return new User($user["id"], $user["username"], $user["email"], $user["password"], $user["name"], $user["phone"], $user["role"], $user["description"], $user["rating"]);
    }

    public function setType(PDO $db, string $type): void
    {
        if (!in_array($type, ["client", "freelancer", "admin"])) {
            throw new Exception("Invalid type");
        }
        $this->type = $type;
        $stmt = $db->prepare("UPDATE USER SET type = :type WHERE id = :id");
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function setName(PDO $db, string $name): void
    {
        $name = trim($name);
        if (empty($name)) {
            throw new InvalidArgumentException("Name cannot be empty.");
        }
        if (!preg_match('/^[\p{L}\s\'-]+$/u', $name)) {
            throw new InvalidArgumentException("Name contains invalid characters.");
        }
        if (strlen($name) > 100) {
            throw new InvalidArgumentException("Name is too long (max 100 chars).");
        }
        $this->name = $name;
        $stmt = $db->prepare("UPDATE USER SET name = :name WHERE id = :id");
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function setEmail(PDO $db, string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format.");
        }
        $this->email = $email;
        $stmt = $db->prepare("UPDATE USER SET email = :email WHERE id = :id");
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function setPassword(PDO $db, string $password): void
    {
        if (empty($password)) {
            throw new InvalidArgumentException("Password cannot be empty.");
        }
        $this->password = $password;
        $this->hashPassword();
        $stmt = $db->prepare("UPDATE USER SET password = :password WHERE id = :id");
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function setRating(PDO $db, float $rating): void{
        if ($rating < 0.0 || $rating > 5.0) {
            throw new InvalidArgumentException("Rating must be between 0.00 and 5.00.");
        }
        $this->rating = $rating;
        $stmt = $db->prepare("UPDATE USER SET rating = :rating WHERE id = :id");
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }


    public function upload(PDO $db): void
    {
        if ($this->id == null) {
            $stmt = $db->prepare("INSERT INTO USER (email, name, password, username ,role) VALUES (:email, :name, :password, :username ,:role)");
        } else {
            $stmt = $db->prepare("INSERT INTO USER (id, email, name, password,username,role) VALUES (:id, :email, :name, :password, :username,:role)");
            $stmt->bindParam(":id", $this->id);
        }

        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if ($this->id == null) {
            $stmt = $db->prepare("SELECT last_insert_rowid()");
            $stmt->execute();
            $id = $stmt->fetch();
            $this->id = $id[0];
        }
    }
    
    public function setUsername(PDO $db, string $username): void
    {
        $username = trim($username);
        if (empty($username)) {
            throw new InvalidArgumentException("Username cannot be empty.");
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
            throw new InvalidArgumentException("Username contains invalid characters or is too short/long.");
        }
        $this->username = $username;
        $stmt = $db->prepare("UPDATE USER SET username = :username WHERE id = :id");
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function updateProfile(PDO $db, ?string $phone, ?string $description): void
    {
        if ($phone !== null && !preg_match('/^[0-9\s\-\(\)\+]{5,20}$/', $phone)) {
            throw new InvalidArgumentException("Invalid phone number format.");
        }
        if ($description !== null && strlen($description) > 1000) {
            throw new InvalidArgumentException("Description is too long (max 1000 chars).");
        }

        $this->phone = $phone;
        $this->description = $description;

        $stmt = $db->prepare("
        UPDATE USER 
        SET phone = :phone, 
            description = :description 
        WHERE id = :id
    ");

        $stmt->bindValue(":phone", $phone, $phone === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":description", $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public static function countUsers(PDO $db): int
    {
        $stmt = $db->prepare("SELECT COUNT(*) FROM USER");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function ElevateToAdmin(PDO $db): void{
        $stmt = $db->prepare("UPDATE USER SET role = 'admin' WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $this->role = 'admin';
    }

    
}

?>