<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use function Symfony\Component\String\s;

// Старт PHP сессии для flash сообщений
session_start();

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];
//$usersFilePath = __DIR__ . '/../files/users/users.txt';

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
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    //$response->getBody()->write('Welcome to Slim!');
    //return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
    //print_r(file_get_contents('example.txt', FILE_USE_INCLUDE_PATH));
    //print_r(__DIR__);
    $messages = $this->get('flash')->getMessages();

    return $response->write("Welcome to Hexlet!<br>{$messages['error'][0]}");
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

$app->get('/users', function ($request, $response) {
    $users = $request->getCookieParam('users', json_encode([]));

    $repo = new App\UserRepository($users);
    $usersArr = $repo->all();
    
    $messages = $this->get('flash')->getMessages();

    $params = ['users' => $usersArr, 'flash' => $messages];
    return $this->get('renderer')->render($response, 'users/users.phtml', $params);
})->setName('users');


$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => [],
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'users/new.phtml');
})->setName('newUser');

//Named Routes
$router = $app->getRouteCollector()->getRouteParser();

$app->post('/users', function ($request, $response) use ($router) {
    $user = $request->getParsedBodyParam('user');

    $validator = new App\Validator();
    $errors = $validator->validate($user);

    if (count($errors) === 0) {
        $users = $request->getCookieParam('users', json_encode([]));
        $repo = new App\UserRepository($users);

        $this->get('flash')->addMessage('success', 'The user added successfully!'); 

        $repo->save($user);
        $usersJson = $repo->getJson();

        return $response->withHeader('Set-Cookie', "users={$usersJson}")
                ->withRedirect($router->urlFor('users'), 302);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $users = $request->getCookieParam('users', json_encode([]));
    $id = $args['id'];
   
    $repo = new App\UserRepository($users);
    $usr = $repo->find($id);

    if (!isset($usr)) {
        return $response->write('Page not found')->withStatus(404);
    }
    $params = ['user' => $usr];

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

$app->get('/users/{id}/edit', function ($request, $response, $args) {
    $users = $request->getCookieParam('users', json_encode([]));
    $repo = new App\UserRepository($users);
    $id = $args['id'];

    $user = $repo->find($id);

    if (!isset($user)) {
        return $response->write('Page not found')->withStatus(404);
    }

    $messages = $this->get('flash')->getMessages();

    $params = [
        'user' => $user,
        'errors' => [],
        'flash' => $messages
    ];

    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');

$app->patch('/users/{id}', function ($request, $response, $args) use ($router) {
    $users = $request->getCookieParam('users', json_encode([]));
    $repo = new App\UserRepository($users);
    $id = $args['id'];

    $user = $repo->find($id);
    $data = $request->getParsedBodyParam('user');

    $validator = new App\Validator();
    $errors = $validator->validate($data);

    if (count($errors) === 0) {
        $user['nickname'] = $data['nickname'];
        $user['email'] = $data['email'];

        $this->get('flash')->addMessage('success', 'User has been updated');
        $repo->save($user);

        $url = $router->urlFor('editUser', ['id' => $user['id']]);
        $usersJson = $repo->getJson();
        return $response->withHeader('Set-Cookie', "users={$usersJson}")->withRedirect($url);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});


$app->delete('/users/{id}', function ($request, $response, $args) use ($router) {
    $users = $request->getCookieParam('users', json_encode([]));
    $repo = new App\UserRepository($users);
    $id = $args['id'];

    $repo->destroy($id);

    $this->get('flash')->addMessage('success', 'User has been removed');
    $usersJson = $repo->getJson();
    //$url = $router->urlFor('users');
    return $response->withHeader('Set-Cookie', "users={$usersJson}")->withRedirect($router->urlFor('users'));
});

$app->post('/session', function($request, $response) use ($router) {
    $user = $request->getParsedBodyParam('user');

    $path = __DIR__ . '/../files/users/users.json';
    $users = file_get_contents($path);
    $usersArr = array_values(json_decode($users, true));

    $userExists = (collect($usersArr))->firstWhere('email', $user['email']);

    if (isset($userExists)) {

        session_start();

        if (!isset($_SESSION['user'])) {
            $_SESSION['user']['email'] = $userExists['email'];
            $_SESSION['user']['nickname'] = $userExists['nickname'];
        }
        return $response->withRedirect($router->urlFor('users'));
    }

    $this->get('flash')->addMessage('error', 'Wrong email');
    return $response->withRedirect('/');
});

$app->delete('/session', function($request, $response) use ($router) {
    $_SESSION = [];
    session_destroy();
    return $response->withRedirect($router->urlFor('users'));
});

$app->run();
