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

// Function to retrieve all data for a user email
function getUserData($userId, $pdo) {

    // Retrieve Investment Classes data
    $investmentClassesQuery = "SELECT * FROM InvestmentClasses WHERE fund_manager_id = :userId";
    $investmentClassesStmt = $pdo->prepare($investmentClassesQuery);
    $investmentClassesStmt->bindParam(':userId', $userId);
    $investmentClassesStmt->execute();
    $investmentClassesData = $investmentClassesStmt->fetchAll(PDO::FETCH_ASSOC);
// Retrieve  total Investment Classes data
$investmentClassesQueryTotal = "SELECT * FROM InvestmentClasses";
$investmentClassesTotalStmt = $pdo->prepare($investmentClassesQueryTotal);
$investmentClassesTotalStmt->execute();
$investmentTotalClassesData = $investmentClassesTotalStmt->fetchAll(PDO::FETCH_ASSOC);

/*Retrieve Investment Performance data*/
    $investmentPerformanceQuery = "SELECT * FROM InvestmentPerformance WHERE investment_class_id IN (SELECT id FROM InvestmentClasses WHERE fund_manager_id = :userId)";
    $investmentPerformanceStmt = $pdo->prepare($investmentPerformanceQuery);
    $investmentPerformanceStmt->bindParam(':userId', $userId);
    $investmentPerformanceStmt->execute();
    $investmentPerformanceData = $investmentPerformanceStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the final data array
    $userData = array(
        'myclasses' => $investmentClassesData,
        'investment_Total_classes' => $investmentTotalClassesData,
        'investment_performance' => $investmentPerformanceData
    );

    return $userData;
}

// Assuming you have retrieved the user ID from the API request

// Check if the email and password match a user in the database
$stmt = $pdo->prepare('SELECT * FROM fundmanagers WHERE email = :email');
$stmt->bindValue(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'];
// Retrieve the data for the user ID
$userData = getUserData($userId, $pdo);

// Output the data in JSON format
header('Content-Type: application/json');
echo json_encode($userData);
?>
