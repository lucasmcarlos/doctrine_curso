<?php

use App\Entity\Category;
use Aura\Router\RouterContainer;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals(
      $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$routerContainer = new RouterContainer();
//armazemna as rotas e configuracao

$generator = $routerContainer->getGenerator();
$map = $routerContainer->getMap();

$view = new \Slim\Views\PhpRenderer(__DIR__. '/../templates/');


$entityManager = getEntityManager();


$map->get('home', '/', function($request, $response) use($view) {
    //$response->getBody()->write("Hello World");
    return $view->render($response, 'home.phtml', ['test' => 'Funcionou']);
});

$map->get('categoria', '/categorias', function($request, $response) use($view, $entityManager) {
    $repository = $entityManager->getRepository(\App\Entity\Category::class);
    $categories = $repository->findAll();
    return $view->render($response, 'categories/list.phtml', ['categories' => $categories]);
});

$map->get('categories.create', '/categorias/create', function($request, $response) use($view) {
    return $view->render($response, 'categories/create.phtml');
});

$map->post('categorias.store', '/categorias/store', function(ServerRequestInterface $request, $response) use($view, $entityManager, $generator) {
        echo "passou aqui";
    $data = $request->getParsedBody();
    $category = new Category();

    $category->setName($data['name']);

    $entityManager->persist($category);
        $entityManager->flush();
    $uri = $generator->generate('categories.create');
    return new Response\RedirectResponse($uri);
});


$matcher = $routerContainer->getMatcher();

$route = $matcher->match($request);

foreach($route->attributes as $key => $values){
    $request = $request->withAttribute($key, $value);
}

$callable = $route->handler;

/** @var Response $response */
$response = $callable($response, new Response());

if ($response instanceof Response\RedirectResponse) {
    header("location:{$response->getHeader("location")[0]}");
} elseif ($response instanceof Response) {
    echo $response->getBody();
}


