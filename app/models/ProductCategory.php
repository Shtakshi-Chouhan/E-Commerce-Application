<?php
namespace OS\models;

use Doctrine\DBAL\Connection;
use Exception;
use stdClass;

class ProductCategory extends stdClass{
    
    private $id;
    private $name;
    private $description;
    private $isActive;
    
    /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    public function getId(): int{
        return $this->id;
    }
    
    public function setId(int $value): ProductCategory{
        $this->id = $value;
        return $this;
    }
    
    public function getName(): string{
        return $this->name;
    }
    
    public function setName(string $value): ProductCategory{
        if (empty(trim($value))) {
            throw new Exception("Name field cannot be empty.");
        }
        $this->name = trim($value);
        return $this;
    }
    
    public function getDescription(): string{
        return $this->description;
    }
    
    public function setDescription(string $value): ProductCategory{
        $this->description = trim($value);
        return $this;
    }
    
    public function getIsActive(): bool{
        return $this->isActive;
    }
    
    public function setIsActive(bool $value): ProductCategory{
        $this->isActive = $value;
        return $this;
    }
    
    public function insert(): ProductCategory{
        $this->conn->createQueryBuilder()
                ->insert('product_category')
                ->values([
                    'name' => '?',
                    'description' => '?',
                    'is_active' => '?'
                ])->setParameters([
                    $this->name,
                    $this->description,
                    intval($this->isActive)
                ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(): ProductCategory{
        $this->conn->createQueryBuilder()
                ->update("product_category")
                ->set("name", "?")
                ->set("description", "?")
                ->set("is_active", "?")
                ->where("id = ?")
                ->setParameters([
                    $this->name,
                    $this->description,
                    intval($this->isActive),
                    $this->id
                ])->execute();
        return $this;
    }
    
    public function delete(){
        $productsCount = $this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('product')
            ->where('category_id = ?')
            ->setParameter(0, $this->id)
            ->execute()->fetchOne();
        if($productsCount > 0)
            throw new Exception("Category cannot be deleted anymore since products are attached to it.");
        $this->conn->createQueryBuilder()
                ->delete("product_category")
                ->where("id = ?")
                ->setParameter(0, $this->id)
                ->execute();
    }
    
    
}

