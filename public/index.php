<?php
require '../vendor/autoload.php';
require 'connect.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new \Slim\App;
$app->get('/getList', function (ServerRequestInterface $request, ResponseInterface $response) {
    global $db;
    $params = $request->getQueryParams();
    if (isset($params['count'])) {
        $count = (int)$params['count'];
    } else {
        $count = 10;
    }
    if (isset($params['id'])) {
        $id = (int)$params['id'];
    }
    $sql = "SELECT * FROM posts WHERE 1 ";
    if (isset($id)) {
        $sql .= " AND id < $id ";
    }
    $sql .= " ORDER BY id DESC LIMIT 0, $count";
    $res = mysqli_query($db, $sql) or die($sql);
    while ($row = mysqli_fetch_object($res)) {
        echo $row->id . " " . $row->description . "<br>";
    }
});
//$app->post('/postPhoto/[]')
$app->run();