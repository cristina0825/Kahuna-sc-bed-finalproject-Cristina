<?php
// CORS headers for development (allow all origins, methods, and headers)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 86400");
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Products.php';

header('Content-Type: application/json; charset=utf-8');

$db = new Database(__DIR__ . '/../data/database.sqlite');
$pdo = $db->getConnection();
$auth = new Auth($pdo);
$products = new Products($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove any project folder prefix when using the built-in server
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/') {
    $path = preg_replace('#^' . preg_quote($scriptName) . '#', '', $path);
}

function jsonInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Simple router 
if ($path === '/register' && $method === 'POST') {
    $in = jsonInput();
    // Allow missing role - default to ‘client’
    if (empty($in['name']) || empty($in['email']) || empty($in['password'])) respond(['error'=>'name,email,password required'],400);
    $role = !empty($in['role']) ? $in['role'] : 'client';
    try {
        $user = $auth->register($in['name'],$in['email'],$in['password'],$role);
        respond(['message'=>'user created','user'=>['id'=>$user['id'],'email'=>$user['email'],'role'=>$user['role']]],201);
    } catch (Exception $e) {
        respond(['error'=>$e->getMessage()], 400);
    }

} elseif ($path === '/login' && $method === 'POST') {
    $in = jsonInput();
    if (empty($in['email']) || empty($in['password'])) respond(['error'=>'email and password required'],400);
    $token = $auth->login($in['email'],$in['password']);
    if (!$token) respond(['error'=>'invalid credentials'],401);
    // Return token and user information
    $user = $auth->authenticateToken($token);
    respond(['token'=>$token, 'user'=>$user]);

} elseif ($path === '/logout' && $method === 'POST') {
    $hdr = getallheaders();
    $token = null;
    if (!empty($hdr['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $hdr['Authorization'], $m)) $token = $m[1];
    }
    if (!$token) respond(['error'=>'missing token'],401);
    $user = $auth->authenticateToken($token);
    if (!$user) respond(['error'=>'invalid token'],401);
    $auth->logout($user['id']);
    respond(['message'=>'logged out']);

} elseif ($path === '/products' && $method === 'GET') {
    $list = $products->getProducts();
    respond(['products'=>$list]);

} elseif ($path === '/admin/products' && $method === 'POST') {
    // Add product (admin only)
    $hdr = getallheaders();
    $token = null;
    if (!empty($hdr['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $hdr['Authorization'], $m)) $token = $m[1];
    }
    if (!$token) respond(['error'=>'missing token'],401);
    $user = $auth->authenticateToken($token);
    if (!$user) respond(['error'=>'invalid token'],401);
    if ($user['role'] !== 'admin') respond(['error'=>'admin only'],403);
    $in = jsonInput();
    if (empty($in['serial']) || empty($in['name']) || !isset($in['warranty_years'])) respond(['error'=>'serial,name,warranty_years required'],400);
    try {
        $products->addProduct($in['serial'],$in['name'],(int)$in['warranty_years']);
        respond(['message'=>'product added'],201);
    } catch (Exception $e) {
        respond(['error'=>$e->getMessage()],400);
    }

} elseif ($path === '/register-product' && $method === 'POST') {
    $hdr = getallheaders();
    $token = null;
    if (!empty($hdr['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $hdr['Authorization'], $m)) $token = $m[1];
    }
    if (!$token) respond(['error'=>'missing token'],401);
    $user = $auth->authenticateToken($token);
    if (!$user) respond(['error'=>'invalid token'],401);
    $in = jsonInput();
    if (empty($in['serial']) || empty($in['purchase_date'])) respond(['error'=>'serial and purchase_date required'],400);
    try {
        $reg = $products->registerProductForUser($user['id'],$in['serial'],$in['purchase_date']);
        respond(['message'=>'registered','registration'=>$reg],201);
    } catch (Exception $e) {
        respond(['error'=>$e->getMessage()],400);
    }

} elseif ($path === '/my-products' && $method === 'GET') {
    $hdr = getallheaders();
    $token = null;
    if (!empty($hdr['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $hdr['Authorization'], $m)) $token = $m[1];
    }
    if (!$token) respond(['error'=>'missing token'],401);
    $user = $auth->authenticateToken($token);
    if (!$user) respond(['error'=>'invalid token'],401);
    $list = $products->getUserProducts($user['id']);
    respond(['registrations'=>$list]);

} elseif (preg_match('#^/product/([^/]+)$#', $path, $m) && $method === 'GET') {
    $serial = $m[1];
    $hdr = getallheaders();
    $token = null;
    if (!empty($hdr['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $hdr['Authorization'], $mm)) $token = $mm[1];
    }
    if (!$token) respond(['error'=>'missing token'],401);
    $user = $auth->authenticateToken($token);
    if (!$user) respond(['error'=>'invalid token'],401);
    $detail = $products->getProductDetailForUser($user['id'],$serial);
    if (!$detail) respond(['error'=>'not found or not owned'],404);
    respond(['product'=>$detail]);

} elseif ($path === '/me' && $method === 'GET') {
    // Endpoint to return the currently authenticated user
    $hdr = getallheaders();
    $token = null;
    if (!empty($hdr['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $hdr['Authorization'], $m)) $token = $m[1];
    }
    if (!$token) respond(['error'=>'missing token'],401);
    $user = $auth->authenticateToken($token);
    if (!$user) respond(['error'=>'invalid token'],401);
    respond(['user'=>$user]);

} else {
    respond(['error'=>'not found','path'=>$path],404);
}

// Helper functions
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

