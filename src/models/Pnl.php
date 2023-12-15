<?php

declare(strict_types=1);

namespace hipanel\modules\finance\models;

use hipanel\base\Model;
use hipanel\base\ModelTrait;
use Yii;

class Pnl extends Model
{
    use ModelTrait;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                ['id', 'charge_id', 'client_id', 'type_id', 'currency_id', 'sum', 'charge_sum', 'discount_sum', 'bill_id', 'eur_amount'],
                'integer',
            ],
            [['rate'], 'number'],
            [['type', 'currency', 'exchange_date', 'charge_date', 'charge_label', 'charge_type', 'client', 'note'], 'string'],
            [['commonObject'], 'safe'],
            [['update_time', 'month'], 'date', 'format' => 'php:Y-m-d'],
            [['data'], 'safe'],
            [['id', 'note'], 'safe', 'on' => 'set-note'],
        ]);
    }

    public function attributeLabels()
    {
        return [
            'plan_id' => Yii::t('hipanel:finance', 'Tariff plan'),
            'plan' => Yii::t('hipanel:finance', 'Tariff plan'),
            'quantity' => Yii::t('hipanel:finance', 'Prepaid'),
            'unit' => Yii::t('hipanel:finance', 'Unit'),
            'price' => Yii::t('hipanel:finance', 'Price'),
            'formula' => Yii::t('hipanel.finance.price', 'Formula'),
            'note' => Yii::t('hipanel', 'Note'),
            'type' => Yii::t('hipanel', 'Type'),
            'eur_amount' => Yii::t('hipanel', 'EUR'),
            'discount_sum' => Yii::t('hipanel:finance', 'Discount'),
        ];
    }

    public function getDescribePnl(): string
    {
        return implode(" / ", [$this->type, $this->month, $this->sum / 100, $this->currency]);
    }
}