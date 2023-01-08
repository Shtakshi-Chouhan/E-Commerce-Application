<?php
namespace OS\controllers\customer;

use OS\helpers\AjaxResponse;
use OS\helpers\ViewRenderer;
use OS\models\CustomerQuery;
use OS\models\CustomerShippingAddressQuery;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

class AddressController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function saveBillingAddress(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $billingName = trim($formData['billingName'] ?? false);
            $addressLine1 = trim($formData['addressLine1'] ?? false);
            $addressLine2 = trim($formData['addressLine2'] ?? false);
            $city = trim($formData['city'] ?? false);
            $state = trim($formData['state'] ?? false);
            $country = trim($formData['country'] ?? false);
            $pincode = trim($formData['pincode'] ?? false);

            if(empty($billingName) || empty($addressLine1) || empty($addressLine2) || empty($city) || empty($state) ||
                empty($country) || empty($pincode))
                throw new \Exception("All fields are mandatory.");
            //adding or, updating billing address
            $customer = (new CustomerQuery($this->conn))->findById($_SESSION['customerId']);
            if($customer->hasBillingAddress()){
                $this->conn->createQueryBuilder()
                    ->update("customer_billing_address")
                    ->set('billing_name', '?')
                    ->set('address_line_1', '?')
                    ->set('address_line_2', '?')
                    ->set('landmark', '?')
                    ->set('city', '?')
                    ->set('state', '?')
                    ->set('country', '?')
                    ->set('pincode', '?')
                    ->where('customer_id = ?')
                    ->setParameters([
                        $billingName, $addressLine1, $addressLine2, "", $city,
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
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function showShippingAddresses(Response $response): Response{
        $error = false;
        $errorMessage = null;
        $records = null;

        try{
            $records = (new CustomerShippingAddressQuery($this->conn))->getAllByCustomerId($_SESSION['customerId']);
        }catch(Throwable $e){
            $error = true;
            $errorMessage = $e->getMessage();
        }

        return ViewRenderer::render(
            $response,
            "customer/address/show-shipping-address",
            [
                "pageTitle" => "Manage Shipping Address",
                "error" => $error,
                "errorMessage" => $errorMessage,
                "records" => $records
            ],
            "customer"
        );
    }
    
    public function addShippingAddress(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $type = $formData['addressType'] ?? false;
            $firstName = trim($formData['firstName'] ?? false);
            $lastName = trim($formData['lastName'] ?? false);
            $emailAddress = trim($formData['emailAddress'] ?? false);
            $mobileNumber = trim($formData['mobileNumber'] ?? false);
            $addressLine1 = trim($formData['addressLine1'] ?? false);
            $addressLine2 = trim($formData['addressLine2']);
            $landmark = trim($formData['landmark'] ?? false);
            $city = trim($formData['city'] ?? false);
            $state = trim($formData['state'] ?? false);
            $country = trim($formData['country'] ?? false);
            $pincode = trim($formData['pincode'] ?? false);

            if(empty($firstName) || empty($lastName) || empty($emailAddress) || empty($mobileNumber) || empty($addressLine1) ||
                empty($landmark) || empty($city) || empty($state) || empty($country) || empty($pincode))
                throw new \Exception("Mandatory fields are found empty.");
            if(!filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
                throw new \Exception("Invalid email address.");
            if(!preg_match('#^\d{10}$#', $mobileNumber))
                throw new \Exception("Invalid mobile number, must be 10 digits numeric number.");

            //adding shipping address
            $this->conn->createQueryBuilder()
                ->insert('customer_shipping_address')
                ->values([
                    'customer_id' => '?',
                    'type' => '?',
                    'first_name' => '?',
                    'last_name' => '?',
                    'email_address' => '?',
                    'mobile_number' => '?',
                    'address_line_1' => '?',
                    'address_line_2' => '?',
                    'landmark' => '?',
                    'city' => '?',
                    'state' => '?',
                    'country' => '?',
                    'pincode' => '?'
                ])->setParameters([
                    $_SESSION['customerId'],
                    $type,
                    $firstName,
                    $lastName,
                    $emailAddress,
                    $mobileNumber,
                    $addressLine1,
                    $addressLine2,
                    $landmark,
                    $city,
                    $state,
                    $country,
                    $pincode
                ])->execute();
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function editShippingAddress(Response $response, $recordId): Response{
        
    }
    
    public function saveShippingAddress(Request $request, Response $response, $recordId): Response{
        
    }
    
}

