<?php

declare(strict_types=1);

class Category
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function upload(PDO $db)
    {
        self::validateName($this->name);

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO CATEGORY (name) VALUES (:name)");
            $stmt->bindParam(":name", $this->name, PDO::PARAM_STR);
            $stmt->execute();
            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            throw new RuntimeException("Failed to upload category: " . $e->getMessage());
        }
    }

    public static function getCategory(PDO $db, string $category): Category
    {
        self::validateName($category);

        $stmt = $db->prepare("SELECT name FROM CATEGORY WHERE name = :category");
        $stmt->bindParam(":category", $category, PDO::PARAM_STR);
        $stmt->execute();

        $categoryName = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($categoryName === false) {
            throw new Exception("Category not found");
        }

        return new Category($categoryName["name"]);
    }

    public static function getAll(PDO $db): array
    {
        $stmt = $db->prepare("SELECT name FROM CATEGORY ORDER BY name ASC");
        $stmt->execute();

        $categories = array_map(function ($category) {
            return new Category($category["name"]);
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        return $categories;
    }

    public static function addCategory(PDO $db, string $name): Category
    {
        self::validateName($name);
        $db->beginTransaction();

        try {
            $checkStmt = $db->prepare("SELECT 1 FROM CATEGORY WHERE name = :name");
            $checkStmt->bindParam(":name", $name, PDO::PARAM_STR);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                throw new RuntimeException("Category already exists");
            }

            $stmt = $db->prepare("INSERT INTO CATEGORY (name) VALUES (:name)");
            $stmt->bindParam(":name", $name);
            $stmt->execute();

            $db->commit();
            return new Category($name);
        } catch (PDOException $e) {
            $db->rollBack();
            throw new RuntimeException("Failed to add category: " . $e->getMessage());
        }
    }

    public static function deleteCategory(PDO $db, string $name): bool
    {
        self::validateName($name);

        $db->beginTransaction();

        try{
            $stmt = $db->prepare("DELETE FROM CATEGORY WHERE name = :name");
            $stmt->bindParam(":name", $name);
            $stmt->execute();

            $deleted = $stmt->rowCount() > 0;
            $db->commit();

            return $deleted;
        } catch (PDOException $e) {
            $db->rollBack();
            throw new RuntimeException("Failed to delete category: " . $e->getMessage());
        }
    }

    private static function validateName(string $name): void {
        if (empty(trim($name))) {
            throw new InvalidArgumentException("Category name cannot be empty");
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException("Category name is too long (max 255 characters)");
        }

        // Permite letras (incluindo acentuadas), números, espaços, hífens e underscores
        if (!preg_match('/^[\p{L}\p{N}\s\-_]+$/u', $name)) {
            throw new InvalidArgumentException("Category name contains invalid characters. Only letters, numbers, spaces, hyphens and underscores are allowed.");
        }
    }
}