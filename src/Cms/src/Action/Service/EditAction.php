<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Cms\Action\Service;

use App\Entity\Service;
use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class EditAction
 *
 * @package Cms\Action
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::service/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse|static
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $id = $request->getAttribute('id');
        $service = null;
        if (empty($id)) {
            $service = new Service();
        } else {
            $service = $this->entityManager->find(Service::class, $id);
        }

        if ($service === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            return $this->saveService($request, $service);
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['service' => $service]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param Service $service
     *
     * @return Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveService(ServerRequestInterface $request, Service $service)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $params = $request->getParsedBody();

        $service->setName($params['name']);
        $service->setMeasure($params['measure']);
        $service->setPrice($params['price']);
        $service->setDefaultValue($params['defaultValue']);
        $this->entityManager->persist($service);
        $this->entityManager->flush();

        $flashMessage->addSuccessMessage('Данные сохранены');

        return new Response\RedirectResponse($this->urlHelper->generate('admin.service.edit', ['id' => $service->getId()]));
    }
}
