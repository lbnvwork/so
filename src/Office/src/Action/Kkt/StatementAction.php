<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 14.01.19
 * Time: 12:42
 */

namespace Office\Action\Kkt;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Expressive\Authentication\UserInterface;
use Doctrine\ORM\EntityManager;
use App\Service\SpreadsheetCreator\SpreadsheetCreatorService;
use App\Service\SpreadsheetCreator\SprValueString;
use App\Service\SpreadsheetCreator\SprValueBuilder;
use Office\Entity\Kkt;

/**
 * Class StatementAction
 *
 * @package Office\Action\Kkt
 */
class StatementAction implements ServerMiddlewareInterface
{
    private $router;

    private $template;

    private $entityManager;

    private $spreadsheetCreator;

    /**
     * StatementAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Router\RouterInterface $router
     * @param Template\TemplateRendererInterface $template
     * @param SpreadsheetCreatorService $spreadsheetCreator
     */
    public function __construct(
        EntityManager $entityManager,
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template,
        SpreadsheetCreatorService $spreadsheetCreator
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->template = $template;
        $this->spreadsheetCreator = $spreadsheetCreator;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        $kktId = $request->getAttribute('id');

        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(
            [
                'id'        => $kktId,
                'isDeleted' => false
            ]
        );
        //begin проверка на существование ККТ
        if (empty($kkt)) {
            return (new Response())->withStatus(404);
        }
        //end проверка на существование ККТ

        //begin проверка на существование компании в списке принадлежащих пользователю
        $userCompanies = $user->getCompany();
        $company = $kkt->getShop()->getCompany();
        $companyExists = false;
        foreach ($userCompanies as $userCompany) {
            if (!$userCompany->getIsDeleted() && $userCompany->getId() === $company->getId()) {
                $companyExists = true;
                break;
            }
        }

        if (!$companyExists) {
            return (new Response())->withStatus(404);
        }
        //end проверка на существование компании в списке принадлежащих пользователю
        $sprCreatorData = [];
        //ОГРН
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('compOgrn')
            ->setValue($company->getOgrn())
            ->setPosition(
                [
                    new SprValueString(1, 40, 84)
                ]
            );
        //ИНН
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('compInn')
            ->setValue($company->getInn())
            ->setPosition(
                [
                    new SprValueString(4, 40, 75)
                ]
            );
        //КПП
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('compKpp')
            ->setValue($company->getKpp())
            ->setPosition(
                [
                    new SprValueString(6, 40, 66)
                ]
            );
        //Вид документа (о регистрации/перерегистрации ККТ)
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('docType')
            ->setValue('1')
            ->setPosition(
                [
                    new SprValueString(12, 27, 27)
                ]
            );
        //Код причины перерегистрации ККТ
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('regReasonCode')
//            ->setJumpLength(6)
//            ->setValue('')
//            ->setPosition([
//                new SprValueString(15,33,77)
//            ]);
        //Наименование организации
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('compTitle')
            ->setValue(
                ($company->getOrgType() === 0)
                    ? $company->getType().' '.$company->getTitle()
                    : 'Индивидуальный предприниматель'.' '.$company->getIpLastName().' '.$company->getIpFirstName().' '
                    .$company->getIpMiddleName()
            )
            ->setPosition(
                [
                    new SprValueString(17, 1, 120),
                    new SprValueString(19, 1, 120),
                    new SprValueString(21, 1, 120)
                ]
            );
        //Статус (пользователь/представитель)
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('userStatus')
            ->setValue(($company->getOrgType() === 0) ? '2' : '1')
            ->setPosition(
                [
                    new SprValueString(33, 2, 2)
                ]
            );
//        ФИО руководителя (уточнить)
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('compDirector')
            ->setValue(
                ($company->getOrgType() === 0)
                    ?
                    $user->getFIO()
                    :
                    $company->getDirectorLastName().' '.$company->getDirectorFirstName().' '.$company->getDirectorMiddleName()
            )
            ->setPosition(
                [
                    new SprValueString(36, 1, 60),
                    new SprValueString(38, 1, 60),
                    new SprValueString(40, 1, 60)
                ]
            );
//        Дата заявления
//        $sprCreatorData[] = (new SprValueBuilder())
//            ->setName('procurationDate')
//            ->setValue(date('d.m.Y'))
//            ->setPosition(
//                [
//                    new SprValueString(45, 28, 57)
//                ]
//            );
//        Документ представителя
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('agentDocName')
//            ->setSheetIndex(1)
//            ->setValue('')
//            ->setPosition([
//                new SprValueString(12, 1, 60),
//                new SprValueString(14, 1, 60),
//                new SprValueString(16, 1, 60)
//            ]);

//      Регистрационный номер ККТ
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('KKTNumber')
//            ->setSheetIndex(1)
//            ->setValue($kkt->getRegNumber())
//            ->setPosition([
//                new SprValueString(21, 22, 81),
//            ]);
//        Дата регистрационного номера ККТ
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('KKTNumberDate')
//            ->setSheetIndex(1)
////            ->setValue(date('d.m.Y'))
//            ->setValue('??.??.????')
//            ->setPosition([
//                new SprValueString(21, 91, 120),
//            ]);
//        Наименование модели ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTModelName')
            ->setSheetIndex(2)
            ->setValue('УМКА-лайт')
            ->setPosition(
                [
                    new SprValueString(13, 55, 114),
                    new SprValueString(15, 55, 114),
                ]
            );
//      Заводской номер экземпляра ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTModelInstanceNumber')
            ->setSheetIndex(2)
            ->setValue($kkt->getSerialNumber())
            ->setPosition(
                [
                    new SprValueString(17, 55, 114),
                    new SprValueString(19, 55, 114),
                ]
            );
//       Полное или краткое наименование модели фискального накопителя TODO LBNV ПОЛУЧИТЬ У СЧЕТМАША МОДЕЛЬ ФН
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('FNModelName')
            ->setSheetIndex(2)
            ->setValue($kkt->getFsVersion())
            ->setPosition(
                [
                    new SprValueString(21, 55, 114),
                    new SprValueString(23, 55, 114),
                    new SprValueString(25, 55, 114),
                    new SprValueString(27, 55, 114),
                    new SprValueString(29, 55, 114),
                    new SprValueString(31, 55, 114),
                ]
            );
//        Заводской номер экземпляра модели фискального накопителя
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('FNModelInstanceNumber')
            ->setSheetIndex(2)
            ->setValue($kkt->getFsNumber())
            ->setPosition(
                [
                    new SprValueString(33, 55, 114),
                ]
            );
//      Почтовый индекс ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTZipCode')
            ->setSheetIndex(2)
            ->setValue('305022')
            ->setPosition(
                [
                    new SprValueString(38, 31, 48),
                ]
            );
//        Регион ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTRegion')
            ->setSheetIndex(2)
            ->setValue('46')
            ->setPosition(
                [
                    new SprValueString(38, 115, 120),
                ]
            );
//        Район ККТ
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('KKTDistrict')
//            ->setSheetIndex(2)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(40, 31, 120),
//            ]);
//        Город ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTTown')
            ->setSheetIndex(2)
            ->setValue('Курск')
            ->setPosition(
                [
                    new SprValueString(42, 31, 120),
                ]
            );
//        Населенный пункт ККТ
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('KKTSettlement')
//            ->setSheetIndex(2)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(44, 31, 120),
//            ]);
//        Улица ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTStreet')
            ->setSheetIndex(3)
            ->setValue('РАБОЧАЯ 2-Я')
            ->setPosition(
                [
                    new SprValueString(9, 31, 120),
                ]
            );
//        Дом ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTHouse')
            ->setSheetIndex(3)
            ->setValue('23')
            ->setPosition(
                [
                    new SprValueString(11, 31, 54),
                ]
            );
//        Корпус ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTBuilding')
            ->setSheetIndex(3)
            ->setValue('В 1')
            ->setPosition(
                [
                    new SprValueString(13, 31, 54),
                ]
            );
//        Квартира ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTFlat')
            ->setSheetIndex(3)
            ->setValue('59')
            ->setPosition(
                [
                    new SprValueString(15, 31, 54),
                ]
            );
//        Место установки ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTInstalationSite')
            ->setSheetIndex(3)
            ->setValue($kkt->getShop()->getTitle())
            ->setPosition(
                [
                    new SprValueString(18, 52, 111),
                    new SprValueString(20, 52, 111),
                    new SprValueString(22, 52, 111),
                    new SprValueString(24, 52, 111),
                ]
            );
//     (1 - да, 2 - нет) Режим ККТ
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTMode')
            ->setSheetIndex(3)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(27, 52, 52),
                ]
            );

        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse080')
            ->setSheetIndex(4)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(11, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse090')
            ->setSheetIndex(4)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(18, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse100')
            ->setSheetIndex(4)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(23, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse105')
            ->setSheetIndex(4)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(27, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse110')
            ->setSheetIndex(4)
            ->setValue('1')
            ->setPosition(
                [
                    new SprValueString(30, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse130')
            ->setSheetIndex(4)
            ->setValue('1')
            ->setPosition(
                [
                    new SprValueString(34, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse140')
            ->setSheetIndex(5)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(10, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse150')
            ->setSheetIndex(5)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(14, 58, 58),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('KKTUse155')
            ->setSheetIndex(5)
            ->setValue('2')
            ->setPosition(
                [
                    new SprValueString(18, 58, 58),
                ]
            );
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceNumber')
//            ->setSheetIndex(6)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(18, 58, 58),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceNumber')
//            ->setSheetIndex(6)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(11, 53, 112),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceZipCode')
//            ->setSheetIndex(6)
//            ->setValue('305025')
//            ->setPosition([
//                new SprValueString(19, 31, 48),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceRegion')
//            ->setSheetIndex(6)
//            ->setValue('46')
//            ->setPosition([
//                new SprValueString(19, 115, 120),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceDistrict')
//            ->setSheetIndex(6)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(21, 31, 120),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceTown')
//            ->setSheetIndex(6)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(23, 31, 120),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceSettlement')
//            ->setSheetIndex(6)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(25, 31, 120),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceStreet')
//            ->setSheetIndex(6)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(27, 31, 120),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceHouse')
//            ->setSheetIndex(6)
//            ->setValue('123456')
//            ->setPosition([
//                new SprValueString(29, 31, 54),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceBuilding')
//            ->setSheetIndex(6)
//            ->setValue('12345678')
//            ->setPosition([
//                new SprValueString(31, 31, 54),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceFlat')
//            ->setSheetIndex(6)
//            ->setValue('12345678')
//            ->setPosition([
//                new SprValueString(33, 31, 54),
//            ]);
//        $sprCreatorData[]=(new SprValueBuilder())
//            ->setName('CalcDeviceInstalationSite')
//            ->setSheetIndex(7)
//            ->setValue($content)
//            ->setPosition([
//                new SprValueString(9, 53, 112),
//                new SprValueString(11, 53, 112),
//                new SprValueString(13, 53, 112),
//            ]);
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('FiccalOperatorName')
            ->setSheetIndex(8)
            ->setValue($kkt->getShop()->getCompany()->getOfd()->getTitle())
            ->setPosition(
                [
                    new SprValueString(12, 55, 114),
                    new SprValueString(14, 55, 114),
                    new SprValueString(16, 55, 114),
                    new SprValueString(18, 55, 114),
                ]
            );
        $sprCreatorData[] = (new SprValueBuilder())
            ->setName('FiccalOperatorInn')
            ->setSheetIndex(8)
            ->setValue($kkt->getShop()->getCompany()->getOfd()->getInn())
            ->setPosition(
                [
                    new SprValueString(21, 55, 90),
                ]
            );
        $this->spreadsheetCreator->getSpreadsheet(ROOT_PATH.'src/Office/templates/xls/statement.xls', ...$sprCreatorData);
        $data = $this->spreadsheetCreator->putSpreadsheetIntoStream();

        $resp = new Response(
            'php://memory',
            200,
            [
                'Content-Type'              => 'application/x-unknown',
                'Content-transfer-encoding' => 'binary',
                'Content-Disposition'       => 'attachment; filename=list.xlsx',
                'Pragma'                    => 'no-cache'
            ]
        );
        $resp->getBody()->write($data);

        return $resp;
    }
}
