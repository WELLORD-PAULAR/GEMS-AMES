<?php

namespace AMS\Models;

class User extends Model
{
    protected $table = 'user_account';

    public function findByUsername(string $username): ?self
    {
        $result = $this->where('username', $username);

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function findByEmail(string $email): ?self
    {
        $result = $this->where('email', $email);

        if ($result) {
            $this->attributes = $result;
            $this->dirty = [];
            return $this;
        }

        return null;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        if (!isset($this->attributes['password_hash'])) {
            return false;
        }

        return password_verify($plainPassword, $this->attributes['password_hash']);
    }

    public function setPassword(string $plainPassword): self
    {
        $this->attributes['password_hash'] = password_hash($plainPassword, PASSWORD_BCRYPT);
        return $this;
    }

    public function getActive(): array
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1");
        return $this->db->fetchAll();
    }
}
