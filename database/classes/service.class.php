<?php
declare(strict_types=1);

require_once('category.class.php');
require_once('transactions.class.php');
require_once('user.class.php');

class Service
{
    private ?int $id;
    private string $title;
    private string $description;
    private float $price;
    private ?float $rating; // Valor entre 0.00 e 5.00
    private int $delivery_time; // Tempo de entrega em dias
    private User $freelancer;
    private ?Category $category;
    private string $status; // Added status property

    public function __construct(
        ?int      $id,
        string    $title,
        string    $description,
        float     $price,
        int       $delivery_time,
        User      $freelancer,
        ?Category $category,
        string    $status // Added status parameter
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->delivery_time = $delivery_time;
        $this->freelancer = $freelancer;
        $this->category = $category;
        $this->status = $status; // Set status

        if (empty(trim($title))) {
            throw new InvalidArgumentException("Title cannot be empty");
        }
        if (strlen($title) > 100) {
            throw new InvalidArgumentException("Title too long (max 100 chars)");
        }

        // Validação do preço
        if ($price <= 0) {
            throw new InvalidArgumentException("Price must be positive");
        }

        // Validação do tempo de entrega
        if ($delivery_time <= 0) {
            throw new InvalidArgumentException("Delivery time must be positive");
        }

        // Validação do status
        $validStatuses = ['canceled', 'pending', 'completed', 'in progress'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid status");
        }
    }
    private function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function getDeliveryTime(): int
    {
        return $this->delivery_time;
    }

    public function getFreelancer(): User
    {
        return $this->freelancer;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getStatus(): string
    {
        return $this->status;
    }


    public function setStatus(string $status, PDO $db): void
    {


        $stmt = $db->prepare("UPDATE SERVICE SET status = :status WHERE id = :id");
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();


        $this->status = $status;
    }

    public function markAsInProgress(PDO $db): void
    {
        $this->setStatus('in progress', $db);
    }


    public function markAsCompleted(PDO $db): void
    {
        $this->setStatus('completed', $db);
    }

    private static function rowToService(array $row, PDO $db): Service
    {
        return new Service(
            $row["id"],
            $row["title"],
            $row["description"],
            $row["price"],
            $row["delivery_time"],
            User::getUserByID($db, $row["freelancer_id"]),
            $row["category_name"] ? Category::getCategory($db, $row["category_name"]) : null,
            $row["status"] // Add this to capture the status
        );
    }

    public static function getServiceByID(PDO $db, int $id, bool $onlyValid = true): ?Service
    {
        $stmt = $db->prepare("SELECT * FROM SERVICE WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $service = $stmt->fetch();
        if (!isset($service["id"])) {
            return null;
        }
        return Service::rowToService($service, $db);
    }

    public function setTitle(string $title, PDO $db): void
    {
        $title = $this->sanitizeInput($title);
        $stmt = $db->prepare("UPDATE SERVICE SET title = :title WHERE id = :id");
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $this->title = $title;
    }

    public function setPrice(float $price, PDO $db): void
    {
        $stmt = $db->prepare("UPDATE SERVICE SET price = :price WHERE id = :id");
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->price = (float)$price;
    }

    public function setDescription(string $description, PDO $db): void
    {
        $description = $this->sanitizeInput($description);
        $stmt = $db->prepare("UPDATE SERVICE SET description = :description WHERE id = :id");
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->description = $description;
    }

    public function setDeliveryTime(int $delivery_time, PDO $db): void
    {
        $stmt = $db->prepare("UPDATE SERVICE SET delivery_time = :delivery_time WHERE id = :id");
        $stmt->bindParam(":delivery_time", $delivery_time);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->delivery_time = (int)$delivery_time;
    }

    public function setCategory(?Category $category, PDO $db): void
    {
        $categoryName = $category?->getName();
        $stmt = $db->prepare("UPDATE SERVICE SET category_name = :category WHERE id = :id");
        $stmt->bindParam(":category", $categoryName);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->category = $category;
    }

    public static function getServices($db): array
    {
        $stmt = $db->prepare("SELECT * FROM SERVICE");
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $servicesArray = array();
        foreach ($services as $service) {
            $servicesArray[] = Service::rowToService($service, $db);
        }
        return $servicesArray;
    }

    public function upload(PDO $db): void
    {
        if ($this->id === null) {
            $stmt = $db->prepare("
                INSERT INTO SERVICE (title, description, price, delivery_time, freelancer_id, category_name, status) 
                VALUES (:title, :description, :price, :delivery_time, :freelancer_id, :category, :status)
            ");
        } else {
            $stmt = $db->prepare("
                INSERT INTO SERVICE (id, title, description, price, delivery_time, freelancer_id, category, status) 
                VALUES (:id, :title, :description, :price, :delivery_time, :freelancer_id, :category, :status)
            ");
            $stmt->bindParam(":id", $this->id);
        }

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":delivery_time", $this->delivery_time);
        $freelancerId = $this->freelancer->getId();
        $stmt->bindParam(":freelancer_id", $freelancerId);
        $categoryName = $this->category?->getName();
        $stmt->bindParam(":category", $categoryName);
        $stmt->bindParam(":status", $this->status); // Added status parameter for upload
        $stmt->execute();

        if ($this->id === null) {
            $stmt = $db->prepare("SELECT last_insert_rowid()");
            $stmt->execute();
            $id = $stmt->fetch();
            $this->id = (int)$id[0];
        }
    }

    public static function getServicesByFreelancer(PDO $db, int $freelancerId): array
    {
        $stmt = $db->prepare("SELECT * FROM SERVICE WHERE freelancer_id = :freelancer_id");
        $stmt->bindParam(":freelancer_id", $freelancerId);
        $stmt->execute();

        $services = array();
        while ($service = $stmt->fetch()) {
            $services[] = Service::rowToService($service, $db);
        }
        return $services;
    }

    public function delete(PDO $db): bool
    {
        $stmt = $db->prepare('DELETE FROM Service WHERE id = ?');
        return $stmt->execute([$this->id]);
    }

    public static function countServices(PDO $db): int
    {
        $stmt = $db->prepare("SELECT COUNT(*) FROM SERVICE");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public static function getFilteredServices(PDO $db, ?string $category = null, ?float $maxPrice = null, ?int $maxDeliveryTime = null): array
    {
        $query = "SELECT * FROM SERVICE WHERE 1=1";
        $params = [];

        if (!empty($category)) {
            // Verifica se a categoria existe
            $validCategories = array_map(function ($cat) {
                return $cat->getName();
            }, Category::getAll($db));

            if (in_array($category, $validCategories)) {
                $query .= " AND category_name = :category";
                $params[':category'] = $category;
            }
        }

        if ($maxPrice !== null && $maxPrice > 0) {
            $query .= " AND price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        if ($maxDeliveryTime !== null && $maxDeliveryTime > 0) {
            $query .= " AND delivery_time <= :maxDeliveryTime";
            $params[':maxDeliveryTime'] = $maxDeliveryTime;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        $results = $stmt->fetchAll();

        if (empty($results)) {
            error_log("Nenhum serviço encontrado com filtros: " . print_r([
                    'category' => $category,
                    'maxPrice' => $maxPrice,
                    'maxDeliveryTime' => $maxDeliveryTime
                ], true));
        }

        return array_map(function ($row) use ($db) {
            return self::rowToService($row, $db);
        }, $results);
    }


    public static function searchByTitle(PDO $db, string $query): array {
        
        $stmt = $db->prepare('SELECT * FROM Service WHERE title LIKE :query COLLATE NOCASE');
        $stmt->execute([':query' => '%' . $query . '%']);

        $services = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = self::rowToService($row, $db); 
        }

        return $services;
    }
}