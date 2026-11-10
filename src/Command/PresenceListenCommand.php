<?php

namespace App\Command;

use App\Service\PresenceHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:presence:listen')]
class PresenceListenCommand extends Command
{
    private ?string $lastEventId = null;

    public function __construct(
        private HttpClientInterface    $client,
        private PresenceHandler        $handler,
        private LoggerInterface        $logger,
        private EntityManagerInterface $em,
        private ManagerRegistry        $managerRegistry,
        #[Autowire('%env(MERCURE_URL)%')]
        private string                 $mercureUrl,
        #[Autowire('%env(MERCURE_SUBSCRIBER_JWT)%')]
        private string                 $subscriberJwt,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $delay = 1;

        while (true) {
            try {
                $lastEventId = $this->resync();
                $this->lastEventId = $lastEventId ?? $this->lastEventId;

                $this->listen($output);
                $delay = 1; // connexion propre fermée par le hub
            } catch (ExceptionInterface $e) {
                $this->logger->warning('Connexion Mercure perdue : ' . $e->getMessage());
            } catch (JsonException $e) {
                $this->logger->warning('Exception JSON : ' . $e->getMessage());
            }

            $output->writeln(sprintf('<comment>Reconnexion dans %ds...</comment>', $delay));
            sleep($delay);
            $delay = min($delay * 2, 30);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws JsonException
     */
    private function listen(OutputInterface $output): void
    {
        $url = $this->mercureUrl . '?topic=' . urlencode('/.well-known/mercure/subscriptions/{+topic}/{+subscriber}');

        $headers = [
            'Authorization' => 'Bearer ' . $this->subscriberJwt,
            'Accept' => 'text/event-stream',
        ];
        if ($this->lastEventId !== null) {
            $headers['Last-Event-ID'] = $this->lastEventId;
        }

        $response = $this->client->request('GET', $url, [
            'headers' => $headers,
            'timeout' => null,
        ]);

        $output->writeln('<info>Connecté au hub Mercure.</info>');

        $buffer = '';

        foreach ($this->client->stream($response) as $chunk) {
            if ($chunk->isTimeout()) {
                continue;
            }

            $buffer .= str_replace("\r\n", "\n", $chunk->getContent());

            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $raw = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);

                $payload = $this->parseEvent($raw);

                if ($payload !== null) {
                    $this->handleSafely($payload);
                }
            }

            if ($chunk->isLast()) {
                return;
            }
        }
    }

    private function parseEvent(string $rawEvent): ?array
    {
        $data = [];

        foreach (explode("\n", $rawEvent) as $line) {
            if (str_starts_with($line, 'id:')) {
                $this->lastEventId = trim(substr($line, 3));
            } elseif (str_starts_with($line, 'data:')) {
                $data[] = ltrim(substr($line, 5));
            }
        }

        if ($data === []) {
            return null; // commentaire ou keep-alive
        }

        try {
            // ['active' => bool, 'topic' => ..., 'subscriber' => ..., 'payload' => ...]
            return json_decode(implode("\n", $data), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->logger->error('Événement Mercure invalide : ' . $e->getMessage(), ['raw' => $rawEvent]);
            return null;
        }
    }

    private function handleSafely(array $payload): void
    {
        try {
            $this->handler->handle($payload);
        } catch (\Throwable $e) {
            // un événement foireux ne doit pas arrêter le listener
            $this->logger->error('Erreur PresenceHandler : ' . $e->getMessage(), ['payload' => $payload]);
        } finally {
            if ($this->em->isOpen()) {
                $this->em->clear();                       // vide la mémoire de l'EM
            } else {
                $this->managerRegistry->resetManager();   // EM fermé : on en recrée un
            }
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function resync(): ?string
    {
        $url = $this->mercureUrl . '/subscriptions';
        // adapte si MERCURE_URL = https://hub/.well-known/mercure
        // dans ce cas : $url = rtrim($this->mercureUrl, '/') . '/subscriptions';

        $response = $this->client->request('GET', $url, [
            'headers' => ['Authorization' => 'Bearer ' . $this->subscriberJwt],
        ]);

        $data = $response->toArray();

        $this->handler->resync($data['subscriptions'] ?? []);
        $this->em->clear();

        return $data['lastEventID'] ?? null;
    }
}
