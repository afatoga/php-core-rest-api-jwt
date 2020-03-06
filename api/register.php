<?php
include_once './config/database.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$email = '';
$password = '';
$conn = null;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$query = 'SELECT Id from users WHERE Email = ? LIMIT 0,1';
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $email);
$stmt->execute();
$rows = $stmt->rowCount();

if ($rows > 0)
{
    http_response_code(400);
    echo json_encode(array("message" => "User with this email already exists."));
    return;
}

$query = "INSERT INTO users
                SET Email = :email,
                    Password = :password";

$stmt = $conn->prepare($query);

$stmt->bindParam(':email', $email);

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt->bindParam(':password', $password_hash);

if($stmt->execute()){

    http_response_code(200);
    echo json_encode(array("message" => "User was successfully registered."));
}
else{
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register the user."));
}
?>