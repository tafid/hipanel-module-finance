<?php
/**
 * Finance module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\widgets;

use hipanel\modules\finance\models\Merchant;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class AvailableMerchants extends Widget
{
    /**
     * @var Merchant[]
     */
    public $merchants;

    const PAYPAL_PROCESSING = 'PayPal';

    public function run()
    {
        // PurchaseRequestCollection::fetchMerchants() explicitly supports guest
        // requests (falls back to Yii::$app->params['user.seller'] when there's no
        // logged-in username), so gating this purely informational "what payment
        // methods do you accept" marketing block behind the 'deposit' permission
        // hid it from every anonymous visitor - same guest-allowed pattern as
        // MainMenu::canBuyVds()/canBuyCertificates().
        $user = Yii::$app->user;
        if ($this->merchants === null && Yii::$app->hasModule('merchant') && ($user->can('deposit') || $user->isGuest)) {
            $this->merchants = Yii::$app->getModule('merchant')->getPurchaseRequestCollection()->getItems();
        }

        if (empty($this->merchants)) {
            return '';
        }

        $list = array_map(function ($merchant) { return $merchant->label; }, $this->merchants);
        $htmlList = array_unique(array_map(function ($merchant) { return Html::tag('b', $merchant); }, $list));

        $out[] = Yii::t('hipanel:finance', 'We accept the following automatic payment methods') . ':';
        $out[] = implode(',&nbsp; ', $htmlList);
        if (\in_array(self::PAYPAL_PROCESSING, $list, true)) {
            $out[] = Yii::t('hipanel:finance', 'as well as PayPal payments from your Visa and MasterCard');
        }

        return implode(' ', $out);
    }
}
