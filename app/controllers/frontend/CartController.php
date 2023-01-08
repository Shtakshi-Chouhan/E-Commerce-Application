<?php
namespace OS\controllers\frontend;

use OS\helpers\AjaxResponse;
use OS\helpers\ViewRenderer;
use OS\models\ProductCategoryQuery;
use OS\models\ProductQuery;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CartController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function showCart(Response $response): Response{
        $error = false;
        $products = [];
        $totalAmount = 0;
        $totalTaxes = 0;
        $shippingCharges = 0;
        $categories = [];
        try{
            $categories = (new ProductCategoryQuery($this->conn))->getAll();
            $cart = $_SESSION['cart'] ?? (object)['totalItems' => 0, 'products' => []];
            foreach($cart->products as $entry){
                $productId = $entry['id'];
                $qty = $entry['qty'];
                $product = (new ProductQuery($this->conn))->findById($productId);
                $productTotalPrice = ($qty * $product->getSellingPrice());
                $totalAmount += $productTotalPrice;
                $products[] = [
                    'id' => $productId,
                    'name' => $product->getName(),
                    'image' => $product->getImage(),
                    'price' => $product->getSellingPrice(),
                    'qty' => $qty,
                    'total' => $productTotalPrice
                ];
            }
            $totalTaxes = ($totalAmount * TAX_RATE);
            $shippingCharges = ($cart->totalItems > 3 ? SHIPPING_CHARGES_ABOVE_3 : SHIPPING_CHARGES_BELOW_3);
        }catch(\Exception $e){
            $error = true;
        }
        return ViewRenderer::render(
            $response,
            "frontend/cart/show",
            [
                "pageTitle" => "Cart",
                "error" => $error,
                "categories" => $categories,
                "products" => $products,
                "totalAmount" => $totalAmount,
                "totalTaxes" => $totalTaxes,
                "shippingCharges" => $shippingCharges
            ],
            "frontend"
        );
    }
    
    public function addToCart(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $cart = $_SESSION['cart'] ?? (object)['totalItems' => 0, 'products' => []];
            $formData = $request->getParsedBody();
            $productId = $formData['pId'];
            $isProductAlreadyInCart = false;
            foreach($cart->products as $key => $entry){
                if($entry['id'] == $productId){
                    $cart->products[$key]['qty'] += 1;
                    $isProductAlreadyInCart = true;
                    break;
                }
            }
            if($isProductAlreadyInCart === false){
                $cart->products[] = ['id' => $productId, 'qty' => 1];
            }
            $totalItems = 0;
            foreach($cart->products as $entry){
                $totalItems += $entry['qty'];
            }
            $cart->totalItems = $totalItems;
            $_SESSION['cart'] = $cart;
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function clearCart(Response $response): Response{
        $res = new AjaxResponse();

        try{
            unset($_SESSION['cart']);
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function removeFromCart(Response $response, $productId): Response{
        $res = new AjaxResponse();

        try{
            $cart = $_SESSION['cart'] ?? (object)['totalItems' => 0, 'products' => []];
            $itemHasBeenDeleted = false;
            foreach($cart->products as $key => $entry){
                if($entry['id'] == $productId){
                    unset($cart->products[$key]);
                    $itemHasBeenDeleted = true;
                    break;
                }
            }
            if($itemHasBeenDeleted){
                $totalItems = 0;
                foreach($cart->products as $entry){
                    $totalItems += $entry['qty'];
                }
                $cart->totalItems = $totalItems;
            }
            $_SESSION['cart'] = $cart;
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
}

