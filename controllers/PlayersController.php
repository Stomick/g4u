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
use app\components\HelpFunction;
use app\components\UploadImage;
use app\models\admin\MergePersonal;
use app\models\admin\MergePlayers;
use app\models\admin\Players;
use app\models\admin\PositionInField;
use app\models\LikedPlayers;
use app\models\LoginForm;
use app\models\News;
use app\models\SignupForm;
use app\models\statistic\PlayersToGame;
use app\models\User;
use function Symfony\Component\Debug\Tests\testHeader;
use yii\filters\auth\CompositeAuth;
use app\components\HttpBearerAuthG4U;
use app\components\AuthG4U;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;

use yii\rest\Controller;
use yii\web\Response;

class PlayersController extends Controller
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
                } else {
                    $this->locale = $this->user->app_loc;
                    $this->persona = MergePersonal::findOne(['user_id' => $this->user->id]);
                    $this->player = MergePlayers::findOne(['user_id' => $this->user->id]);
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

    private function getPlayers($limit, $offset,$type, $name, $tied, $liked = false){
        $where = 'players.player_id !=0';
        $andwhere = '1';

        if($name != null){
            $n = $name;
            $where = 'CONCAT (LCASE(name) ,\' \',LCASE(surename) , \' \' , (patronymic)  ) like "%' . strtolower(iconv(mb_detect_encoding($n),"UTF-8//IGNORE",$n)) .'%"';
            //$where .= 'or email like "%' . $n .'%"';
            //$where .= 'or pl.patronymic like "%' . $n .'%"';
        }
        if($type){
            $andwhere = 'position_id=' . intval($type);
        }

        $tied == null ? $where = 'players.tied='.$tied : $where .= ' AND players.tied='.$tied;

        if($liked){
            if($this->player != null) {
                $likeId = [];

                foreach (LikedPlayers::find()->where(['user_id' => $this->user->id])->all() as $k => $like) {
                    $likeId[$k] = $like->player_id;
                }
                if (count($likeId) > 0) {
                    $andwhere = 'players.player_id in (' . join(',', $likeId) . ')';
                } else {
                    return 1;
                }
            }else{
                return [];
            }
        }
        return  Players::find()
            ->select([
                "players.player_id",
                "name",
                "surename",
                "patronymic",
                "photo",
                "position.type as position",
                "position.desc as desc",
                "com.title as comTitle",
                "birthday",
                "stature",
                "weight",
                "leg",
                "FB",
                "VK",
                "phone",
            ])
            ->join('left join' , 'position' , 'position.position_id=players.position_id')
            ->join('left join' , 'user_to_players utp' , 'utp.player_id=players.player_id')
            ->join('left join' , 'pl_to_com plc' , 'plc.player_id=players.player_id')
            ->join('left join' , 'commands com' , 'com.command_id=plc.command_id')
            ->join('left join' , 'user usr' , 'usr.id=utp.user_id')
            ->where($where)
            ->andWhere($andwhere)
            ->limit(intval($limit))
            ->offset($offset)
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    public function actionIndex($limit = 20, $offset = 0,$type = null, $name = null, $tied = null)
    {
        $this->answer = self::getPlayers($limit, $offset,$type, $name, $tied);
    }

    public function actionAdd()
    {
        $player = new Players();
        if (count($this->body) > 0) {
            foreach ($this->body as $k => $v) {
                if ($v == null && $v == '' && $k != 'VK' && $k != 'FB' && $k != 'patronymic' && $k != 'phone' && $k != 'photo') {
                   return $this->error = true && $this->message = 'Is value of ' . $k . ' empty';
                }elseif ($k == 'photo'){
                    $player->photo = UploadImage::save_image($v , $k,'img/player/' . \Yii::$app->security->generateRandomString(32) . '/');
                }
                else {
                    $player->$k = $v;
                }
            }
            if(!MergePlayers::find()->where(['user_id' => $this->user->id])->one()) {
                if ($player->save()) {
                    $mergePers = new MergePlayers();
                    $mergePers->user_id = intval($this->user->id);
                    $mergePers->player_id = intval($player->player_id);
                    return $mergePers->save() ? $this->answer = $player->player_id : $this->error = true && $this->message = $mergePers->errors;
                } else {
                    return $this->error = true && $this->message = $player->errors;
                }
            }else {
                return $this->error = true && $this->message = 'Request is already send ';
            }
        } else {
            $this->error = true;
            $this->message = 'Empty info of player';
        }
    }

    public function actionList($limit = 20, $offset = 0, $type = null, $name = null, $tied = null){
        $ret = [];
        $ret['liked'] = self::getPlayers(200, 0,null, null, null , true);
        $pl = self::getPlayers($limit, $offset*$limit,$type, $name, $tied);
        $ret['all'] = $pl;

        $ret['filters'] = [
            'prev' => $offset == 0 ? null : $offset - 1,
            'current' => $offset,
            'next' => count($pl) == $limit ? $offset+1 : null
        ];

        $this->answer = $ret;
    }

    public function  actionLike($id){
        if(!$like = LikedPlayers::find()->where(['player_id' => $id, 'user_id' => $this->user->id ] )->one()){
            $like = new LikedPlayers();
            $like->player_id = intval($id);
            $like->user_id = $this->user->id;
            if($like->save()){
                $this->answer=true;
                $this->message = ErrorType::answer_true_add[$this->locale];
            }
        }else{
            $like->delete();
            $this->answer=true;
            $this->message = ErrorType::answer_true_delete[$this->locale];

         return;
        }
        $this->error = true;
        $this->message = ErrorType::answer_false_delete[$this->locale];
    }

    public function actionInfo($id){
        $ret['liked'] = LikedPlayers::find()->where(['player_id' => $id, 'user_id' => $this->user->id ] )->one() ? true:false;
        if ($ret['info'] = Players::find()->from('players pl')
            ->select([
                'pl.photo',
                'cm.title',
                'ptc.number',
                'ps.desc',
                'pl.name',
                'pl.surename',
                'pl.patronymic',
                'DATE_FORMAT(pl.birthday, "%d.%m.%Y") as birthday',
                'pl.stature',
                'pl.weight',
                'pl.leg',
                'pl.phone',
                'pl.FB',
                'pl.VK'

            ])
            ->join('left join', 'pl_to_com ptc', 'ptc.player_id=pl.player_id')
            ->join('left join', 'commands cm', 'cm.command_id=ptc.command_id')
            ->join('left join', 'position ps', 'ps.position_id=ptc.position_id')
            ->where(['pl.player_id' => $id])->asArray()->one()) {
            $ret['info']['age'] = HelpFunction::getDuration(date("Y-m-d",strtotime($ret['info']['birthday'])), date("Y-m-d"));
        }else{
            $ret['info'] = [];
        }
        $ret['games'] = [];
        if ($games = PlayersToGame::find()->from('pl_to_game plg')
            ->select([
                'sea.title as seaTitle',
                'sub.title as tourTitle',
                'cm.title as comTitle',
                'cm.logo as comLogo',
                'pos.type as plPos',
                'cm.command_id as comId',
                'plc.number',
                '(SELECT COUNT(*) FROM game_statistic gst WHERE cm.command_id=gst.command_id) as gCount',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.player_id=' . $id . ' AND command_id=cm.command_id AND `type_event_id`=1) as goal',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.player_id=' . $id . ' AND command_id=cm.command_id AND `type_event_id`=12) as penalty',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.assist_id=' . $id . ' AND command_id=cm.command_id AND `assist_type_id`= 1) as assist',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.assist_id=' . $id . ' AND command_id=cm.command_id AND `type_event_id`= 1) as score',
            ])
            ->join('inner join', 'games gm', 'gm.game_id=plg.game_id')
            ->join('inner join', 'players pl', 'pl.player_id=plg.player_id')
            ->join('left join', 'pl_to_com plc', 'plc.player_id=plg.player_id')
            ->join('left join', 'commands cm', 'cm.command_id=plc.command_id')
            ->join('left join', 'position pos', 'pos.position_id=plc.position_id')
            ->join('inner join', 'tournaments_sub sub', 'sub.sub_tournament_id=gm.sub_tournament_id')
            ->join('inner join', 'seasons sea', 'sea.season_id=sub.season_id')
            ->where(['plg.player_id' => $id])
            ->distinct()
            ->asArray()
            ->all()) {

            foreach ($games as $k => $game) {
                $ret['games'][$k] = $game;
            }
        }

        return $this->answer = $ret;
    }
    public function actionInfofromgame($id){
        if ($ret['info'] = Players::find()->from('players pl')
            ->select([
                'pl.photo',
                'cm.title',
                'ptc.number',
                'ps.desc',
                'pl.name',
                'pl.surename',
                'pl.birthday',
                'pl.stature',
                'pl.weight',
                'pl.leg'

            ])
            ->join('left join', 'pl_to_com ptc', 'ptc.player_id=pl.player_id')
            ->join('left join', 'commands cm', 'cm.command_id=ptc.command_id')
            ->join('inner join', 'position ps', 'ps.position_id=ptc.position_id')
            ->where(['pl.player_id' => $id])->asArray()->one()) {
            $ret['info']['age'] = HelpFunction::getDuration($ret['info']['birthday'], date("Y-m-d"));
        }
        $ret['games'] = [];
        if ($games = PlayersToGame::find()->from('pl_to_game plg')
            ->select([
                'sea.title as seaTitle',
                'sub.title as tourTitle',
                'cm.title as comTitle',
                'cm.logo as comLogo',
                'pos.type as plPos',
                'cm.command_id as comId',
                'plc.number',
                '(SELECT COUNT(*) FROM game_statistic gst WHERE cm.command_id=gst.command_id) as gCount',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.player_id=' . $id . ' AND command_id=cm.command_id AND `type_event_id`=1) as goal',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.player_id=' . $id . ' AND command_id=cm.command_id AND `type_event_id`=12) as penalty',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.assist_id=' . $id . ' AND command_id=cm.command_id AND `assist_type_id`= 1) as assist',
                '(SELECT COUNT(*) FROM `events` as ev WHERE ev.assist_id=' . $id . ' AND command_id=cm.command_id AND `type_event_id`= 1) as score',
            ])
            ->join('inner join', 'games gm', 'gm.game_id=plg.game_id')
            ->join('inner join', 'players pl', 'pl.player_id=plg.player_id')
            ->join('left join', 'pl_to_com plc', 'plc.player_id=plg.player_id')
            ->join('left join', 'commands cm', 'cm.command_id=plc.command_id')
            ->join('left join', 'position pos', 'pos.position_id=plc.position_id')
            ->join('inner join', 'tournaments_sub sub', 'sub.sub_tournament_id=gm.sub_tournament_id')
            ->join('inner join', 'seasons sea', 'sea.season_id=sub.season_id')
            ->where(['plg.player_id' => $id])
            ->distinct()
            ->asArray()
            ->all()) {

            foreach ($games as $k => $game) {
                $ret['games'][$k] = $game;
            }
        }

        return $this->answer = $ret;
    }
}
