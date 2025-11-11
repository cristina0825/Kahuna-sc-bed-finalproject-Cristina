#!/usr/bin/env bash
set -euo pipefail
BASE="http://localhost:8000"

echo "== E2E script: Kahuna (start) =="

# 1) Init DB inside container
echo "[1/8] Initializing DB (docker-compose exec kahuna php scripts/db_init.php)"
docker-compose exec kahuna php scripts/db_init.php

# helper to pretty print JSON if jq exists
JQ=cat
if command -v jq >/dev/null 2>&1; then
  JQ=jq
fi

# 2) Register test user (idempotent: may return 400 if exists)
echo "[2/8] Registering test user..."
curl -s -X POST -H "Content-Type: application/json" -d '{"name":"E2E Tester","email":"e2e@test.local","password":"testpass"}' "$BASE/register" | $JQ || true

# 3) Login test user -> extract token
echo "[3/8] Logging in test user..."
user_login_resp=$(curl -s -H "Content-Type: application/json" -d '{"email":"e2e@test.local","password":"testpass"}' "$BASE/login")
user_token=$(echo "$user_login_resp" | sed -n 's/.*"token":"\([^\"]*\)".*/\1/p')
if [ -z "$user_token" ]; then
  echo "ERROR: could not obtain user token. Response:"; echo "$user_login_resp" | $JQ || true; exit 2
fi
echo "user token: ${user_token}"

# 4) Login admin -> extract token
echo "[4/8] Logging in admin..."
admin_login_resp=$(curl -s -H "Content-Type: application/json" -d '{"email":"admin@example.com","password":"adminpass"}' "$BASE/login")
admin_token=$(echo "$admin_login_resp" | sed -n 's/.*"token":"\([^\"]*\)".*/\1/p')
if [ -z "$admin_token" ]; then
  echo "ERROR: could not obtain admin token. Response:"; echo "$admin_login_resp" | $JQ || true; exit 2
fi
echo "admin token: ${admin_token}"

# 5) Admin adds a product
echo "[5/8] Admin adding product..."
curl -s -H "Content-Type: application/json" -H "Authorization: Bearer ${admin_token}" -d '{"serial":"E2E-SERIAL-001","name":"E2E Widget","warranty_years":2}' "$BASE/admin/products" | $JQ || true

# 6) User registers the product
echo "[6/8] User registering product..."
curl -s -H "Content-Type: application/json" -H "Authorization: Bearer ${user_token}" -d '{"serial":"E2E-SERIAL-001","purchase_date":"2025-10-29"}' "$BASE/register-product" | $JQ || true

# 7) Fetch my-products
echo "[7/8] Fetching /my-products..."
curl -s -H "Authorization: Bearer ${user_token}" "$BASE/my-products" | $JQ || true

# 8) Fetch product detail
echo "[8/8] Fetching /product/E2E-SERIAL-001..."
curl -s -H "Authorization: Bearer ${user_token}" "$BASE/product/E2E-SERIAL-001" | $JQ || true

echo "== E2E script: done =="
