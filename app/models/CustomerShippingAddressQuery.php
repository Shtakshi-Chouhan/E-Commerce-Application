<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Throwable;

class CustomerShippingAddressQuery{
    
     /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

    /**
     *
     * @param int $id
     * @return CustomerShippingAddress
     * @throws Throwable
     */
    public function findById(int $id): CustomerShippingAddress{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_shipping_address')
            ->where('id = ?')->setParameter(0, $id)
            ->execute()->fetchOne();
        if(!$record){
            throw new \Exception ('No record found with specified id.');
        }
        return (new CustomerShippingAddress($this->conn))->setId($record['id'])
            ->setCustomerId($record['customer_id'])
            ->setType($record['type'])
            ->setFirstName($record['first_name'])
            ->setLastName($record['last_name'])
            ->setEmailAddress($record['email_address'])
            ->setMobileNumber($record['mobile_number'])
            ->setAddressLine1($record['address_line_1'])
            ->setAddressLine2($record['address_line_2'])
            ->setLandmark($record['landmark'])
            ->setCity($record['city'])
            ->setState($record['state'])
            ->setCountry($record['country'])
            ->setPincode($record['pincode']);
    }

    /**
     * @param int $customerId
     * @return array
     * @throws Throwable
     */
    public function getAllByCustomerId(int $customerId): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_shipping_address')
            ->where('customer_id = ?')->setParameter(0, $customerId)
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerShippingAddress($this->conn))->setId($record['id'])
                ->setCustomerId($record['customer_id'])
                ->setType($record['type'])
                ->setFirstName($record['first_name'])
                ->setLastName($record['last_name'])
                ->setEmailAddress($record['email_address'])
                ->setMobileNumber($record['mobile_number'])
                ->setAddressLine1($record['address_line_1'])
                ->setAddressLine2($record['address_line_2'])
                ->setLandmark($record['landmark'])
                ->setCity($record['city'])
                ->setState($record['state'])
                ->setCountry($record['country'])
                ->setPincode($record['pincode']);
        }
        return $recordArray;
    }

    /**
     * @param int $customerId
     * @param string $type
     * @return array
     * @throws Throwable
     */
    public function getByAddressType(int $customerId, string $type): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer_shipping_address')
            ->where('customer_id = ?')
            ->andWhere('type = ?')
            ->setParameter(0, $customerId)
            ->setParameter(1, $type)
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = (new CustomerShippingAddress($this->conn))->setId($record['id'])
                ->setCustomerId($record['customer_id'])
                ->setType($record['type'])
                ->setFirstName($record['first_name'])
                ->setLastName($record['last_name'])
                ->setEmailAddress($record['email_address'])
                ->setMobileNumber($record['mobile_number'])
                ->setAddressLine1($record['address_line_1'])
                ->setAddressLine2($record['address_line_2'])
                ->setLandmark($record['landmark'])
                ->setCity($record['city'])
                ->setState($record['state'])
                ->setCountry($record['country'])
                ->setPincode($record['pincode']);
        }
        return $recordArray;
    }

}