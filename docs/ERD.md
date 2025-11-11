# CREATE DATABASE

```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'client',
  token TEXT NULL
);

CREATE TABLE products (
  serial TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  warranty_years INT NOT NULL DEFAULT 1
);

CREATE TABLE registrations (
  id INT PRIMARY KEY AUTOINCREMENT,
  user_id INT NOT NULL,
  serial TEXT NOT NULL,
  purchase_date TEXT NOT NULL,
  registered_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(serial) REFERENCES products(serial) ON DELETE CASCADE
);
```

