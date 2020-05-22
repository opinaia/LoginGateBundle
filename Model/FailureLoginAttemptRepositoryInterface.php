<?php

namespace Anyx\LoginGateBundle\Model;

interface FailureLoginAttemptRepositoryInterface
{
    /**
     * @param string $method
     * @param string $id
     * @param \DateTime $startDate
     * @return integer
     */
    public function getCountAttempts($method, $id, \DateTime $startDate);

    /**
     * @param string $method
     * @param string $id
     * @return \Anyx\LoginGateBundle\Model\FailureLoginAttempt | null
     */
    public function getLastAttempt($method, $id);

    /**
     * @param string $method
     * @param string $id
     * @return integer
     */
    public function clearAttempts($method, $id);
}
