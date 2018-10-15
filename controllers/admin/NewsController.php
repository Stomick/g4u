<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 15:25
 */

namespace app\controllers\admin;

use app\components\CompAuthG4U;
use app\models\LoginForm;
use app\models\News;
use app\models\SignupForm;
use app\models\User;
use yii\filters\auth\CompositeAuth;
use app\components\HttpBearerAuthG4U;
use app\components\AuthG4U;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;

use yii\rest\Controller;
use yii\web\Response;

class NewsController extends Controller
{
    private $answer;
    private $error = false;
    private $body;
    private $getId;
    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompAuthG4U::className(),
            'except' => ['login', 'registration','update'],
            'authMethods' => [
                HttpBearerAuthG4U::className()
            ],
        ];

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    public function beforeAction($action)
    {

        if(\Yii::$app->request->isPost) {
                if (!$this->body = \Yii::$app->request->getBodyParams()) {
                    $this->error = 'empty body';
                }
        }elseif(\Yii::$app->request->isGet){
            $this->getId= \Yii::$app->request->get('id');
        }
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function afterAction($action, $result)
    {
        return ['answer' => $this->answer,'error' => $this->error];
    }

    public function actionIndex(){
        $this->answer = News::find()->asArray()->all();
    }
    public function actionDate($id){
        return $this->answer = $id;
    }
}