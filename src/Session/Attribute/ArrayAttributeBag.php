<?php

declare(strict_types=1);

/*
 * This file is part of Dummy Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/dummy-bundle
 */

namespace Markocupic\DummyBundle\Session\Attribute;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * Provides an array access adapter for a session attribute bag.
 */
class ArrayAttributeBag extends AttributeBag implements \ArrayAccess
{
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function &offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
