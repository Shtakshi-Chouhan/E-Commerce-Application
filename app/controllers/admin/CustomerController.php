<?php
namespace OS\controllers\admin;

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use OS\helpers\ViewRenderer;

class CustomerController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function list(Request $request, Response $response): Response{
        
    }
    
    public function activate(Response $response, $customerId): Response{
        
    }
    
    public function deactivate(Response $response, $customerId): Response{
        
    }
}

