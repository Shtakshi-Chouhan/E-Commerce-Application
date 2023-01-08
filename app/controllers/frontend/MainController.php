<?php
namespace OS\controllers\frontend;

use Doctrine\DBAL\Schema\View;
use OS\helpers\ViewRenderer;
use OS\models\ProductCategoryQuery;
use OS\models\ProductQuery;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function home(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        $error = false;
        $products = [];
        $pageSize = 12;
        $currentPage = $queryParams['p'] ?? 1;
        $totalActiveProducts = 0;
        $totalPages = 1;
        $categories = [];
        try{
            $totalActiveProducts = (new ProductQuery($this->conn))->getTotalRecords(true);
            $totalPages = ceil($totalActiveProducts / $pageSize);
            $categories = (new ProductCategoryQuery($this->conn))->getAll();
            $products = (new ProductQuery($this->conn))->getActiveProductPage($currentPage, $pageSize);
        }catch(\Exception $e){
            $error = true;
        }
        return ViewRenderer::render(
            $response,
            "frontend/main/home",
            [
                "pageTitle" => "HomePage",
                "currentMenuItem" => "home",
                "categories" => $categories,
                "currentPage" => $currentPage,
                "totalPages" => $totalPages,
                "products" => $products,
                "error" => $error
            ],
            "frontend"
        );
    }
    
    public function category(Request $request, Response $response, $categoryId): Response{
        $queryParams = $request->getQueryParams();
        $error = false;
        $categories = [];
        $pageSize = 12;
        $currentPage = $queryParams['p'] ?? 1;
        $totalProducts = 0;
        $totalPages = 1;
        $products = [];
        $currentCategory = null;
        try{
            $query = new ProductCategoryQuery($this->conn);
            $currentCategory = $query->findById($categoryId);
            $categories = $query->getAll();
            $totalProducts = $query->getTotalActiveProducts($categoryId);
            $totalPages = ceil($totalProducts / $pageSize);
            $products = $query->getActiveProducts($categoryId, $currentPage, $pageSize);
        }catch(\Exception $e){
            $error = true;
            echo $e->getMessage();
        }
        return ViewRenderer::render(
            $response,
            "frontend/main/category",
            [
                "pageTitle" => $currentCategory->getName() . ' | Category ',
                "currentMenuItem" => "category",
                "error" => $error,
                "currentCategory" => $currentCategory,
                "categories" => $categories,
                "currentPage" => $currentPage,
                "totalPages" => $totalPages,
                "products" => $products
            ],
            "frontend"
        );
    }
    
    public function showContactUs(Response $response): Response{
        return ViewRenderer::render(
            $response,
            "frontend/main/contact",
            [
                "pageTitle" => "Contact Us",
                "currentMenuItem" => "contact"
            ],
            "frontend"
        );
    }
    
    public function submitContactUs(Request $request, Response $response): Response{
        
    }

    public function showAboutUs(Response $response): Response{
        return ViewRenderer::render(
            $response,
            "frontend/main/about",
            [
                "pageTitle" => "About Us",
                "currentMenuItem" => "about"
            ],
            "frontend"
        );
    }
    
    
}