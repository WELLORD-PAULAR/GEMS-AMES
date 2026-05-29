<?php
namespace AMS;

use PDO;
use PDOException;

class Database
{
    private $pdo;
    private $statement;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function query(string $sql, array $params = []): self
    {
        try {
            $this->statement = $this->pdo->prepare($sql);
            $this->statement->execute($params);
            return $this;
        } catch (PDOException $e) {
            throw new \Exception('Query failed: ' . $e->getMessage());
        }
    }

    public function fetch(): ?array
    {
        return $this->statement ? $this->statement->fetch(PDO::FETCH_ASSOC) : null;
    }

    public function fetchAll(): array
    {
        return $this->statement ? $this->statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function lastId()
    {
        return $this->pdo->lastInsertId();
    }

    public function rowCount(): int
    {
        return $this->statement ? $this->statement->rowCount() : 0;
    }

    public function success(): bool
    {
        return $this->rowCount() > 0;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}
