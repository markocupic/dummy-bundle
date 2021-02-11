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

namespace Markocupic\DummyBundle;

use Markocupic\DummyBundle\DependencyInjection\Compiler\AddSessionBagsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MarkocupicDummyBundle.
 */
class MarkocupicDummyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddSessionBagsPass());
    }
}
