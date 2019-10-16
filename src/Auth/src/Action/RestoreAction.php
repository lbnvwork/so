<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 16:42
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Auth\Service\SendMail;
use Auth\UserRepository\Database;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Class RestoreAction
 *
 * @package Auth\Action
 */
class RestoreAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $database;

    private $sendMail;

    private $urlHelper;

    /**
     * ConfirmAction constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, Database $database, SendMail $sendMail, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->database = $database;
        $this->sendMail = $sendMail;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $hash = $request->getAttribute('hash');
        if ($hash !== null) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['hashKey' => $hash]);
            if ($user !== null) {
                $user->setHashKey(null);

                $pass = Database::generateStrongPassword();
                $user->setNewPassword($pass)
                    ->setIsConfirmed(true);

                $this->entityManager->flush();

                $this->sendMail->sendNewPassword($user, $pass);

                $flashMessage->addSuccessMessage('На Ваш e-mail выслан новый пароль');
            } else {
                $flashMessage->addErrorMessage('Неверный код');
            }
        }

        return new RedirectResponse($this->urlHelper->generate('login'));
    }
}
