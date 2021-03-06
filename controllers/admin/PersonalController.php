<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 15:25
 */

namespace app\controllers\admin;

use app\components\CompAuthG4U;
use app\components\ErrorType;
use app\components\UploadImage;
use app\models\admin\AdminLeagues;
use app\models\admin\Personal;
use app\models\admin\PersonalModels;
use app\models\admin\PersonalType;
use app\models\admin\Players;
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

class PersonalController extends Controller
{
    private $answer;
    private $error = false;
    private $body;
    private $message;
    private $user;
    private $locale;
    private $league_id;
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
        if (\Yii::$app->request->isPost) {
            if (!$this->body = \Yii::$app->request->getBodyParams()) {
                $this->error = true;
                $this->message = 'empty body';
                return;
            }
        }
        if ($accept = \Yii::$app->request->getHeaders()->get('Authorization')) {
            if ($arr = explode(' ', $accept)) {
                if (is_array($arr) && count($arr) > 0) {
                    if ($this->user = User::findIdentityByAccessToken($arr[1])) {
                        $this->locale = $this->user->app_loc;
                        if (!$this->league_id = AdminLeagues::find()->select(['leagues_id as id'])->where(['user_id' => $this->user->id])->one()) {
                            $this->error = true;
                            $this->message = ErrorType::admin['not_permission'][$this->locale];
                        } else {
                            $this->league_id = $this->league_id['id'];
                        };
                    } else {
                        $this->error = true;
                        $this->message = 'User not fount';
                        return;
                    }
                }
            }
        }
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function afterAction($action, $result)
    {
        return ['answer' => $this->answer, 'error' => $this->error, 'message' => $this->message];
    }

    public function actionAdd(){
        $persona = new PersonalModels();
        $persona->league_id = $this->league_id;
        foreach ($this->body as $k => $val){
            if($val == ''){
                $this->error = true;
                $this->message = ErrorType::personal['error'][$this->locale] . ' '. ErrorType::personal[$k][$this->locale] ;
                return;
            }elseif ($k == 'photo'){
                $persona->photo = UploadImage::save_image($val , $k,'img/personal/' . \Yii::$app->security->generateRandomString(32) . '/');
            }else {
                $persona->$k = $val;
            }
        };

        if($persona->save())
        {
            $this->answer = true;
            $this->message = ErrorType::answer_true_add[$this->locale];
        }else{
            $this->error = true;
            $this->message = $persona->errors;
        }
    }

    public function actionList($type=null,$name = null , $tied = null){
        $where = 'personal_id != 0';
        if($tied !=null)
        {
            $where = 'tied='.$tied;
        }
        if($name != null){
            return $this->answer = Personal::find()->select([
                'personal_id as id',
                'photo',
                'name',
                'surename',
                'patronymic',
                'type_id',
                'pt.type_ru as type'

            ])
                ->join('inner join','personal_type as pt', 'pt.id=personals.type_id')
                ->andWhere('LOWER(surename) like "'. strtolower($name).'%" or LOWER(name) like "'. strtolower($name).'%" or LOWER(patronymic) like "'. strtolower($name).'%"')
                ->andWhere($where)
                ->asArray()
                ->all();
        }

        if($type == null){
            return   $this->answer = Personal::find()->select([
                'personal_id as id',
                'photo',
                'name',
                'surename',
                'patronymic',
                'type_id',
                'pt.type_ru as type'

            ])
                ->join('inner join','personal_type as pt', 'pt.id=personals.type_id')
                ->andWhere($where)
                ->asArray()
                ->all();
        }elseif($type !=null){
            return $this->answer = Personal::find()->select([
                'personal_id as id',
                'photo',
                'name',
                'surename',
                'patronymic',
                'type_id',
                'pt.type_ru as type'

            ])
                ->join('inner join','personal_type as pt', 'pt.id=personals.type_id')
                ->andWhere($where)
                ->andWhere('pt.id='.intval($type))
                ->asArray()
                ->all();
        }
    }

    public function actionType(){
        $this->answer = PersonalType::find()->asArray()->all();
    }
}
