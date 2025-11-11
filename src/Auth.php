<?php
class Auth {
    private $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function register(string $name, string $email, string $password, string $role) {
        $role = strtolower($role) === 'admin' ? 'admin' : 'client';
    // Check if the e-mail exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) throw new Exception('email exists');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
        $stmt->execute([$name,$email,$hash,$role]);
        $id = $this->pdo->lastInsertId();
        return ['id'=>$id,'email'=>$email,'role'=>$role];
    }
    public function login(string $email, string $password) {
        $stmt = $this->pdo->prepare('SELECT id,password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        if (!password_verify($password, $row['password_hash'])) return false;
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare('UPDATE users SET token = ? WHERE id = ?');
        $stmt->execute([$token,$row['id']]);
        return $token;
    }
    public function authenticateToken(string $token) {
        $stmt = $this->pdo->prepare('SELECT id,name,email,role FROM users WHERE token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }
    public function logout(int $userId) {
        $stmt = $this->pdo->prepare('UPDATE users SET token = NULL WHERE id = ?');
        $stmt->execute([$userId]);
    }
}
