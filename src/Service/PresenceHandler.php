<?php

namespace App\Service;

use App\Entity\SeanceAttendant;
use App\Repository\SeanceAttendantRepository;
use App\Repository\SessionRepository;
use App\Repository\SevenByNineRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;

class PresenceHandler
{
    public function __construct(
        private EntityManagerInterface    $em,
        private SeanceAttendantRepository $attendantRepository,
        private SevenByNineRepository     $sevenByNines,
        private SessionRepository         $sessions,
        private UserRepository            $users,
        private PresencePublisher         $publisher,
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function handle(array $event): void
    {
        $parsed = $this->parseTopic($event['topic']); // "/seven-by-nine/12" => ['seven-by-nine', 12]

        if (empty($parsed)) {
            return; // topic qui ne nous concerne pas
        }

        [$type, $id] = $parsed;

        $event['active']
            ? $this->open($event, $type, $id)
            : $this->close($event['subscriber'], $type, $id);

        $this->publisher->publish([$type, $id]);
    }

    private function open(array $event, string $type, int $id): void
    {
        $subscriber = $event['subscriber'];

        // Idempotence : la resync au démarrage peut rejouer l'événement
        if ($this->attendantRepository->findOpenByHubSubscriber($subscriber)) {
            return;
        }

        $userId = $event['payload']['userId'] ?? null;
        $guestId = $event['payload']['guestId'] ?? null;

        if (!$userId && !$guestId) {
            return; // identité inconnue, on n'enregistre pas une ligne inexploitable
        }

        $attendant = new SeanceAttendant()
            ->setHubSubscriberId($subscriber)
            ->setJoinedAt(new DateTimeImmutable());

        if ($userId) {
            $user = $this->users->find($userId);
            if (!$user) {
                return;
            }
            $attendant->setUser($user);
        } else {
            $attendant->setGuestId($guestId);
        }

        match ($type) {
            'seven-by-nine' => $attendant->setSevenByNine($this->sevenByNines->find($id)),
            'session' => $attendant->setSession($this->sessions->find($id)),
        };

        $this->em->persist($attendant);
        $this->em->flush();
    }

    private function close(string $subscriber, string $type, int $id): void
    {
        $presence = $this->attendantRepository->findOpenByHubSubscriber($subscriber);

        if ($presence) {
            $presence->setLeftAt(new DateTimeImmutable());
            $this->em->flush();
        }
    }

    /**
     * @return array{0: string, 1: int}|null ex. "/seven-by-nine/12" => ['seven-by-nine', 12]
     */
    private function parseTopic(string $topic): ?array
    {
        if (!preg_match('#^/(seven-by-nine|session)/(\d+)$#', $topic, $m)) {
            return null;
        }

        return [$m[1], (int)$m[2]];
    }

    /**
     * @param array<int, array> $subscriptions Subscriptions actives retournées par le hub
     */
    public function resync(array $subscriptions): mixed
    {
        $liveSubscribers = [];

        // 1. Ouvrir ce qui est actif sur le hub (open() est idempotent)
        foreach ($subscriptions as $sub) {
            if (!($sub['active'] ?? false)) {
                continue;
            }

            $parsed = $this->parseTopic($sub['topic']);

            if ($parsed === null) {
                continue;
            }

            $liveSubscribers[] = $sub['subscriber'];
            [$type, $id] = $parsed;
            $this->open($sub, $type, $id);
        }

        // 2. Fermer les présences ouvertes en base qui ne sont plus sur le hub
        $stale = $this->attendantRepository->findOpenExcept($liveSubscribers);

        foreach ($stale as $attendant) {
            $attendant->setLeftAt(new DateTimeImmutable());
        }

        $this->em->flush();

       return $data['lastEventID'] ?? null;
    }
}
