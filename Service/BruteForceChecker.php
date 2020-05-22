<?php

namespace Anyx\LoginGateBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Anyx\LoginGateBundle\Storage\StorageInterface;

class BruteForceChecker
{
    /**
     * @var \Anyx\LoginGateBundle\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    private $options = [
        'max_count_attempts' => 3,
        'timeout' => 60
    ];

    /**
     * @return \Anyx\LoginGateBundle\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param \Anyx\LoginGateBundle\Storage\StorageInterface $storage
     * @param array $options
     */
    public function __construct(StorageInterface $storage, array $options)
    {
        $this->storage = $storage;
        $this->options = $options;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function canLogin(Request $request)
    {
        $checkMethods = $this->options['method'] == 'both' ? [ 'ip', 'user' ] : [ $this->options['method'] ];
        foreach ($checkMethods as $method) {
            $maxCount = $this->options['max_count_attempts_by_' . $method];
            $timeout = $this->options['timeout_by_' . $method];
            if ($this->getStorage()->getCountAttempts($method, $request) >= $maxCount) {
                $lastAttemptDate = $this->getStorage()->getLastAttemptDate($method, $request);
                $dateAllowLogin = $lastAttemptDate->modify('+' . $timeout . ' second');
                if ($dateAllowLogin->diff(new \DateTime())->invert === 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get time left of user ban
     *
     * @param  Request $request [description]
     * @return integer remaining in seconds of user ban
     */
    public function getBanTimeLeft(Request $request)
    {
        $checkMethods = $this->options['method'] == 'both' ? [ 'ip', 'user' ] : [ $this->options['method'] ];
        foreach ($checkMethods as $method) {
            $maxCount = $this->options['max_count_attempts_by_' . $method];
            $timeout = $this->options['timeout_by_' . $method];
            if ($this->getStorage()->getCountAttempts($method, $request) >= $maxCount) {
                $lastAttemptDate = $this->getStorage()->getLastAttemptDate($method, $request);
                $dateAllowLogin = clone $lastAttemptDate;
                $dateAllowLogin = $dateAllowLogin->modify('+' . $timeout . ' second');
                if ($dateAllowLogin->diff(new \DateTime())->invert === 1) {
                    $now = (new \DateTime());
                    return ($lastAttemptDate->getTimestamp() - $now->getTimestamp());
                }
            }
        }

        return 0;
    }
}
