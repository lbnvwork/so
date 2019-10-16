<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 04.02.18
 * Time: 13:25
 */

namespace ApiV1\Service\Umka;

/**
 * Class DocIn
 * @method $this setSessionId($_id)
 * @method mixed getSessionId()
 * @method $this setPrint($_is)
 * @method mixed getPrint()
 * @method $this setResult($_result)
 * @method mixed getResult()
 * @method $this setDocName($_name)
 * @method mixed getDocName()
 * @method $this setMoneyType($_type)
 * @method mixed getMoneyType()
 * @method $this setSum($_sum)
 * @method mixed getSum()
 * @method $this setType($_type)
 * @method mixed getType()
 * @method $this setNalogSystem($_system)
 * @method mixed getNalogSystem()
 * @method $this setKktNumber($_number)
 * @method mixed getKktNumber()
 * @method $this setReceiptSumElectr($_sum)
 * @method mixed getReceiptSumElectr()
 * @method $this setUserInn($_inn)
 * @method mixed getUserInn()
 * @method $this setCalculationSign($_is)
 * @method mixed getCalculationSign()
 * @method $this setEmailOrPhoneBuyer($_EmailOrPhone)
 * @method mixed getEmailOrPhoneBuyer()
 * @method mixed getBuyerName()
 * @method mixed getBuyerINN()
 */
class DocIn
{
    const PROPS = [
        //применяемая система налогообложения
        'NalogSystem'     => [
            'tag'     => 1055,
            'default' => 1,
        ],
        //ИНН пользователя
        'UserInn'         => [
            'tag'     => 1018,
            'default' => '4632118082',
        ],
        //признак расчета
        'CalculationSign' => [
            'tag'     => 1054,
            'default' => 1,
        ],
    ];

    protected $sessionId;

    protected $print = 0;

    protected $result = 0;

    protected $docName;

    protected $moneyType = 1;

    protected $sum = 0;

    protected $type = 1;

    protected $fiscprops = [];

    /**
     * DocIn constructor.
     *
     * @param array $propsTag
     */
    public function __construct($propsTag = [
        1055,
        1018,
        1054,
    ]
    )
    {
        foreach ($propsTag as $tag) {
            $item = null;
            foreach (self::PROPS as $prop) {
                if ($tag == $prop['tag']) {
                    $item = $prop;
                    break;
                }
            }

            if ($item === null) {
                throw new \InvalidArgumentException('Tag '.$tag.' not found');
            }

            $this->addFiscProp(new FiscProp($item['tag'], isset($item['default']) ? $item['default'] : null));
        }
    }

    /**
     * @param FiscProp $prop
     */
    public function addFiscProp(FiscProp $prop)
    {
        $this->fiscprops[] = $prop;
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
        if ($type === 'set' && isset(self::PROPS[$propName])) {
            $tag = self::PROPS[$propName]['tag'];
            /** @var FiscProp $fiscprop */
            foreach ($this->fiscprops as $fiscprop) {
                if ($fiscprop->getTag() == $tag) {
                    $fiscprop->setValue(current($arguments));

                    return $this;
                }
            }
        } elseif ($type === 'get' && isset(self::PROPS[$propName])) {
            $tag = self::PROPS[$propName]['tag'];
            /** @var FiscProp $fiscprop */
            foreach ($this->fiscprops as $fiscprop) {
                if ($fiscprop->getTag() == $tag) {
                    return $fiscprop->getValue();
                }
            }
        } elseif (property_exists($this, lcfirst($propName))) {
            if ($type === 'get') {
                return $this->{lcfirst($propName)};
            }

            if ($type === 'set') {
                $this->{lcfirst($propName)} = current($arguments);

                return $this;
            }
        }

        throw new \Exception('Call to undefined method '.$name);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'document' => [
                'print' => $this->print,
            ],
        ];

        if ($this->sessionId !== null) {
            $data['document']['sessionId'] = $this->sessionId;
        }
        if ($this->docName !== null) {
            $data['document']['data']['docName'] = $this->docName;
        }
        if ($this->moneyType !== null) {
            $data['document']['data']['moneyType'] = $this->moneyType;
        }
        if ($this->sum !== null) {
            $data['document']['data']['sum'] = $this->sum;
        }
        if ($this->type !== null) {
            $data['document']['data']['type'] = $this->type;
        }
        /** @var FiscProp $fiscprop */
        foreach ($this->fiscprops as $fiscprop) {
            $data['document']['data']['fiscprops'][] = $fiscprop->toArray();
        }

        return $data;
    }
}
