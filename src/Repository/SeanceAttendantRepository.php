<?php

namespace App\Repository;

use App\Entity\SeanceAttendant;
use App\Entity\Session;
use App\Entity\SevenByNine;
use App\Entity\User;
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
}
