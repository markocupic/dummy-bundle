<?php

declare(strict_types=1);

/*
 * Copyright Marko Cupic <m.cupic@gmx.ch>, 2019
 * @author Marko Cupic
 * @link https://github.com/markocupic/dummy-bundle
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Markocupic\DummyBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\Environment;
use Contao\FormSelectMenu;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\Template;
use Contao\Input;
use Haste\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;

/**
 * Class VuePixabayModuleController
 * @package Markocupic\DummyBundle\Controller\FrontendModule
 */
class VuePixabayModuleController extends AbstractFrontendModuleController
{

    /**
     * Like generate-method in past contao modules
     * @param Request $request
     * @param ModuleModel $model
     * @param string $section
     * @param array|null $classes
     * @param PageModel|null $page
     * @return Response
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Get subscribed services
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        // Do not set $services['contao.framework'] because it is done already in parent::getSubscribedServices()
        // Do not set $services['translator'] because it is done already in parent::getSubscribedServices()

        return $services;
    }

    /**
     * Like compile-method in past contao modules
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var  Environment $environmentAdapter */
        $environmentAdapter = $this->get('contao.framework')->getAdapter(Environment::class);

        // Get translator
        $translator = $this->get('translator');

        // Load language file
        System::loadLanguageFile('modules');
        System::loadLanguageFile('default');

        $template->vueElementId = 'vuePixabayDummyContainer_' . $this->id;


        return $template->getResponse();
    }



}
