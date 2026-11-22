<?php

namespace App\Core;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function instance(): static
    {
        return new static();
    }

    public static function find(int $id): ?array
    {
        $instance = static::instance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ? LIMIT 1";
        return $instance->db->fetch($sql, [$id]);
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        $instance = static::instance();
        $sql = "SELECT * FROM {$instance->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $instance->db->fetchAll($sql);
    }

    public static function where(string $column, mixed $value, string $operator = '='): array
    {
        $instance = static::instance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} {$operator} ?";
        return $instance->db->fetchAll($sql, [$value]);
    }

    public static function firstWhere(string $column, mixed $value, string $operator = '='): ?array
    {
        $instance = static::instance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} {$operator} ? LIMIT 1";
        return $instance->db->fetch($sql, [$value]);
    }

    public static function create(array $data): int|string|false
    {
        $instance = static::instance();
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$instance->table} ({$columns}) VALUES ({$placeholders})";
        $success = $instance->db->execute($sql, array_values($data));

        return $success ? $instance->db->lastInsertId() : false;
    }

    public static function update(int $id, array $data): bool
    {
        $instance = static::instance();
        $setClauses = [];
        $values = [];

        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $sql = "UPDATE {$instance->table} SET " . implode(', ', $setClauses) . " WHERE {$instance->primaryKey} = ?";

        return $instance->db->execute($sql, $values);
    }

    public static function delete(int $id): bool
    {
        $instance = static::instance();
        $sql = "DELETE FROM {$instance->table} WHERE {$instance->primaryKey} = ?";
        return $instance->db->execute($sql, [$id]);
    }

    public static function count(string $where = '', array $params = []): int
    {
        $instance = static::instance();
        $sql = "SELECT COUNT(*) as total FROM {$instance->table}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $res = $instance->db->fetch($sql, $params);
        return (int)($res['total'] ?? 0);
    }

    public static function raw(string $sql, array $params = []): array
    {
        return static::instance()->db->fetchAll($sql, $params);
    }

    public static function rawOne(string $sql, array $params = []): ?array
    {
        return static::instance()->db->fetch($sql, $params);
    }
}
