<?php
class Products {
    private $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function addProduct(string $serial, string $name, int $warranty_years) {
        $serial = trim($serial);
        if ($serial === '') throw new Exception('invalid serial');
        $stmt = $this->pdo->prepare('INSERT INTO products (serial,name,warranty_years) VALUES (?,?,?)');
        $stmt->execute([$serial,$name,$warranty_years]);
    }
    public function getProducts(): array {
        $stmt = $this->pdo->query('SELECT serial,name,warranty_years FROM products ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function registerProductForUser(int $userId, string $serial, string $purchaseDate) {
    // Check if the product exists
        $stmt = $this->pdo->prepare('SELECT serial,warranty_years,name FROM products WHERE serial = ?');
        $stmt->execute([$serial]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod) throw new Exception('invalid serial');
    // Check if it is not already registered
        $stmt = $this->pdo->prepare('SELECT id FROM registrations WHERE serial = ?');
        $stmt->execute([$serial]);
        if ($stmt->fetch()) throw new Exception('serial already registered');
    //  Validate the date
        $d = date_create($purchaseDate);
        if (!$d) throw new Exception('invalid purchase_date, use YYYY-MM-DD');
        $stmt = $this->pdo->prepare('INSERT INTO registrations (user_id,serial,purchase_date,registered_at) VALUES (?,?,?,?)');
        $stmt->execute([$userId,$serial,$d->format('Y-m-d'),date('c')]);
        return ['id'=>$this->pdo->lastInsertId(),'serial'=>$serial,'name'=>$prod['name'],'purchase_date'=>$d->format('Y-m-d')];
    }
    public function getUserProducts(int $userId): array {
        $stmt = $this->pdo->prepare('SELECT r.id,r.serial,p.name,r.purchase_date,p.warranty_years,r.registered_at FROM registrations r JOIN products p ON p.serial = r.serial WHERE r.user_id = ? ORDER BY r.registered_at DESC');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = $this->decorateRegistration($r);
        }
        return $out;
    }
    public function getProductDetailForUser(int $userId, string $serial) {
        $stmt = $this->pdo->prepare('SELECT r.id,r.serial,p.name,r.purchase_date,p.warranty_years,r.registered_at FROM registrations r JOIN products p ON p.serial = r.serial WHERE r.user_id = ? AND r.serial = ? LIMIT 1');
        $stmt->execute([$userId,$serial]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        return $this->decorateRegistration($r);
    }
    private function decorateRegistration(array $r) {
        $purchase = new DateTime($r['purchase_date']);
        $wYears = (int)$r['warranty_years'];
        $expiry = (clone $purchase)->modify('+' . $wYears . ' years');
        $now = new DateTime();
        $remaining = $expiry > $now ? $now->diff($expiry)->format('%y years %m months %d days') : 'expired';
        return [
            'id'=>$r['id'],
            'serial'=>$r['serial'],
            'name'=>$r['name'],
            'purchase_date'=>$purchase->format('Y-m-d'),
            'warranty_years'=>$wYears,
            'warranty_expires'=>$expiry->format('Y-m-d'),
            'warranty_remaining'=>$remaining,
            'registered_at'=>$r['registered_at']
        ];
    }
}
