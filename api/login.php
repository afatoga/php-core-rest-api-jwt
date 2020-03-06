<?php
/*use Tracy\Debugger;

Debugger::enable();*/


include_once './config/database.php';
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$email = '';
$password = '';

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$table_name = 'users';

$query = "SELECT Id, Password FROM " . $table_name . " WHERE Email = ? LIMIT 0,1";

$stmt = $conn->prepare( $query );
$stmt->bindParam(1, $email);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //$id = $row['id'];
    $password2 = $row['Password'];

    if(password_verify($password, $password2))
    {
        $secret_key = "n<up,[QXXc07wK<M0eYpA?+3{~r;05cZCg>MH73^o#Uz8LhlTKB<&ZL_CuG3-unU"; //"YOUR_SECRET_KEY";
        $issuer_claim = "dubsea.g6.cz"; // this can be the servername
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 10; //not before in seconds
        $expire_claim = $issuedat_claim + 2629743; // expire time in seconds, month
        $token = [
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => [
                //"id" => $id,
                //"firstname" => $firstname,
                //"lastname" => $lastname,
                "email" => $email,
            ]];

        http_response_code(200);

        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => $jwt,
                "email" => $email,
                "expireAt" => $expire_claim
            ));
    }
    else{
        http_response_code(401);
        echo json_encode(array("message" => "Login failed.", "password" => $password));
    }
}
