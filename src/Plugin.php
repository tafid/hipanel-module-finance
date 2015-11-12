<?php

/*
 * Finance Plugin for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2015, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance;

use hipanel\modules\finance\models\Merchant;

class Plugin extends \hiqdev\pluginmanager\Plugin
{
    public function items()
    {
        return [
            'aliases' => [
                '@bill'   => '/finance/bill',
                '@purse'  => '/finance/purse',
                '@tariff' => '/finance/tariff',
                '@pay'    => '/merchant/pay',
            ],
            'menus' => [
                'hipanel\modules\finance\SidebarMenu',
            ],
            'modules' => [
                'finance' => [
                    'class' => 'hipanel\modules\finance\Module',
                ],
                'cart' => [
                    'class' => 'hiqdev\yii2\cart\Module',
                ],
                'merchant' => [
                    'class'           => 'hiqdev\yii2\merchant\Module',
                    'notifyPage'      => '/finance/pay/notify',
                    'depositClass'    => 'hipanel\modules\finance\merchant\Deposit',
                    'collectionClass' => 'hipanel\modules\finance\merchant\Collection',
                ],
            ],
        ];
    }
}
