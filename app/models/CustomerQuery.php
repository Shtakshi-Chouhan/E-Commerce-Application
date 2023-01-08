<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Exception;
use Throwable;

class CustomerQuery{
    
     /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

    /**
     * @param array $record
     * @return Customer
     * @throws Exception
     */
    private function generateCustomerRecord(array $record): Customer{
        return (new Customer($this->conn))->setId($record['id'])
            ->setFirstName($record['first_name'])
            ->setLastName($record['last_name'])
            ->setEmailAddress($record['email_address'])
            ->setMobileNumber($record['mobile_number'])
            ->setAlternateNumber($record['alternate_number'])
            ->setIsActive((bool)$record['is_active'])
            ->setRegisteredOn(strtotime($record['registered_on']))
            ->setAccountPassword($record['account_password'])
            ->setLastLoginOn(is_null($record['last_login_on']) ? null : strtotime($record['last_login_on']))
            ->setAccountVerificationCode($record['account_verification_code'])
            ->setAccountVerified($record['account_verified']);
    }
    
    /**
     * 
     * @param int $id
     * @return Customer
     * @throws Throwable
     */
    public function findById(int $id): Customer{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer')
            ->where('id = ?')->setParameter(0, $id)
            ->execute()->fetchAssociative();
        if(!$record){
            throw new Exception ('No record found with specified id.');
        }
        return $this->generateCustomerRecord($record);
    }

    /**
     * @param string $emailAddress
     * @return Customer
     * @throws Throwable
     */
    public function findByEmailAddress(string $emailAddress): Customer{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('customer')
            ->where('email_address = ?')
            ->setParameter(0, $emailAddress)
            ->execute()->fetchAssociative();
        if(!$record){
            throw new Exception ('No record found with specified email address.');
        }
        return $this->generateCustomerRecord($record);
    }
    
    public function getTotalRecords(): int{
        return $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('customer')
            ->execute()
            ->fetchOne();
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getAll(): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer')
            ->orderBy('first_name', 'asc')
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = $this->generateCustomerRecord($record);
        }
        return $recordArray;
    }
    
    public function getPage(int $page = 1, int $pageSize = 24): array{
        $startRecordIndex = ($page - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
            ->select('*')->from('customer')
            ->orderBy('first_name', 'asc')
            ->setFirstResult($startRecordIndex)
            ->setMaxResults($pageSize)
            ->execute()->fetchAllAssociative();
        $recordArray = [];
        foreach($records as $record){
            $recordArray[] = $this->generateCustomerRecord($record);
        }
        return $recordArray;
    }

    public function hasAccountWithEmailAddress(string $emailAddress): bool{
        return (bool)$this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('customer')
            ->where('email_address = ?')
            ->setParameter(0, $emailAddress)
            ->execute()->fetchOne();
    }

    /**
     * @param string $verificationCode
     * @return Customer
     * @throws Throwable
     */
    public function findByVerificationCode(string $verificationCode): Customer{
        $record = $this->conn->createQueryBuilder()
            ->select('*')
            ->from('customer')
            ->where("account_verification_code = ?")
            ->setParameter(0, $verificationCode)
            ->execute()->fetchAssociative();
        if(!$record)
            throw new Exception("Invalid verification code. No account found matching verification code.");
        return $this->generateCustomerRecord($record);
    }
}