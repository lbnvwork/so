<?php

namespace Office\Action\Kkt;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\UploadedFile;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class FilesAction
 *
 * @package Office\Action\Kkt
 */
class FilesAction implements ServerMiddlewareInterface
{
    public const ALLOWED_FORMAT_DOC = [
        'jpg'  => [
            'image/jpeg',
        ],
        'jpeg' => [
            'image/jpeg',
        ],
        'png'  => [
            'image/png',
        ],
        'gif'  => [
            'image/gif',
        ],
        'pdf'  => [
            'application/pdf',
        ],
        'xls'  => [
            'application/vnd.ms-office',
            'application/vnd.ms-excel',
            'application/x-excel',
            'application/msexcel',
            'application/octet-stream',
        ],
        'xlsx' => [
            'application/vnd.ms-office',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream',
        ],
        'doc'  => [
            'application/vnd.ms-office',
            'application/msword',
            'application/octet-stream',
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/octet-stream',
        ],
        'zip'  => [
            'application/zip',
        ],
        'rar'  => [
            'application/x-rar-compressed',
        ],
        'txt'  => [
            'text/plain',
        ],
        'tif'  => [
            'image/tiff',
        ],
        'tiff' => [
            'image/tiff',
        ],
        'bmp'  => [
            'image/bmp',
        ],
        'odt'  => [
            'application/vnd.oasis.opendocument.text',
        ],
    ];

    private $template;

    private $entityManager;

    private $urlHelper;

    /**
     * FilesAction constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     * @param Template\TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper, Template\TemplateRendererInterface $template)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse|Response\RedirectResponse|static
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var Kkt $kkt */
        $kkt = $request->getAttribute(Kkt::class);
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        if ($request->getMethod() === 'POST' && $user->getId() !== User::TEST_USER_ID) {
            $this->saveFile($request, 'rnm', $kkt);
            $this->saveFile($request, 'fiscalResult', $kkt);

            $this->entityManager->flush();

            return new Response\RedirectResponse($this->urlHelper->generate('office.kkt.files', ['id' => $kkt->getId()]));
        }

        return new HtmlResponse($this->template->render('office::kkt/files', ['kkt' => $kkt]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $inputName
     * @param Kkt $kkt
     */
    public function saveFile(ServerRequestInterface $request, string $inputName, Kkt $kkt): void
    {
        $files = $request->getUploadedFiles();
        if (isset($files[$inputName])) {
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $files[$inputName];
            if ($uploadedFile->getError()) {
                return;
            }

            $ext = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
            if (isset(self::ALLOWED_FORMAT_DOC[$ext]) && \in_array($uploadedFile->getClientMediaType(), self::ALLOWED_FORMAT_DOC[$ext], true)) {
                $sitePatch = '/docs/'.$kkt->getId();
                $patch = ROOT_PATH.'data'.$sitePatch;
                if (!is_dir($patch)) {
                    if (!mkdir($patch, 0775, true) && !is_dir($patch)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $patch));
                    }
                }

                $fileName = md5($uploadedFile->getClientFilename().time()).'.'.$ext;
                $uploadedFile->moveTo($patch.'/'.$fileName);
                if ($inputName === 'rnm') {
                    $kkt->setRnmFile($sitePatch.'/'.$fileName);
                } else {
                    $kkt->setFiscalResultFile($sitePatch.'/'.$fileName);
                }

                $flashMessage->addSuccessMessage('Файл загружен');
            } else {
                $flashMessage->addErrorMessage('Не разрешенный формат файла');
            }
        }
    }
}
