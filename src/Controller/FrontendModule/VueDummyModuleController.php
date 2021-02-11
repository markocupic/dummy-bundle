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

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Class VueDummyModuleController.
 *
 * @FrontendModule(category="miscellaneous", type="vue_dummy_module")
 */
class VueDummyModuleController extends AbstractFrontendModuleController
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var ModuleModel
     */
    private $model;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Like generate-method in past contao modules.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        $this->model = $model;

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

    public function validateCsrfToken(string $strToken = ''): bool
    {
        $container = System::getContainer();

        return $container->get('contao.csrf.token_manager')->isTokenValid(new CsrfToken($container->getParameter('contao.csrf_token_name'), $strToken));
    }

    /**
     * Like compile-method in past contao modules.
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var Environment $environmentAdapter */
        $environmentAdapter = $this->get('contao.framework')->getAdapter(Environment::class);

        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        // Get extra session bag $_SESSION['_markocupic_dummy_bundle_attributes']
        $session = $this->session->getBag('markocupic_dummy_bundle');

        // Do some session stuff here
        $session->set('alfa','beta');

        // Handle ajax requests
        if ($environmentAdapter->get('isAjaxRequest')) {
            if ($inputAdapter->post('action') && method_exists(self::class, $inputAdapter->post('action'))) {
                /** @var JsonResponse $response */
                $response = $this->{$inputAdapter->post('action')}();
                $response->send();
                exit();
            }

            throw new \BadMethodCallException(sprintf('Could not find %s::%s', self::class, $inputAdapter->post('action')));
        }

        return $template->getResponse();
    }

    protected function loadImages(): JsonResponse
    {
        $arrJson = ['status' => 'error'];

        $arrPictures = [];

        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->get('contao.framework')->getAdapter(StringUtil::class);

        /** @var Controller $controllerAdapter */
        $controllerAdapter = $this->get('contao.framework')->getAdapter(Controller::class);

        $multiSRC = $stringUtilAdapter->deserialize($this->model->multiSRC, true);
        $imgSize = $stringUtilAdapter->deserialize($this->model->imgSize, true);

        if (!empty($multiSRC) && isset($imgSize[2])) {
            $pictureSize = $imgSize[2];

            if (null !== ($objFiles = FilesModel::findMultipleByUuids($multiSRC))) {
                while ($objFiles->next()) {
                    $insertTag = sprintf('{{picture::%s?size=%s&template=picture_default}}', $objFiles->id, $pictureSize);

                    $strMarkup = $controllerAdapter->replaceInsertTags($insertTag);

                    if (!empty($strMarkup)) {
                        $arrPictures[] = base64_encode($controllerAdapter->replaceInsertTags($insertTag));
                    }
                }
            }
        }

        if ($this->validateCsrfToken((string) $inputAdapter->post('REQUEST_TOKEN'))) {
            $arrJson = [
                'status' => 'success',
                'data' => ['images' => $arrPictures],
            ];
        } else {
            $arrJson = ['status' => 'invalid csrf token'];
        }

        $response = new JsonResponse();
        $response->setData($arrJson);

        return $response;
    }
}
