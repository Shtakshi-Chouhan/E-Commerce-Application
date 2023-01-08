<?php
namespace OS\helpers;

use stdClass;
use Psr\Http\Message\ResponseInterface;

class AjaxResponse extends stdClass{
    
    public $error = false;
    public $success = false;
    public $errorMessage = null;

    public function handleError(\Throwable $ex){
        $this->error = true;
        $this->errorMessage = $ex->getMessage();
    }

    public function getJSON(ResponseInterface $response): ResponseInterface{
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($this));
        return $response;
    }
    
}