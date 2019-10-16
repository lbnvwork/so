<?php
declare(strict_types=1);

namespace Cms\Action\File;

use App\Entity\File;
use App\Entity\FileCategory;
use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Office\Action\Kkt\FilesAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\UploadedFile;

/**
 * Class EditAction
 *
 * @package Cms\Action\File
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::file/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $id = $request->getAttribute('id');

        /** @var File $file */
        $file = null;
        if ($id > 0) {
            $file = $this->entityManager->getRepository(File::class)->findOneBy(['id' => $id]);
        } else {
            $file = new File();
        }

        if ($file === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $this->saveFile($request, $file);

            return new Response\RedirectResponse($this->urlHelper->generate('admin.file.edit', ['id' => $file->getId()]));
        }

        $categories = $this->entityManager->getRepository(FileCategory::class)->findAll();

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'file'       => $file,
                    'categories' => $categories,
                ]
            )
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param File $file
     */
    public function saveFile(ServerRequestInterface $request, File $file): void
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        $files = $request->getUploadedFiles();
        if (isset($files['file'])) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $files['file'];
            if ($uploadedFile->getError()) {
                return;
            }

            $ext = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
            if (isset(FilesAction::ALLOWED_FORMAT_DOC[$ext]) && \in_array($uploadedFile->getClientMediaType(), FilesAction::ALLOWED_FORMAT_DOC[$ext], true)) {
                $sitePatch = '/upload/content/'.$file->getCategoryId();
                $patch = ROOT_PATH.'data'.$sitePatch;
                if (!is_dir($patch)) {
                    if (!mkdir($patch, 0775, true) && !is_dir($patch)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $patch));
                    }
                }

                $fileName = md5($uploadedFile->getClientFilename().time()).'.'.$ext;
                $uploadedFile->moveTo($patch.'/'.$fileName);
                $file->setFilename($fileName);

                $flashMessage->addSuccessMessage('Файл загружен');
            } else {
                $flashMessage->addErrorMessage('Не разрешенный формат файла');
            }
        }
    }
}
