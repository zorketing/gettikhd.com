<?php
namespace App\Services;

use PDO;

/**
 * Service class for handling contact form submissions and message management.
 */
class ContactService {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function submitContact(string $name, string $email, string $message): bool {
        $stmt = $this->pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $message]);
    }

    public function getAllContacts(int $limit = 10, int $offset = 0): array {
        $stmt = $this->pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalContacts(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    }

    public function getContactById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteContact(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM contacts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
