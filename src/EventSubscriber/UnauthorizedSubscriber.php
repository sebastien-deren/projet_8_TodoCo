<?php

namespace App\EventSubscriber;


use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;

class UnauthorizedSubscriber implements EventSubscriberInterface
{
    public function __construct(private RouterInterface $router)
    {
    }
    public function onKernelException(ExceptionEvent $event): void
    {
        //might be better to get an unauthorized page setup
        $exception = $event->getThrowable();
        if ($exception instanceof AccessDeniedHttpException) {
            $response = new RedirectResponse($this->router->generate('homepage'));
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
