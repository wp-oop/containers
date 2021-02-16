<?php

declare(strict_types=1);

namespace WpOop\Containers;

use Dhii\Collection\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use WP_Site;
use WpOop\Containers\Exception\NotFoundException;
use WpOop\Containers\Util\StringTranslatingTrait;

/**
 * Allows retrieval of WP site objects by ID.
 *
 * @package WpOop\Containers
 */
class Sites implements ContainerInterface
{
    use StringTranslatingTrait;

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MissingParamType Missing in PSR-11.
     *
     * @return WP_Site The site for the specified ID.
     */
    public function get($id)
    {
        $site = get_site($id);

        if (!$site) {
            throw new NotFoundException(
                (string) $id,
                $this->__('No site found for ID "%1$d"', [$id]),
                0,
                null,
                $this
            );
        }

        return $site;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MissingParamType Missing in PSR-11.
     */
    public function has($id)
    {
        /** @psalm-suppress InvalidCatch */
        try {
            $site = $this->get($id);
        } catch (NotFoundExceptionInterface $e) {
            return false;
        }

        return true;
    }
}
