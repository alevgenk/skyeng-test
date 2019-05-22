<?php declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use src\Decorator\DecoratorManager;
use src\Integration\DataProvider;
use src\Manger\ApiManager;

class DecoratorManagerTest extends TestCase
{
    /**
     * Test manager behavior if valid cache item acquired from cache
     */
    public function testGetResponseWithValidCacheItem(): void
    {
        try {
            $cacheItem = $this
                ->createMock(CacheItemInterface::class);
            $cacheItem->method('isHit')
                ->willReturn(true);
            $cacheItem
                ->expects($this->once())
                ->method('get');
            $cache = $this->getCacheWithCacheItem($cacheItem);
            $logger = $this->createMock(LoggerInterface::class);
            $provider = new DataProvider('host', 'user', 'password');
            $manager = new ApiManager($provider, $cache, $logger);
            $manager->getResponse([]);
        } catch (ReflectionException $e) {
            echo $e->getMessage();
        }
    }

    private function getCacheWithCacheItem($item)
    {
        try {
            $cache = $this
                ->getMockForAbstractClass(CacheItemPoolInterface::class);
            $cache->method('getItem')->willReturn($item);
            return $cache;
        } catch (ReflectionException $e) {
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * If we get an invalid cache item from the cache
     * do not call get() method on it
     * but
     */
    public function testGetResponseWithInValidCacheItem(): void
    {
        try {
            $cacheItem = $this
                ->getMockBuilder(CacheItemInterface::class)
                ->setMethods(['isHit', 'get', 'set', 'expiresAt'])
                ->getMockForAbstractClass();
            $cacheItem->method('isHit')
                ->willReturn(false);
            $cacheItem->method('set')->willReturn($cacheItem);

            $cacheItem
                ->expects($this->never())
                ->method('get');
            $cacheItem
                ->expects($this->once())
                ->method('set');
            $cacheItem
                ->expects($this->once())
                ->method('expiresAt');
            $cache = $this->getCacheWithCacheItem($cacheItem);
            $logger = $this->createMock(LoggerInterface::class);
            $provider = new DataProvider('host', 'user', 'password');
            $manager = new ApiManager($provider, $cache, $logger);
            $manager->getResponse([]);
        } catch (ReflectionException $e) {
            echo $e->getMessage();
        }
    }

}
