<?php
namespace App\Controllers;

use \App\DatabaseService;

class OrderController
{   
    public function getOrders(int $count): ?array
    {
        $databaseService = new DatabaseService();
        $conn = $databaseService->getConnection();

        $query = "SELECT * FROM orders ORDER BY CreatedAt DESC LIMIT ?";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $count, \PDO::PARAM_INT);
        $stmt->execute();

        $num = $stmt->rowCount();
        if($num > 0)
        {
            $orderList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $orderList;
        }

        return null;
    }
    
	public function createNewOrder(): bool
	{
        $data = json_decode(file_get_contents("php://input"));

        $title = 'preklad';
        $price = (float) $data->price;
        //carka v objeku json {} if (!$price) return false;

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