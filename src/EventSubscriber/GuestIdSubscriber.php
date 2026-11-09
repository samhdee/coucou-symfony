<?php
namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

class GuestIdSubscriber implements EventSubscriberInterface
{
    private const string COOKIE = 'guest_id';

    public function __construct(private Security $security)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 7], // juste après le routing/firewall si besoin
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        error_log('GuestIdSubscriber::onRequest main=' . var_export($event->isMainRequest(), true));

        if (!$event->isMainRequest() || $this->security->getUser()) {
            return;
        }

        $request = $event->getRequest();

        $guest_id = $request->cookies->get(self::COOKIE);

        if (!$guest_id || !Uuid::isValid($guest_id)) {
            $guest_id = Uuid::v4()->toRfc4122();
            $request->attributes->set('_guest_id_new', true); // à poser en réponse
        }

        // disponible partout via $request->attributes->get('guest_id')
        $request->attributes->set('guest_id', $guest_id);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('_guest_id_new')) {
            return;
        }

        $event->getResponse()->headers->setCookie(
            Cookie::create(self::COOKIE)
                ->withValue($request->attributes->get('guest_id'))
                ->withHttpOnly(true)
                ->withSecure($request->isSecure())
                ->withSameSite('lax')
        );
    }
}
