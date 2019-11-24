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
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
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
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * DummyModuleController constructor.
     * @param ContaoFramework $framework
     * @param Connection $connection
     * @param string $projectDir
     * @param Security $security
     * @param RequestStack $requestStack
     * @param ScopeMatcher $scopeMatcher
     */
    public function __construct(ContaoFramework $framework, Connection $connection, string $projectDir, Security $security, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->projectDir = $projectDir;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

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

        // Return empty string, if user is not logged in as a frontend user
        if ($this->isFrontend())
        {
            if (!$this->security->getUser() instanceof FrontendUser)
            {
                return new Response('', Response::HTTP_NO_CONTENT);
            }
        }

        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
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
        // Redirect if the form has been submitted
        if ($request->isMethod('post'))
        {
            if (null !== ($redirectPage = PageModel::findByPk($model->jumpTo)))
            {
                throw new RedirectResponseException($redirectPage->getAbsoluteUrl());
            }
        }

        // Get the logged in user object
        $template->user = 'Please log in to see your username';
        if (($user = $this->security->getUser()) instanceof BackendUser)
        {
            $template->user = 'You are logged in as backend user ' . $user->getUsername() . ' (' . $user->email . ')';
        }

        if (($user = $this->security->getUser()) instanceof FrontendUser)
        {
            $template->user = 'You are logged in as frontend user ' . $user->getUsername() . ' (' . $user->email . ')';
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

        return $template->getResponse();
    }

    /**
     * Identify the Contao scope (TL_MODE) of the current request
     * @return bool
     */
    public function isBackend()
    {
        return $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest());
    }

    /**
     * Identify the Contao scope (TL_MODE) of the current request
     * @return bool
     */
    public function isFrontend()
    {
        return $this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest());
    }
}
