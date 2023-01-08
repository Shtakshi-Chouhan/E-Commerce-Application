<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Throwable;

class CustomerOrderQuery{
    
     /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

    /**
     *
     * @param int $id
     * @return CustomerOrder
     * @throws Throwable
     */
    public function findById(int $id): CustomerOrder{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_order')
            ->where('id = ?')->setParameter(0, $id)
            ->execute()->fetchOne();
        if(!$record)
            throw new \Exception ('No record found with specified id.');
        return (new CustomerOrder($this->conn))->setId($record['id'])
            ->setOrderDate(strtotime($record['order_date']))
            ->setCustomerId($record['customer_id'])
            ->setShippingAddressId($record['shipping_address_id'])
            ->setOrderStatus($record['order_status'])
            ->setTotalAmount($record['total_amount'])
            ->setTotalTax($record['total_tax'])
            ->setDiscountAmount($record['discount_amount'])
            ->setShippingCharges($record['shipping_charges'])
            ->setGrandTotal($record['grand_total']);
    }
    
    public function getTotalRecords(): int{
        return $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('customer_order')
            ->execute()
            ->fetchOne();
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getAll(): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_order')
            ->orderBy('order_date', 'desc')
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerOrder($this->conn))->setId($record['id'])
            ->setOrderDate(strtotime($record['order_date']))
            ->setCustomerId($record['customer_id'])
            ->setShippingAddressId($record['shipping_address_id'])
            ->setOrderStatus($record['order_status'])
            ->setTotalAmount($record['total_amount'])
            ->setTotalTax($record['total_tax'])
            ->setDiscountAmount($record['discount_amount'])
            ->setShippingCharges($record['shipping_charges'])
            ->setGrandTotal($record['grand_total']);
        }
        return $recordArray;
    }
    
    public function getPage(int $page = 1, int $pageSize = 25): array{
        $startRecordIndex = ($page - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_order')
            ->orderBy('order_date', 'desc')
            ->setFirstResult($startRecordIndex)
            ->setMaxResults($pageSize)
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerOrder($this->conn))->setId($record['id'])
            ->setOrderDate(strtotime($record['order_date']))
            ->setCustomerId($record['customer_id'])
            ->setShippingAddressId($record['shipping_address_id'])
            ->setOrderStatus($record['order_status'])
            ->setTotalAmount($record['total_amount'])
            ->setTotalTax($record['total_tax'])
            ->setDiscountAmount($record['discount_amount'])
            ->setShippingCharges($record['shipping_charges'])
            ->setGrandTotal($record['grand_total']);
        }
        return $recordArray;
    }

    /**
     * @param int $customerId
     * @return array
     * @throws Throwable
     */
    public function getAllByCustomerId(int $customerId): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_order')
            ->where('customer_id = ?')
            ->setParameter(0, $customerId)
            ->orderBy('order_date', 'desc')
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerOrder($this->conn))->setId($record['id'])
            ->setOrderDate(strtotime($record['order_date']))
            ->setCustomerId($record['customer_id'])
            ->setShippingAddressId($record['shipping_address_id'])
            ->setOrderStatus($record['order_status'])
            ->setTotalAmount($record['total_amount'])
            ->setTotalTax($record['total_tax'])
            ->setDiscountAmount($record['discount_amount'])
            ->setShippingCharges($record['shipping_charges'])
            ->setGrandTotal($record['grand_total']);
        }
        return $recordArray;
    }
    
    public function getPageByCustomerId(int $customerId, int $page = 1, int $pageSize = 25): array{
        $startRecordIndex = ($page - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_order')
            ->where('customer_id = ?')
            ->setParameter(0, $customerId)
            ->orderBy('order_date', 'desc')
            ->setFirstResult($startRecordIndex)
            ->setMaxResults($pageSize)
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerOrder($this->conn))->setId($record['id'])
            ->setOrderDate(strtotime($record['order_date']))
            ->setCustomerId($record['customer_id'])
            ->setShippingAddressId($record['shipping_address_id'])
            ->setOrderStatus($record['order_status'])
            ->setTotalAmount($record['total_amount'])
            ->setTotalTax($record['total_tax'])
            ->setDiscountAmount($record['discount_amount'])
            ->setShippingCharges($record['shipping_charges'])
            ->setGrandTotal($record['grand_total']);
        }
        return $recordArray;
    }

}