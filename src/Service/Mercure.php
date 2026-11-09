<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\SevenByNine;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class Mercure
{
    private const string SESSION_TOPIC = 'session';
    private const string SEVEN_BY_NINE_TOPIC = 'seven-by-nine';

    public function __construct(private HubInterface $hub, private SerializerInterface $serializer) {}

    /**
     * @throws ExceptionInterface
     */
    public function sessionUpdated(Session $session): void
    {
        $this->hub->publish(new Update(
            $this::SESSION_TOPIC . "/{$session->getId()}",
            $this->serializer->serialize($session, 'json', ['groups' => 'session:read']),
        ));
    }

    /**
     * @throws ExceptionInterface
     */
    public function sessionJoined(Session $session): void
    {
        $this->hub->publish(new Update(
            $this::SESSION_TOPIC . "/{$session->getId()}",
            $this->serializer->serialize($session, 'json', ['groups' => 'session:read']),
        ));
    }

    /**
     * @throws ExceptionInterface
     */
    public function sevenByNineUpdated(SevenByNine $sevenByNine): void
    {
        $this->hub->publish(new Update(
            $this::SEVEN_BY_NINE_TOPIC . "/{$sevenByNine->getId()}",
            $this->serializer->serialize($sevenByNine, 'json', ['groups' => 'seven_by_nine:read']),
        ));
    }

    /**
     * @throws ExceptionInterface
     */
    public function sevenByNineJoined(SevenByNine $seven_by_nine): void
    {
        $this->hub->publish(new Update(
            $this::SEVEN_BY_NINE_TOPIC . "/{$seven_by_nine->getId()}",
            $this->serializer->serialize($seven_by_nine, 'json', ['groups' => 'seven_by_nine:read']),
        ));
    }
}
