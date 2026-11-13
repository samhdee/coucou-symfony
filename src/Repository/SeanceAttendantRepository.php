<?php

namespace App\Repository;

use App\Entity\SeanceAttendant;
use App\Entity\Session;
use App\Entity\SevenByNine;
use App\Entity\User;
use App\Enum\SeanceType;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SeanceAttendant>
 */
class SeanceAttendantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeanceAttendant::class);
    }

    public function isUserAttendingSession(Session $session, User $user): bool
    {
        return (bool)$this->createQueryBuilder('a')
            ->select('1')
            ->where('a.session = :session')
            ->setParameter('session', $session)
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isGuestAttendingSession(Session $session, $guest_id): bool
    {
        return (bool)$this->createQueryBuilder('a')
            ->select('1')
            ->where('a.session = :session')
            ->setParameter('session', $session)
            ->andWhere('a.guestId = :guest_id')
            ->setParameter('guest_id', $guest_id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isUserAttendingSevenByNine(SevenByNine $seven_by_nine, User $user): bool
    {
        return (bool)$this->createQueryBuilder('a')
            ->select('1')
            ->where('a.sevenByNine = :seven_by_nine')
            ->setParameter('seven_by_nine', $seven_by_nine)
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isGuestAttendingSevenByNine(SevenByNine $seven_by_nine, $guest_id): bool
    {
        return (bool)$this->createQueryBuilder('a')
            ->select('1')
            ->where('a.sevenByNine = :seven_by_nine')
            ->setParameter('seven_by_nine', $seven_by_nine)
            ->andWhere('a.guestId = :guest_id')
            ->setParameter('guest_id', $guest_id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $guestId
     * @param User $user
     * @return void
     */
    public function mergeGuestIntoUser(string $guestId, User $user): void
    {
        $em = $this->getEntityManager();

        $em->wrapInTransaction(function () use ($guestId, $user) {
            // 1. Supprimer les lignes invité qui feraient doublon avec une ligne user existante
            $this->createQueryBuilder('g')
                ->delete()
                ->where('g.guestId = :guestId')
                ->setParameter('guestId', $guestId)
                ->andWhere('EXISTS (
                    SELECT 1 FROM App\Entity\SeanceAttendant u
                    WHERE u.user = :user
                        AND (
                            (u.sevenByNine IS NOT NULL AND u.sevenByNine = g.sevenByNine)
                            OR (u.session IS NOT NULL AND u.session = g.session)
                    )
                )')
                ->setParameter('user', $user)
                ->getQuery()
                ->execute();

            // 2. Rattacher les lignes restantes au user
            $this->createQueryBuilder('g')
                ->update()
                ->set('g.user', ':user')
                ->set('g.guestId', 'NULL')
                ->where('g.guestId = :guestId')
                ->setParameter('user', $user)
                ->setParameter('guestId', $guestId)
                ->getQuery()
                ->execute();
        });
    }

    public function countDistinctVisitors(string $type, int $id): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT COALESCE(IDENTITY(p.user), p.guestId))')
            ->andWhere('p.leftAt IS NULL');

        match ($type) {
            'seven-by-nine' => $qb->andWhere('p.sevenByNine = :id'),
            'session' => $qb->andWhere('p.session = :id'),
        };

        return (int)$qb->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSeanceAttendants(SeanceType $type, SevenByNine|Session $seance)
    {
        if ($type === SeanceType::SevenByNine) {
            $qb = $this->createQueryBuilder('sa')
                ->leftJoin('sa.user', 'u')
                ->addSelect('u')
                ->where('sa.sevenByNine = :seven_by_nine')
                ->setParameter('seven_by_nine', $seance);

            $qb->andWhere(
                    $qb->expr()->orX(
                        'sa.leftAt IS NULL',
                        'sa.leftAt > :now'
                    )
                )
                ->setParameter('now', new DateTimeImmutable());

            return $qb->getQuery()->getResult();
        } elseif ($type === SeanceType::Session) {
            $qb = $this->createQueryBuilder('sa')
                ->leftJoin('sa.user', 'u')
                ->addSelect('u')
                ->where('sa.session = :session')
                ->setParameter('session', $seance);

            $qb->andWhere(
                    $qb->expr()->orX(
                        'sa.leftAt IS NULL',
                        'sa.leftAt > :now'
                    )
                )
                ->setParameter('now', new DateTimeImmutable());

            return $qb->getQuery()->getResult();
        }
    }
}
