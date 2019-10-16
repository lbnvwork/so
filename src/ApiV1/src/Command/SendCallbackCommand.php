<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace ApiV1\Command;

use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaApi;
use ApiV1\Service\Umka;
use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Zend\Json\Json;

/**
 * Class SendCheckCommand
 *
 * @package ApiV1\Command
 */
class SendCallbackCommand extends Command
{
    private $logger;

    private $entityManager;

    private $umka;

    /**
     * @var Normal
     */
    private $normal;

    /**
     * SendCallbackCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param UmkaApi $umka
     * @param Normal $normal
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        UmkaApi $umka,
        Normal $normal
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->umka = $umka;

        parent::__construct();
        $this->normal = $normal;
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('apiv1:send-callback')
            ->setDescription('Отправка чеков на callback URL')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED,
                'ID чека'
            )
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Лимит запросов')
            ->addOption('shop', 's', InputOption::VALUE_REQUIRED, 'ID магазина');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getOption('id');

        /** @var Processing[] $tickets */
        $tickets = [];
        if ($id) {
            $tickets = $this->entityManager->getRepository(Processing::class)->findBy(['id' => $id]);
        } else {
            $shop = null;
            if ($input->getOption('shop')) {
                $shop = $this->entityManager->getRepository(Shop::class)->findOneBy(['id' => $input->getOption('shop')]);
            }

            $tickets = $this->entityManager
                ->getRepository(Processing::class)
                ->getProcessingByStatus(Processing::STATUS_SEND_CLIENT, $input->getOption('limit'), $shop);
        }

        foreach ($tickets as $ticket) {
            $this->logger->addInfo('Отправка чека #'.$ticket->getId());
            if (trim($ticket->getCallbackUrl())) {
                if ($this->sendTicket($ticket)) {
                    $ticket->setStatus(Processing::STATUS_SUCCESS);
                    $this->logger->addWarning('Чек отправлен');
                } else {
                    $this->logger->addWarning('Чек не отправлен');
                }
            } else {
                $this->logger->addWarning('Нет url для отправки');
                $ticket->setStatus(Processing::STATUS_SUCCESS);
            }
        }
        $this->entityManager->flush();

        $this->logger->info('Отправка чеков', ['id' => $id]);
    }

    /**
     * @param Processing $processing
     *
     * @return bool
     * @throws \Exception
     */
    protected function sendTicket(Processing $processing): bool
    {
        $data = $this->normal->report($processing);
        $jsonString = Json::encode($data);
        if ($this->send($processing, $jsonString)) {
            $processing->setError(null);

            return true;
        }

        return false;
    }

    protected function send(Processing $processing, string $jsonString): bool
    {
        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
//        $this->logger->addDebug($processing->getCallbackUrl());

        $url = trim($processing->getCallbackUrl());
        if ($processing->getShop()->getId() === 14 && strpos($url, '/api.php') === 0) {
            $url = 'http://admin.fanatkastraz.ru'.$url;
            $processing->setCallbackUrl($url);
        }

        $this->logger->addDebug('URL: '.trim($processing->getCallbackUrl()));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, trim($processing->getCallbackUrl()));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonString);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: '.\strlen($jsonString),
            ]
        );
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        try {
            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
            $err = curl_error($curl);
            $lastUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
//            var_dump($out, $code);
            curl_close($curl); #Завершаем сеанс cURL
        } catch (\Exception $e) {
            $processing->setError(
                'Данные не отправлены! '.Json::encode(
                    [
                        'response' => mb_strcut($out, 0, 1000),
                        'code'     => $code,
                        'err'      => $err,
                    ]
                )
            );
            $this->logger->addCritical(
                'Данные не отправлены!',
                [
                    $processing->getCallbackUrl(),
                    Json::decode($jsonString, Json::TYPE_ARRAY),
                ]
            );

            return false;
        }

        if ($code === 301) {
            static $countRedirect;
            $countRedirect++;
            if ($countRedirect >= 30) {
                $this->logger->addError('Превышен лимит редиректов');

                return false;
            }
            $tmp = clone $processing;
            $tmp->setCallbackUrl($lastUrl);
            $this->logger->addDebug('Обнаружен редирект на: '.$lastUrl);

            return $this->send($tmp, $jsonString);
        }

        if ($code !== 200) {
            $processing->setError(
                'Ответ сервера не 200! '.Json::encode(
                    [
                        $out,
                        'code'  => $code,
                        'error' => $err,
                    ]
                )
            );
            $this->logger->addWarning(
                'Ответ сервера не 200!',
                [
                    $out,
                    'code'  => $code,
                    'error' => $err,
                ]
            );

            return false;
        }

        try {
            $dataJson = Json::decode($out, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            $processing->setError('Данные не отправлены! Некорректный ответ сервера');
            $this->logger->addCritical(
                'Данные не отправлены! Некорректный ответ сервера',
                [
                    $processing->getCallbackUrl(),
                    $e->getMessage(),
                ]
            );

            return false;
        }

        if (!isset($dataJson['status']) || $dataJson['status'] !== 'success') {
            $processing->setError('В ответе сервера нет отчета о принятии данных');
            $this->logger->addWarning('В ответе сервера нет отчета о принятии данных');
            $this->logger->addDebug($out);

            return false;
        }

        return true;
    }
}
