<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 28.03.18
 * Time: 12:01
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Auth\UserRepository\Database;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Class UserProfileAction
 *
 * @package Auth\Action
 */
class UserProfileAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'auth::user/profile';
    /**
     * Описание полей формы
     */
    private const FIELDS = [
        'lastName'   => ['title' => 'Фамилия'],
        'firstName'  => ['title' => 'Имя'],
        'middleName' => ['title' => 'Отчество'],
//        'email'      => ['title' => 'Email'],
        'phone'      => ['title' => 'Телефон'],
    ];

    private $urlHelper;

    private $template;

    private $entityManager;

    private $database;

    /**
     * UserProfileAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Database $database
     * @param UrlHelper $urlHelper
     * @param Template\TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, Database $database, UrlHelper $urlHelper, Template\TemplateRendererInterface $template)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->database = $database;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse|RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {

        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();

            $allowedParams = array_intersect_key($params, self::FIELDS);
            $messages = [];

            foreach (self::FIELDS as $key => $field) {
                if (empty($allowedParams[$key])) {
                    $messages[] = 'Не заполнено поле `'.$field['title'].'`';
                }
            }
            if (\count($messages)) {
                foreach ($messages as $message) {
                    $flashMessage->addErrorMessage($message);
                }
            } else {
                foreach ($allowedParams as $key => $value) {
                    $method = 'set'.ucfirst($key);
                    $user->{$method}($value);
                }

                if (!$user->getIsConfirmed()) {
                    if (!empty($params['newPassword']) && $params['newPassword'] === $params['confirmPassword']) {
                        $user->setNewPassword($params['newPassword'])
                            ->setHashKey(null)
                            ->setIsConfirmed(true);
                        $session->set('u_first_edit', true);
                    } else {
                        $flashMessage->addErrorMessage('Не корректный ввод паролей');
                    }
                }

                $this->entityManager->flush();
                $flashMessage->addSuccessMessage('Данные обновлены');

                return new RedirectResponse($this->urlHelper->generate('user.profile'));
            }
        }
        $uFirstEdit = false;
        if ($session->has('u_first_edit')) {
            $uFirstEdit = true;
            $session->unset('u_first_edit');
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['uFirstEdit' => $uFirstEdit]));
    }
}
