<?php
class Database
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function query($query, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetch($query, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Fetch failed: " . $e->getMessage());
        }
    }

    public function insert($table, $data)
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($data));
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }

    public function update($table, $data, $conditions)
    {
        try {
            $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
            $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
            
            $query = "UPDATE $table SET $set WHERE $where";
            $values = array_merge(array_values($data), array_values($conditions));
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }

    public function delete($table, $conditions)
    {
        try {
            $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
            $query = "DELETE FROM $table WHERE $where";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($conditions));
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }

    public function getPDO()
    {
        return $this->pdo;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollBack();
    }
}
?>
