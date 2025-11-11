# Kahuna — REST API for Product Registration (PHP + SQLite)

This is a minimal implementation of a REST API in PHP for the Kahuna project (assignment).
I used SQLite for simplicity and ease of running locally or in containers.

## Repository Contents
-	PHP backend (no external framework) in the project root directory.
- Database initialization script: `scripts/db_init.php` (creates tables and seeds an admin user and some products).
- SQLite database file: `data/database.sqlite` (generated after running the init script).
- Frontend: React (Vite) app located in `frontend/`.
- Postman collection for testing (optional).

## Recomanded application to run(Docker)
1. Start the service using Docker Compose (recommended, no local PHP needed):

   docker-compose up --build

   If you make changes and want to stop it:

   docker-compose down

2. Initialize the database (only the first time or when you want to reset it):

   docker-compose exec kahuna php scripts/db_init.php

3. The API will be available at: http://localhost:8000

4. Frontend(development) — from another terminal,go to the `frontend/` and run:

   cd frontend
   npm install
   npm run dev

   Vite will start (by default on port 5173). Access the UI in your browser (e.g: http://localhost:5173).

## Rulare fără Docker (dacă ai PHP instalat local)
1. Install Node dependencies for the frontend and run it as above (optional).
2. Create/reset the database and seed data:

   php scripts/db_init.php

3. Start the PHP built-in server from the project root:

   php -S localhost:8000 -t public

4. Access the API at: http://localhost:8000

## Endpoint-uri principale (JSON)
- POST /register -> {name,email,password,role}  — register a user (role: `client` sau `admin`).
- POST /login -> {email,password}  — returns {token, user} upon authentification.
- POST /logout -> header Authorization: Bearer <token>
- GET /me -> header Authorization: Bearer <token>  —  returns the current user’s information
- GET /products -> lists all available products
- POST /admin/products -> (admin) {serial,name,warranty_years} — adds a new product
- POST /register-product -> (autentificat) {serial,purchase_date} — registers a product for a user
- GET /my-products -> (autentificat) — shows products registered by the current user
- GET /product/{serial} -> (autentificat) — returns details of a registered product

### Date utile
- Admin test account (seed): admin@example.com / adminpass
- Database file: `data/database.sqlite`

## Why SQLite?
	•	SQLite is extremely easy to use for testing and evaluation environments — it doesn’t require a separate server installation, and the database file can be easily transported or reset.

## API Testing
- Use Postman or curl to test the endpoints.
- The Postman collection is included in the repository  (look for `postman_collection.json`).
