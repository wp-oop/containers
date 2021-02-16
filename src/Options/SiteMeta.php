<?php

declare(strict_types=1);

namespace WpOop\Containers\Options;

use Dhii\Collection\MutableContainerInterface;
use Exception;
use RuntimeException;
use UnexpectedValueException;
use WpOop\Containers\Exception\ContainerException;
use WpOop\Containers\Exception\NotFoundException;
use WpOop\Containers\Util\StringTranslatingTrait;

/**
 * Metadata for a particular site.
 *
 * @package WpOop\Containers\
 */
class SiteMeta implements MutableContainerInterface
{
    use StringTranslatingTrait;

    /**
     * @var int
     */
    protected $siteId;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @param int $siteId ID of the site.
     * @param mixed $default The value that, if returned by WP, will indicate that the key is not found.
     */
    public function __construct(int $siteId, $default)
    {
        $this->siteId = $siteId;
        $this->default = $default;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MissingParamType Missing in PSR-11.
     */
    public function get($id)
    {
        try {
            return $this->getMeta($id);
        } catch (UnexpectedValueException $e) {
            throw new NotFoundException(
                $id,
                $this->__('Meta key "%1$s" not found', [$id]),
                0,
                $e,
                $this
            );
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not get value for meta key "%1$s', [$id]),
                0,
                $e,
                $this
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress MissingParamType Missing in PSR-11.
     */
    public function has($id)
    {
        try {
            $this->getMeta($id);

            return true;
        } catch (UnexpectedValueException $e) {
            return false;
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not check for meta key "%1$s"', [$id]),
                0,
                $e,
                $this
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): void
    {
        try {
            $this->setMeta($key, $value);
        } catch (Exception $e) {
            throw new ContainerException(
                $this->__('Could not set value for meta key "%1$s"', [$key]),
                0,
                $e,
                $this
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function unset(string $key): void
    {
        $siteId = $this->siteId;
        $result = delete_network_option($siteId, $key);

        if ($result === false) {
            throw new ContainerException(
                $this->__('Could not delete meta key "%1$s"', [$key]),
                0,
                null,
                $this
            );
        }
    }

    /**
     * Retrieves a meta value.
     *
     * @param string $name The name of the meta key to retrieve.
     *
     * @return mixed The meta value.
     *
     * @throws UnexpectedValueException If the meta value matches the configured default.
     * @throws RuntimeException If problem retrieving.
     * @throws Exception If problem running.
     */
    protected function getMeta(string $name)
    {
        $siteId = $this->siteId;
        $default = $this->default;
        $value = get_network_option($siteId, $name, $default);

        if ($value === $default) {
            throw new UnexpectedValueException(
                $this->__(
                    'Meta key "%1$s" for blog #%2$d does not exist',
                    [$name, $siteId]
                )
            );
        }

        return $value;
    }

    /**
     * Assigns a value to a meta key.
     *
     * @param string $name The name of the meta key to set the value for.
     * @param mixed $value The value to set.
     *
     * @throws UnexpectedValueException If new meta value does not match what was being set.
     * @throws RuntimeException If problem setting.
     * @throws Exception If problem running.
     */
    protected function setMeta(string $name, $value): void
    {
        $siteId = $this->siteId;

        $isSuccessful = update_network_option($siteId, $name, $value);
        if (!$isSuccessful) {
            $newValue = $this->getMeta($name);
            $isSuccessful = $value === $newValue;
        }

        /** @psalm-suppress PossiblyUndefinedVariable If unsuccessful, $newValue will be defined */
        if (!$isSuccessful) {
            throw new UnexpectedValueException(
                $this->__(
                    'New meta value did not match the intended value: "%1$s" VS "%2$s"',
                    [
                        print_r($value, true),
                        print_r($newValue, true),
                    ]
                )
            );
        }
    }
}
