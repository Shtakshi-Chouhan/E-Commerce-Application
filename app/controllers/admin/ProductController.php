<?php
namespace OS\controllers\admin;

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use OS\helpers\ViewRenderer;
use OS\helpers\AjaxResponse;
use OS\models\Product;
use OS\models\ProductQuery;
use OS\models\ProductCategoryQuery;

class ProductController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function list(Request $request, Response $response): Response{
        $queryParams = $request->getQueryParams();
        $error = false;
        $pageSize = 25;
        $records = [];
        $pageNo = $queryParams['page'] ?? 1;
        $totalPages = 1;
        $categories = [];
        $recordCounter = (($pageNo - 1) * $pageSize) + 1 ;
        try{
            $records = (new \OS\models\ProductQuery($this->conn))->getPage($pageNo, $pageSize);
            $totalRecords = (new \OS\models\ProductQuery($this->conn))->getTotalRecords();
            $totalPages = ceil($totalRecords / $pageSize);
            $categories = (new \OS\models\ProductCategoryQuery($this->conn))->getAll();
        } catch (Exception $ex) {
            $error = true;
        }
        return ViewRenderer::render(
            $response,
            "admin/product/list",
            [
                'error' => $error,
                'records' => $records,
                'currentPage' => $pageNo,
                'totalPages' => $totalPages,
                'pageTitle' => 'Manage Product',
                'currentMenuItem' => 'product',
                'categories' => $categories,
                'recordCounter' => $recordCounter
            ],
            "admin"
        );
    }
    
    public function add(Request $request, Response $response): Response{
        $res = new AjaxResponse();
        
        try{
            $formData = $request->getParsedBody();
            $productImage = $_FILES['productImage'] ?? false;
            
            if($productImage === false){
                throw new Exception('Product Image is mandatory field.');
            }
            if($productImage['error'] != UPLOAD_ERR_OK){
                throw new Exception('Product Image failed to upload.');
            }
            if(in_array($productImage['type'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp']) === false){
                throw new Exception('Product image file extension must be of type - png, jpeg, gif or webp');
            }
            if(is_uploaded_file($productImage['tmp_name']) === false){
                throw new Exception("Fail to save product image. #1");
            }
            $uploadFileExtension = strtolower(pathinfo($productImage['name'], PATHINFO_EXTENSION));
            $saveFileName = uniqid('product-image-') . '.' . $uploadFileExtension;
            $saveFilePath = UPLOAD_DIR . '/' . $saveFileName;
            if(move_uploaded_file($productImage['tmp_name'], $saveFilePath) === false){
                throw new Exception("Fail to save product image. #2");
            }   
            
            $product = new Product($this->conn);
            $product->setCategoryId(intval($formData['categoryId']))
                    ->setName($formData['productName'])
                    ->setDescription($formData['description'])
                    ->setImage($saveFileName)
                    ->setCostPrice(floatval($formData['costPrice']))
                    ->setSellingPrice(floatval($formData['sellingPrice']))
                    ->setIsInStock((bool)$formData['isInStock'])
                    ->setIsActive((bool)$formData['isActive'])
                    ->insert();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
    public function edit(Response $response, $recordId): Response{
        $error = false;
        try{
            $product = (new ProductQuery($this->conn))->findById($recordId);
            $categories = (new ProductCategoryQuery($this->conn))->getAll();
        } catch (Exception $ex) {
            $error = true;
        }
        return ViewRenderer::render(
            $response, 
            "admin/product/edit", 
            [
                'error' => $error, 
                'product' => $product,
                'categories' => $categories
            ]
        );
    }
    
    public function save(Request $request, Response $response, $recordId): Response{
        $res = new AjaxResponse();
        
        try{
            $formData = $request->getParsedBody();
            $productImage = $_FILES['productImage'];
            $product = (new ProductQuery($this->conn))->findById($recordId);
            $product->setName($formData['productName'])
                    ->setCategoryId($formData['categoryId'])
                    ->setDescription($formData['description'])
                    ->setCostPrice($formData['costPrice'])
                    ->setSellingPrice($formData['sellingPrice'])
                    ->setIsInStock($formData['isInStock'] == "yes")
                    ->setIsActive($formData["isActive"] == "yes")
                    ->update();
            if($productImage['error'] == UPLOAD_ERR_OK){
                if(in_array($productImage['type'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp']) === false){
                    throw new Exception('Product image file extension must be of type - png, jpeg, gif or webp');
                }
                if(is_uploaded_file($productImage['tmp_name']) === false){
                    throw new Exception("Fail to save product image. #1");
                }
                $uploadFileExtension = strtolower(pathinfo($productImage['name'], PATHINFO_EXTENSION));
                $saveFileName = uniqid('product-image-') . '.' . $uploadFileExtension;
                $saveFilePath = UPLOAD_DIR . '/' . $saveFileName;
                if(move_uploaded_file($productImage['tmp_name'], $saveFilePath) === false){
                    throw new Exception("Fail to save product image. #2");
                } 
                $product->setImage($saveFileName)->update();
            }
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
    public function delete(Response $response, $recordId): Response{
        $res = new AjaxResponse();
        
        try{
            (new ProductQuery($this->conn))->findById($recordId)->delete();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
}
