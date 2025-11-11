<?php
class Database {
    private $path;
    private $pdo;
    public function __construct(string $path) {
        $this->path = $path;
    }
    public function getConnection(): PDO {
        if ($this->pdo) return $this->pdo;
        $dir = dirname($this->path);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $this->pdo = new PDO('sqlite:' . $this->path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        return $this->pdo;
    }
}
