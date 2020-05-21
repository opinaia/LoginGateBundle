<?php

namespace Anyx\LoginGateBundle\Storage;

use Anyx\LoginGateBundle\Exception\BruteForceAttemptException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DatabaseStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $modelClassName;

    /**
     * @var integer
     */
    private $watchPeriod = 200;

    /**
     * @var string
     */
    private $method = 'user';

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $objectManager;

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityClass
     * @param integer $watchPeriod
     */
    public function __construct(ObjectManager $objectManager, $entityClass, $watchPeriod, $method)
    {
        $this->objectManager = $objectManager;
        $this->modelClassName = $entityClass;
        $this->watchPeriod = $watchPeriod;
        $this->method = $method;
    }

    /**
     * @param string $method
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function clearCountAttempts($method, Request $request)
    {
        $id = $this->getIdByMethod($method, $request);
        if (!$id) {
            return;
        }
        $this->getRepository()->clearAttempts($method, $id);
    }

    /**
     * Get id value from given request determined by given method (ip/user)
     *
     * @param  string $method
     * @param  Request $request
     * @return string
     */
    private function getIdByMethod($method, $request)
    {
        if ($method == 'ip') {
            if (!$this->hasIp($request)) {
                return 0;
            }
            return $request->getClientIp();
        } else {
            return $request->get('_username');
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return integer
     */
    public function getCountAttempts($method, Request $request)
    {
        $id = $this->getIdByMethod($method, $request);
        $startWatchDate = new \DateTime();
        $startWatchDate->modify('-' . $this->getWatchPeriod(). ' second');

        return $this->getRepository()->getCountAttempts($this->method, $id, $startWatchDate);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \DateTime|false
     */
    public function getLastAttemptDate($method, Request $request)
    {
        $id = $this->getIdByMethod($method, $request);
        if (!$id) {
            return;
        }
        $lastAttempt = $this->getRepository()->getLastAttempt($method, $id);
        if (!empty($lastAttempt)) {
            return $lastAttempt->getCreatedAt();
        }

        return false;
    }

    /**
     * @param  string $method
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     */
    public function incrementCountAttempts($method, Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof BruteForceAttemptException) {
            return;
        }

        if (!$this->hasIp($request)) {
            return;
        }
        $model = $this->createModel();

        $model->setIp($request->getClientIp());

        $data = [
            'exception' => $exception->getMessage(),
            'clientIp'  => $request->getClientIp(),
            'sessionId' => $request->getSession()->getId()
        ];

        $username = $request->get('_username');
        if (!empty($username)) {
            $data['user'] = $username;
            $model->setUsername($username);
        }

        $model->setData($data);

        $objectManager = $this->getObjectManager();

        $objectManager->persist($model);
        $objectManager->flush($model);
    }

    /**
     * @return integer
     */
    protected function getWatchPeriod()
    {
        return $this->watchPeriod;
    }

    /**
     * @return string
     */
    protected function createModel()
    {
        return new $this->modelClassName;
    }

    /**
     * @return \Anyx\LoginGateBundle\Model\FailureLoginAttemptRepositoryInterface
     */
    protected function getRepository()
    {
        return $this->getObjectManager()->getRepository($this->modelClassName);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    protected function hasIp(Request $request)
    {
        return $request->getClientIp() != '';
    }

    protected function doCheckByIp()
    {
        return $this->method == 'ip' || $this->method == 'both';
    }

    protected function doCheckByUser()
    {
        return $this->method == 'user' || $this->method == 'both';
    }
}
