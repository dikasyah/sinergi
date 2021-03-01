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
    $host = 'localhost';
    $db   = 'sinergi';
    $user = 'admin';
    $pass = 'admin123';
    $port = "3306";
    $charset = 'utf8mb4';

    $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
    try {
        $pdo = new \PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
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
    return $this->view->render($response, 'index.twig');
});

$app->get('/master', function($request, $response, $args){
    $master = $this->db->query("SELECT * FROM master")->fetchAll();

    return $response->withJson($master,200);
});

$app->get('/detail', function($request, $response, $args){
    $detail = $this->db->query("SELECT * FROM detail")->fetchAll();

    return $response->withJson($detail,200);
});

$app->get('/master/{kode_master}', function($request, $response, $args){
    $kode_master = $args['kode_master'];
    $master = $this->db->query("SELECT * FROM master WHERE kode_master = '$kode_master'")->fetchAll();

    return $response->withJson($master,200);
});

$app->post("/detail/update", function ($request, $response, $args){
    $now = date("Y-m-d H:i:s");
    $detail = $request->getParsedBody();
    $kode_detail = $detail['inputKodeDetail'];
    $nama_detail = $detail['inputNameDetail'];
    $this->db->query("UPDATE detail SET nama_detail = '$nama_detail', updated_at = '$now' WHERE kode_detail = '$kode_detail'");

    return $response->withJson("success", 200);
});

$app->post("/master/create", function ($request, $response, $args){
    $now = date("Y-m-d H:i:s");
    $master = $request->getParsedBody();
    $kode_master = $master['inputKodeMaster'];
    $nama_master = $master['inputNamaMaster'];
    $this->db->query("INSERT INTO master (kode_master,nama_master,created_at,updated_at) VALUES ('$kode_master','$nama_master','$now','$now')");

    return $response->withJson("success", 200);
});

$app->get('/cetak', function($request, $response){
    $detail = $this->db->query("SELECT * FROM detail")->fetchAll();
    return $this->view->render($response, 'laporan/stock.twig', [
        "detail" => $detail
    ]);
});

$app->run();