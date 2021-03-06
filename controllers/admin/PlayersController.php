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
use app\models\admin\Players;
use app\models\admin\PlayersEd;
use app\models\admin\PositionInField;
use app\models\Commands;
use app\models\User;
use app\components\HttpBearerAuthG4U;
use yii\db\Command;
use yii\filters\ContentNegotiator;

use yii\rest\Controller;
use yii\web\Response;

class PlayersController extends Controller
{
    private $answer;
    private $error = false;
    private $body;
    private $user;
    private $message;
    private $locale;

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompAuthG4U::className(),
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

    public function actionGetposition()
    {
        $this->answer = PositionInField::find()->asArray()->all();
    }

    public function actionInfo($id)
    {
        if ($pl = Players::find()
            ->select([
                "player_id",
                "name",
                "surename",
                "patronymic",
                "photo",
                "position.type as position",
                "players.position_id",
                "birthday",
                "stature",
                "weight",
                "leg",
                "FB",
                "VK",
                "phone",
            ])
            ->join('inner join', 'position', 'position.position_id=players.position_id')
            ->where(['player_id' => $id])
            ->asArray()->one()) {
            return $this->answer = $pl;
        } else {
            $this->error = true;
            $this->message = ErrorType::player_not_found[$this->locale];
            return;
        }
    }

    public function actionIndex($limit = 50, $offset = 0, $comId = null, $name = null, $tied = null)
    {

        $where = 'pl.player_id != 0';
        $andwhere = 'pl.player_id != 0';

        if ($name) {
            $n = $name;
            $where = 'CONCAT (LCASE(name) ,\' \',LCASE(surename) , \' \' , (patronymic)  ) like "%' . strtolower(iconv(mb_detect_encoding($n), "UTF-8//IGNORE", $n)) . '%"';
            $where .= 'or email like "%' . $n . '%"';
            //$where .= 'or pl.patronymic like "%' . $n .'%"';
        }

        if ($tied) {
            $where .= ' AND pl.tied=' . $tied;
        }

        if ($comId == 0 && $comId != null) {
            $where .= ' AND cm.command_id is null';
        }

        if ($comId > 0) {
            $where .= ' AND cm.command_id=' . $comId;
        }

        $maxPl = count(Players::find()->from(['players as pl'])
            ->join('left join', 'pl_to_com ptc', 'ptc.player_id = pl.player_id')
            ->join('left join', 'commands cm', 'cm.command_id=ptc.command_id')
            ->join('left join', 'user_to_players utp', 'utp.player_id=pl.player_id')
            ->join('left join', 'user usr', 'usr.id=utp.user_id')
            ->where($where)
            ->asArray()
            ->all());


        $players = Players::find()->from(['players as pl'])
            ->select([
                "pl.player_id as plId",
                "position.type as pos",
                "name",
                "surename",
                "patronymic",
                "photo",
                "birthday",
                "stature",
                "weight",
                "pl.status",
                "usr.email",
                "FB",
                "VK",
                'cm.title as cmTitle',
                'cm.logo as cmLogo',
            ])
            ->join('left join', 'pl_to_com ptc', 'ptc.player_id = pl.player_id')
            ->join('left join', 'commands cm', 'cm.command_id=ptc.command_id')
            ->join('left join', 'position', 'position.position_id=ptc.position_id')
            ->join('left join', 'user_to_players utp', 'utp.player_id=pl.player_id')
            ->join('left join', 'user usr', 'usr.id=utp.user_id')
            ->distinct(true)
            ->where($where)
            ->andWhere($andwhere)
            ->limit($limit)
            ->offset($offset * $limit)
            ->orderBy('name')
            ->asArray()
            ->all();

        $ps = [];
        $is = 0;
        /*
                for($p=0;$p < $maxPl; $p++){
                    if(isset($players[$p])) {
                        if (array_search($players[$p]['plId'], $ps)) {
                            array_splice($players, $p, 1);
                            $maxPl = count($players);
                        } else {
                            $ps[$is++] = $players[$p]['plId'];
                        }
                    }
                }
        */
        //$ret['t'] = $ps;

        $ret['players'] = $players;

        $lim = 0;
        for ($i = 25; $i < $maxPl; $i += $i) {
            $limits[$lim++] = $i;
        }
        $limits[$lim] = $maxPl;

        $offsets = [
            'prev' => $offset == 0 ? null : $offset - 1,
            'curr' => $limit == $maxPl ? 1 : intval($offset),
            'next' => $limit > count($players) ? null : $offset + 1
        ];


        $ret['filters'] = [
            'commands' => Commands::find()->select(['command_id as comId', 'title'])->orderBy('title')->asArray()->all(),
            'offset' => $offsets,
            'limit' => $limits
        ];

        $this->answer = $ret;
    }

    public function actionRequest($id = null)
    {
        if ($id == null) {
            $req = [
                'add' => [],
                'edit' => []
            ];
            $index = 0;
            foreach (PlayersEd::find()->select([
                'player_ed as id',
                "name",
                "surename",
                "patronymic",
                'command_id'
            ])->where('player_id is null')->asArray()->all() as $k => $pl) {
                $req['add'][$index++] = [
                    'player' => $pl,
                    'command' => Commands::find()
                        ->select([
                            'CONCAT(title ," (", cit.name, ")") as title',
                            'logo',
                            'command_id',
                            'state'
                        ])
                        ->join('inner join', 'cities cit', 'cit.id=commands.city_id')
                        ->where(['command_id' => $pl['command_id']])
                        //->andWhere('state = "on"')
                        ->asArray()
                        ->one()
                ];
            }
            $index = 0;
            foreach (PlayersEd::find()->select([
                "player_ed as id",
                "name",
                "surename",
                "patronymic",
                'command_id'
            ])->where('player_id is not null')->asArray()->all() as $k => $pl) {
                $req['edit'][$index++] = [
                    'player' => $pl,
                    'command' => Commands::find()
                        ->select([
                            'CONCAT(title ," (", cit.name, ")") as title',
                            'logo',
                            'command_id',
                            'state'
                        ])
                        ->join('inner join', 'cities cit', 'cit.id=commands.city_id')
                        ->where(['command_id' => $pl['command_id']])
                        //->andWhere('state = "on"')
                        ->asArray()
                        ->one()
                ];
            }
        } else {
            $req = PlayersEd::find()
                ->select([
                    "player_id",
                    "name",
                    "surename",
                    "patronymic",
                    "photo",
                    "position_id",
                    "birthday",
                    "stature",
                    'leg',
                    "weight",
                    "status",
                    "FB",
                    "VK",
                    "phone",
                ])
                ->where(['player_ed' => $id])
                ->asArray()
                ->one();
        }
        return $this->answer = $req;
    }

    public function actionAccept($id)
    {
        $req = PlayersEd::find()
            ->select([
                "player_id",
                "name",
                "surename",
                "patronymic",
                "photo",
                "position_id",
                "birthday",
                "stature",
                'leg',
                "weight",
                "status",
                "FB",
                "VK",
                "phone",
            ])
            ->where(['player_ed' => $id])
            ->asArray()
            ->one();

        if ($req != null && $req['player_id'] != null && $out = Players::find()
                ->select([
                    "name",
                    "surename",
                    "patronymic",
                    "photo",
                    "position_id",
                    "birthday",
                    "stature",
                    "weight",
                    "status",
                    "FB",
                    "VK",
                    "phone",
                ])
                ->where(['player_id' => $req['player_id']])
                ->one()) {

            foreach ($out as $k => $v) {
                if ($k != 'tied' && $k != 'created_at' && $k != 'updated_at') {
                    $out->$k = $req[$k];
                }
            }
            if ($out->update()) {
                $r = PlayersEd::findOne($id);
                $r->delete();
                $this->answer = true;
                $this->message = ErrorType::answer_true_update[$this->locale];
                return;
            } else {
                $this->error = true;
                $this->message = ErrorType::not_update[$this->locale];
            }
        } else if ($req != null && $req['player_id'] == null) {
            $out = new Players();

            foreach ($out as $k => $v) {
                if ($k != 'tied' && $k != 'created_at' && $k != 'updated_at') {
                    $out->$k = $req[$k];
                }
            }
            if ($out->save()) {
                $r = PlayersEd::findOne($id);
                $r->delete();
                $this->answer = true;
                $this->message = ErrorType::answer_true_add[$this->locale];
                return;
            } else {
                $this->error = true;
                $this->message = ErrorType::not_add[$this->locale];
            }
        }
        $this->error = true;
        $this->message = ErrorType::not_add[$this->locale];
    }

    public function actionDecline($id)
    {
        if ($req = PlayersEd::findOne($id)) {
            if ($req->delete()) {
                $this->answer = true;
                $this->message = ErrorType::answer_true_delete[$this->locale];
                return;
            } else {
                $this->error = true;
                $this->message = ErrorType::answer_false_delete[$this->locale];
            }
        }
        $this->error = true;
        $this->message = ErrorType::answer_false_delete[$this->locale];

    }

    public function actionAdd()
    {
        $player = new Players();
        if (count($this->body) > 0) {
            foreach ($this->body as $k => $v) {
                if ($v == null && $v == '' && $k != 'VK' && $k != 'FB' && $k != 'phone' && $k != 'patronymic' && $k != 'photo') {
                    $this->error = true;
                    $this->message = 'Is value of ' . $k . 'empty';
                    return;
                } else {
                    $player->$k = trim($v);
                }
            }
            if ($player->save()) {
                $this->answer = true;
                $this->message = ErrorType::answer_true_add[$this->locale];
            } else {
                $this->error = true;
                $this->message = ErrorType::not_add[$this->locale];
            }
        } else {
            $this->error = true;
            $this->message = ErrorType::not_add[$this->locale];
        }
    }

    public function actionUpdate($id)
    {
        if ($player = Players::findOne($id)) {
            if (count($this->body) > 0) {
                foreach ($this->body as $k => $v) {
                    if ($v == null && $v == '' && $k != 'VK' && $k != 'FB' && $k != 'phone' && $k != 'patronymic' && $k != 'photo') {
                        $this->error = true;
                        $this->message = 'Is value of ' . $k . 'empty';
                        return;
                    } else {
                        $player->$k = trim($v);
                    }
                }
                if ($player->update()) {
                    $this->answer = true;
                    $this->message = ErrorType::answer_true_add[$this->locale];
                } else {
                    $this->error = true;
                    $this->message = ErrorType::not_add[$this->locale];
                }
            } else {
                $this->error = true;
                $this->message = ErrorType::not_add[$this->locale];
            }
        } else {
            $this->error = true;
            $this->message = 'Player not found';
        }
    }
}
