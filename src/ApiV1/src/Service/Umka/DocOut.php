<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 24.04.18
 * Time: 13:12
 */

namespace ApiV1\Service\Umka;

/**
 * Class DocOut
 * @method $this setSessionId($_id)
 * @method mixed getSessionId()
 * @method $this setName($_name)
 * @method mixed getName()
 * @method $this setDocNumber($_number)
 * @method mixed getDocNumber()
 * @method $this setDocType($_type)
 * @method mixed getDocType()
 *
 * @package ApiV1\Service\Umka
 */
class DocOut
{
    protected $docNumber;

    protected $docType;

    protected $fiscprops;

    protected $name;

    protected $sessionId;

    private $fiscalDosc = [
        1012 => 'Datetime',
        1037 => 'KktNumber',
        1038 => 'ShiftNumber',
        1040 => 'FD',
        1041 => 'FN',
        1042 => 'ReceiptNumber',
        1054 => 'CalculationSign',
        1077 => 'FPD',
        1081 => 'ReceiptSumElectro',
        1196 => 'OfdLink',
    ];

    /**
     * DocOut constructor.
     *
     * @param array $_document
     *
     * @throws \Exception
     */
    public function __construct(array $_document)
    {
        if (!isset($_document['document']['data'])) {
            throw new \Exception('Incorrect document!');
        }

        $this->setSessionId($_document['document']['sessionId'])
            ->setDocNumber($_document['document']['data']['docNumber'])
            ->setDocType($_document['document']['data']['docType'])
            ->setName($_document['document']['data']['name']);
//            ->setFiscprops($_document['document']['data']['fiscprops']);

        foreach ($_document['document']['data']['fiscprops'] as $prop) {
            if (isset($this->fiscalDosc[$prop['tag']])) {
                $name = '\ApiV1\Service\Umka\Props\\'.$this->fiscalDosc[$prop['tag']];
                $this->fiscprops[] = new $name($prop['value']);
            }
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return $this|mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $type = substr($name, 0, 3);
        $propName = substr($name, 3);

        if ($type === 'get') {
            if (property_exists($this, lcfirst($propName))) {
                return $this->{lcfirst($propName)};
            }

            /** @var ExtendFiscProp $prop */
            foreach ($this->fiscprops as $prop) {
                $class = 'ApiV1\Service\Umka\Props\\'.$propName;
                if ($prop instanceof $class) {
                    return $prop->getValue();
                }
            }
        } elseif ($type === 'set' && property_exists($this, lcfirst($propName))) {
            $this->{lcfirst($propName)} = current($arguments);

            return $this;
        }

        throw new \Exception('Call to undefined method '.$name);
    }

    /**
     * @return mixed
     */
    public function getFiscprops()
    {
        return $this->fiscprops;
    }

    /**
     * @param mixed $_fiscprops
     *
     * @return $this;
     */
    public function setFiscprops($_fiscprops)
    {
        $this->fiscprops = $_fiscprops;

        return $this;
    }
}
