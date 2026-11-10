<?php
#[AsCommand(name: 'app:mercure:presence')]
class MercurePresenceCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
        private SeanceAttendantRepository $repo,
        private EntityManagerInterface $em,
        private string $hubUrl,
        private string $subscriberJwt,
    ) { parent::__construct(); }

    protected function execute(InputInterface $in, OutputInterface $out): int
    {
        $url = $this->hubUrl . '?topic=' . urlencode('/.well-known/mercure/subscriptions/{topic}/{subscriber}');
        $response = $this->client->request('GET', $url, [
            'headers' => ['Authorization' => 'Bearer ' . $this->subscriberJwt],
            'timeout' => 0,
        ]);

        foreach ($this->client->stream($response) as $chunk) {
            foreach ($this->parseSse($chunk->getContent()) as $event) {
                $this->handle($event);
            }
        }
        return Command::SUCCESS;
    }

    private function handle(array $sub): void
    {
        $guestId = $sub['payload']['guestId'] ?? null;
        $seanceId = $sub['payload']['seanceId'] ?? null;
        
        if (!$guestId || !$seanceId) {
            return;
        }

        $attendant = $this->repo->findOneBy(['guestId' => $guestId, 'seance' => $seanceId]);

        if ($sub['active']) {
            // join : créer si absent, sinon remettre leftAt à null
        } elseif ($attendant) {
            $attendant->setLeftAt(new \DateTimeImmutable());
            $this->em->flush();
        }
    }
}
