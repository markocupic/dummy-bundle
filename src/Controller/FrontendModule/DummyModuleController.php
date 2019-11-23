<?php

namespace Markocupic\DummyBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class DummyModuleController
 * @package Markocupic\DummyBundle\Controller\FrontendModule
 * @FrontendModule(category="miscellaneous", type="dummy_module")
 */
class DummyModuleController extends AbstractFrontendModuleController
{
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
     * DummyModuleController constructor.
     * @param ContaoFramework $framework
     * @param Connection $connection
     * @param string $projectDir
     */
    public function __construct(ContaoFramework $framework, Connection $connection, string $projectDir)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->projectDir = $projectDir;
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        if ($request->isMethod('post'))
        {
            if (null !== ($redirectPage = PageModel::findByPk($model->jumpTo)))
            {
                throw new RedirectResponseException($redirectPage->getAbsoluteUrl());
            }
        }

        $template->projectDir = 'The projectDir is located in "' . $this->projectDir .'".';
        $template->action = $request->getUri();

        return $template->getResponse();
    }
}
