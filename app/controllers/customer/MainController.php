<?php
namespace OS\controllers\customer;

use Exception;
use OS\helpers\AjaxResponse;
use OS\helpers\Mailer;
use OS\helpers\ViewRenderer;
use OS\models\Customer;
use OS\models\CustomerBillingAddressQuery;
use OS\models\CustomerQuery;
use OS\models\CustomerShippingAddressQuery;
use OS\models\ProductCategoryQuery;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Swift_Message;
use Throwable;

class MainController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function show(Response $response): Response {
        $categories = (new ProductCategoryQuery($this->conn))->getAll();
        return ViewRenderer::render(
            $response,
            "customer/main/show",
            [
                "pageTitle" => "Login / Register | Customer",
                "categories" => "categories"
            ],
            "frontend"
        );
    }
    
    public function login(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $emailAddress = trim($formData['emailAddress']);
            $password = trim($formData['password']);
            $customer = (new CustomerQuery($this->conn))->findByEmailAddress($emailAddress);
            $res->accountVerified = true;
            if(strcmp(sha1($password), $customer->getAccountPassword()) != 0)
                throw new Exception("Invalid login details");
            if($customer->getAccountVerified() === false) {
                $res->accountVerified = false;
                throw new Exception("Your account is not verified yet. Please check your email to complete account verification.");
            }
            if($customer->getIsActive() === false)
                throw new Exception("Your account has been deactivated. Please contact site admin.");
            $customer->setLastLoginOn(time())->update();
            $_SESSION['customerLoggedIn'] = true;
            $_SESSION['customerId'] = $customer->getId();
            $_SESSION['customerName'] = $customer->getFirstName().' '.$customer->getLastName();
            $res->success = true;
        }catch(Throwable $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function register(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $firstName = trim($formData['firstName']);
            $lastName = trim($formData['lastName']);
            $emailAddress = trim($formData['emailAddress']);
            $mobileNumber = trim($formData['mobileNumber']);
            $alternateMobileNumber = trim($formData['aMobileNumber']);
            $password = $formData['password'];
            $confirmPassword = $formData['confirmPassword'];

            if(empty($password) || empty($confirmPassword))
                throw new Exception('Password fields cannot be empty.');
            if(strlen($password) < 8)
                throw new Exception('Password must be at least 8 characters long.');
            if(strcmp($password, $confirmPassword) != 0)
                throw new Exception('Password do not match.');

            $query = new CustomerQuery($this->conn);
            if($query->hasAccountWithEmailAddress($emailAddress) === true)
                throw new Exception('An account with specified email address already exists.');

            $customer = new Customer($this->conn);
            $verificationCode = str_shuffle(sha1(time()));
            $customer->setFirstName($firstName)
                ->setLastName($lastName)
                ->setEmailAddress($emailAddress)
                ->setMobileNumber($mobileNumber)
                ->setAlternateNumber($alternateMobileNumber)
                ->setAccountPassword(sha1($password))
                ->setAccountVerificationCode($verificationCode)
                ->setLastLoginOn(time())
                ->insert();
            //sending verification email
            try{
                $message = new Swift_Message();
                $message->setSubject("Please complete account verification")
                    ->setTo([$emailAddress => $firstName." ".$lastName])
                    ->setFrom(MAILER_CONFIG["senderEmail"])
                    ->setBody(
                        '<html lang="en"><head><title>Complete Account Verification</title></head><body>' .
                        '<p>You need to complete account verification to activate your account. Please click "Verify Account" button below to complete verification procedure.</p>' .
                        '<div style="padding: 15px 25px; border: solid 1px #ccc;">' .
                        '<a style="display: inline-block; padding: 5px 10px; background: black; color: #fff;" href="http://onlineshop.local/customer/verify/'.$verificationCode.'">Verify Account</a>' .
                        '</div>' .
                        '</body></html>',
                        'text/html',
                        'utf-8'
                    );
                Mailer::send($message);
            } catch(Exception $e){
                //do nothing
            }
            $_SESSION['customerLoggedIn'] = true;
            $_SESSION['customerId'] = $customer->getId();
            $_SESSION['customerName'] = $customer->getFirstName().' '.$customer->getLastName();
            $res->success = true;
        }catch(Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }

    public function resendVerificationEmail(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $emailAddress = trim($formData['emailAddress']);

            if(empty($emailAddress))
                throw new Exception("Email Address must be specified.");
            if(!filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
                throw new Exception("Invalid email address.");

            //finding customer account corresponding to email address
            $customer = (new CustomerQuery($this->conn))->findByEmailAddress($emailAddress);
            $verificationCode = $customer->getAccountVerificationCode();

            //sending verification email
            try{
                $verificationURL = "http://online-shop.local/customer/verify/".$verificationCode;
                $message = new Swift_Message();
                $message->setSubject("Please complete account verification")
                    ->setTo([$customer->getEmailAddress() => $customer->getFirstName()." ".$customer->getLastName()])
                    ->setFrom(MAILER_CONFIG["senderEmail"])
                    ->setBody(
                        '<html lang="en"><head><title>Complete Account Verification</title></head><body>' .
                        '<p>You need to complete account verification to activate your account. Please click "Verify Account" button below to complete verification procedure.</p>' .
                        '<div style="padding: 15px 25px; border: solid 1px #ccc;">' .
                        '<a style="display: inline-block; padding: 5px 10px; background: black; color: #fff;" href="'.$verificationURL.'">Verify Account</a>' .
                        '</div>' .
                        '</body></html>',
                        'text/html',
                        'utf-8'
                    );
                Mailer::send($message);
            } catch(Exception $e){
                //do nothing
            }
            $res->success = true;
        }catch(Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function forgotPassword(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $emailAddress = trim($formData['emailAddress']);

            if(empty($emailAddress))
                throw new Exception("Email Address must be specified.");
            if(!filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
                throw new Exception("Invalid email address.");

            //finding customer account corresponding to email address
            $customer = (new CustomerQuery($this->conn))->findByEmailAddress($emailAddress);
            $newPassword = substr(str_shuffle(sha1(time().$customer->getId())), 0, 8);
            $customer->setAccountPassword(sha1($newPassword))->update();
            //sending email containing new credentials
            try{
                $message = new Swift_Message();
                $message->setSubject("You requested for your account credentails")
                    ->setTo([$customer->getEmailAddress() => $customer->getFirstName()." ".$customer->getLastName()])
                    ->setFrom(MAILER_CONFIG["senderEmail"])
                    ->setBody(
                        '<html lang="en"><head><title>Your Account Credential</title></head><body>' .
                        '<p>You have initiated forgot password feature. We have generated new credentials for your account. Please find the same below.</p>' .
                        '<ul><li><strong>Email Address: </strong>'.$customer->getEmailAddress().'</li>' .
                        '<li><strong>Password: </strong>'.$newPassword.'</li></ul>' .
                        '</body></html>',
                        'text/html',
                        'utf-8'
                    );
                Mailer::send($message);
            }catch(Exception $e){
                throw $e;
            }
            $res->success = true;
        }catch(Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function dashboard(Response $response): Response{
        return ViewRenderer::render(
            $response,
            "customer/main/dashboard",
            [
                "pageTitle" => "Dashboard",
                "currentMenuItem" => "dashboard",
                "customer" => (new CustomerQuery($this->conn))->findById($_SESSION['customerId'])
            ],
            "customer"
        );
    }
    
    public function editProfile(Response $response): Response{
        $error = false;
        $errorMessage = null;
        $customer = null;
        $billingAddress = null;

        try{
            $customerId = $_SESSION['customerId'];
            $customer = (new CustomerQuery($this->conn))->findById($customerId);
        }catch(Throwable $e){
            $error = true;
            $errorMessage = $e->getMessage();
        }

        return ViewRenderer::render(
            $response,
            "customer/main/edit-profile",
            [
                "pageTitle" => "Edit Profile",
                "error" => $error,
                "errorMessage" => $errorMessage,
                "customer" => $customer
            ],
            "customer"
        );
    }
    
    public function saveProfile(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $firstName = trim($formData['firstName'] ?? false);
            $lastName = trim($formData['lastName'] ?? false);
            $mobileNumber = trim($formData['mobileNumber'] ?? false);
            $alternateNumber = trim($formData['alternateNumber'] ?? false);

            if(empty($firstName) || empty($lastName) || empty($mobileNumber))
                throw new Exception("Mandatory fields are found empty.");
            if(!preg_match('#^\d{10}$#', $mobileNumber))
                throw new Exception("Invalid mobile number, must be 10-digits numeric number.");
            if(!empty($alternateNumber) and !preg_match('#^\d{10}$#', $alternateNumber))
                throw new Exception("Invalid alternate number, must be 10-digits numeric number.");

            //updating profile
            $this->conn->createQueryBuilder()->update("customer")
                ->set('first_name', '?')
                ->set('last_name', '?')
                ->set('mobile_number', '?')
                ->set('alternate_number', '?')
                ->where('id = ?')
                ->setParameters([
                    $firstName, $lastName,
                    $mobileNumber, $alternateNumber,
                    $_SESSION['customerId']
                ])->execute();
            $res->success = true;
        }catch(Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }

    public function changePassword(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData = $request->getParsedBody();
            $customerId = $_SESSION['customerId'];
            $currentPassword = $formData['currentPassword'];
            $newPassword = $formData['newPassword'];
            $rePassword = $formData['rePassword'];

            if(empty($currentPassword) || empty($newPassword) || empty($rePassword))
                throw new Exception("All fields are mandatory.");
            if(strlen($newPassword) < 8)
                throw new Exception("New password must not be less than 8 characters.");
            if(strcmp($newPassword, $rePassword) != 0)
                throw new Exception("New password does not match.");

            $customer = (new CustomerQuery($this->conn))->findById($customerId);
            if(strcmp($customer->getAccountPassword(), sha1($currentPassword)) != 0)
                throw new Exception("Current password do not match.");

            //updating customer password
            $customer->setAccountPassword(sha1($newPassword))->update();
            unset($_SESSION['customerId']);
            unset($_SESSION['customerName']);
            unset($_SESSION['customerLoggedIn']);
            $res->success = true;
        }catch(Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function logout(Response $response): Response{
        $res = new AjaxResponse();

        try{
            unset($_SESSION['customerId']);
            unset($_SESSION['customerName']);
            unset($_SESSION['customerLoggedIn']);
            $res->success = true;
        }catch(Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function showSupport(Response $response): Response{
        
    }
    
    public function submitSupportRequest(Request $request, Response $response): Response{
        
    }

    public function verifyAccount(Response $response, $verificationCode): Response{
        $error = false;
        $errorMessage = null;
        $verificationSuccess = false;

        try{
            $customer = (new CustomerQuery($this->conn))->findByVerificationCode($verificationCode);
            if($customer->getAccountVerified())
                throw new Exception("Your account is already verified. Please click the login button below to continue.");
            $customer->setAccountVerified(true)->setIsActive(true)->update();
            $verificationSuccess = true;
        }catch(Throwable $e){
            $error = true;
            $errorMessage = $e->getMessage();
        }

        return ViewRenderer::render(
            $response,
            "customer/main/verify-account",
            [
                "pageTitle" => "Verify Account",
                "error" => $error,
                "errorMessage" => $errorMessage,
                "verificationSuccess" => $verificationSuccess
            ],
            "frontend"
        );
    }
    
}

