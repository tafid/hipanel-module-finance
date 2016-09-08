<?php

/*
 * Finance module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\models;

use hipanel\base\ModelTrait;
use hipanel\modules\finance\models\decorators\AbstractResourceDecorator;
use hipanel\modules\finance\models\decorators\server\AbstractServerResourceDecorator;
use hipanel\modules\finance\models\decorators\server\BackupResourceDecorator;
use hipanel\modules\finance\models\decorators\server\ServerResourceDecoratorFactory;
use Yii;

/**
 * Class DomainResource
 * @package hipanel\modules\finance\models
 */
class ServerResource extends Resource
{
    use ModelTrait;

    public static function index()
    {
        return 'resources';
    }

    public static function type()
    {
        return 'resource';
    }

    const MODEL_TYPE_CPU = 'cpu';
    const MODEL_TYPE_RAM = 'ram';
    const MODEL_TYPE_HDD = 'hdd';
    const MODEL_TYPE_CHASSIS = 'chassis';

    const TYPE_ISP5 = 'isp5';
    const TYPE_ISP = 'isp';
    const TYPE_SUPPORT_TIME = 'support_time';
    const TYPE_IP_NUMBER = 'ip_num';
    const TYPE_SERVER_TRAF_MAX = 'server_traf_max';
    const TYPE_SERVER_TRAF95_MAX = 'server_traf95_max';
    const TYPE_BACKUP_DU = 'backup_du';

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['model_type', 'partno', 'r_object_id'], 'safe'];
        $rules['create-required'] = [
            ['object_id'],
            'required',
            'on' => ['create', 'update'],
            'when' => function ($model) {
                return $model->isHardwareTypeCorrect();
            }
        ];
        unset($rules['create-required-price']);


        return $rules;
    }

    /**
     * @return array
     */
    public function getHardwareTypes()
    {
        return [
            static::MODEL_TYPE_CHASSIS => Yii::t('hipanel/finance/tariff', 'Chassis'),
            static::MODEL_TYPE_CPU => Yii::t('hipanel/finance/tariff', 'CPU'),
            static::MODEL_TYPE_RAM => Yii::t('hipanel/finance/tariff', 'RAM'),
            static::MODEL_TYPE_HDD => Yii::t('hipanel/finance/tariff', 'HDD'),
        ];
    }

    public function isHardwareTypeCorrect()
    {
        return isset($this->getHardwareTypes()[$this->model_type]);
    }


    public function getTypes()
    {
        return [
            static::TYPE_ISP5 => Yii::t('hipanel/finance/tariff', 'ISP Manager 5'),
            static::TYPE_ISP => Yii::t('hipanel/finance/tariff', 'ISP Manager'),
            static::TYPE_SUPPORT_TIME => Yii::t('hipanel/finance/tariff', 'Support time'),
            static::TYPE_IP_NUMBER => Yii::t('hipanel/finance/tariff', 'IP addresses count'),
            static::TYPE_SERVER_TRAF_MAX => Yii::t('hipanel/finance/tariff', 'Server traffic'),
            static::TYPE_SERVER_TRAF95_MAX => Yii::t('hipanel/finance/tariff', '95 percentile traffic'),
            static::TYPE_BACKUP_DU => Yii::t('hipanel/finance/tariff', 'Backup disk usage'),
        ];
    }

    /**
     * @return AbstractServerResourceDecorator
     */
    public function decorator()
    {
        if (empty($this->decorator)) {
            $this->decorator = ServerResourceDecoratorFactory::createFromResource($this);
        }

        return $this->decorator;
    }
}