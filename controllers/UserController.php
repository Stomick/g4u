<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 15:25
 */

namespace app\controllers;

use app\components\CompAuthG4U;
use app\components\ErrorType;
use app\models\admin\Asgmt;
use app\models\admin\MergePersonal;
use app\models\admin\MergePlayers;
use app\models\admin\Personal;
use app\models\admin\Players;
use app\models\Commands;
use app\models\Games;
use app\models\Leagues;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\User;
use app\components\HttpBearerAuthG4U;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

class UserController extends Controller
{
    private $answer = [];
    private $error = false;
    private $body;
    private $user;
    private $message;
    private $persona;
    private $player;
    private $locale = 'uk';

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompAuthG4U::className(),
            'except' => ['login', 'registration'],
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

    public function actionLogin()
    {
        $login = new LoginForm();
        if (isset($this->body['email']) && isset($this->body['password'])) {
            $login->email = $this->body['email'];
            $login->password = $this->body['password'];
            if ($user = $login->login()) {
                $this->persona = MergePersonal::findOne(['user_id' => $user->id]);
                $this->player = MergePlayers::findOne(['user_id' => $user->id]);
                $type = $this->persona == null ? null : Personal::findOne($this->persona->personal_id);
                return $this->answer = [
                    'token' => $user->auth_key,
                    'type' => $user->type,
                    'personal' => $this->persona == null ? 0 : $this->persona->personal_id,
                    'personal_type' => $type == null ? 0 : $type->type_id,
                    'player' => $this->player == null ? 0 : $this->player->player_id,
                    'league' => $user->league_id
                ];
            } else {
                $this->error = true;
                $this->message = (User::findByEmail($this->body['email']) != null ? ErrorType::err_login[$this->locale] : ErrorType::err_emeil[$this->locale]);
            }
        } else {
            $this->error = true;
            $this->message = 'invalid user email or password';
        }


    }

    public function actionRegistration()
    {
        $reg = new SignupForm();
        if (User::find()->where(['email' => $this->body['email']])->one()) {
            $this->message = 'email занят';
            $this->error = true;

        } else {
            foreach ($this->body as $k => $value) {
                $reg->$k = $value;
            }
            if ($user = $reg->signup()) {
                return $this->answer = true;
            } else {
                $this->message = 'regisration false';
                $this->error = true;
            }
        }
    }

    public function actionUpdate()
    {
        //return $this->answer = $this->user;
        foreach ($this->body as $k => $value) {
            $this->user->$k = $value;
        }
        if ($this->user->update()) {
            $this->answer = true;
            $this->message = ErrorType::answer_true_update[$this->locale];
        } else {
            $this->message = $this->user->errors;//'update false';
            $this->error = true;
        }

    }


    public function actionSetleague()
    {
        if (Leagues::findOne($this->body['league_id'])) {
            $this->user->league_id = $this->body['league_id'];
            if ($this->user->update()) {
                $this->answer = true;
                $this->message = ErrorType::answer_true_save[$this->locale];
            } else {
                $this->error = true;
                $this->message = $this->user->errors;
            }
        } else {
            $this->message = 'league not finded';
            $this->error = true;
        }
    }


    public function actionProfile()
    {
        $this->answer = User::find()->where([
            'id' => (\Yii::$app->user->getId())
        ])->select(
            ['id', 'nickname', 'locale', 'leagues.title as league', 'email']
        )->join('inner join', 'leagues', 'leagues.leagues_id=user.league_id')
            ->asArray()->one();
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
                } else {
                    $this->locale = $this->user->app_loc;
                    $this->persona = MergePersonal::findOne(['user_id' => $this->user->id]);
                    $this->player = MergePlayers::findOne(['user_id' => $this->user->id]);
                }
            }
        }

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        return ['answer' => $this->answer, 'error' => $this->error, 'message' => $this->message];
    }

    public function actionPlayer()
    {
        if ($this->player != null) {
            $this->answer = Players::find()
                ->select([
                    "player_id",
                    "name",
                    "surename",
                    "patronymic",
                    "photo",
                    "position.type as position",
                    "birthday",
                    "stature",
                    "weight",
                    "leg",
                    "FB",
                    "VK",
                    "phone",
                ])
                ->join('inner join', 'position', 'position.position_id=players.position_id')
                ->where(['player_id' => $this->player->player_id])
                ->asArray()->one();
        } else {
            return $this->error = true && $this->message = ErrorType::player_not_found[$this->locale];
        }
    }

    public function actionDelplmerge()
    {
        if ($this->player != null) {
            if ($merg = MergePlayers::find()->where(['player_id' => $this->player->player_id, 'user_id' => $this->user->id])->one()) {
                if ($merg->delete()) {
                    $this->answer = true;
                    $this->message = ErrorType::answer_true_update[$this->locale];
                } else {
                    $this->message = $merg->errors;
                    $this->error = true;
                }
            }
        } else {
            $this->error = true;
            $this->message = ErrorType::player_not_merget[$this->locale];
        }
    }

    public function actionUpdateplayer()
    {
        if ($this->player != null) {
            if ($pl = Players::find()->where(['player_id' => $this->player->player_id])->one()) {
                foreach ($this->body as $k => $val) {
                    $pl->$k = $val;
                }
                if ($pl->update()) {
                    $this->answer = true;
                    $this->message = ErrorType::answer_true_update[$this->locale];
                } else {
                    $this->message = $pl->errors;
                    $this->error = true;
                }
            }
        }
        return $this->error = true && $this->message = 'player not found';
    }

    public function actionSetlocale()
    {
        $this->user->app_loc = $this->body['lang'];
        $this->locale = $this->body['lang'];
        if ($this->user->update()) {
            $this->answer = true;
            $this->message = ErrorType::answer_true_update[$this->locale];
        } else {
            $this->message = $this->user->errors;
            $this->error = true;
        }
    }

    public function actionMyasgmt()
    {
        if ($this->persona != null) {
            $index = 0;
            foreach (Asgmt::find()->where(['personal_id' => $this->persona->personal_id])->all() as $k => $val) {
                if ($game = Games::find()
                    ->where(['game_id' => $val->game_id])
                    ->join('inner join', 'commands as cm_in', 'cm_in.command_id=command_id_in')
                    ->join('inner join', 'commands as cm_out', 'cm_out.command_id=command_id_out')
                    ->one()) {
                    $this->answer[$index++] = [
                        'id' => $val->asgm_id,
                        'game' => [
                            'id' => $game->game_id,
                            'tour' => $game->tour,
                            'in' => Commands::find()->select([
                                'title',
                                'logo',
                                'command_id'
                            ])->where(['command_id' => $game->command_id_in])->one(),
                            'out' => Commands::find()->select([
                                'title',
                                'logo',
                                'command_id'
                            ])->where(['command_id' => $game->command_id_out])->one()]
                    ];
                }
            }
        }
    }

    public function actionIndex()
    {
        $this->answer;
    }
}
