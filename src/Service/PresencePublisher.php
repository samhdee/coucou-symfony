<?php

namespace App\Service;

use App\Repository\SeanceAttendantRepository;
use JsonException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class PresencePublisher
{
    public function __construct(
        private SeanceAttendantRepository $attendantRepository,
        private HubInterface              $hub,
    ) {}

    /**
     * @param array{0: string, 1: int} $target ex. ['seven-by-nine', 12]
     * @throws JsonException
     */
    public function publish(array $target): void
    {
        [$type, $id] = $target;

        $count = $this->attendantRepository->countDistinctVisitors($type, $id);

        $this->hub->publish(new Update(
            topics: "presence/{$type}/{$id}",
            data: json_encode([
                'type'  => 'presence',
                'count' => $count,
            ], JSON_THROW_ON_ERROR),
            private: false,
        ));
    }
}
