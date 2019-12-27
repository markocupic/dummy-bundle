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
use Contao\FormTextArea;
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
 * Class VueDummyModuleController
 * @package Markocupic\DummyBundle\Controller\FrontendModule
 * @FrontendModule(category="miscellaneous", type="vue_dummy_module")
 */
class VueDummyModuleController extends AbstractFrontendModuleController
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

        // Handle ajax requests
        if ($environmentAdapter->get('isAjaxRequest'))
        {
            $this->handleAjax();
            exit();
        }

        // Load language file
        System::loadLanguageFile('modules');
        System::loadLanguageFile('default');

        // Get translator
        $translator = $this->get('translator');

        // Generate text field
        $opt = [
            'id'        => 'myTextarea',
            'name'      => 'myTextField',
            'label'     => $translator->trans('MSC.dummy_module_text_field_lbl.0', [], 'contao_default'),
            'mandatory' => true,
        ];

        /** @var  FormTextField $widget */
        $widget = $this->get('contao.framework')->createInstance(FormTextArea::class, [$opt]);
        $template->textarea = $widget->parse();

        return $template->getResponse();
    }

    protected function handleAjax()
    {
        $arrJson = ['status' => 'error'];

        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        if ($this->validateCsrfToken((string)$inputAdapter->post('REQUEST_TOKEN')))
        {
            $arrJson = array(
                'status' => 'success',
                'data'   => array('post' => $_POST),
            );
        }
        else
        {
            $arrJson = ['status' => 'invalid csrf token'];
        }

        $json = new JsonResponse();
        $json->setData($arrJson);
        $json->send();
    }

    /**
     * @param string $strToken
     * @return bool
     */
    public function validateCsrfToken(string $strToken = ''): bool
    {
        $container = System::getContainer();

        return $container->get('contao.csrf.token_manager')->isTokenValid(new CsrfToken($container->getParameter('contao.csrf_token_name'), $strToken));
    }
}
