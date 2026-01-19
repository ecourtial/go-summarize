<?php

declare(strict_types=1);

namespace App\Security\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsEventListener(event: RequestEvent::class)]
class ApiDocsAccessListener
{
    private string $environment;

    public function __construct(KernelInterface $kernel)
    {
        $this->environment = $kernel->getEnvironment();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Match path (you could also check for route name if needed)
        if (str_starts_with($request->getPathInfo(), '/api/docs')) {
            if ('prod' === $this->environment) {
                $event->setResponse(new JsonResponse(['message' => 'Hello']));
            }
        }
    }
}
