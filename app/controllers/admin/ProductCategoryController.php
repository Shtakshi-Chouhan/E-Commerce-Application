<?php
namespace OS\controllers\admin;

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use OS\helpers\ViewRenderer;
use OS\models\ProductCategory;
use OS\models\ProductCategoryQuery;
use OS\helpers\AjaxResponse;
use Exception;

class ProductCategoryController {
    
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
            $records = (new \OS\models\ProductCategoryQuery($this->conn))->getPage($pageNo, $pageSize);
            $totalRecords = (new \OS\models\ProductCategoryQuery($this->conn))->getTotalRecords();
            $totalPages = ceil($totalRecords / $pageSize);
        } catch (Exception $ex) {
            $error = true;
        }
        return ViewRenderer::render(
            $response,
            "admin/product-category/list",
            [
                'error' => $error,
                'records' => $records,
                'currentPage' => $pageNo,
                'totalPages' => $totalPages,
                'pageTitle' => 'Manage Product Category',
                'currentMenuItem' => 'category',
                'recordCounter' => $recordCounter
            ],
            "admin"
        );
    }
    
    public function add(Request $request, Response $response): Response{
        $res = new AjaxResponse();
        
        try{
            $formData = $request->getParsedBody();
            $productCategory = new ProductCategory($this->conn);
            $productCategory->setName($formData['categoryName'])
                    ->setDescription($formData['categoryDescription'])
                    ->setIsActive(($formData['isActive'] == 'yes'))
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
            $productCategory = (new ProductCategoryQuery($this->conn))->findById($recordId);
        } catch (Exception $ex) {
            $error = true;
        }
        return ViewRenderer::render(
            $response, 
            "admin/product-category/edit", 
            [
                'error' => $error, 
                'productCategory' => $productCategory
            ]
        );
    }
    
    public function save(Request $request, Response $response, $recordId): Response{
        $res = new AjaxResponse();
        
        try{
            $formData = $request->getParsedBody();
            $productCategory = (new ProductCategoryQuery($this->conn))->findById($recordId);
            $productCategory->setName($formData['categoryName'])
                    ->setDescription($formData['categoryDescription'])
                    ->setIsActive($formData["isActive"] == "yes")
                    ->update();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
    public function delete(Response $response, $recordId): Response{
        $res = new AjaxResponse();
        
        try{
            $query = new ProductCategoryQuery($this->conn);
            $query->findById($recordId)->delete();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
        
    }
    
}

