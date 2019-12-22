<?php
/*
 * Copyright Marko Cupic <m.cupic@gmx.ch>, 2019
 * @author Marko Cupic
 * @link https://github.com/markocupic/dummy-bundle
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Markocupic\DummyBundle\Controller\FrontendModule;

use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
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
     * ! This method is optional and can be used, if the response should contain an empty string only
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

        // $services['contao.framework'] is loaded in parent::getSubscribedServices()
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
        // Display empty string if frontend user is not logged in
        if (!$this->get('security.helper')->getUser() instanceof FrontendUser)
        {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        // Generate text field
        $opt = [
            'id'        => 'myTextField',
            'name'      => 'myTextField',
            'label'     => 'My text field',
            'mandatory' => true,
        ];

        /** @var  FormTextField $widget */
        $widget = $this->get('contao.framework')->createInstance(FormTextField::class, [$opt]);

        // Preset value
        if (!$request->isMethod('post') && $request->request->get($widget->name) == '')
        {
            $widget->value = 'Holy moly, please write something in there!';
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
        $template->userText = 'Please log in to see your username';

        if (($user = $this->get('security.helper')->getUser()) instanceof FrontendUser)
        {
            $template->userText = 'You are logged in as frontend user ' . $user->getUsername() . ' (' . $user->email . ')';
        }

        // Get the contao scope (TL_MODE will be deprecated in future releases)
        $scope = 'No scope!';
        $scope = $this->isFrontend() ? 'The scope of the current request is Frontend.' : $scope;
        $scope = $this->isBackend() ? 'The scope of the current request is Backend.' : $scope;
        $template->scope = $scope;

        // Get the page alias
        $template->pageAlias = $this->page->alias;
        $template->projectDir = 'The projectDir is located in "' . $this->projectDir . '".';
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
    public function isBackend()
    {
        return $this->get('request_stack')->getCurrentRequest() !== null ? $this->get('contao.routing.scope_matcher')->isBackendRequest($this->get('request_stack')->getCurrentRequest()) : false;
    }

    /**
     * Identify the Contao scope (TL_MODE) of the current request
     * @return bool
     */
    public function isFrontend()
    {
        return $this->get('request_stack')->getCurrentRequest() !== null ? $this->get('contao.routing.scope_matcher')->isFrontendRequest($this->get('request_stack')->getCurrentRequest()) : false;
    }
}
