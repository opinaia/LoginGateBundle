<?php

namespace Anyx\LoginGateBundle\Storage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SessionStorage implements StorageInterface
{
    const COUNT_LOGIN_ATTEMPTS = '_security.count_login_attempts';

    const DATE_LAST_LOGIN_ATTEMPT = '_security.last_failurelogin_attempt';
    
    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function clearCountAttempts($method, Request $request)
    {
        $request->getSession()->remove(self::COUNT_LOGIN_ATTEMPTS . "_" . $method);
        $request->getSession()->remove(self::DATE_LAST_LOGIN_ATTEMPT . "_" . $method);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return integer
     */
    public function getCountAttempts($method, Request $request)
    {
        return (int) $request->getSession()->get(self::COUNT_LOGIN_ATTEMPTS . "_" . $method, 0);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     */
    public function incrementCountAttempts($method, Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(self::COUNT_LOGIN_ATTEMPTS . "_" . $method, $this->getCountAttempts($method, $request) + 1);
        $request->getSession()->set(self::DATE_LAST_LOGIN_ATTEMPT . "_" . $method, new \DateTime());
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \DateTime
     */
    public function getLastAttemptDate($method, Request $request)
    {
        $session = $request->getSession();
        if ($session->has(self::DATE_LAST_LOGIN_ATTEMPT . "_" . $method)) {
            return clone $session->get(self::DATE_LAST_LOGIN_ATTEMPT . "_" . $method);
        }
        
        return false;
    }
}