<?php

namespace Anyx\LoginGateBundle\Storage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


interface StorageInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return integer
     */
    public function getCountAttempts($method, Request $request);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     */
    public function incrementCountAttempts($method, Request $request, AuthenticationException $exception);
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function clearCountAttempts($method, Request $request);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function getLastAttemptDate($method, Request $request);
}
