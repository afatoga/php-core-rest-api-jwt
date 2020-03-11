<?php
namespace App\Controllers;

use \App\DatabaseService;

class OrderController
{
	public function createNewOrder(): bool
	{
        $data = json_decode(file_get_contents("php://input"));

        $title = 'preklad';
        $price = $data->price;

        $databaseService = new DatabaseService();
        $conn = $databaseService->getConnection();

        $table_name = 'orders';

        $query = "INSERT INTO " . $table_name . " (Title, Price) VALUES (?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $price);
        $status = $stmt->execute();

        if ($status) return true;
        return false;
    }
}