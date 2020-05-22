<?php

namespace Anyx\LoginGateBundle\Entity;

use Doctrine\ORM\EntityRepository as Repository;
use Anyx\LoginGateBundle\Model\FailureLoginAttemptRepositoryInterface;

class FailureLoginAttemptRepository extends Repository implements FailureLoginAttemptRepositoryInterface
{
    /**
     * @param string $method
     * @param string $id
     * @param \DateTime $startDate
     * @return integer
     */
    public function getCountAttempts($method, $id, \DateTime $startDate)
    {
        if ($method == 'ip') {
            $fieldName = 'ip';
        } else {
            $fieldName = 'username';
        }
        return $this->createQueryBuilder('attempt')
                    ->select('COUNT(attempt.id)')
                    ->where('attempt.' . $fieldName . ' = :id')
                    ->andWhere('attempt.createdAt > :createdAt')
                    ->setParameters(array(
                        'id'        => $id,
                        'createdAt' => $startDate
                    ))
                    ->getQuery()
                    ->getSingleScalarResult();
    }
    
    /**
     * @param string $method
     * @param string $id
     * @return \Anyx\LoginGateBundle\Entity\FailureLoginAttempt | null
     */
    public function getLastAttempt($method, $id)
    {
        if ($method == 'ip') {
            $fieldName = 'ip';
        } else {
            $fieldName = 'username';
        }
        return $this->createQueryBuilder('attempt')
                    ->where('attempt.' . $fieldName . ' = :id')
                    ->orderBy('attempt.createdAt', 'DESC')
                    ->setParameters(array(
                        'id'        => $id
                    ))
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult()
        ;
    }
    
    /**
     * @param string $method
     * @param string $id
     * @return integer
     */
    public function clearAttempts($method, $id)
    {
        if ($method == 'ip') {
            $fieldName = 'ip';
        } else {
            $fieldName = 'username';
        }
        return $this->getEntityManager()
                ->createQuery('DELETE FROM ' . $this->getClassMetadata()->name . ' attempt WHERE attempt.' . $fieldName . ' = :id')
                ->execute(['id' => $id])
            ;
    }
}
