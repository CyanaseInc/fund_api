<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
    header("HTTP/1.1 200 OK");
    die();
}
include "./config.php";
// Retrieve the data from the HTTP POST request
$headers = apache_request_headers();
$data = json_decode(file_get_contents('php://input'), true);
$companyName = $data['companyName'];
$email = $data['email'];
$phoneNumber = $data['phoneNumber'];
$password_1 = $data['password'];


$password     = password_hash($password_1, PASSWORD_BCRYPT, array('cost' => 11));
  $date = date('d:M:Y:h:i');



// Check if the email already exists in the database
$stmt = $pdo->prepare('SELECT COUNT(*) FROM fundmanagers WHERE email = :email');
$stmt->bindValue(':email', $email);
$stmt->execute();
$emailExists = ($stmt->fetchColumn() > 0);

if ($emailExists) {
  // Email already exists, return JSON response
  $response = array('signup' => 'Email already taken');
  echo json_encode($response);
} else {
  // Email does not exist, register the user in the database
  $stmt = $pdo->prepare('INSERT INTO fundmanagers (name, email, Phone_number, password, created_at) 
  VALUES (:companyName, :email, :phoneNumber, :password, :date1)');
  $stmt->bindValue(':companyName', $companyName);
  $stmt->bindValue(':email', $email);
  $stmt->bindValue(':phoneNumber', $phoneNumber);
  $stmt->bindValue(':password', $password);
  $stmt->bindValue(':date1', $date);
  $success = $stmt->execute();


  if ($success) {
    // Registration successful, return JSON response
    $response = array('signup' => 'success');
    echo json_encode($response);
  } else {
    // Registration failed, return JSON response
    $response = array('signup' => 'Registration failed');
    echo json_encode($response);
  }
}
