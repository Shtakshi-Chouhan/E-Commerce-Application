<?php
namespace OS\models;

use Doctrine\DBAL\Connection;

class CustomerOrderDetailQuery{
    
     /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    /**
     * 
     * @param int $id
     * @return Product
     * @throws Exception
     */
    public function findById(int $id): CustomerOrderDetail{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_order_details')
            ->where('id = ?')->setParameter(0, $id)
            ->execute()->fetchOne();
        if(!$record){
            throw new Exception ('No record found with specified id.');
        }
        return (new CustomerOrderDetail($this->conn))->setId($record['id'])
            ->setOrderId($record['order_id'])
            ->setProductId($record['product_id'])
            ->setSellingPrice($record['selling_price'])
            ->setQuantity($record['quantity']);
    }
    
    public function getAllByOrderId(int $orderId): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')
            ->from('customer_order_details')
            ->where('order_id = ?')
            ->setParameter(0, $orderId)
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerOrderDetail($this->conn))->setId($record['id'])
            ->setOrderId($record['order_id'])
            ->setProductId($record['product_id'])
            ->setSellingPrice($record['selling_price'])
            ->setQuantity($record['quantity']);
        }
    }
}