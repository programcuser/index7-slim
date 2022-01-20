<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use function Symfony\Component\String\s;

// Старт PHP сессии
session_start();

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];
$usersFilePath = __DIR__ . '/../files/users/users.txt';

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::setContainer($container);
$app = AppFactory::create();
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
    $usersText = s(file_get_contents($usersFilePath));

    // Извлечение flash сообщений установленных на предыдущем запросе
    $messages = $this->get('flash')->getMessages();
    //print_r($messages);
    //var_dump($usersText);
    //var_dump(s("fjfjf\n")->split("\n"));
    //print_r($usersText);
    $usersArr = [];

    if (!$usersText->isEmpty()) {
        $strArr = $usersText->split("\n");

        $usersArr = array_map(function ($usr) {
            return json_decode($usr, true);
        }, $strArr);
    }
    //$usersArr = json_decode($usersText, true);
    
    $params = ['users' => $usersArr, 'flash' => $messages];
    return $this->get('renderer')->render($response, 'users/users.phtml', $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) {
        // Добавление флеш-сообщения. Оно станет доступным на следующий HTTP-запрос.
    $this->get('flash')->addMessage('success', 'The user added successfully!');
    $this->get('flash')->addMessage('error', 'Incorrect fields');

    $params = [
        'user' => [],
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml');
})->setName('newUser');

//Named Routes
$router = $app->getRouteCollector()->getRouteParser();

$app->post('/users', function ($request, $response) use ($usersFilePath, $router) {
    $user = $request->getParsedBodyParam('user');

    $validator = new App\Validator();
    $errors = $validator->validate($user);

    if (count($errors) === 0) {
    
        $usersText = s(file_get_contents($usersFilePath));
        $newUsersText = '';
        if (!$usersText->isEmpty()) {
            $strArr = $usersText->split("\n");
            //var_dump($strArr);
            $usersArr = array_map(function ($usr) {
                return json_decode($usr, true);
            }, $strArr);
            //var_dump($usersArr);
            $usersNum = count($strArr);
            $user['id'] = $usersNum + 1;
            $usersArr[] = $user;

            $usersPlusOneArr = array_map(function ($usr) {
                return json_encode($usr, true);
            }, $usersArr);

            $newUsersText = s("\n")->join($usersPlusOneArr);
        } else {
            $user['id'] = 1;
            $newUsersText = json_encode($user);
        }

        file_put_contents($usersFilePath, $newUsersText);

        return $response->withRedirect($router->urlFor('users'), 302);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) use ($usersFilePath) {
    $usersText = s(file_get_contents($usersFilePath));

    $usersArr = [];

    if (!$usersText->isEmpty()) {
        $strArr = $usersText->split("\n");

        $usersArr = array_map(function ($usr) {
            return json_decode($usr, true);
        }, $strArr);
    }

    $usr = collect($usersArr)->firstWhere('id', $args['id']);
    //var_dump($usr);
    if (!isset($usr)) {
        return $response->write('Page not found')->withStatus(404);
    }
    $params = ['id' => $usr['id'], 'nickname' => $usr['nickname'], 'email' => $usr['email']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->run();

