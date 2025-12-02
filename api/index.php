<?php
// index.php
require __DIR__.'/db.php';
require __DIR__.'/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = substr($path, strlen($base));
$parts = array_values(array_filter(explode('/', $path)));

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($method === 'OPTIONS') { 
    http_response_code(204); 
    exit; 
}

$resource = $parts[0] ?? null;
$id = $parts[1] ?? null;

switch($resource) {
    case 'employees':
        handleEmployees($method, $id, $pdo);
        break;
    case 'departments':
        handleDepartments($method, $id, $pdo);
        break;
    case 'devices':
        handleDevices($method, $id, $pdo);
        break;
    case 'assignments':
        handleAssignments($method, $id, $pdo);
        break;
    case 'accounts':
        handleAccounts($method, $id, $pdo);
        break;
    default:
        jsonResponse(['error' => 'Unknown resource'], 404);
}

/* === Handlers below === */

function handleEmployees($method, $id, $pdo) {
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM employee WHERE user_id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            jsonResponse($row ?: []);
        } else {
            $stmt = $pdo->query("SELECT e.*, d.department_name FROM employee e LEFT JOIN department d ON e.department_id = d.department_id");
            jsonResponse($stmt->fetchAll());
        }
    } elseif ($method === 'POST') {
        $data = getJsonInput();
        $sql = "INSERT INTO employee (user_id,user_name,gender,department_id,join_date,leave_date,remark) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['user_id'],$data['user_name'],$data['gender'],$data['department_id'],$data['join_date'],$data['leave_date'],$data['remark']]);
        jsonResponse(['ok' => true], 201);
    } elseif ($method === 'PUT' && $id) {
        $data = getJsonInput();
        $sql = "UPDATE employee SET user_name=?, gender=?, department_id=?, join_date=?, leave_date=?, remark=? WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['user_name'],$data['gender'],$data['department_id'],$data['join_date'],$data['leave_date'],$data['remark'],$id]);
        jsonResponse(['ok' => true]);
    } elseif ($method === 'DELETE' && $id) {
        $stmt = $pdo->prepare("DELETE FROM employee WHERE user_id = ?");
        $stmt->execute([$id]);
        jsonResponse(['ok' => true]);
    } else {
        jsonResponse(['error' => 'Method not allowed'], 405);
    }
}

function handleDepartments($method, $id, $pdo) {
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM department WHERE department_id = ?");
            $stmt->execute([$id]);
            jsonResponse($stmt->fetch() ?: []);
        } else {
            jsonResponse($pdo->query("SELECT * FROM department")->fetchAll());
        }
    } elseif ($method === 'POST') {
        $d = getJsonInput();
        $stmt = $pdo->prepare("INSERT INTO department (department_id, department_name) VALUES (?, ?)");
        $stmt->execute([$d['department_id'],$d['department_name']]);
        jsonResponse(['ok'=>true],201);
    } else {
        jsonResponse(['error'=>'Not implemented'], 405);
    }
}

function handleDevices($method,$id,$pdo){
    if ($method==='GET'){
        if ($id){
            $stmt = $pdo->prepare("SELECT * FROM device WHERE device_id = ?");
            $stmt->execute([$id]);
            jsonResponse($stmt->fetch() ?: []);
        } else {
            jsonResponse($pdo->query("SELECT * FROM device")->fetchAll());
        }
    } elseif ($method==='POST'){
        $d = getJsonInput();
        $stmt = $pdo->prepare("INSERT INTO device (device_id, device_name, description) VALUES (?, ?, ?)");
        $stmt->execute([$d['device_id'],$d['device_name'],$d['description']]);
        jsonResponse(['ok'=>true],201);
    } elseif ($method==='PUT' && $id){
        $d = getJsonInput();
        $stmt = $pdo->prepare("UPDATE device SET device_name=?, description=? WHERE device_id=?");
        $stmt->execute([$d['device_name'],$d['description'],$id]);
        jsonResponse(['ok'=>true]);
    } elseif ($method==='DELETE'&& $id){
        $stmt = $pdo->prepare("DELETE FROM device WHERE device_id=?");
        $stmt->execute([$id]);
        jsonResponse(['ok'=>true]);
    } else jsonResponse(['error'=>'Method not allowed'],405);
}

function handleAssignments($method,$id,$pdo){
    if ($method==='GET'){
        if ($id){
            $stmt = $pdo->prepare("SELECT * FROM device_assignment WHERE assignment_id = ?");
            $stmt->execute([$id]);
            jsonResponse($stmt->fetch() ?: []);
        } else {
            $stmt = $pdo->query("SELECT da.*, e.user_name, d.device_name FROM device_assignment da 
                LEFT JOIN employee e ON da.user_id = e.user_id
                LEFT JOIN device d ON da.device_id = d.device_id
                ORDER BY da.assigned_at DESC");
            jsonResponse($stmt->fetchAll());
        }
    } elseif ($method==='POST'){
        $d = getJsonInput();
        $stmt = $pdo->prepare("INSERT INTO device_assignment (user_id, device_id, assigned_at, note) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$d['user_id'],$d['device_id'],$d['note'] ?? null]);
        jsonResponse(['ok'=>true],201);
    } elseif ($method==='PUT' && $id){
        // update released_at or note
        $d = getJsonInput();
        $stmt = $pdo->prepare("UPDATE device_assignment SET released_at=?, note=? WHERE assignment_id=?");
        $stmt->execute([$d['released_at'] ?? null, $d['note'] ?? null, $id]);
        jsonResponse(['ok'=>true]);
    } elseif ($method==='DELETE' && $id){
        $stmt = $pdo->prepare("DELETE FROM device_assignment WHERE assignment_id=?");
        $stmt->execute([$id]);
        jsonResponse(['ok'=>true]);
    } else jsonResponse(['error'=>'Not allowed'],405);
}

function handleAccounts($method,$id,$pdo){
    // accounts includes domain,email,gmail,erp,bpm - for simplicity we implement domain/email here and follow same for others
    $sub = $_GET['sub'] ?? null; // expects ?sub=domain|email|gmail|erp|bpm
    if (!$sub) jsonResponse(['error'=>'sub param required, e.g. ?sub=domain'],400);

    $tableMap = [
        'domain' => 'domain_account',
        'email'  => 'email_account',
        'gmail'  => 'gmail_account',
        'erp'    => 'erp_account',
        'bpm'    => 'bpm_account'
    ];
    if (!isset($tableMap[$sub])) jsonResponse(['error'=>'invalid sub'],400);
    $table = $tableMap[$sub];

    if ($method==='GET'){
        if ($id){
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE user_id = ?");
            $stmt->execute([$id]);
            jsonResponse($stmt->fetch() ?: []);
        } else {
            jsonResponse($pdo->query("SELECT a.*, e.user_name FROM {$table} a LEFT JOIN employee e ON a.user_id = e.user_id")->fetchAll());
        }
    } elseif ($method==='POST'){
        $d = getJsonInput();
        // build dynamic insert based on expected columns
        $cols = array_keys($d);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO {$table} (" . implode(',',$cols) . ") VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($d));
        jsonResponse(['ok'=>true],201);
    } elseif ($method==='PUT' && $id){
        $d = getJsonInput();
        $sets = [];
        foreach ($d as $k=>$v) $sets[] = "{$k}=?";
        $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge(array_values($d),[$id]));
        jsonResponse(['ok'=>true]);
    } elseif ($method==='DELETE' && $id){
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE user_id = ?");
        $stmt->execute([$id]);
        jsonResponse(['ok'=>true]);
    } else jsonResponse(['error'=>'Method not allowed'],405);
}
