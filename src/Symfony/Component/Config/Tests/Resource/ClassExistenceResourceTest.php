<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Tests\Resource;

use Symfony\Component\Config\Resource\ClassExistenceResource;

class ClassExistenceResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $res = new ClassExistenceResource('BarClass');
        $this->assertSame('BarClass', (string) $res);
    }

    public function testGetResource()
    {
        $res = new ClassExistenceResource('BarClass');
        $this->assertSame('BarClass', $res->getResource());
    }

    public function testIsFreshWhenClassDoesNotExist()
    {
        $res = new ClassExistenceResource('Symfony\Component\Config\Tests\Fixtures\BarClass');

        $this->assertTrue($res->isFresh(time()));

        eval(<<<EOF
namespace Symfony\Component\Config\Tests\Fixtures;

class BarClass
{
}
EOF
        );

        $this->assertFalse($res->isFresh(time()));
    }

    public function testIsFreshWhenClassExists()
    {
        $res = new ClassExistenceResource('Symfony\Component\Config\Tests\Resource\ClassExistenceResourceTest');

        $this->assertTrue($res->isFresh(time()));
    }

    public function testExistsKo()
    {
        spl_autoload_register($autoloader = function ($class) use (&$loadedClass) { $loadedClass = $class; });

        try {
            $res = new ClassExistenceResource('MissingFooClass');
            $this->assertTrue($res->isFresh(0));

            $this->assertSame('MissingFooClass', $loadedClass);

            $loadedClass = 123;

            $res = new ClassExistenceResource('MissingFooClass', ClassExistenceResource::EXISTS_KO);

            $this->assertSame(123, $loadedClass);
        } finally {
            spl_autoload_unregister($autoloader);
        }
    }
}
