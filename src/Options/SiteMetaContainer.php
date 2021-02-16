<?php

declare(strict_types=1);

namespace WpOop\Containers\Options;

use Dhii\Collection\ContainerInterface;
use Dhii\Collection\MutableContainerInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as BaseContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use WP_Site;
use WpOop\Containers\Exception\ContainerException;
use WpOop\Containers\Util\StringTranslatingTrait;

/**
 * Creates and returns metadata containers for sites.
 *
 * @package WpOop\Containers
 */
class SiteMetaContainer implements ContainerInterface
{
    use StringTranslatingTrait;

    /**
     * @var callable
     */
    protected $optionsFactory;
    /**
     * @var BaseContainerInterface
     */
    protected $sitesContainer;

    /**
     * @param callable $optionsFactory A callable with the following signature:
     * `function (int $id): ContainerInterface`
     * Accepts a site ID, and returns a container with meta for that site.
     * @param BaseContainerInterface $sitesContainer The container of WP Site instances.
     * Used for checking if a site exists.
     */
    public function __construct(
        callable $optionsFactory,
        BaseContainerInterface $sitesContainer
    ) {
        $this->optionsFactory = $optionsFactory;
        $this->sitesContainer = $sitesContainer;
    }

    /**
     * Retrieves metadata for a site with the specified ID.
     *
     * @inheritDoc
     *
     * @param int|string $id The numeric ID of the site to retrieve metadata for.
     *
     * @return MutableContainerInterface The metadata.
     */
    public function get($id): MutableContainerInterface
    {
        /** @psalm-suppress InvalidCatch PSR-11 exceptions will always implement the interface */
        try {
            $site = $this->getSite($id);
            $id = (int) $site->blog_id;
            $options = $this->createMeta($id);
        } catch (ContainerExceptionInterface $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not get meta for site #%1$d', [$id]),
                0,
                $e,
                $this
            );
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress MissingParamType Missing in PSR-11.
     */
    public function has($id)
    {
        /** @psalm-suppress InvalidCatch PSR-11 exceptions will always implement respective interfaces */
        try {
            $this->getSite($id);
        } catch (NotFoundExceptionInterface $e) {
            return false;
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not check for meta key "%1$s"', [$id]),
                0,
                $e,
                $this
            );
        }

        return true;
    }

    /**
     * Retrieve a site instance for the specified ID.
     *
     * @param int|string $id The ID of the site to retrieve.
     * @return WP_Site The site instance.
     * @psalm-suppress InvalidThrow PSR-11 exceptions always implement respective interfaces
     * @throws NotFoundExceptionInterface If problem retrieving.
     * @throws Exception If problem retrieving.
     * @throws ContainerExceptionInterface If problem retrieving.
     */
    protected function getSite($id): WP_Site
    {
        $site = $this->sitesContainer->get((string) $id);

        return $site;
    }

    /**
     * Creates a container that represents metadata for a specific site.
     *
     * @param int $siteId The ID of the site to get the metadata for.
     *
     * @return MutableContainerInterface The metadata.
     *
     * @throws Exception If problem creating.
     */
    protected function createMeta(int $siteId): MutableContainerInterface
    {
        $factory = $this->optionsFactory;

        if (!is_callable($factory)) {
            throw new Exception(
                $this->__('Could not invoke metadata factory'),
                0,
                null
            );
        }

        $options = $factory($siteId);

        return $options;
    }
}
