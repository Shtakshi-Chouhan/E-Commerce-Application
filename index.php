<?php

use DI\Container;
use Doctrine\DBAL\DriverManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require "vendor/autoload.php";

session_start();

const APP_BASE_PATH = __DIR__ . '/app';
const UPLOAD_DIR = __DIR__ . '/uploads';
const TAX_RATE = 0.18;
const SHIPPING_CHARGES_BELOW_3 = 150;
const SHIPPING_CHARGES_ABOVE_3 = 300;
const MAILER_CONFIG = [
    "server" => "",
    "username" => "",
    "password" => "",
    "port" => "",
    "senderEmail" => "admin@onlineshop.local"
];

$app = \DI\Bridge\Slim\Bridge::create();

/** @var Container $container */
$container = $app->getContainer();
$container->set("db", function(){
    return DriverManager::getConnection(array(
        'dbname' => 'onlineshop',
        'user' => 'onlineshop',
        'password' => 'nTIiVikhLqUY2bal',
        'host' => 'localhost',
        'driver' => 'pdo_mysql'
    ));
});

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Frontend Routes
$app->get("/", [OS\controllers\frontend\MainController::class, "home"]);
$app->get("/category/{categoryId}", [\OS\controllers\frontend\MainController::class, "category"]);
$app->get("/about", [\OS\controllers\frontend\MainController::class, "showAboutUs"]);
$app->get("/contact-us", [\OS\controllers\frontend\MainController::class, "showContactUs"]);
$app->post("/contact-us", [\OS\controllers\frontend\MainController::class, "submitContactUs"]);

$app->get("/cart/show", [\OS\controllers\frontend\CartController::class, "showCart"]);
$app->post("/cart/add", [\OS\controllers\frontend\CartController::class, "addToCart"]);
$app->post("/cart/clear", [\OS\controllers\frontend\CartController::class, "clearCart"]);
$app->post("/cart/remove/{productId}", [\OS\controllers\frontend\CartController::class, "removeFromCart"]);

//Customer Routes
$app->get("/customer", [\OS\controllers\customer\MainController::class, "show"]);
$app->post("/customer/login", [\OS\controllers\customer\MainController::class, "login"]);
$app->post("/customer/register", [\OS\controllers\customer\MainController::class, "register"]);
$app->post("/customer/forgot-password", [\OS\controllers\customer\MainController::class, "forgotPassword"]);
$app->get("/customer/verify/{verificationCode}", [\OS\controllers\customer\MainController::class, "verifyAccount"]);
$app->post("/customer/resend-verification-email", [\OS\controllers\customer\MainController::class, "resendVerificationEmail"]);
$app->group("/customer", function(\Slim\Routing\RouteCollectorProxy $group){
    $group->get("/checkout", [\OS\controllers\customer\CheckoutController::class, "show"]);
    $group->get("/dashboard", [\OS\controllers\customer\MainController::class, "dashboard"]);
    $group->post("/logout", [\OS\controllers\customer\MainController::class, "logout"]);
    $group->post("/change-password", [\OS\controllers\customer\MainController::class, "changePassword"]);
    $group->get("/profile", [\OS\controllers\customer\MainController::class, "editProfile"]);
    $group->post("/save-profile", [\OS\controllers\customer\MainController::class, "saveProfile"]);
    $group->post("/save-billing-address", [\OS\controllers\customer\AddressController::class, "saveBillingAddress"]);
    $group->get("/shipping-address", [\OS\controllers\customer\AddressController::class, "showShippingAddresses"]);
    $group->post("/add-shipping-address", [\OS\controllers\customer\AddressController::class, "addShippingAddress"]);
    $group->post("/place-order", [\OS\controllers\customer\CheckoutController::class, "placeOrder"]);
    $group->get("/orders", [\OS\controllers\customer\OrderController::class, "show"]);
})->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler){
    if(isset($_SESSION['customerLoggedIn']) === false){
        $response = new \Slim\Psr7\Response();
        return $response->withHeader('Location', '/customer?_r='.urlencode($request->getServerParams()['REQUEST_URI']));
    }
    return $handler->handle($request);
});

//admin routes
$app->get("/admin/login", [\OS\controllers\admin\MainController::class, "loginPage"]);
$app->post("/admin/login", [\OS\controllers\admin\MainController::class, "login"]);
$app->post("/admin/forgot-password", [\OS\controllers\admin\MainController::class, "forgotPassword"]);
$app->group("/admin", function(Slim\Routing\RouteCollectorProxy $group){
    $group->get("/dashboard", [\OS\controllers\admin\MainController::class, "dashboard"]);
    $group->get("/edit-profile", [\OS\controllers\admin\MainController::class, "editProfile"]);
    $group->post("/save-profile", [\OS\controllers\admin\MainController::class, "saveProfile"]);
    $group->post("/change-password", [\OS\controllers\admin\MainController::class, "changePassword"]);
    $group->post("/logout", [\OS\controllers\admin\MainController::class, "logout"]);
    //product routes
    $group->get("/product/list", [\OS\controllers\admin\ProductController::class, "list"]);
    $group->post("/product/add", [\OS\controllers\admin\ProductController::class, "add"]);
    $group->get("/product/edit/{recordId}", [\OS\controllers\admin\ProductController::class, "edit"]);
    $group->post("/product/save/{recordId}", [\OS\controllers\admin\ProductController::class, "save"]);
    $group->post("/product/delete/{recordId}", [\OS\controllers\admin\ProductController::class, "delete"]);
    //produt category routes
    $group->get("/product-category/list", [\OS\controllers\admin\ProductCategoryController::class, "list"]);
    $group->post("/product-category/add", [\OS\controllers\admin\ProductCategoryController::class, "add"]);
    $group->get("/product-category/edit/{recordId}", [\OS\controllers\admin\ProductCategoryController::class, "edit"]);
    $group->post("/product-category/save/{recordId}", [\OS\controllers\admin\ProductCategoryController::class, "save"]);
    $group->post("/product-category/delete/{recordId}", [\OS\controllers\admin\ProductCategoryController::class, "delete"]);
    //customer routes
    $group->get("/customer/list", [\OS\controllers\admin\CustomerController::class, "list"]);
    $group->post("/customer/activate/{customerId}", [\OS\controllers\admin\CustomerController::class, "activate"]);
    $group->post("/customer/deactivate/{customerId}", [\OS\controllers\admin\CustomerController::class, "deactivate"]);
    //order routes
    $group->get("/order/list", [\OS\controllers\admin\OrderController::class, "list"]);
    $group->get("/order/view/{orderId}", [\OS\controllers\admin\OrderController::class, "viewOrder"]);
    $group->post("/order/change-status/{orderId}", [\OS\controllers\admin\OrderController::class, "changeStatus"]);
    //support routes
    $group->get("/support/list", [\OS\controllers\admin\SupportController::class, "list"]);
    //contact submission routes
    $group->get("/contact/list", [\OS\controllers\admin\ContactController::class, "list"]);
    //admin user routes
    $group->get("/admin-user/list", [\OS\controllers\admin\AdminUserController::class, "list"]);
    $group->post("/admin-user/add", [\OS\controllers\admin\AdminUserController::class, "add"]);
    $group->get("/admin-user/edit/{userId}", [\OS\controllers\admin\AdminUserController::class, "edit"]);
    $group->post("/admin-user/save/{userId}", [\OS\controllers\admin\AdminUserController::class, "save"]);
    $group->post("/admin-user/delete/{userId}", [\OS\controllers\admin\AdminUserController::class, "delete"]);
})->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler){
    if(isset($_SESSION['adminLoggedIn']) === false){
        $response = new \Slim\Psr7\Response();
        $response = $response->withHeader('Location', '/admin/login');
        return $response;
    }
    return $handler->handle($request);
});


$app->run();