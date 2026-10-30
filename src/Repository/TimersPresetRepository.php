<?php

namespace App\Repository;

use App\Entity\TimersPreset;
use App\Enum\TimerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimersPreset>
 */
class TimersPresetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimersPreset::class);
    }

    /**
     * @return TimersPreset[] Returns an array of TimersPreset objects
     */
    public function getAllActive(): array
    {
        return $this->createQueryBuilder('tp')
            ->select('tp.id', 'tp.value', 'tp.type')
            ->andWhere('tp.active = :active')
            ->setParameter('active', true)
            ->orderBy('tp.count', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TimersPreset[] Returns an array of TimersPreset objects
     */
    public function getByValue(int $value, TimerType $type): array|null
    {
        return $this->createQueryBuilder('tp')
            ->select('tp.id', 'tp.value')
            ->andWhere('tp.value = :value')
            ->setParameter('value', $value)
            ->andWhere('tp.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function incrementCount(int $id): int
    {
        return $this->createQueryBuilder('tp')
            ->update()
            ->set('tp.count', 'tp.count + 1')
            ->where('tp.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
