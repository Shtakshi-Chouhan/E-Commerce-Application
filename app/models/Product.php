<?php
namespace OS\models;

use Exception;
use Doctrine\DBAL\Connection;
use stdClass;

class Product extends stdClass{
    
    private $id;
    private $categoryId;
    private $name;
    private $description;
    private $image;
    private $costPrice;
    private $sellingPrice;
    private $isInStock = false;
    private $isActive = false;
    
    /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    public function getId(): int{
        return $this->id;
    }
    
    public function setId(int $id): Product{
        $this->id = $id;
        return $this;
    }
    
    public function getCategoryId(): int{
        return $this->categoryId;
    }
    
    public function getCategory(): ProductCategory{
        return (new ProductCategoryQuery($this->conn))->findById($this->categoryId);
    }
    
    /**
     * 
     * @param int $catId
     * @return Product
     */
    public function setCategoryId(int $catId): Product{
        if($catId == 0){
            throw new Exception("You must select category.");
        }
        $this->categoryId = $catId;
        return $this;
    }
    
    public function getName(): string{
        return $this->name;
    }
    
    /**
     * 
     * @param string $name
     * @return Product
     * @throws Exception
     */
    public function setName(string $name): Product{
        if (empty(trim($name))) {
            throw new Exception("Name cannot be empty");
        }
        $this->name = $name;
        return $this;
    }
    
    public function getDescription(): string{
        return $this->description;
    }
    
    public function setDescription(string $description): Product{
        $this->description = $description;
        return $this;
    }
    
    public function getImage(): string{
        return $this->image;
    }
    
    /**
     * 
     * @param string $value
     * @return Product
     */
    public function setImage(string $value): Product{
        $this->image = $value;
        return $this;
    }
    
    public function getCostPrice(): float{
        return $this->costPrice;
    }
    
    /**
     * 
     * @param float $cp
     * @return Product
     * @throws Exception
     */
    public function setCostPrice(float $cp): Product{
        if ($cp < 0) {
            throw new Exception("Cost price must be greater than or equal to zero.");
        }
        $this->costPrice = $cp;
        return $this;
    }
    
    public function getSellingPrice(): float{
        return $this->sellingPrice;
    }
    
    /**
     * 
     * @param float $sp
     * @return Product
     * @throws Exception
     */
    public function setSellingPrice(float $sp): Product{
        if ($sp < 0) {
            throw new Exception("Selling price must be greater than or equal to zero.");
        }
        $this->sellingPrice = $sp;
        return $this;
    }
    
    public function getIsInStock(): bool{
        return $this->isInStock;
    }
    
    public function setIsInStock(bool $value): Product{
        $this->isInStock = $value;
        return $this;
    }
    
    public function getIsActive(): bool{
        return $this->isActive;
    }
    
    public function setIsActive(bool $value): Product{
        $this->isActive = $value;
        return $this;
    }
    
    // Database Operation for this record
    
    public function insert(): Product{
        $this->conn->createQueryBuilder()
                ->insert("product")
                ->values([
                    "category_id" => "?",
                    "name" => "?",
                    "description" => "?",
                    "image" => "?",
                    "cost_price" => "?",
                    "selling_price" => "?",
                    "is_in_stock" => "?",
                    "is_active" => "?"
                ])->setParameters([
                    $this->categoryId,
                    $this->name,
                    $this->description,
                    $this->image,
                    $this->costPrice,
                    $this->sellingPrice,
                    intval($this->isInStock),
                    intval($this->isActive)
                ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(): Product{
        $this->conn->createQueryBuilder()
                ->update("product")
                ->set("category_id", "?")
                ->set("name", "?")
                ->set("description", "?")
                ->set("image", "?")
                ->set("cost_price", "?")
                ->set("selling_price", "?")
                ->set("is_in_stock", "?")
                ->set("is_active", "?")
                ->where("id = ?")
                ->setParameters([
                    $this->categoryId,
                    $this->name,
                    $this->description,
                    $this->image,
                    $this->costPrice,
                    $this->sellingPrice,
                    intval($this->isInStock),
                    intval($this->isActive),
                    $this->id
                ])->execute();
        return $this;
    }
    
    public function delete(){
        $this->conn->createQueryBuilder()
                ->update("product")
                ->set("is_deleted", "1")
                ->where("id = ?")
                ->setParameter(0, $this->id)
                ->execute();
    }
    
}

