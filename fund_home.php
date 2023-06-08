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

// Assuming you have a database connection established
include './config.php';

// Retrieve the data from the HTTP POST request
$headers = apache_request_headers();
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];

function getUserData($userId, $pdo) {

    // get total depossit to the fund
    $query = "SELECT SUM(deposit) AS totalDeposits FROM deposit WHERE fundmanager = :userId AND available='0'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalDeposits = $result['totalDeposits'];

/// get total wallet deposit by the fund


$Walletquery = "SELECT SUM(amount) AS totalDeposits FROM funds WHERE fund_manager_id = :userId";
$Walletstmt = $pdo->prepare($Walletquery);
$Walletstmt->bindParam(':userId', $userId);
$Walletstmt->execute();
$result = $Walletstmt->fetch(PDO::FETCH_ASSOC);
$totalWallet = $result['totalDeposits'];



///Get all pending withdrawrequests

$Withdrawquery = "SELECT SUM(withdraw) AS totalDeposits FROM withdraw WHERE fundmanager = :userId AND status ='pending'";
$Withdrawstmt = $pdo->prepare($Withdrawquery);
$Withdrawstmt->bindParam(':userId', $userId);
$Withdrawstmt->execute();
$result = $Withdrawstmt->fetch(PDO::FETCH_ASSOC);
$totalWithdraw = $result['totalDeposits'];
// Get investment perfomace
$investmentPerformanceQuery = "SELECT * FROM InvestmentPerformance WHERE class_id IN (SELECT id FROM InvestmentClasses WHERE fund_manager_id = :userId)";
$investmentPerformanceStmt = $pdo->prepare($investmentPerformanceQuery);
$investmentPerformanceStmt->bindParam(':userId', $userId);
$investmentPerformanceStmt->execute();
$investmentPerformanceData = $investmentPerformanceStmt->fetchAll(PDO::FETCH_ASSOC);

   
    $usersData = array(
        'login'=>'success',
        'totalDeposit' => $totalDeposits,
        'totalWallet'=> $totalWallet,
        'totalWithdraw'=> $totalWithdraw,
        'investmentPerformace'=>$investmentPerformanceData,
    );

    return $usersData;
}

// Check if the email and password match a user in the database
$stmt = $pdo->prepare('SELECT * FROM fundmanagers WHERE email = :email');
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'];

// Retrieve the data for the user ID
$userData = getUserData($userId, $pdo);

// Output the data in JSON format
echo json_encode($userData);
?>
