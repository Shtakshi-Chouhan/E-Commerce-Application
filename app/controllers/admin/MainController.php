<?php
namespace OS\controllers\admin;

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use OS\helpers\ViewRenderer;
use OS\helpers\AjaxResponse;
use Exception;

class MainController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function loginPage(Response $response): Response {
        return ViewRenderer::render(
            $response, 
            "admin/main/login",
            [
                'pageTitle' => 'Login | Admin Panel'
            ]
        );
    }
    
    public function login(Request $request, Response $response): Response{
        $res = new AjaxResponse();
        try{
            $formData = $request->getParsedBody();
            $query = new \OS\models\AdminUserQuery($this->conn);
            $user = $query->getByEmailAddress($formData['emailAddress']);
            $res->enteredPassword = sha1($formData['password']);
            $res->savedPassword = $user->getPassword();
            if(strcmp(sha1($formData['password']), $user->getPassword()) != 0){
                throw new Exception("Invalid login details.");
            }
            if($user->getIsActive() === false){
                throw new Exception("Your account has been deactivated. Please contact site admin.");
            }
            $_SESSION['adminLoggedIn'] = true;
            $_SESSION['adminUserId'] = $user->getId();
            $_SESSION['adminUserName'] = $user->getName();
            $_SESSION['isSuperAdmin'] = $user->getIsSuperAdmin();
            $user->setLastLoginOn(time())->update();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
    public function forgotPassword(Request $request, Response $response): Response{
        
    }
    
    public function dashboard(Response $response): Response{
        return ViewRenderer::render(
            $response, 
            "admin/main/dashboard", 
            [
                'pageTitle' => 'Dashboard',
                'currentMenuItem' => 'dashboard'
            ], 
            "admin"
        );
    }
    
    public function editProfile(Response $response): Response{
        return ViewRenderer::render(
            $response, 
            "admin/main/edit-profile",
            [
                'user' => (new \OS\models\AdminUserQuery($this->conn))->findById($_SESSION['adminUserId'])
            ]
        );
    }
    
    public function saveProfile(Request $request, Response $response): Response{
        $res = new AjaxResponse();
        try{
            $formData = $request->getParsedBody();
            $user = (new \OS\models\AdminUserQuery($this->conn))->findById($_SESSION['adminUserId']);
            $user->setName($formData['userName'])->update();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
    public function logout(Response $response): Response{
        $res = new AjaxResponse();
        
        try{
            unset($_SESSION['adminLoggedIn']);
            unset($_SESSION['adminUserId']);
            unset($_SESSION['adminUserName']);
            unset($_SESSION['isSuperAdmin']);
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
    public function changePassword(Request $request, Response $response): Response{
        $res = new AjaxResponse();
        
        try{
            $formData = $request->getParsedBody();
            $currentPassword = $formData['currentPassword'];
            $newPassword = $formData['newPassword'];
            $rePassword = $formData['rePassword'];
            
            $query = new \OS\models\AdminUserQuery($this->conn);
            $user = $query->findById($_SESSION['adminUserId']);
            if(strcmp(sha1($currentPassword), $user->getPassword()) != 0){
                throw new Exception("Current password do not match.");
            }
            if(strlen($newPassword) < 8) {
                throw new Exception ("New password must be at least 8 characters long.");
            }
            if(strcmp($newPassword, $rePassword) != 0) {
                throw new Exception ("New password do not match.");
            }
            $user->setPassword($newPassword)->update();
            $res->success = true;
        } catch (Exception $ex) {
            $res->handleError($ex);
        }
        
        return $res->getJSON($response);
    }
    
}