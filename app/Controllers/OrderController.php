<?php

namespace App\Controllers;

use \App\DatabaseService;

class OrderController
{
    private $db;

    function __construct()
    {
        $databaseService = new DatabaseService();
        $this->db = $databaseService->getConnection();
    }

    public function getOrders(int $count): ?array
    {
        $query = "SELECT * FROM Orders ORDER BY CreatedAt DESC LIMIT ?";

        $stmt = $this->db->prepare($query);

        if ($count == 0 || $count >= 99) $count = 100;

        $stmt->bindParam(1, $count, \PDO::PARAM_INT);
        $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) 
        {
            $orderList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $orderList;
        }

        return null;
    }

    public function getOrderTotalPrice(int $orderId): ?float
    {
        if (!$this->doesOrderExist($orderId)) return null;

        $query = "SELECT SUM(Price) FROM OrderItems WHERE OrderId = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $orderId);
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }

    public function createNewOrder(): bool
    {
        $data = json_decode(file_get_contents("php://input"));

        $query = "INSERT INTO Orders (Id) VALUES (NULL)";

        $stmt = $this->db->prepare($query);
        $status = $stmt->execute();

        if ($status)
        {
            $newOrderItemId = $this->insertNewOrderItem( $this->db->lastInsertId() );
            if ($newOrderItemId) return true;
        }
        return false;
    }

    public function insertNewOrderItem(int $orderId = null): bool
    {
        if ($orderId == null) {
            $data = json_decode(file_get_contents("php://input"));
            $orderId = (int) $data->orderId;
            $price = (float) $data->price;
            $title = (string) $data->title;
        } else {
            $title = 'preklad';
        }

        $query = "INSERT INTO OrderItems (Title, Price) VALUES (?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $price);
        $status = $stmt->execute();

        // if ($status)
        // {
        //     $query = "UPDATE OrderItems SET Price = ? WHERE Id = ?";

        //     $stmt = $this->db->prepare($query);
        //     $stmt->bindParam(1, $price);
        //     $stmt->bindParam(2, $orderId);
        //     $status = $stmt->execute();
        // }

        if ($status) return true;
        return false;
    }

    public function updateOrderItem(): bool
    {
        $query = "UPDATE OrderItems SET (Title, Price) VALUES (?, ?) WHERE Id = ?";

        $data = json_decode(file_get_contents("php://input"));
        $orderItemId = (int) $data->orderItemId;
        $title = (string) $data->title;
        $price = (float) $data->price;

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $price);
        $stmt->bindParam(3, $orderItemId);
        $status = $stmt->execute();

        if ($status) return true;
        return false;
    }

    private function doesOrderExist(int $orderId): bool
    {
        $query = "SELECT Id FROM Orders WHERE Id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $orderId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) return true;
        return false;
    }
}
