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

namespace Markocupic\DummyBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\Environment;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VuePixabayModuleController.
 */
class VuePixabayModuleController extends AbstractFrontendModuleController
{
    /**
     * Like generate-method in past contao modules.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Get subscribed services.
     */
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices();
    }

    /**
     * Like compile-method in past contao modules.
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
      
        // Load language file
        System::loadLanguageFile('default');

        $template->vueElementId = 'vuePixabayDummyContainer_'.$this->id;

        return $template->getResponse();
    }
}
