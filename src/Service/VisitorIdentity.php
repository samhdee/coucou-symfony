<?php

namespace App\Service;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class VisitorIdentity
{
    public function __construct(private Security $security)
    {
    }

    public function resolve(Request $request): array
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            return ['userId' => $user->getId(), 'guestId' => null];
        }

        $guestId = $request->cookies->get('guest_id') ?? Uuid::v4()->toRfc4122();
        return ['userId' => null, 'guestId' => $guestId];
    }
}
