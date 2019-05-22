<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;

class DecoratorManager extends DataProvider
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string                 $host
     * @param string                 $user
     * @param string                 $password
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface        $logger
     */
    public function __construct($host, $user, $password, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        parent::__construct($host, $user, $password);
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse(array $input)
    {
        try {
            $cacheKey = $this->getCacheKey($input);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $this->get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );

            $this->cache->save($cacheItem);

            return $result;
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->critical('Error');
        }

        return [];
    }

    public function getCacheKey(array $input)
    {
        return json_encode($input);
    }
}


