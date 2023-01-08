<?php
namespace OS\controllers\admin;

use OS\helpers\AjaxResponse;
use OS\models\AdminUser;
use OS\models\AdminUserQuery;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use OS\helpers\ViewRenderer;

class AdminUserController {
    
    /** @var Connection */
    private $conn;
    
    public function __construct(ContainerInterface $container){
        $this->conn = $container->get("db");
    }
    
    public function list(Request $request, Response $response): Response{
        $error = false;
        $records = [];
        try{
            $records = (new AdminUserQuery($this->conn))->getAll();
        }catch(\Exception $e){
            $error = true;
        }
        return ViewRenderer::render(
            $response,
            "admin/admin-user/list",
            [
                'pageTitle' => 'Manage Admin User',
                'currentMenuItem' => 'adminuser',
                'error' => $error,
                'records' => $records
            ],
            "admin"
        );
    }
    
    public function add(Request $request, Response $response): Response{
        $res = new AjaxResponse();

        try{
            $formData= $request->getParsedBody();

            if(empty($formData['password']) || empty($formData['confirmPassword']))
                throw new \Exception('Password fields cannot be empty.');
            if(strlen($formData['password']) < 8)
                throw new \Exception('Password must be at least 8 characters long.');
            if(strcmp($formData['password'], $formData['confirmPassword']) != 0)
                throw new \Exception('Password do not match.');
            $record = new AdminUser($this->conn);
            $record->setName($formData['userName'])
                ->setEmailAddress($formData['userEmail'])
                ->setPassword(sha1($formData['password']))
                ->setIsSuperAdmin(false)
                ->setIsActive($formData['isActive'] == "yes")
                ->insert();
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function edit(Response $response, $userId): Response{
        $error = false;
        $record = null;
        try{
            $record = (new AdminUserQuery($this->conn))->findById($userId);
        }catch(\Exception $e){
            $error = true;
        }
        return ViewRenderer::render(
            $response,
            "admin/admin-user/edit",
            [
                'error' => $error,
                'record' => $record
            ]
        );
    }
    
    public function save(Request $request, Response $response, $userId): Response{
        $res = new AjaxResponse();

        try{
            $formData= $request->getParsedBody();

            if(!empty($formData['password']) and strlen($formData['password']) < 8)
                throw new \Exception('Password must be at least 8 characters long.');
            if(!empty($formData['password']) and strcmp($formData['password'], $formData['confirmPassword']) != 0)
                throw new \Exception('Password do not match.');
            $record = (new AdminUserQuery($this->conn))->findById($userId);
            $record->setName($formData['userName'])
                ->setEmailAddress($formData['userEmail'])
                ->setIsActive($formData['isActive'] == "yes")
                ->update();
            if(!empty($formData['password'])){
                $record->setPassword(sha1($formData['password']))->update();
            }
            $res->success = true;
        }catch(\Exception $e){
            $res->handleError($e);
        }

        return $res->getJSON($response);
    }
    
    public function delete(Response $response, $userId): Response{
        
    }
    
}