<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 15:25
 */

namespace app\controllers;

use app\components\CompAuthG4U;
use app\models\Leagues;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\User;
use app\components\HttpBearerAuthG4U;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

class MailController extends Controller
{
    private $answer = [];
    private $error = false;
    private $body;
    private $user;
    private $message;
    private $toFootball = 'ua.football@mygame4u.com';

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompAuthG4U::className(),
            'except' => ['lending'],
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
        $accept = \Yii::$app->request->getHeaders()->get('Authorization');
        if ($accept != null && $action->id != 'login' && $action->id != 'registration') {
            if (is_array($arr = explode(' ', $accept))) {

                if (!$this->user = User::findIdentityByAccessToken(explode(' ', $accept)[1])) {
                    $this->error = true;
                    $this->message = 'User not fount';
                    return;
                }

            }
        }

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function afterAction($action, $result)
    {
        return ['answer' => $this->answer, 'error' => $this->error, 'message' => $this->message];
    }

    public function actionIndex($type = 'test', $data = null)
    {
        return $this->answer = \Yii::$app->mailer->compose()
            ->setFrom(\Yii::$app->params['noreplyEmail'])
            ->setTo($this->to)
            ->setSubject('')
            ->send();
    }

    public function actionLending($type = 'test', $data = null)
    {
        $message = '';
        $subject = '';
        if (array_key_exists('find_command', $this->body)) {
            $message .= '<b>Имя : </b>' . $this->body['name'] . '<br/>';
            $message .= '<b>Возраст : </b>' . intval($this->body['age']) . '<br/>';
            $message .= '<b>Телефон : </b>' . $this->body['tel'] . '<br/>';
            $message .= '<b>Город : </b>' . $this->body['city'] . '<br/>';
            $message .= '<b>Откуда пришел : </b>' . $this->body['how__know'] . '<br/>';
            $message .= '<b>Ответ : </b>' . strip_tags($this->body['answer']) . '<br/>';
            $subject = $this->body['find_command'];
            $this->answer = \Yii::$app->mailer->compose()
                ->setFrom('no-reply@mygame4u.com')
                ->setTo($this->toFootball)
                ->setHtmlBody($message)
                ->setSubject($subject)
                ->send();
        }elseif(array_key_exists('open_league', $this->body)) {
            $message .= '<b>Имя : </b>' . $this->body['name'] . '<br/>';
            $message .= '<b>Возраст : </b>' . intval($this->body['age']) . '<br/>';
            $message .= '<b>Телефон : </b>' . $this->body['tel'] . '<br/>';
            $message .= '<b>Город : </b>' . $this->body['city'] . '<br/>';
            $message .= '<b>Ответ : </b>' . strip_tags($this->body['answer']) . '<br/>';
            $subject = $this->body['open_league'];
            $this->answer = \Yii::$app->mailer->compose()
                ->setFrom('no-reply@mygame4u.com')
                ->setTo($this->toFootball)
                ->setHtmlBody($message)
                ->setSubject($subject)
                ->send();
        }elseif(array_key_exists('claim_team', $this->body)) {
            $message .= '<b>Имя : </b>' . $this->body['name'] . '<br/>';
            $message .= '<b>Телефон : </b>' . $this->body['tel'] . '<br/>';
            $message .= '<b>Город : </b>' . $this->body['city'] . '<br/>';
            $message .= '<b>Откуда пришел : </b>' . $this->body['how__know'] . '<br/>';
            $message .= '<b>Ответ : </b>' . strip_tags($this->body['answer']) . '<br/>';
            $subject = $this->body['claim_team'];
            $this->answer = \Yii::$app->mailer->compose()
                ->setFrom('no-reply@mygame4u.com')
                ->setTo($this->toFootball)
                ->setHtmlBody($message)
                ->setSubject($subject)
                ->send();
        }
        /*return $this->answer =
        */
    }

}
