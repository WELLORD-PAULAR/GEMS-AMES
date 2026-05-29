<?php
/**
 * Database Helper Class
 * Wrapper around PDO for consistent database operations
 */

class Database
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Execute SELECT query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array Result set
     */
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

    /**
     * Fetch single row
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|null Single row or null
     */
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

    /**
     * Execute INSERT query
     * @param string $table Table name
     * @param array $data Key-value pairs
     * @return int Last insert ID
     */
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

    /**
     * Execute UPDATE query
     * @param string $table Table name
     * @param array $data Key-value pairs to update
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int Number of affected rows
     */
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

    /**
     * Execute DELETE query
     * @param string $table Table name
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int Number of affected rows
     */
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

    /**
     * Get PDO instance for complex queries
     * @return PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->pdo->rollBack();
    }
}
?>
