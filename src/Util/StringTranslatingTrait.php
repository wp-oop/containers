<?php

declare(strict_types=1);

namespace WpOop\Containers\Util;

/**
 * Methods for classes which can translate.
 *
 * @since [*next-version*]
 */
trait StringTranslatingTrait
{

    /**
     * Translates a string, and replaces placeholders.
     *
     * The translation itself is delegated to another method.
     *
     * @param string $string  The format string to translate.
     * @param scalar[]  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     * @return string The translated string.
     *@see sprintf()
     * @see translate()
     */
    protected function __(string $string, array $args = array(), $context = null): string
    {
        $string = $this->translate($string, $context);
        array_unshift($args, $string);

        return call_user_func_array('sprintf', $args);
    }

    /**
     * Translates a string.
     *
     * A no-op implementation.
     *
     * @since [*next-version*]
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $string The string to translate.
     * @param string|null $context The translation context.
     * @return string $context The translated string.
     */
    protected function translate(string $string, $context = null): string
    {
        return $string;
    }
}
