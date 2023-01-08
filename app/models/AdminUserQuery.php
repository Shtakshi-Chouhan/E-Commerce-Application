<?php
namespace OS\models;

use Doctrine\DBAL\Connection;

class AdminUserQuery{
    
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
    public function findById(int $id): AdminUser{
        $record = $this->conn->createQueryBuilder()
            ->select('*')->from('user')
            ->where('id = ?')->setParameter(0, $id)
            ->execute()->fetchAssociative();
        if(!$record)
            throw new Exception ('No record found with specified id.');
        return (new AdminUser($this->conn))->setId($record['id'])
            ->setName($record['name'])
            ->setEmailAddress($record['email_address'])
            ->setPassword($record['password'])
            ->setIsActive((bool)$record['is_active'])
            ->setIsSuperAdmin((bool)$record['is_super_admin'])
            ->setLastLoginOn(is_null($record['last_login_on']) ? null : strtotime($record['last_login_on']));
    }
    
    public function getAll(bool $loadSuperAdmin = false): array{
        $query = $this->conn->createQueryBuilder()
            ->select('*')->from('user')
            ->orderBy("name", "asc");
        if($loadSuperAdmin === false)
            $query->where("is_super_admin = ?")->setParameter(0, false);
        $records = $query->execute()->fetchAllAssociative();
        $users = [];
        foreach($records as $record){
            $users[] = (new AdminUser($this->conn))->setId($record['id'])
                ->setName($record['name'])
                ->setEmailAddress($record['email_address'])
                ->setPassword($record['password'])
                ->setIsActive((bool)$record['is_active'])
                ->setIsSuperAdmin((bool)$record['is_super_admin'])
                ->setLastLoginOn(is_null($record['last_login_on']) ? null : strtotime($record['last_login_on']));
        }
        return $users;
    }
    
    public function getPage(int $pageNumber = 1, int $pageSize = 25): array{
        $startRecordIndex = ($pageNumber - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
            ->select("*")->from("user")
            ->orderBy("name", "asc")
            ->setFirstResult($startRecordIndex)
            ->setMaxResults($pageSize)
            ->execute()->fetchAllAssociative();
        foreach($records as $record){
            $users[] = (new AdminUser($this->conn))->setId($record['id'])
                ->setName($record['name'])
                ->setEmailAddress($record['email_address'])
                ->setPassword($record['password'])
                ->setIsActive((bool)$record['is_active'])
                ->setIsSuperAdmin((bool)$record['is_super_admin'])
                ->setLastLoginOn(is_null($record['last_login_on']) ? null : strtotime($record['last_login_on']));
        }
    }
    
    public function getTotalRecords(): int{
        return $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('user')
            ->execute()->fetchOne();
    }
    
    public function getByEmailAddress(string $emailAddress): AdminUser{
        $record = $this->conn->createQueryBuilder()
            ->select('*')
            ->from('user')
            ->where("email_address = ?")
            ->setParameter(0, $emailAddress)
            ->execute()->fetchAssociative();
        if(!$record) {
            throw new Exception ('No account found matching login details.');
        }
        return (new AdminUser($this->conn))->setId($record['id'])
            ->setName($record['name'])
            ->setEmailAddress($record['email_address'])
            ->setPassword($record['password'])
            ->setIsActive((bool)$record['is_active'])
            ->setIsSuperAdmin((bool)$record['is_super_admin'])
            ->setLastLoginOn(is_null($record['last_login_on']) ? null : strtotime($record['last_login_on']));
    }

}