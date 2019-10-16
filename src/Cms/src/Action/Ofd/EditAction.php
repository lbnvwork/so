<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 29.03.18
 * Time: 20:33
 */

namespace Cms\Action\Ofd;

use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Ofd;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class EditAction
 *
 * @package Cms\Action\Ofd
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::ofd/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|Response|HtmlResponse|static
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        $ofd = $this->entityManager->getRepository(Ofd::class)->findOneBy(['id' => $request->getAttribute('id')]);
        if ($ofd === null) {
            $ofd = new Ofd();
        }

        if ($request->getMethod() === 'DELETE') {
            $this->entityManager->remove($ofd);
            $this->entityManager->flush();

            $flashMessage->addSuccessMessage('Офд удалено');

            return new Response\JsonResponse(['url' => $this->urlHelper->generate('admin.ofd')]);
        }

        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            $ofd->setIsEnabled(isset($params['isEnabled']));

            foreach ($params as $key => $val) {
                if ($key === 'isEnabled') {
                    continue;
                }

                $name = 'set'.ucfirst($key);
                if (method_exists($ofd, $name)) {
                    $ofd->{$name}($val);
                }
            }

            $this->entityManager->persist($ofd);
            $this->entityManager->flush();

            $flashMessage->addSuccessMessage('Данные сохранены');

            return new Response\RedirectResponse($this->urlHelper->generate('admin.ofd.edit', ['id' => $ofd->getId()]));
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['ofd' => $ofd]));
    }
}
