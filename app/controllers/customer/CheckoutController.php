<?php
namespace OS\controllers\customer;

use Doctrine\DBAL\Connection;
use OS\helpers\AjaxResponse;
use OS\helpers\ViewRenderer;
use OS\models\CustomerQuery;
use OS\models\ProductQuery;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;

class CheckoutController {

    /** @var Connection */
    private $conn;

    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }

    public function show(Response $response): Response{
        $error = false;
        $errorMessage = null;
        $customer = null;

        try{
            $customer = (new CustomerQuery($this->conn))->findById($_SESSION['customerId']);
        }catch(\Exception $e){
            $error = true;
            $errorMessage = $e->getMessage();
        }

        return ViewRenderer::render(
            $response,
            "customer/checkout/show",
            [
                "pageTitle" => "Checkout",
                "conn" => $this->conn,
                "customer" => $customer
            ],
            "frontend"
        );
    }

    public function placeOrder(ServerRequestInterface $request, Response $response): Response{
        $res = new AjaxResponse();
        $res->cartSessionExpired = false;

        try{
            $formData = $request->getParsedBody();
            $billingName = trim($formData['billingName'] ?? false);
            $addressLine1 = trim($formData['addressLine1'] ?? false);
            $addressLine2 = trim($formData['addressLine2'] ?? false);
            $city = trim($formData['city'] ?? false);
            $state = trim($formData['state'] ?? false);
            $country = trim($formData['country'] ?? false);
            $pincode = trim($formData['pincode'] ?? false);
            $selectedShippingAddress = $formData['shippingAddress'] ?? false;

            if(!isset($_SESSION['cart'])) {
                $res->cartSessionExpired = true;
                throw new \Exception('Your cart has expired.');
            }
            if(empty($billingName) || empty($addressLine1) || empty($city) || empty($state) || empty($country) || empty($pincode))
                throw new \Exception('Mandatory billing address fields cannot be empty.');
            if($selectedShippingAddress === false)
                throw new \Exception('You must selected shipping address.');

            //calculating cart info
            $totalAmount = 0;
            foreach($_SESSION['cart']->products as $entry){
                $product = (new ProductQuery($this->conn))->findById($entry['id']);
                $qty = $entry['qty'];
                $productTotalPrice = ($qty * $product->getSellingPrice());
                $totalAmount += $productTotalPrice;
            }
            $totalTaxes = ($totalAmount * TAX_RATE);
            $shippingCharges = ($_SESSION['cart']->totalItems > 3 ? SHIPPING_CHARGES_ABOVE_3 : SHIPPING_CHARGES_BELOW_3);

            $customer = (new CustomerQuery($this->conn))->findById($_SESSION['customerId']);
            if($customer->hasBillingAddress()) {
                //saving billing address
                $this->conn->createQueryBuilder()
                    ->update("customer_billing_address")
                    ->set('billing_name', '?')
                    ->set('address_line_1', '?')
                    ->set('address_line_2', '?')
                    ->set('city', '?')
                    ->set('state', '?')
                    ->set('country', '?')
                    ->set('pincode', '?')
                    ->where('customer_id = ?')
                    ->setParameters([
                        $billingName, $addressLine1, $addressLine2, $city,
                        $state, $country, $pincode, $_SESSION['customerId']
                    ])->execute();
            }else{
                $this->conn->createQueryBuilder()
                    ->insert('customer_billing_address')
                    ->values([
                        'customer_id' => '?',
                        'billing_name' => '?',
                        'address_line_1' => '?',
                        'address_line_2' => '?',
                        'landmark' => '?',
                        'city' => '?',
                        'state' => '?',
                        'country' => '?',
                        'pincode' => '?'
                    ])->setParameters([
                        $_SESSION['customerId'], $billingName, $addressLine1, $addressLine2, "", $city,
                        $state, $country, $pincode
                    ])->execute();
            }

            //creating order
            $grandTotal = ($totalAmount + $totalTaxes + $shippingCharges);
            $this->conn->createQueryBuilder()
                ->insert("customer_order")
                ->values([
                    'order_date' => '?',
                    'customer_id' => '?',
                    'shipping_address_id' => '?',
                    'order_status' => '?',
                    'total_amount' => '?',
                    'total_tax' => '?',
                    'discount_amount' => '?',
                    'shipping_charges' => '?',
                    'grand_total' => '?'
                ])->setParameters([
                    date('Y-m-d'),
                    $_SESSION['customerId'],
                    $selectedShippingAddress,
                    'Order Placed',
                    $totalAmount,
                    $totalTaxes,
                    0,
                    $shippingCharges,
                    $grandTotal
                ])->execute();
            $orderId = $this->conn->lastInsertId();

            //adding all order products
            foreach($_SESSION['cart']->products as $entry){
                $product = (new ProductQuery($this->conn))->findById($entry['id']);
                $this->conn->createQueryBuilder()
                    ->insert("customer_order_details")
                    ->values([
                        'order_id' => '?',
                        'product_id' => '?',
                        'selling_price' => '?',
                        'quantity' => '?'
                    ])->setParameters([
                        $orderId,
                        $entry['id'],
                        $product->getSellingPrice(),
                        $entry['qty']
                    ])->execute();
            }
            unset($_SESSION['cart']);
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }

}