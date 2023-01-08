<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Exception;

Class ProductQuery{
    
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
    public function findById(int $id): Product{
        $record = $this->conn->createQueryBuilder()
                ->select('*')->from('product')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute()->fetchAssociative();
        if(!$record)
            throw new Exception("No product found with specified \$id");
        $product = new Product($this->conn);
        return $product->setId($id)
                ->setCategoryId($record['category_id'])
                ->setName($record['name'])
                ->setDescription($record['description'])
                ->setSellingPrice($record['selling_price'])
                ->setCostPrice($record['cost_price'])
                ->setImage($record['image'])
                ->setIsInStock((bool)$record['is_in_stock'])
                ->setIsActive($record['is_active']);
    }
    
    /**
     * 
     * @param type $pageNumber
     * @param type $pageSize
     * @param type $totalPages
     * @return array
     * @throws Exception
     */
    public function getAll(): array{
        $records = $this->conn->createQueryBuilder()
                ->select('*')
                ->from('product')
                ->where('is_deleted = 0')
                ->execute()->fetchAllAssociative();
        $products = [];
        foreach($records as $record){
            $products[] = (new Product($this->conn))->setId($record['id'])
                ->setCategoryId($record['category_id'])
                ->setName($record['name'])
                ->setDescription($record['description'])
                ->setSellingPrice($record['selling_price'])
                ->setCostPrice($record['cost_price'])
                ->setImage($record['image'])
                ->setIsInStock((bool)$record['is_in_stock'])
                ->setIsActive($record['is_active']);
        }        
        return $products;
    }
    
    public function getTotalRecords(bool $activeProduct = false): int{
        $query = $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('product')
            ->where('is_deleted = 0');
        if($activeProduct)
            $query->where("is_active = 1");
        return $query->execute()->fetchOne();
    }
    
    /**
     * 
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     */
    public function getPage(int $pageNumber = 1, int $pageSize = 25): array{
        $startRecordIndex = ($pageNumber - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
                ->select("*")
                ->from("product")
                ->orderBy("name", "asc")
                ->where("is_deleted = 0")
                ->setFirstResult($startRecordIndex)
                ->setMaxResults($pageSize)
                ->execute()
                ->fetchAllAssociative();
        $products = [];
        foreach($records as $record){
            $products[] = (new Product($this->conn))
                    ->setId($record["id"])
                    ->setCategoryId($record['category_id'])
                    ->setName($record["name"])
                    ->setDescription($record["description"])
                    ->setImage($record['image'])
                    ->setSellingPrice($record['selling_price'])
                    ->setCostPrice($record['cost_price'])
                    ->setIsInStock((bool)$record['is_in_stock'])
                    ->setIsActive($record['is_active']);
        }
        return $products;
    }

    /**
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getActiveProductPage(int $pageNumber = 1, int $pageSize = 25): array{
        $startRecordIndex = ($pageNumber - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
            ->select("*")
            ->from("product")
            ->orderBy("name", "asc")
            ->where("is_active = 1")
            ->where("is_deleted = 0")
            ->setFirstResult($startRecordIndex)
            ->setMaxResults($pageSize)
            ->execute()
            ->fetchAllAssociative();
        $products = [];
        foreach($records as $record){
            $products[] = (new Product($this->conn))
                ->setId($record["id"])
                ->setCategoryId($record['category_id'])
                ->setName($record["name"])
                ->setDescription($record["description"])
                ->setImage($record['image'])
                ->setSellingPrice($record['selling_price'])
                ->setCostPrice($record['cost_price'])
                ->setIsInStock((bool)$record['is_in_stock'])
                ->setIsActive($record['is_active']);
        }
        return $products;
    }
    
    public function getDeleted(): array{
        $records = $this->conn->createQueryBuilder()
            ->select('*')
            ->from('product')
            ->where('is_deleted = 1')
            ->execute()->fetchAllAssociative();
        $products = [];
        foreach($records as $record){
            $products[] = (new Product($this->conn))->setId($record['id'])
                ->setCategoryId($record['category_id'])
                ->setName($record['name'])
                ->setDescription($record['description'])
                ->setSellingPrice($record['selling_price'])
                ->setCostPrice($record['cost_price'])
                ->setImage($record['image'])
                ->setIsInStock((bool)$record['is_in_stock'])
                ->setIsActive($record['is_active']);
        }        
        return $products;
    }
    
}