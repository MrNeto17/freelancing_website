<?php

declare(strict_types=1);

class Message
{
    private ?int $id;
    private int $datetime;
    private string $content;
    private User $sender;
    private User $receiver;

    public function __construct(?int $id, int $datetime, string $content, User $sender, User $receiver)
    {
        $this->id = $id;
        $this->datetime = $datetime;
        $this->content = $this->sanitizeContent($content);
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    private static function sanitizeContent(string $content): string {
        if (empty(trim($content))) {
            throw new InvalidArgumentException("Message content cannot be empty");
        }

        // Remove tags HTML/JS e limita tamanho
        $sanitized = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (strlen($sanitized) > 1000) {
            throw new InvalidArgumentException("Message is too long (max 1000 chars)");
        }

        return $sanitized;
    }

    public function upload(PDO $db)
    {
        $db->beginTransaction();
        try {
            $senderId = $this->sender->getId();
            $receiverId = $this->receiver->getId();

            if ($this->id == null) {
                $stmt = $db->prepare("INSERT INTO MESSAGE (created_at, content, sender_id, receiver_id) 
                                 VALUES (:datetime, :content, :sender, :receiver)");
            } else {
                $stmt = $db->prepare("INSERT INTO MESSAGE (id, created_at, content, sender_id, receiver_id) 
                                 VALUES (:id, :datetime, :content, :sender, :receiver)");
                $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            }

            $stmt->bindParam(":datetime", $this->datetime, PDO::PARAM_INT);
            $stmt->bindParam(":content", $this->content, PDO::PARAM_STR);
            $stmt->bindParam(":sender", $senderId, PDO::PARAM_INT);
            $stmt->bindParam(":receiver", $receiverId, PDO::PARAM_INT);
            $stmt->execute();

            if ($this->id == null) {
                $this->id = (int)$db->lastInsertId(); // Use PDO's standard method
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Message upload failed: " . $e->getMessage());
            return false;
        }
    }

    public static function getNumberOfMessages(PDO $db) {
        $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM Message");
        $stmt->execute();
        return $stmt->fetch()['cnt'];
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function getDatetime(): int
    {
        return (int)$this->datetime;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function getReceiver(): User
    {
        return $this->receiver;
    }

    public static function getMessages(PDO $db,User $user1, User $user2, int $lastId): array {


        $userId1 = $user1->getId();
        $userId2 = $user2->getId();

        $stmt = $db->prepare("SELECT * FROM MESSAGE WHERE (id < :last_id) AND ((sender_id == :user1 AND receiver_id == :user2) OR (sender_id == :user2 AND receiver_id == :user1)) ORDER BY created_at DESC LIMIT 16");
        $stmt->bindParam(":user1", $userId1);
        $stmt->bindParam(":user2", $userId2);
        $stmt->bindParam(":last_id", $lastId);
        $stmt->execute();

        return array_map(function ($row) use ($db) {
            $sender = User::getUserByID($db, $row['sender_id']);
            $receiver = User::getUserByID($db, $row['receiver_id']);
            return new Message($row['id'], (int)$row['created_at'], $row['content'], $sender, $receiver);
        }, $stmt->fetchAll());
    }

    public static function getRecentContacts(PDO $db, User $user): array {
        $userId = $user->getId();

        $stmt = $db->prepare("
        SELECT DISTINCT
            CASE
                WHEN sender_id = :userId THEN receiver_id
                ELSE sender_id
            END AS contact_id
        FROM MESSAGE
        WHERE sender_id = :userId OR receiver_id = :userId
        ORDER BY created_at DESC
        LIMIT 100 -- Limit the number of contacts to avoid too many
    ");
    $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch distinct contact IDs.
    $contactIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Fetch only the first column (contact_id)

    $contacts = [];
    foreach($contactIds as $contactId) {
        // Exclude the current user's own ID from the contacts list
        if ($contactId !== $userId) {
            $contactUser = User::getUserByID($db, $contactId);
            if ($contactUser) {
                $contacts[] = $contactUser;
            }
        }
    }
    return $contacts; // Now returns an array of User objects
}

    public static function deleteById(PDO $db, int $id): bool {
        $stmt = $db->prepare("DELETE FROM MESSAGE WHERE id = :id");
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
}