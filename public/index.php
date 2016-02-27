<?php
header('Access-Control-Allow-Origin: *');
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
    $res = mysqli_query($db, $sql);
    $items = [];
    while ($row = mysqli_fetch_object($res)) {
        $item = [];
        $item['id'] = $row->id;
        $item['description'] = $row->description;
        $item['expectationUrl'] = "/img/items/" . $row->placeId . "/" . $row->id . "/exp.jpg";
        $item['realityUrl'] = "/img/items/" . $row->placeId . "/" . $row->id . "/real.jpg";
        $item['userId'] = $row->userId;
        $item['userPhoto'] = "/img/users/" . $row->userId . "/photo.jpg";
        $items[] = $item;
    }
    $response->getBody()->write(json_encode($items, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
    return $response;
});
$app->get('/getRestaurant', function (ServerRequestInterface $request, ResponseInterface $response) {
    global $db;
    if (isset($request->getAttributes()['cityId'])) {
        $cityId = $request->getAttributes()['cityId'];
    } else {
        $cityId = 1;
    }
    $sql = "SELECT * FROM places WHERE cityId = $cityId";
    $res = mysqli_query($db, $sql);
    $items = [];
    while ($row = mysqli_fetch_object($res)) {
        $item = [];
        $item['id'] = $row->id;
        $item['cityId'] = $row->cityId;
        $item['name'] = $row->name;
        $items[] = $item;
    }
    $response->getBody()->write(json_encode($items, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
    return $response;
});

$app->post("/updateCities", function (ServerRequestInterface $request, ResponseInterface $response) {
    global $db;
    if (isset(json_decode(file_get_contents("http://catalog.api.2gis.ru/2.0/region/list?key=ruidms8871&page_size=1000&locale_filter=ru_RU"))->result->items)) {
        $cities = json_decode(file_get_contents("http://catalog.api.2gis.ru/2.0/region/list?key=ruidms8871&page_size=1000&locale_filter=ru_RU"))->result->items;

        foreach ($cities as $city) {
            $gisId = $city->id;
            $name = $city->name;
            $sql = "SELECT * FROM cities WHERE gisId = $gisId AND name = '$name' ";
            $res = mysqli_query($db, $sql)or die($sql);
            if (mysqli_num_rows($res) == 0) {
                $sql = "INSERT INTO cities(`gisId`, `name`) VALUES  ($gisId, '$name')";
                mysqli_query($db, $sql) or die($sql);
            }
        }
    }
});

$app->post('/addPost', function (ServerRequestInterface $request, ResponseInterface $response) {
    global $db;
    $attrs = $request->getParsedBody();
    if (isset($attrs['placeId']) && $attrs['userId']) {
        $placeId = (int)$attrs['placeId'];
        $userId = (int)$attrs['userId'];
    } else {
        return $response->withStatus(405, "no userId or placeId");
    }
    $description = htmlspecialchars(mysqli_real_escape_string($db, $attrs['description']));


    $sql = "INSERT INTO posts(`placeId`, `userId`, `description`, `date`) VALUES ($placeId, $userId, '$description', NOW())";
    mysqli_query($db, $sql);
    $postId = mysqli_insert_id($db);
    $width = 512;
    foreach ($_FILES as $file) {
        $size = getimagesize($file['tmp_name']);
        $originalWidth = $size[0];
        $originalHeight = $size[1];
        $height = round($originalHeight / ($originalWidth / $width));
        $imagine = new Imagine\Gd\Imagine();
        $size = new Imagine\Image\Box($width, $height);
        if (!is_dir("img/items/" . $placeId)) {
            mkdir("img/items/" . $placeId);
        }
        if (!is_dir("img/items/" . $placeId . "/" . $postId)) {
            mkdir("img/items/" . $placeId . "/" . $postId);
        }

        print_r($imagine->open($file['tmp_name'])
            ->resize($size)
            ->save('img/items/' . $placeId . '/' . $postId . '/' . $file['name'], array('jpeg_quality' => 99)));
    }
});
$app->get('/getCities', function (ServerRequestInterface $request, ResponseInterface $response) {
    global $db;
    if (isset($request->getQueryParams()['id'])) {
        $id = (int)$request->getQueryParams()['id'];
    } else {
        $id = 0;
    }
    $sql = "SELECT * FROM cities WHERE id > $id";
    $res = mysqli_query($db, $sql);
    $items = [];
    while ($row = mysqli_fetch_object($res)) {
        $item = [];
        $item['id'] = $row->id;
        $item['name'] = $row->name;
        $items[] = $item;
    }
    $response->getBody()->write(json_encode($items, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE));
    return $response;
});
$app->run();