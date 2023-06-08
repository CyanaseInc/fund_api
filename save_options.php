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


// Retrieve the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract the email and options from the data
$email = $data['email'];
$options = $data['selectedOptions'];
include 'config.php';

try {

  // Get the user's ID based on the email
  $stmt = $pdo->prepare("SELECT id FROM fundmanagers WHERE email = :email");
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $userId = $stmt->fetchColumn();
  $date = date('d:M:Y:h:i');
  if ($userId) {
    // Insert each option with the user's ID into the database
    foreach ($options as $option) {

        $stmt = $pdo->prepare("SELECT * FROM investmentoptions WHERE fund_id = :userId AND class_id = :optionId");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':optionId', $option['value']);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        
        if ($rowCount > 0) {
            // Rows exist
            // Perform your desired actions
                // Return a success message
   
    $response = ['success' => false, 'message' => 'Options already saved.'];

        } else {
            $stmt = $pdo->prepare("INSERT INTO investmentoptions (fund_id, class_id,date) VALUES (:userId, :optionId,:date)");
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':optionId', $option['value']);
            $stmt->execute();
                // Return a success message
   
    $response = ['success' => true, 'message' => 'Options saved successfully.'];
        }

     
    }


  } else {
    // Return an error message if user not found
    $response = ['success' => false, 'message' => 'User not found.'];
  }
} catch (PDOException $e) {
  // Return an error message if an exception occurs
  $response = ['success' => false, 'message' => 'Failed to save options: ' . $e->getMessage()];
}

echo json_encode($response);
