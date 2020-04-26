<?php

namespace App\Controllers;

use \App\DatabaseService;
use FilipSedivy\EET\Certificate;
use FilipSedivy\EET\Dispatcher;
use FilipSedivy\EET\Receipt;
use Ramsey\Uuid\Uuid;

class OrderController
{
    private $db;

    function __construct()
    {
        $databaseService = new DatabaseService();
        $this->db = $databaseService->getConnection();
    }

    public function getOrders(int $ownerId, int $pageIndex = 0, int $pageSize = 4): ?array
    {   
        $query = "SELECT COUNT(*) FROM Orders WHERE OwnerId = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $ownerId, \PDO::PARAM_INT);
        $stmt->execute();

        $totalOrdersCount = (int) $stmt->fetchColumn();

        $query = "SELECT * FROM Orders WHERE OwnerId = ? ORDER BY CreatedAt DESC LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);

        if ($pageIndex == 0) $offset = 0;
        else {
            $offset = $pageIndex * $pageSize;
        }

        $stmt->bindParam(1, $ownerId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $pageSize, \PDO::PARAM_INT);
        $stmt->bindParam(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $orderList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $response["hasMore"] = ($totalOrdersCount > ($offset+$pageSize)) ? 1 : 0;
            $response["totalOrdersCount"] = $totalOrdersCount;
            $response["orders"] = $orderList;
            return $response;
        }

        return null;
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

        if ($status) {
            $orderId = (int) $this->db->lastInsertId();
            $newOrderItemId = $this->insertNewOrderItem($orderId);
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
        $isCancelled = (isset($data->isCancelled)) ? (int) $data->isCancelled : 0;

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $ownerId);
        $stmt->bindParam(2, $isCancelled);
        $stmt->bindParam(3, $orderId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) return true;
        return false;
    }

    public function submitOrder()
    {
        $receipt = new Receipt;
        $receipt->uuid_zpravy = Uuid::uuid4()->toString();
        $receipt->id_provoz = '141';
        $receipt->id_pokl = '1patro-vpravo';
        $receipt->porad_cis = '141-18543-05';
        $receipt->dic_popl = 'CZ00000019';
        $receipt->dat_trzby = new \DateTime;
        $receipt->celk_trzba = 500;

        $certificate = new Certificate('.\app\Src\EET_CA1_Playground-CZ00000019.p12', 'eet');
        $dispatcher = new Dispatcher($certificate, Dispatcher::PLAYGROUND_SERVICE);

        try {
            $dispatcher->send($receipt);

            echo 'FIK: ' . $dispatcher->getFik();
            echo 'BKP: ' . $dispatcher->getBkp();
        } catch (FilipSedivy\EET\Exceptions\EET\ClientException $exception) {
            echo 'BKP: ' . $exception->getBkp();
            echo 'PKP:' . $exception->getPkp();
        } catch (FilipSedivy\EET\Exceptions\EET\ErrorException $exception) {
            echo '(' . $exception->getCode() . ') ' . $exception->getMessage();
        } catch (FilipSedivy\EET\Exceptions\Receipt\ConstraintViolationException $violationException) {
            echo implode('<br>', $violationException->getErrors());
        }

        // // eet
        // $query = "UPDATE Orders SET OwnerId = ?, IsCancelled = ? WHERE Id = ?";

        // $data = json_decode(file_get_contents("php://input"));
        // $orderId = (int) $data->orderId;
        // $ownerId = (int) $data->ownerId;
        // $isCancelled = (isset( $data->isCancelled )) ? (int) $data->isCancelled : 0;

        // $stmt = $this->db->prepare($query);
        // $stmt->bindParam(1, $ownerId);
        // $stmt->bindParam(2, $isCancelled);
        // $stmt->bindParam(3, $orderId);
        // $stmt->execute();

        // if ($stmt->rowCount() > 0) return true;
        // return false;
    }

    public function insertNewOrderItem(int $orderId = null): bool
    {
        if (is_null($orderId)) {
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

    public function getOrderItems(int $orderId): ?array
    {
        $query = "SELECT * FROM OrderItems WHERE OrderId = ? ORDER BY Id ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $orderId, \PDO::PARAM_INT);
        $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $itemList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $itemList;
        }
    }
}
