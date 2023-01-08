<?php
namespace OS\models;

use stdClass;
use Exception;
use Doctrine\DBAL\Connection;

class CustomerOrderDetail extends stdClass{
    
    private $id;
    private $orderId;
    private $productId;
    private $sellingPrice;
    private $quantity;

    /** @var Connection */
    private $conn;

    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getOrderId(): int {
        return $this->orderId;
    }

    public function getProductId(): int {
        return $this->productId;
    }
    
    public function getProduct(): Product{
        return (new ProductQuery($this->conn))->findById($this->productId);
    }

    public function getSellingPrice(): float {
        return $this->sellingPrice;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function setId(int $id): CustomerOrderDetail {
        $this->id = $id;
        return $this;
    }

    public function setOrderId(int $orderId): CustomerOrderDetail {
        $this->orderId = $orderId;
        return $this;
    }

    public function setProductId(int $productId): CustomerOrderDetail {
        $this->productId = $productId;
        return $this;
    }

    public function setSellingPrice(float $sellingPrice): CustomerOrderDetail {
        $this->sellingPrice = $sellingPrice;
        return $this;
    }

    public function setQuantity(int $quantity): CustomerOrderDetail {
        $this->quantity = $quantity;
        return $this;
    }
    
    public function insert(): CustomerOrderDetail{
        $this->conn->createQueryBuilder()
            ->insert('customer_order_details')
            ->values([
                'order_id' => '?',
                'product_id' => '?',
                'selling_price' => '?',
                'quantity' => '?'
            ])->setParameters([
                $this->orderId,
                $this->productId,
                $this->sellingPrice,
                $this->quantity
            ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(){
        $this->conn->createQueryBuilder()
            ->update('customer_order_details')
            ->set('order_id', '?')
            ->set('product_id', '?')
            ->set('selling_price', '?')
            ->set('quantity', '?')
            ->where('id = ?')
            ->setParameters([
                $this->orderId,
                $this->productId,
                $this->sellingPrice,
                $this->quantity,
                $this->id
            ])->execute();
    }
    
    public function delete(){
        $this->conn->createQueryBuilder()
            ->delete('customer_order_details')
            ->where('id = ?')
            ->setParameter(0, $this->id)
            ->execute();
    }


}