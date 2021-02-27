<?php

require __DIR__ . '/vendor/autoload.php';
session_start();

$app = new Slim\App;

$container = $app->getContainer();

$container['view'] = function($container){
    $view = new \Slim\Views\Twig( __DIR__ . '/pages', [
        'cache' => false
    ]);

    //instantiate and add Slim Specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()));
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['db'] = function(){
    return new PDO('mysql:host=localhost; dbname=attandance', 'admin', 'admin123');
};

$container['auth'] = function(){
    $status = 'logged_in';

    // if(isset($_SESSION['email']) == null || isset($_SESSION['password']) == null){
    //     $email = "dikasyah2106@gmail.com";
    //     $password = "admin123";
    // }else{
    //     $email = $_SESSION['email'];
    //     $password = $_SESSION['password'];
    // }

    return $status;
};

$app->get('/', function($request, $response){
    if($this->auth == 'logged_in'){
        return $this->view->render($response, 'user/index.twig');
    }else{
        return $this->view->render($response, 'auth/login.twig');
    }
});

$app->get('/user', function($request, $response, $args){
    $user = $this->db->query("SELECT * FROM user")->fetchAll(PDO::FETCH_OBJ);

    return $response->withJson($user);
});

$app->get('/user/{email}', function($request, $response, $args){
    $email = $args['email'];
    $user = $this->db->query("SELECT * FROM user WHERE email = '$email'")->fetchAll(PDO::FETCH_OBJ);

    return $response->withJson($user);
});

$app->run();