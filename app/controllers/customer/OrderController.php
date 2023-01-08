<?php
namespace OS\controllers\customer;

use OS\helpers\ViewRenderer;
use OS\models\CustomerOrderQuery;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OrderController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function show(Request $request, Response $response): Response{
        $error = false;
        $errorMessage = null;
        $records = [];

        try{
            $records = (new CustomerOrderQuery($this->conn))->getAllByCustomerId($_SESSION['customerId']);
        }catch(\Exception $e){
            $error = true;
            $errorMessage = $e->getMessage();
        }

        return ViewRenderer::render(
            $response,
            "customer/order/show",
            [
                "currentMenuItem" => "order",
                "pageTitle" => "My Orders",
                "error" => $error,
                "errorMessage" => $errorMessage,
                "records" => $records
            ],
            "customer"
        );
    }
    
    public function viewOrder(Response $response, $orderId): Response{
        
    }
    
    public function cancelOrder(Response $response, $orderId): Response{
        
    }
    
}

