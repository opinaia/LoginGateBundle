<?php

namespace Anyx\LoginGateBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository as Repository;
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
        return $this->createQueryBuilder()
            ->field($fieldName)->equals($id)
            ->field('createdAt')->gt($startDate)
            ->getQuery()->count();
    }

    /**
     * @param string $method
     * @param string $id
     * @return \Anyx\LoginGateBundle\Model\FailureLoginAttempt | null
     */
    public function getLastAttempt($method, $id)
    {
        if ($method == 'ip') {
            $fieldName = 'ip';
        } else {
            $fieldName = 'username';
        }
        return $this->createQueryBuilder()
            ->field($fieldName)->equals($id)
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->getSingleResult();
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
        return $this->createQueryBuilder()
            ->remove()
            ->field($fieldName)->equals($id)
            ->getQuery()
            ->execute();
    }
}
