<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use function Symfony\Component\String\s;


$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];
$usersFilePath = __DIR__ . '/../files/users/users.txt';

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    //$response->getBody()->write('Welcome to Slim!');
    //return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
    //print_r(file_get_contents('example.txt', FILE_USE_INCLUDE_PATH));
    //print_r(__DIR__);
    return $response->write('Welcome to Hexlet!');
});
//$app->run();

//$app->get('/users', function ($request, $response) {
//    return $response->write('GET /users');
//});

//$app->post('/users', function ($request, $response) {
//    return $response->write('POST /users');
//});

//$app->post('/users', function ($request, $response) {
//    return $response->withStatus(302);
//});

//нужно использовать библиотеки Symfony\Component\String\s из Symfony, collect из Laravel
//$app->get('/users', function ($request, $response) use ($users) {
//    $term = $request->getQueryParam('term');
//    $paramsArr = $request->getQueryParams();
//    $newUsers = collect($users)->map(fn($us) => s($us));
//
//    if (count($paramsArr) !== 0) {
//        $newUsers = $newUsers->filter(fn($us) => $us->startsWith($term));
//    }
//    //print_r($newUsers->toArray());
//    $params = ['users' => $newUsers->toArray(), 'term' => $term];
//    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
//});

$app->get('/users', function ($request, $response) use ($usersFilePath) {
    $usersText = file_get_contents($usersFilePath);
    //print_r($usersText);
    //$usersArr = explode("\n", $usersText);
    $usersArr = json_decode($usersText, true);
    //$usersCount = count($usersArr);
    //array_splice($usersArr, $usersCount - 1);
    //var_dump(count($usersArr));
    //$usersArr = $usersArr ?? [];
    /*$newUsers = array_map(function ($us) {
        $usr = json_decode($us, true);
        print_r($usr);
        //return $us;
        return "{$usr['name']} - {$usr['nickname']}";
    }, $usersArr);*/

    $params = ['users' => $usersArr];
    return $this->get('renderer')->render($response, 'users/users.phtml', $params);
});

$app->get('/users/new', function ($request, $response) {
    return $this->get('renderer')->render($response, 'users/new.phtml');
});

$app->post('/users', function ($request, $response) use ($usersFilePath) {
    $user = $request->getParsedBodyParam('user');
    
    $usersText = file_get_contents($usersFilePath);
    $usersArr = json_decode($usersText);
    $usersNum = count($usersArr);

    $user['id'] = $usersNum + 1;
    $usersArr[] = $user;
    $userJson = json_encode($usersArr, JSON_PRETTY_PRINT);
    file_put_contents($usersFilePath, $userJson);

    return $response->withRedirect('/users', 302);
    //return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->run();

