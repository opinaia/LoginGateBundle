<?php

namespace Anyx\LoginGateBundle\Storage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CompositeStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $storages = [];

    /**
     * @param array $storages
     */
    public function __construct(array $storages)
    {
        foreach ($storages as $storage) {
            $this->addStorage($storage);
        }
    }

    /**
     * @return array
     */
    public function getStorages()
    {
        return $this->storages;
    }

    /**
     * @param \Anyx\LoginGateBundle\Storage\StorageInterface $storage
     */
    public function addStorage(StorageInterface $storage)
    {
        $this->storages[] = $storage;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function clearCountAttempts($method, Request $request)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->clearCountAttempts($method, $request);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function getCountAttempts($method, Request $request)
    {
        $countAttempts = array();
        
        foreach ($this->getStorages() as $storage) {
            $countAttempts[] = $storage->getCountAttempts($method, $request);
        }

        return (int) max($countAttempts);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \DateTime | false
     */
    public function getLastAttemptDate($method, Request $request)
    {
        $date = false;
        foreach ($this->getStorages() as $storage) {
            $storageDate = $storage->getLastAttemptDate($method, $request);
            if (!empty($storageDate) && (empty($date) || $storageDate->diff($date)->invert == 1)) {
                $date = $storageDate;
            }
        }
        return $date;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     */
    public function incrementCountAttempts($method, Request $request, AuthenticationException $exception)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->incrementCountAttempts($method, $request, $exception);
        }
    }
}
