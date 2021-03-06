<?php
/**
 * Finance module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\actions;

use hipanel\modules\finance\forms\BillForm;
use hipanel\modules\finance\models\Bill;
use hipanel\modules\finance\providers\BillTypesProvider;
use hiqdev\hiart\Collection;
use hiqdev\hiart\ResponseErrorException;
use Yii;
use yii\base\Action;
use yii\web\Controller;
use yii\web\Response;

class BillManagementAction extends Action
{
    /**
     * @var \yii\web\Request
     */
    protected $request;

    /**
     * @var Collection
     */
    protected $collection;
    /**
     * @var BillTypesProvider
     */
    private $billTypesProvider;

    /**
     * @var string
     */
    public $scenario;

    /**
     * @var string The view that represents current update action
     */
    public $_view;

    /**
     * @var bool
     */
    public $forceNewRecord = false;

    public function __construct($id, Controller $controller, BillTypesProvider $billTypesProvider, array $config = [])
    {
        parent::__construct($id, $controller, $config);

        $this->request = Yii::$app->request;
        $this->billTypesProvider = $billTypesProvider;
    }

    public function init()
    {
        parent::init();

        if (!isset($this->scenario) || !in_array($this->scenario, [BillForm::SCENARIO_CREATE, BillForm::SCENARIO_UPDATE, BillForm::SCENARIO_COPY], true)) {
            $this->scenario = $this->id;
        }
    }

    public function run()
    {
        $this->createCollection();
        $this->findBills();

        $result = $this->saveBills();
        if ($result instanceof Response) {
            return $result;
        }

        list($billTypes, $billGroupLabels) = $this->billTypesProvider->getGroupedList();

        return $this->controller->render($this->view, [
            'models' => $this->collection->getModels(),
            'billTypes' => $billTypes,
            'billGroupLabels' => $billGroupLabels,
        ]);
    }

    private function findBills()
    {
        $ids = $this->getRequestedIds();

        if ($ids === false) {
            return;
        }

        $bills = Bill::find()->joinWith('charges')->where(['ids' => $ids, 'with_charges' => true])->all();
        $billForms = BillForm::createMultipleFromBills($bills, $this->scenario);
        if ($this->forceNewRecord === true) {
            foreach ($billForms as $model) {
                $model->forceNewRecord();
            }
        }
        $this->collection->set($billForms);
    }

    private function getRequestedIds()
    {
        $request = $this->request;

        if ($request->isPost && ($ids = $request->post('selection')) !== null) {
            return $ids;
        }

        if ($request->isGet && ($id = $request->get('id')) !== null) {
            return [$id];
        }

        if ($request->isPost && $request->post('BillForm') !== null) {
            return false;
        }

        if ($this->scenario === BillForm::SCENARIO_CREATE) {
            $this->collection->set([new BillForm(['scenario' => $this->scenario])]);

            return true;
        }

        throw new \yii\web\UnprocessableEntityHttpException('Id is missing');
    }

    private function createCollection()
    {
        $this->collection = new Collection([
            'model' => new BillForm(['scenario' => $this->scenario]),
            'scenario' => $this->scenario,
            'loadFormatter' => function ($baseModel, $key, $value) {
                /** @var BillForm $baseModel */
                $charges = $this->request->post($baseModel->newCharge()->formName());
                $value['charges'] = isset($charges[$key]) ? $charges[$key] : [];

                return [$key, $value];
            },
            'dataCollector' => function ($model) {
                /** @var BillForm $model */
                return [$model->getPrimaryKey(), $model->toArray()];
            },
        ]);
    }

    private function saveBills()
    {
        $request = $this->request;
        $collection = $this->collection;

        if (
            $request->isPost
            && ($payload = $request->post($this->collection->formName)) !== null
            && $collection->load($payload)
            && $collection->validate()
        ) {
            try {
                $collection->save();
                $this->addSuccessFlash();

                return $this->controller->redirect(['@bill', 'id_in' => $collection->getIds()]);
            } catch (ResponseErrorException $e) {
                Yii::$app->session->addFlash('error', $e->getMessage());

                return false;
            }
        }

        return null;
    }

    private function addSuccessFlash()
    {
        if ($this->scenario === BillForm::SCENARIO_CREATE) {
            return Yii::$app->session->addFlash('success', Yii::t('hipanel:finance', 'Bill was created successfully'));
        }

        if ($this->scenario === BillForm::SCENARIO_UPDATE) {
            return Yii::$app->session->addFlash('success', Yii::t('hipanel:finance', 'Bill was updated successfully'));
        }
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->_view ?: $this->scenario;
    }

    /**
     * @param string $view
     */
    public function setView($view)
    {
        $this->_view = $view;
    }
}
