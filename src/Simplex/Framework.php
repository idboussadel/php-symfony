<?php
namespace Simplex;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class Framework extends HttpKernel
{
    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected UrlMatcherInterface $matcher,
        protected ControllerResolverInterface $controllerResolver,
        protected RequestStack $requestStack,
        protected ArgumentResolverInterface $argumentResolver,
        
    ) {
    }
    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true
    ): Response {
        $this->requestStack->push($request);
        $this->matcher->getContext()->fromRequest($request);

        $request->attributes->add($this->matcher->match($request->getPathInfo()));
    
        $controller = $this->controllerResolver->getController($request);
        $arguments = $this->argumentResolver->getArguments($request, $controller);
    
        $response = call_user_func_array($controller, $arguments);

        if (!$response instanceof Response) {
            $response = new Response((string) $response);
        }
    
        $event = new ResponseEvent($this, $request, $type, $response);
        $this->dispatcher->dispatch($event, 'kernel.response');
    
        return $event->getResponse();
    }
    
}