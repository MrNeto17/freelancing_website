<?php
declare(strict_types=1);

class Transactions{
    private ?int $id; 
    private float $subtotal;
    private string $firstName;
    private string $lastName;
    private string $email;
    private User $client;
    private ?string $created_at; 
    private Service $service; 

    public function __construct(
        ?int $id, 
        float $subtotal,
        string $firstName,
        string $lastName,
        string $email,
        User $client,
        ?string $created_at, 
        Service $service 
    ) {
        if ($subtotal <= 0) {
            throw new InvalidArgumentException("Subtotal must be positive");
        }

        // Validação de nomes
        $this->firstName = $this->validateName($firstName, "First name");
        $this->lastName = $this->validateName($lastName, "Last name");

        // Validação de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format");
        }

        $this->id = $id;
        $this->subtotal = $subtotal;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->client = $client;
        $this->created_at = $created_at;
        $this->service = $service; 
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

    public function getId(): ?int {
        return $this->id;
    }

    public function getSubtotal(): float {
        return $this->subtotal;
    }

    public function getFirstName(): string {
        return htmlspecialchars($this->firstName, ENT_QUOTES, 'UTF-8');
    }

    public function getLastName(): string {
        return htmlspecialchars($this->lastName, ENT_QUOTES, 'UTF-8');
    }

    public function getEmail(): string {
        return filter_var($this->email, FILTER_SANITIZE_EMAIL);
    }

    public function getClient(): User {
        return $this->client;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function getService(): Service {
        return $this->service;
    }

    public function upload(PDO $db): void{
        $db->beginTransaction();

        try{
        $buyer = $this->client->getId();
        $serviceId = $this->service->getId();

        $stmt = $db->prepare("
        INSERT INTO TRANSACTIONS (subtotal, firstName, lastName, email, client_id, service_id, created_at) 
        VALUES (:subtotal, :firstName, :lastName, :email, :client_id, :service_id, :created_at)
        ");

    
        $stmt->bindParam(":subtotal", $this->subtotal, PDO::PARAM_STR);
        $stmt->bindParam(":firstName", $this->firstName, PDO::PARAM_STR);
        $stmt->bindParam(":lastName", $this->lastName, PDO::PARAM_STR);
        $stmt->bindParam(":email", $this->email, PDO::PARAM_STR);
        $stmt->bindParam(":client_id", $buyer, PDO::PARAM_INT);
        $stmt->bindParam(":service_id", $serviceId, PDO::PARAM_INT); // Bind service ID
        $stmt->bindParam(":created_at", $this->created_at, PDO::PARAM_STR);

        $stmt->execute();
        $stmt = $db->prepare("SELECT last_insert_rowid()");
        $stmt->execute();
        $id = $stmt->fetch();
        $this->id = $id[0];

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        throw new RuntimeException("Transaction failed: " . $e->getMessage());
        }
    }

    public static function getTransactionsByID(PDO $db, int $id): ?Transactions {
        $stmt = $db->prepare("SELECT * FROM TRANSACTIONS WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $transaction = $stmt->fetch();
        if (!isset($transaction["id"]))
            return null;
        return new Transactions(
            $transaction["id"],
            $transaction["subtotal"],
            $transaction["firstName"],
            $transaction["lastName"],
            $transaction["email"],
            User::getUserByID($db, $transaction["client_id"]),
            $transaction["created_at"],
            Service::getServiceByID($db, $transaction["service_id"])
        );
    }

    public static function getTransactionsAssociatedWithService(PDO $db, Service $service): array {
        $serviceId = $service->getId();

        $stmt = $db->prepare("SELECT * FROM TRANSACTIONS WHERE service_id = :service_id");
        $stmt->bindParam(":service_id", $serviceId ,PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($transactions as $transaction) {
            $result[] = new Transactions(
                $transaction["id"],
                $transaction["subtotal"],
                $transaction["firstName"],
                $transaction["lastName"],
                $transaction["email"],
                User::getUserByID($db, $transaction["client_id"]),
                $transaction["created_at"],
                Service::getServiceByID($db, $transaction["service_id"])
            );
        }
        return $result;
    }


    public static function getTransactionsByClient(PDO $db, User $client): array {
        $clientId = $client->getId();
    
        $stmt = $db->prepare("SELECT * FROM TRANSACTIONS WHERE client_id = :client_id");
        $stmt->bindParam(":client_id", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $result = [];
        foreach ($transactions as $transaction) {
            $result[] = new Transactions(
                $transaction["id"],
                $transaction["subtotal"],
                $transaction["firstName"],
                $transaction["lastName"],
                $transaction["email"],
                User::getUserByID($db, $transaction["client_id"]),
                $transaction["created_at"],
                Service::getServiceByID($db, $transaction["service_id"])
            );
        }
    
        return $result;
    }
    
    public static function countTransactions(PDO $db): int {
        $stmt = $db->prepare("SELECT COUNT(*) FROM TRANSACTIONS");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
}
?>