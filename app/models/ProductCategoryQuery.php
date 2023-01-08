<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Exception;

class ProductCategoryQuery{
    
    /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn){
        $this->conn = $conn;
    }
    
    public function findById(int $id): ProductCategory{
        $record = $this->conn->createQueryBuilder()
                ->select('*')
                ->from('product_category')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute()->fetchAssociative();
        if(!$record) {
            throw new Exception('No product category found with specified \$id');
        }
        return (new ProductCategory($this->conn))->setId($record['id'])
                ->setName($record['name'])
                ->setDescription($record['description'])
                ->setIsActive((bool)$record['is_active']);
    }
    
    public function getAll(): array{
        $records = $this->conn->createQueryBuilder()
                ->select("*")
                ->from("product_category")
                ->orderBy("name", "asc")
                ->execute()
                ->fetchAllAssociative();
        $productCategories = [];
        foreach($records as $record){
            $productCategories[] = (new ProductCategory($this->conn))
                    ->setId($record["id"])
                    ->setName($record["name"])
                    ->setDescription($record["description"])
                    ->setIsActive((bool)$record["is_active"]);
        }
        return $productCategories;
    }
    
    public function getTotalRecords(): int{
        return 
            ($this->conn->createQueryBuilder()->select('count(*)')
                ->from('product_category')->execute()->fetchOne());
    }
    
    public function getPage(int $pageNumber = 1, int $pageSize = 25): array{
        $startRecordIndex = ($pageNumber - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
                ->select("*")
                ->from("product_category")
                ->orderBy("name", "asc")
                ->setFirstResult($startRecordIndex)
                ->setMaxResults($pageSize)
                ->execute()
                ->fetchAllAssociative();
        $productCategories = [];
        foreach($records as $record){
            $productCategories[] = (new ProductCategory($this->conn))
                    ->setId($record["id"])
                    ->setName($record["name"])
                    ->setDescription($record["description"])
                    ->setIsActive((bool)$record["is_active"]);
        }
        return $productCategories;
    }
    
    public function hasProduct(int $categoryId): bool{
        $count = $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from("product")
            ->where("category_id = ?")
            ->setParameter(0, $categoryId)
            ->execute()->fetchOne();
        return (bool)$count;
    }

    public function getActiveProducts(int $categoryId, int $pageNumber = 1, int $pageSize = 25): array{
        $startRecordIndex = ($pageNumber - 1) * $pageSize;
        $records = $this->conn->createQueryBuilder()
            ->select("*")
            ->from("product")
            ->orderBy("name", "asc")
            ->where("is_active = 1")
            ->andWhere("is_deleted = 0")
            ->andWhere("category_id = ?")
            ->setParameter(0, $categoryId)
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

    public function getTotalActiveProducts(int $categoryId): int{
        return $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('product')
            ->where('is_deleted = 0')
            ->andWhere('is_active = 1')
            ->andWhere('category_id = ?')
            ->setParameter(0, $categoryId)
            ->execute()->fetchOne();
    }
}

