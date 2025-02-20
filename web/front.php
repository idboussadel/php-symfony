<?php
require_once __DIR__.'/../vendor/autoload.php';

use Simplex\ContentLengthListener;
use Simplex\Framework;
use Simplex\GoogleListener;
use Simplex\StringResponseListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;

$request = Request::createFromGlobals();
$requestStack = new RequestStack();
$routes = include __DIR__.'/../src/app.php';

$context = new RequestContext();
$matcher = new UrlMatcher($routes, $context);

$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher, $requestStack));
$dispatcher->addSubscriber(new ContentLengthListener());
$dispatcher->addSubscriber(new GoogleListener());
$listener = new ErrorListener(
    'Calendar\Controller\ErrorController::exception'
);
$dispatcher->addSubscriber(new StringResponseListener());
$dispatcher->addSubscriber($listener);

$framework = new Framework($dispatcher, $matcher, $controllerResolver,$requestStack, $argumentResolver);
$framework = new HttpCache(
    $framework,
    new Store(__DIR__.'/../cache'),
    new Esi(),
    ['debug' => true]
);
$response = $framework->handle($request);

function render_template(Request $request): Response
{
    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    include __DIR__ . '/../src/Calendar/pages/' . $_route . '.php';

    return new Response(ob_get_clean());
}

$response->send();
