<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Exception;
use Throwable;

class CustomerBillingAddressQuery{
    
     /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

    /**
     *
     * @param int $id
     * @return CustomerBillingAddress
     * @throws Throwable
     */
    public function findById(int $id): CustomerBillingAddress{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_billing_address')
            ->where('id = ?')->setParameter(0, $id)
            ->execute()->fetchAssociative();
        if(!$record){
            throw new Exception ('No record found with specified id.');
        }
        return (new CustomerBillingAddress($this->conn))->setId($record['id'])
            ->setCustomerId($record['customer_id'])
            ->setBillingName($record['billing_name'])
            ->setAddressLine1($record['address_line_1'])
            ->setAddressLine2($record['address_line_2'])
            ->setLandmark($record['landmark'])
            ->setCity($record['city'])
            ->setState($record['state'])
            ->setCountry($record['country'])
            ->setPincode($record['pincode']);
    }

    /**
     * @throws Throwable
     */
    public function getByCustomerId(int $customerId): CustomerBillingAddress{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_billing_address')
            ->where('customer_id = ?')->setParameter(0, $customerId)
            ->execute()->fetchAssociative();
        if(!$record){
            throw new Exception ('No record found with specified id.');
        }
        return (new CustomerBillingAddress($this->conn))->setId($record['id'])
            ->setCustomerId($record['customer_id'])
            ->setBillingName($record['billing_name'])
            ->setAddressLine1($record['address_line_1'])
            ->setAddressLine2($record['address_line_2'])
            ->setLandmark($record['landmark'])
            ->setCity($record['city'])
            ->setState($record['state'])
            ->setCountry($record['country'])
            ->setPincode($record['pincode']);
    }

}