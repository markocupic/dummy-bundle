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

use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Date;
use Contao\FormTextField;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;

/**
 * Class DummyModuleController
 * @package Markocupic\DummyBundle\Controller\FrontendModule
 * @FrontendModule(category="miscellaneous", type="dummy_module")
 */
class DummyModuleController extends AbstractFrontendModuleController
{

    /**
     * @var PageModel
     */
    private $page;

    /**
     * @var string
     */
    private $projectDir;

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
        $this->page = $page;

        // Get rootDir
        $this->projectDir = System::getContainer()->getParameter('kernel.project_dir');

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
        $services['security.helper'] = Security::class;
        $services['request_stack'] = RequestStack::class;
        $services['contao.routing.scope_matcher'] = ScopeMatcher::class;

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
        // Load language file
        System::loadLanguageFile('modules');
        System::loadLanguageFile('default');

        // Get translator
        $translator = $this->get('translator');

        // Generate text field
        $opt = [
            'id'        => 'myTextField',
            'name'      => 'myTextField',
            'label'     => $translator->trans('MSC.dummy_module_text_field_lbl.0', [], 'contao_default'),
            'mandatory' => true,
        ];

        /** @var  FormTextField $widget */
        $widget = $this->get('contao.framework')->createInstance(FormTextField::class, [$opt]);

        // Preset value
        if (!$request->isMethod('post') && $request->request->get($widget->name) == '')
        {
            $widget->value = $translator->trans('MSC.dummy_module_text_1', [], 'contao_default');
        }

        // Redirect if the form has been submitted
        if ($request->isMethod('post') && $request->request->get($widget->name) !== '')
        {
            $widget->validate();
            if (!$widget->hasErrors())
            {
                if (null !== ($redirectPage = PageModel::findByPk($model->jumpTo)))
                {
                    throw new RedirectResponseException($redirectPage->getAbsoluteUrl());
                }
            }
        }

        // Parse form field
        $template->textField = $widget->parse();

        // Get the logged in user object
        $template->userText = $translator->trans('MSC.dummy_module_not_logged_in_text', [], 'contao_default');

        /** @var FrontendUser $user */
        $user = $this->get('security.helper')->getUser();
        if ($user instanceof FrontendUser)
        {
            $template->feUserLoggedIn = true;
            $template->userText = $translator->trans('MSC.dummy_module_logged_in_as_fe_user_text', [implode(' ', [$user->firstname, $user->lastname]), $user->email], 'contao_default');
        }

        // Get the contao scope (TL_MODE will be deprecated in future releases)
        $scope = $translator->trans('MSC.dummy_module_no_scope_text', [], 'contao_default');
        $scope = $this->isFrontend() ? $translator->trans('MSC.dummy_module_scope_frontend_text', [], 'contao_default') : $scope;
        $template->scope = $scope;

        // Get the page alias
        $template->pageAlias = $this->page->alias;

        // Project dir aka TL_ROOT
        $template->projectDir = $translator->trans('MSC.dummy_module_project_dir_location_text', [$this->projectDir], 'contao_default');

        // Get uri
        $template->action = $request->getUri();

        /** @var Date $dateAdapter */
        $dateAdapter = $this->get('contao.framework')->getAdapter(Date::class);

        /** @var Config $configAdapter */
        $configAdapter = $this->get('contao.framework')->getAdapter(Config::class);

        // Get the current date
        $template->date = $dateAdapter->parse($configAdapter->get('dateFormat'));

        return $template->getResponse();
    }

    /**
     * Identify the Contao scope (TL_MODE) of the current request
     * @return bool
     */
    protected function isFrontend(): bool
    {
        if ($this->get('contao.framework')->isInitialized() && $this->get('request_stack')->getMasterRequest() && !$this->isBackend())
        {
            return true;
        }
        return false;
    }

    /**
     * Identify the Contao scope (TL_MODE) of the current request
     * @return bool
     */
    protected function isBackend(): bool
    {
        if ($this->get('contao.framework')->isInitialized())
        {
            if ($this->get('request_stack') !== null)
            {
                if ($this->get('request_stack')->getMasterRequest() !== null)
                {
                    return $this->get('contao.routing.scope_matcher')->isBackendRequest($this->get('request_stack')->getMasterRequest());
                }
            }
        }
        return false;
    }
}
