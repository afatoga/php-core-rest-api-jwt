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
    }

    public function getOrderTotalPrice(int $orderId, int $ownerId): ?float
    {
        if (!$this->doesOrderExist($orderId, $ownerId)) return null;

        $query = "SELECT SUM(Price) FROM OrderItems WHERE OrderId = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $orderId);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function createNewOrder(): bool
    {
        $data = json_decode(file_get_contents("php://input"));
        $ownerId = (int) $data->ownerId;
        $query = "INSERT INTO Orders (OwnerId) VALUES (?)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $ownerId);
        $status = $stmt->execute();

        if ($status)
        {
            $orderId = (int) $this->db->lastInsertId();
            $newOrderItemId = $this->insertNewOrderItem( $orderId );
            if ($newOrderItemId) return true;
        }
        return false;
    }

    public function updateOrder(): bool
    {
        $query = "UPDATE Orders SET OwnerId = ?, IsCancelled = ? WHERE Id = ?";

        $data = json_decode(file_get_contents("php://input"));
        $orderId = (int) $data->orderId;
        $ownerId = (int) $data->ownerId;
        $isCancelled = (isset( $data->isCancelled )) ? (int) $data->isCancelled : 0;

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $ownerId);
        $stmt->bindParam(2, $isCancelled);
        $stmt->bindParam(3, $orderId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) return true;
        return false;
    }

    public function insertNewOrderItem(int $orderId = null): bool
    {
        if (is_null($orderId))
        {
            $data = json_decode(file_get_contents("php://input"));
            $orderId = (int) $data->orderId;
            $price = (float) $data->price;
            $title = (string) $data->title;
        } else {
            //new order creation
            $title = 'preklad';
            $price = 0;
        }

        $query = "INSERT INTO OrderItems (OrderId, Title, Price) VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $orderId);
        $stmt->bindParam(2, $title);
        $stmt->bindParam(3, $price);
        $status = $stmt->execute();

        if ($status) return true;
        return false;
    }

    public function updateOrderItem(): bool
    {
        $query = "UPDATE OrderItems SET Title = ?, Price = ? WHERE Id = ?";

        $data = json_decode(file_get_contents("php://input"));
        $orderItemId = (int) $data->orderItemId;
        $title = (string) $data->title;
        $price = (float) $data->price;

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $price);
        $stmt->bindParam(3, $orderItemId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) return true;
        return false;
    }

    private function doesOrderExist(int $orderId, int $ownerId): bool
    {
        $query = "SELECT Id FROM Orders WHERE Id = ? AND OwnerId = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $orderId);
        $stmt->bindParam(2, $ownerId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) return true;
        return false;
    }
}
