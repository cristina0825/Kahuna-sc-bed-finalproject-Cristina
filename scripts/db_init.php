<?php
// Execute: php scripts/db_init.php
$dbFile = __DIR__ . '/../data/database.sqlite';
if (file_exists($dbFile)) {
    echo "Removing existing DB at $dbFile\n";
    unlink($dbFile);
}
@mkdir(dirname($dbFile), 0777, true);
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

echo "Creating tables...\n";
$pdo->exec(<<<'SQL'
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'client',
  token TEXT NULL
);
CREATE TABLE products (
  serial TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  warranty_years INTEGER NOT NULL DEFAULT 1
);
CREATE TABLE registrations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  serial TEXT NOT NULL,
  purchase_date TEXT NOT NULL,
  registered_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(serial) REFERENCES products(serial) ON DELETE CASCADE
);
SQL
);

echo "Seeding initial data...\n";
$adminPass = password_hash('adminpass', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
$stmt->execute(['Admin','admin@example.com',$adminPass,'admin']);

$products = [
  ['KHWM8199911','CombiSpin Washing Machine',2],
  ['KHWM8199912','CombiSpin + Dry Washing Machine',2],
  ['KHMW789991','CombiGrill Microwave',1],
  ['KHWP890001','K5 Water Pump',5],
  ['KHWP890002','K5 Heated Water Pump',5],
  ['KHSS988881','Smart Switch Lite',2],
  ['KHSS988882','Smart Switch Pro',2],
  ['KHSS988883','Smart Switch Pro V2',2],
  ['KHHM89762','Smart Heated Mug',1],
  ['KHSB0001','Smart Bulb 001',1]
];
$stmt = $pdo->prepare('INSERT INTO products (serial,name,warranty_years) VALUES (?,?,?)');
foreach ($products as $p) $stmt->execute($p);

echo "Database initialized at $dbFile\n";
echo "Admin user: admin@example.com / adminpass\n";
