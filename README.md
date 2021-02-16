# Dhii - WP Containers

![Continuous Integration](https://github.com/wp-oop/containers/workflows/Continuous%20Integration/badge.svg)

## Details
[PSR-11][] container implementations that wrap some WP features, for convenience and interoperability.

## Features
### Retrieve sites by key

```php
use WpOop\Containers\Sites;
use WP_Site;

$sites = new Sites();
$site2 = $sites->get(2);
assert($site2 instanceof WP_Site);
```

### Retrieve site options by key

```php
use WpOop\Containers\Options\BlogOptions;
use WpOop\Containers\Options\BlogOptionsContainer;
use WpOop\Containers\Sites;

// Set up sites container (see other example)
// ...
assert($sites instanceof WP_Site);

// Definition
$optionsContainer = new BlogOptionsContainer(
    function ($id) {
        return new BlogOptions($id, uniqid('default-option-value'));
    },
    $sites
);

// Usage
$blog3Options = $optionsContainer->get(3);
$myOption = $blog3Options->get('my_option');
```
    
### Retrieve site meta by key

```php
use WpOop\Containers\Options\SiteMeta;
use WpOop\Containers\Options\SiteMetaContainer;
use WpOop\Containers\Sites;

// Set up sites container (see other example)
// ...
assert($sites instanceof WP_Site);

// Definition
$metaContainer = new SiteMetaContainer(
    function ($id) {
        return new SiteMeta($id, uniqid('default-meta-value'));
    },
    $sites
);

// Usage
$blog4Meta = $metaContainer->get(4);
$myMeta = $blog4Meta->get('my_meta');
```
    
### Structured error handling

```php
use WpOop\Containers\Options\BlogOptions;
use Psr\Container\NotFoundExceptionInterface;
use WpOop\Containers\Exception\NotFoundException;

// Set up options (see previous examples)
// ...
assert($options instanceof BlogOptions);

try {
    $options->set('other_option', 'My Value');
    $value = $options->get('my_option');
} catch (NotFoundExceptionInterface $e) {
    assert($e instanceof NotFoundException);
    echo sprintf('Option "%1$s" does not exist', $e->getDataKey());
    assert($e->getContainer() === $options);
}
```

This solves the problem of inconsistent behaviour of native WordPress option-related funtions:

* retrieved options returned `false` for both a `false` value and when not found, making them hard to distinguish;
* setting an option returned `false` for both failure, an when the value is the same as the curent value, often
resulting in a false error.

This is no longer the case with the above containers: option operations succeed or correctly fail
by throwing PSR-11 exceptions. Furthermore, the original behaviour of these exceptions has been
extended to allow retrieval of the key that was not found (when applicable) and the container that failed
the operation. This is optional, however, and depending simply on the PSR-11 exceptions will work as expected.

The `set()`, `has()`, and `unset()` also throw [`ContainerExceptionInterface`][] on failure.

### Wraps WP

The containers do not re-create the functionality to go around WordPress. Instead, they wrap native WordPress functionality,
so you can be sure that everything is done in the same way, all the hooks, such as `option_*` or `pre_update_option_*`, still work.

[Dhii]: https://github.com/Dhii/dhii
[PSR-11]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md
[`ContainerExceptionInterface`]: https://github.com/Dhii/data-container-interface/blob/develop/src/Exception/ContainerExceptionInterface.php#L14
