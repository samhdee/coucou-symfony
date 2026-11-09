<?php
namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\SeanceAttendantRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class MergeGuestAttendancesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SeanceAttendantRepository $seanceAttendantRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $guest_id = $event->getRequest()->cookies->get('guest_id');

        if (!$guest_id) {
            return;
        }

        $this->seanceAttendantRepository->mergeGuestIntoUser($guest_id, $user);

        // Le guest_id n'a plus de raison d'être une fois fusionné
        $response = $event->getResponse();
        $response?->headers->clearCookie('guest_id');
    }
}
