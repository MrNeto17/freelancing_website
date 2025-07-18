<?php
declare(strict_types=1);

require_once('service.class.php'); 

class ServiceImage {
    private string $url;
    private Service $service;

    public function __construct(string $url, Service $service) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL format");
        }

        // Verifica se é uma imagem (extensões comuns)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        /*
        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException("Only image files are allowed");
        } */

        $this->url = $url;
        $this->service = $service;
    }

    public function getUrl(): string {
        return htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8');
    }

    public function getService(): Service {
        return $this->service;
    }

    public function save(PDO $db): void {
        $db->beginTransaction();
        try {
            $serviceId = $this->service->getId();
            $stmt = $db->prepare('INSERT INTO SERVICE_IMAGE (url, service_id) VALUES (:url, :service_id)');
            $stmt->bindParam(':url', $this->url);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->execute();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getImagesByService(PDO $db, Service $service): array {
        $serviceId = $service->getId();

        $stmt = $db->prepare('SELECT * FROM SERVICE_IMAGE WHERE service_id = :service_id');
        $stmt->bindParam(':service_id', $serviceId);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $images = [];
        foreach ($rows as $row) {
            $images[] = new ServiceImage($row['url'], $service);
        }
        return $images;
    }

    public static function deleteImage(PDO $db, string $url): void {
        $stmt = $db->prepare('DELETE FROM SERVICE_IMAGE WHERE url = :url');
        $stmt->bindParam(':url', $url);
        $stmt->execute();
    }
}
?>
