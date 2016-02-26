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
    $sql .= " ORDER BY id DESC LIMIT 0, $count ";
    $res = mysqli_query($db, $sql) or die($sql);
    $items = [];
    while ($row = mysqli_fetch_object($res)) {
        $item  = [];
        $item['id'] = $row->id;
        $item['description'] = $row->description;
        $item['expectationUrl'] = "/img/items/" . $row->placeId . "/" . $row->id . "/exp.jpg";
        $item['realityUrl'] = "/img/items/" . $row->placeId . "/" . $row->id . "/real.jpg";
        $item['userId'] = $row->userId;
        $item['userPhoto'] = "/img/users/" . $row->userId . "/photo.jpg";
        $items[] = $item;
    }
    //print_r($items);
    $response->getBody()->write(json_encode($items, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE) );
    return $response;
//    echo  json_encode($items);
});
//$app->post('/postPhoto/[]')
$app->run();