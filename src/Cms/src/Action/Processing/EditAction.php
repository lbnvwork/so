<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

declare(strict_types=1);

namespace Cms\Action\Processing;

use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class EditAction
 *
 * @package Cms\Action\Processing
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::processing/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|Response|HtmlResponse|RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /** @var Processing $item */
        $item = $request->getAttribute(Processing::class);

        if ($request->getMethod() === 'POST') {
            $this->save($request, $item);

            return new RedirectResponse($this->urlHelper->generate('admin.processing.view', ['id' => $item->getId()]));
        }
        ini_set('xdebug.var_display_max_depth', '5');
        $shops = $this->entityManager->getRepository(Shop::class)->findAll();

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'item'  => $item,
                    'shops' => $shops,
                ]
            )
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param Processing $processing
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(ServerRequestInterface $request, Processing $processing)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        $params = $request->getParsedBody();
        $data = json_decode($params['receipt'], true);
        $processing->setOperation($params['operation'])
            ->setStatus($params['status'])
            ->setExternalId($params['external_id'])
            ->setCallbackUrl($params['callback'])
            ->setRawData($params['receipt'])
            ->setSum($data['receipt']['total']);

        if (!$processing->getId()) {
            /** @var Shop $shop */
            $shop = $this->entityManager->getRepository(Shop::class)->findOneBy(['id' => $params['shop']]);
            $processing->setDatetime(new DateTime())
                ->setShop($shop);
            $this->entityManager->persist($processing);
        }
        $this->entityManager->flush();

        $flashMessage->addSuccessMessage('Данные сохранены');
    }
}
