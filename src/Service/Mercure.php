<?php

namespace App\Service;

use App\Entity\SevenByNine;
use App\Entity\Timer;
use App\Enum\TimerStatus;
use JsonException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class Mercure
{
    public function __construct(private HubInterface $hub) {}

    /**
     * @throws JsonException
     */
    public function sessionUpdated($type, $session_id, Timer $timer): void
    {
        $this->hub->publish(new Update(
            "{$type}/{$session_id}",
            json_encode([
                'id' => $session_id,
                'currentTimer' => [
                    'status' => $timer->getStatus()->value,
                    'remainingSeconds' => $timer->getRemainingSeconds(),
                    'remainingAtPause' => $timer->getRemainingAtPause(),
                    'endsAt' => $timer->getStatus() === TimerStatus::Running
                        ? $timer->getEndsAt()
                            ->setTimezone(new \DateTimeZone('Europe/Paris'))
                            ->format('Y-m-d H:i:s.u')
                        : null,
                ]
            ], JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @throws JsonException
     */
    public function sevenByNineUpdated(SevenByNine $sevenByNine, Timer $timer): void
    {
        $this->hub->publish(new Update(
            "seven-by-nine/{$sevenByNine->getId()}",
            json_encode([
                'id' => $sevenByNine->getId(),
                'drawnValues' => $sevenByNine->getDrawnValues(),
                'countTasks' => $sevenByNine->getCountTasks(),
                'countPauses' => $sevenByNine->getCountPauses(),
                'createdBy' => $sevenByNine->getCreatedBy(),
                'currentTimer' => [
                    'status' => $timer->getStatus()->value,
                    'remainingSeconds' => $timer->getRemainingSeconds(),
                    'remainingAtPause' => $timer->getRemainingAtPause(),
                    'endsAt' => $timer->getStatus() === TimerStatus::Running
                        ? $timer->getEndsAt()
                            ->setTimezone(new \DateTimeZone('Europe/Paris'))
                            ->format('Y-m-d H:i:s.u')
                        : null,
                ]
            ], JSON_THROW_ON_ERROR),
        ));
    }
}
