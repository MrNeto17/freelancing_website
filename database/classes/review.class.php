<?php declare(strict_types=1);

require_once('service.class.php');
require_once('user.class.php');

class Review
{
    private ?int $id;
    private Service $service;
    private User $user;
    private int $rating;
    private string $comment;
    private string $created_at;


    public function __construct(
        Service $service,
        User $user,
        int $rating,
        string $comment,
        ?int $id = null,
        string $created_at = ''
    ) {
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException("Rating must be between 1 and 5");
        }
        $this->id = $id;
        $this->service = $service;
        $this->user = $user;
        $this->rating = $rating;
        $this->created_at = $created_at;
        $this->comment = $this->sanitizeComment($comment);

    }
    private function sanitizeComment(string $comment): string
    {
        $comment = trim($comment);
        if (empty($comment)) {
            throw new InvalidArgumentException("Comment cannot be empty");
        }

        if (strlen($comment) > 1000) {
            throw new InvalidArgumentException("Comment exceeds maximum length of 1000 characters");
        }

        return htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

     public function upload(PDO $db): void
     {
         $db->beginTransaction();
         try {
             $service_id = $this->service->getId();
             $user_id = $this->user->getId();

             $stmt = $db->prepare(
                 "INSERT INTO REVIEW (service_id, user_id, rating, comment) 
                VALUES (:service_id, :user_id, :rating, :comment)"
             );
             $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
             $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
             $stmt->bindParam(':rating', $this->rating, PDO::PARAM_INT);
             $stmt->bindParam(':comment', $this->comment, PDO::PARAM_STR);
             $stmt->execute();

             $this->id = (int)$db->lastInsertId();
             $db->commit();
         } catch (Exception $e) {
             $db->rollBack();
             throw new RuntimeException("Failed to upload review: " . $e->getMessage());
         }

     }

    public static function getReviewsByFreelancer(PDO $db, int $freelancer_id): array {
        $stmt = $db->prepare("
        SELECT REVIEW.*
        FROM REVIEW
        JOIN SERVICE ON REVIEW.service_id = SERVICE.id
        WHERE SERVICE.freelancer_id = :freelancer_id
    ");
        $stmt->bindParam(':freelancer_id', $freelancer_id, PDO::PARAM_INT);
        $stmt->execute();

        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $reviewObjects = [];

        foreach ($reviews as $review) {
            $service = Service::getServiceByID($db, $review['service_id']);
            $user = User::getUserByID($db, $review['user_id']);

            $reviewObjects[] = new self(
                $service,              // Service (1st param)
                $user,                 // User (2nd param)
                (int)$review['rating'], // Rating (3rd param, must be int)
                $review['comment'],    // Comment (4th param, must be string)
                (int)$review['id'],    // ID (5th param, optional)
                $review['created_at']  // Created At (6th param, optional)
            );
        }

        return $reviewObjects;
    }

    public static function calculateFreelancerAverageRating(PDO $db, int $freelancer_id): float{
    $stmt = $db->prepare("
        SELECT AVG(REVIEW.rating) AS avg_rating
        FROM REVIEW
        JOIN SERVICE ON SERVICE.id = REVIEW.service_id
        WHERE SERVICE.freelancer_id = :freelancer_id
    ");
    $stmt->bindParam(':freelancer_id', $freelancer_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['avg_rating'] === null) {
        return 0.0;
    }
    return (float) $result['avg_rating'];
}


    public static function deleteReview(PDO $db, int $review_id): void
    {
        $stmt = $db->prepare("DELETE FROM REVIEW WHERE id = :id");
        $stmt->bindParam(':id', $review_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>
