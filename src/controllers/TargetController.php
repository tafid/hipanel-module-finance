<?php

namespace hipanel\modules\finance\controllers;

use hipanel\actions\ComboSearchAction;
use hipanel\actions\IndexAction;
use hipanel\base\CrudController;
use hipanel\filters\EasyAccessControl;
use hipanel\modules\client\models\stub\ClientRelationFreeStub;
use hipanel\modules\finance\actions\ResourceDetailAction;
use hipanel\modules\finance\models\Plan;
use hipanel\modules\finance\models\Target;
use Yii;

class TargetController extends CrudController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => EasyAccessControl::class,
                'actions' => [
                    '*' => ['plan.read', 'price.read'],
                ],
            ],
        ]);
    }

    public function actions(): array
    {
        return array_merge(parent::actions(), [
            'search' => [
                'class' => ComboSearchAction::class,
            ],
            'index' => [
                'class' => IndexAction::class,
            ],
            'view' => [
                'class' => ResourceDetailAction::class,
                'model' => Target::class,
                'configurator' => Yii::$container->get('target-resource-config'),
                'data' => function ($action, $data) {
                    $target = Target::findOne($action->controller->request->get('id'));
                    $attributes = [
                        'id' => $target->client_id,
                        'login' => $target->client,
                    ];
                    $client = new ClientRelationFreeStub($attributes);
                    $tariff = Plan::find()->where(['id' => $target->tariff_id])->one();

                    return array_merge($data, [
                        'originalModel' => $target,
                        'client' => $client,
                        'tariff' => $tariff,
                    ]);
                },
            ],
        ]);
    }
}
