<?php

namespace WpOop\Containers\FuncTest\Options;

use Brain\Monkey\Functions;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use WpOop\Containers\Options\SiteMeta as TestSubject;
use WpOop\Containers\TestHelpers\ComponentMockeryTrait;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

class SiteMetaTest extends TestCase
{

    use ComponentMockeryTrait;

    protected function start()
    {
        setUp();
    }

    protected function stop()
    {
        tearDown();
    }

    /**
     * Creates a new instance of the test subject.
     *
     * @param array $dependencies A list of constructor args.
     * @param array|null $methods The names of methods to mock in the subject.
     * @return MockObject|TestSubject The new instance.
     * @throws Exception If problem creating.
     */
    protected function createSubject(array $dependencies, array $methods = null)
    {
        return $this->createMockBuilder(TestSubject::class, $methods, $dependencies)
            ->getMock();
    }

    /**
     * Tests whether the container correctly determines having an item.
     *
     * @throws Exception If problem testing.
     */
    public function testHasTrue()
    {
        $this->start();

        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $optionValue = uniqid('option-value');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnGetNetworkOption = Functions\expect('get_network_option')
                ->times(1)
                ->with($siteId, $optionName, $default)
                ->andReturn($optionValue);
        }

        {
            $result = $subject->has($optionName);
        }

        {
            $this->assertTrue($result, 'Incorrectly determined not having');
        }

        $this->stop();
    }

    /**
     * Tests whether the container correctly determines not having an item.
     *
     * @throws Exception If problem testing.
     */
    public function testHasFalse()
    {
        $this->start();

        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default-value');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnGetNetworkOption = Functions\expect('get_network_option')
                ->times(1)
                ->with($siteId, $optionName, $default)
                ->andReturn($default);
        }

        {
            $result = $subject->has($optionName);
        }

        {
            $this->assertFalse($result, 'Incorrectly determined having');
        }

        $this->stop();
    }

    /**
     * Provides sets of values for site meta.
     *
     * @return array[] The values.
     */
    public function optionValuesProvider()
    {
        $array = $this->createArray(rand(1, 9), function (int $index) {
            return uniqid(sprintf('element%1$d', $index));
        });
        $object = (object) $this->createArray(
            rand(1, 9),
            function (int $index) {
                return uniqid(sprintf('element-%1$d-', $index));
            },
            function (int $index) {
                return uniqid(sprintf('key-%1$d-', $index));
            }
        );
        return [
            [uniqid('option-value')],
            [rand(0, 99)],
            [$array],
            [$object]
        ];
    }

    /**
     * Tests that the subject will correctly return the existing values.
     *
     * @dataProvider optionValuesProvider
     *
     * @param mixed $value The value to test for.
     *
     * @throws Exception If problem testing.
     */
    public function testGet($optionValue)
    {
        $this->start();

        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnGetNetworkOption = Functions\expect('get_network_option')
                ->times(1)
                ->with($siteId, $optionName, $default)
                ->andReturn($optionValue);
        }

        {
            $result = $subject->get($optionName);
        }

        {
            $this->assertEquals($optionValue, $result, 'Incorrectly retrieved result');
        }

        $this->stop();
    }

    /**
     * Tests that the subject throws correctly when trying to get a non-existing key.
     *
     * @throws Exception If problem testing.
     */
    public function testGetNotFound()
    {
        $this->start();

        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $optionValue = uniqid('option-value');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnGetNetworkOption = Functions\expect('get_network_option')
                ->times(1)
                ->with($siteId, $optionName, $default)
                ->andReturn($default);
            $this->expectException(NotFoundExceptionInterface::class);
        }

        {
            $result = $subject->get($optionName);
        }

        {
            $this->assertEquals($optionValue, $result, 'Incorrectly retrieved result');
        }

        $this->stop();
    }

    /**
     * Tests that values can be set correctly.
     *
     * @dataProvider optionValuesProvider
     * @doesNotPerformAssertions
     *
     * @param mixed $optionValue The option value to test.
     *
     * @throws Exception If problem testing.
     */
    public function testSet($optionValue)
    {
        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnUpdateNetworkOption = Functions\expect('update_network_option')
                ->times(1)
                ->with($siteId, $optionName, $optionValue)
                ->andReturn(true);
        }

        {
            $subject->set($optionName, $optionValue);
        }

        {
            // No exception means success
        }
    }

    /**
     * Tests that values can be set correctly when the set value is the same as already existing.
     *
     * @dataProvider optionValuesProvider
     * @doesNotPerformAssertions
     *
     * @param mixed $optionValue The option value to test.
     *
     * @throws Exception If problem testing.
     */
    public function testSetSame($optionValue)
    {
        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnUpdateNetworkOption = Functions\expect('update_network_option')
                ->times(1)
                ->with($siteId, $optionName, $optionValue)
                ->andReturn(false);
            $fnGetNetworkOption = Functions\expect('get_network_option')
                ->times(1)
                ->with($siteId, $optionName, $default)
                ->andReturn($optionValue);
        }

        {
            $subject->set($optionName, $optionValue);
        }

        {
            // No exception means success
        }
    }

    /**
     * Tests that the correct exception is thrown when a value cannot be set.
     *
     * @throws Exception If problem testing.
     */
    public function testSetFailure()
    {
        {
            $siteId = rand(1, 99);
            $optionName = uniqid('option-name');
            $optionValue = uniqid('option-value');
            $default = uniqid('default');
            $subject = $this->createSubject(
                [$siteId, $default],
                null
            );
            $fnUpdateNetworkOption = Functions\expect('update_network_option')
                ->times(1)
                ->with($siteId, $optionName, $optionValue)
                ->andReturn(false);
            $fnGetNetworkOption = Functions\expect('get_network_option')
                ->times(1)
                ->with($siteId, $optionName, $default)
                ->andReturn(uniqid('different-value'));
            $this->expectException(ContainerExceptionInterface::class);
        }

        {
            $subject->set($optionName, $optionValue);
        }

        {
            // No exception means failure
        }
    }

    /**
     * Tests whether subject correctly deletes an existing key.
     *
     * @doesNotPerformAssertions
     *
     * @throws Exception If problem testing.
     */
    public function testUnset()
    {
        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $subject = $this->createSubject(
                [$blogId, uniqid('default-value')],
                null
            );
            $fnDeleteNetworkOption = Functions\expect('delete_network_option')
                ->times(1)
                ->with($blogId, $optionName)
                ->andReturn(true);
        }

        {
            $subject->unset($optionName);
        }

        {
            // No exception means success
        }
    }

    /**
     * Tests that the subject correctly fails when unable to delete a key.
     *
     * @throws Exception If problem testing.
     */
    public function testUnsetFailure()
    {
        $this->start();

        {
            $blogId = rand(1, 99);
            $optionName = uniqid('option-name');
            $subject = $this->createSubject(
                [$blogId, uniqid('default-value')],
                null
            );
            $fnDeleteNetworkOption = Functions\expect('delete_network_option')
                ->times(1)
                ->with($blogId, $optionName)
                ->andReturn(false);
        }

        {
            $this->expectException(ContainerExceptionInterface::class);
            $subject->unset($optionName);
        }

        {
            // Exception means success
        }

        $this->stop();
    }
}
