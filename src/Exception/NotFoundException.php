<?php

declare(strict_types=1);

namespace WpOop\Containers\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Basic implementation of container exception.
 *
 * @package WpOop\Containers
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{

    /**
     * @var ContainerInterface|null
     */
    protected $container;
    /**
     * @var string|null
     */
    protected $dataKey;

    /**
     * @param string|null $dataKey The key that is not found.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $previous The inner exception, if any,
     * @param ContainerInterface|null $container The container that caused the exception, if any,
     */
    public function __construct(
        string $dataKey,
        string $message = "",
        int $code = 0,
        Throwable $previous = null,
        ContainerInterface $container = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->container = $container;
        $this->dataKey = $dataKey;
    }
}
