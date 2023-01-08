<?php
namespace OS\helpers;

use \Psr\Http\Message\ResponseInterface;
use \Exception;

class ViewRenderer{
    
    const VIEW_FOLDER = APP_BASE_PATH . '/views/';
    const LAYOUT_FOLDER = APP_BASE_PATH . '/layouts/';
    
    static $hooks = [];
    
    public static function registerHook($hookName, $func){
        if(array_key_exists($hookName, self::$hooks)){
            self::$hooks[$hookName][] = $func;
        }else{
            self::$hooks[$hookName] = [$func];
        }
    }

    /**
     * @param $hookName
     */
    public static function renderHook($hookName){
        if(array_key_exists($hookName, self::$hooks)){
            foreach(self::$hooks[$hookName] as $func){
                call_user_func($func);
            }
        }
    } 
    
    public static function render(ResponseInterface $response, $viewFile, $data = [], $layoutFile = null): ResponseInterface {
        try {
            $viewFilePath = self::VIEW_FOLDER . $viewFile . '.phtml';
            if (file_exists($viewFilePath) === false)
                throw new Exception("View file not found.");
            //rendering view file
            ob_start();
            require $viewFilePath;
            $content = ob_get_clean();
            //rendering layout file, if available
            if ($layoutFile !== null) {
                $layoutFilePath = self::LAYOUT_FOLDER . $layoutFile . '.phtml';
                if (file_exists($layoutFilePath) === false)
                    throw new Exception("Layout file not found.");
                ob_start();
                require $layoutFilePath;
                $content = ob_get_clean();
            }
            $response->getBody()->write($content);
        }catch(Exception $e){
            $response = $response->withStatus(500);
            $response->getBody()->rewind();
            $response->getBody()->write($e->getMessage()."<br />".$e->getTraceAsString());
        }
        return $response;                        
    }
    
}