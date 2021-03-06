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
use app\components\SubFunction;
use app\models\admin\Asgmt;
use app\models\admin\MergePersonal;
use app\models\admin\MergePlayers;
use app\models\admin\Personal;
use app\models\admin\Players;
use app\models\admin\PositionInField;
use app\models\admin\Seasons;
use app\models\City;
use app\models\Commands;
use app\models\Country;
use app\models\Games;
use app\models\GameStatistic;
use app\models\Leagues;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\statistic\CommandPosInTour;
use app\models\statistic\CommandToTourn;
use app\models\statistic\EventsInGame;
use app\models\statistic\PlayersInCommand;
use app\models\SubLeagues;
use app\models\SubTournaments;
use app\models\Tournaments;
use app\models\User;
use app\components\HttpBearerAuthG4U;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

class TournamentsController extends Controller
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

    public function actionListcities()
    {
        $countrioies = [];

        foreach (Leagues::find()->select(['country_id', 'leagues_id'])->distinct('country_id')->where(['status' => 1])->all() as $k => $v) {
            $countrioies[$k] = Country::find()->select(['nicename as name'])->where(['iso' => $v->country_id])->asArray()->one();
            $countrioies[$k]['city'] = [];

            foreach (SubLeagues::find()->select(['sub_leagues_id', 'city_id'])->distinct('cyti_id')->where(['leagues_id' => $v->leagues_id])->all() as $c => $city) {
                $countrioies[$k]['city'][$c] = City::find()->select(['name', 'id as cId'])->where(['id' => $city->city_id])->asArray()->one();
                $cities = 1;
                foreach (Tournaments::find()
                             ->select([
                                 'tournament_id',
                                 'title',
                             ])
                             ->where([
                                 'sub_league_id' => $city->sub_leagues_id,
                                 'show_in_app' => 1
                             ])->all() as $t => $tour) {
                    $countrioies[$k]['city'][$c]['count'] = $cities++;
                }
            }
        }
        $this->answer = $countrioies;
    }

    public function actionListtours($id)
    {
        foreach (SubLeagues::find()->select(['sub_leagues_id'])->where(['city_id' => $id])->all() as $k => $value) {
            $this->answer = Tournaments::find()
                ->select([
                    'tournament_id',
                    'title',
                ])
                ->where([
                    'sub_league_id' => $value->sub_leagues_id,
                    'show_in_app' => 1
                ])->asArray()->all();
        }
    }

    public function actionListsubtours($id)
    {
        $logo = null;
        if ($sub = Tournaments::findOne($id)) {
            $logo = SubLeagues::find()
                ->select(['photo'])
                ->where([
                        'sub_leagues_id' => $sub->sub_league_id]
                )->join('inner join', 'leagues as lg', 'lg.leagues_id=leagues_sub.leagues_id')
                ->asArray()->one();
        };
        $this->answer['logo'] = $logo['photo'];
        foreach (Seasons::find()
                     ->select([
                         "season_id",
                         "tournament_id",
                         "status",
                         "title"])
                     ->where(['tournament_id' => $id])->asArray()->all() as $k => $t) {
            $this->answer['seasons'][$k] = $t;
            foreach (SubTournaments::find()
                         ->select(['sub_tournament_id as tourneyId', 'title'])
                         ->where(['season_id' => $t['season_id']])->asArray()->all() as $s => $sea) {
                $this->answer['seasons'][$k]['tourney'][$s] = $sea;
            }
        }
    }

    public function actionListsubcomm($id)
    {
        $logo = null;
        $this->answer['info'] = SubTournaments::find()->select(['title'])->where(['sub_tournament_id' => $id])->asArray()->one();
        $this->answer['commands'] = self::getCommands($id);


    }


    public function actionComminfo($id)
    {
        $comId = explode(":" , $id)[1];
        $tourId = explode(":" , $id)[0];

        $this->answer['info'] = Commands::find()->select([
            'command_id as cId',
            'title',
            'logo',
            'color_in as colorIn',
            'color_out as colorOut'
        ])->where([
            'command_id' => $comId
        ])->asArray()
            ->one();

        $players = [];
        $tmpPl = PlayersInCommand::find()->select(['pl.player_id as id',
            'CONCAT(pl.name, " " , pl.surename) as name',
            //    'photo'
        ])->join('inner join' , 'players as pl' , 'pl.player_id=pl_to_com.player_id')
            ->where(['command_id' => $comId])->orderBy('name')->asArray()->all();
        $games = [];

        $statis = CommandPosInTour::find()
            ->select([
                'position',
                'pts',
                'CONCAT(win , "-", draw,"-" , lose) as stat',
                'scored',
                'missed',
                '(scored - missed) as diff',
                'status',
            ])
            ->where(['sub_tournament_id' => $tourId , 'command_id' => $comId])->asArray()->one();
        $statis['cGame'] = Games::find()->where(['sub_tournament_id' => $tourId , 'the_end' => 1])->andWhere('command_id_in=' . $comId . ' or command_id_out=' . $comId)->asArray()->count();
        $this->answer['stat'] = $statis;
        foreach (Games::find()
                     ->select([
                         'game_id',
                         'IF(date is NULL,0 , date ) date',
                         'cm_in.title as inTitle',
                         'cm_out.title as outTitle',
                         'IF((SELECT COUNT(*) FROM `events` as ev WHERE ev.game_id=games.game_id AND command_id=cm_in.command_id AND `type_event_id`=1) <= (SELECT COUNT(*) FROM `events` as ev WHERE ev.`game_id`=games.game_id AND command_id=cm_out.command_id AND `type_event_id`= 1) 
                         ,cm_out.command_id,cm_in.command_id) as winId',
                         'st.title as stTitle',
                         'CONCAT((SELECT COUNT(*) FROM `events` as ev WHERE ev.game_id=games.game_id AND command_id=cm_in.command_id AND `type_event_id`=1),
                         ":",
                         (SELECT COUNT(*) FROM `events` as ev WHERE ev.`game_id`=games.game_id AND command_id=cm_out.command_id AND `type_event_id`= 1)) 
                         as score
                         ',
                     ])
                     ->join('left join', 'stadiums as st', 'st.stadiums_id=games.stadiums_id')
                     ->join('left join', 'commands cm_in', 'cm_in.command_id=command_id_in')
                     ->join('left join', 'commands cm_out', 'cm_out.command_id=command_id_out')
                     ->where('`sub_tournament_id`='.$tourId.' AND  games.the_end=1')
                     ->andWhere('cm_out.command_id=' . $comId . ' or cm_in.command_id=' . $comId)
                     ->orderBy('games.created_at')
                     ->asArray()
                     ->all() as $g => $game) {

            $games[$g] = $game;
            foreach ($tmpPl as $p => $pl) {
                $tmpPl[$p] = $pl;
                if(!isset($tmpPl[$p]['goal'])){
                    $tmpPl[$p]['goal'] = 0;
                }
                $goal = EventsInGame::find()->where(['game_id' => $game['game_id'], 'type_event_id' => 1 , 'player_id' => $pl['id']])
                    ->asArray()->count();
                $tmpPl[$p]['goal'] += $goal;

                if(!isset($tmpPl[$p]['assist'])){
                    $tmpPl[$p]['assist'] = 0;
                }
                $assist = EventsInGame::find()->where(['game_id' => $game['game_id'], 'type_event_id' => 1 , 'assist_id' => $pl['id']])
                    ->asArray()->count();
                $tmpPl[$p]['assist'] += $assist;
                if(!isset($tmpPl[$p]['game'])){
                    $tmpPl[$p]['game'] = 0;
                }
                $tmpPl[$p]['game'] += 1;
            }
        }


        $this->answer['players'] = $tmpPl;
        $this->answer['lastgame'] = $games;
    }

    public function actionListgames($id){
        $tours = [];
        foreach (Games::find()->select([
            'from_unixtime(date / 1000, \'%d.%m.%Y\') as date',
            'from_unixtime(date / 1000, \'%d.%m.%Y %h:%m\') as dateG',
            'command_id_in',
            'command_id_out',
            '(SELECT COUNT(*) FROM `events` as ev WHERE ev.game_id=games.game_id AND command_id=cm_in.command_id AND `type_event_id`=1) as goalIn',
            '(SELECT COUNT(*) FROM `events` as ev WHERE ev.`game_id`=games.game_id AND command_id=cm_out.command_id AND `type_event_id`= 1) as goalOut',
            '(SELECT COUNT(*) FROM `events` as ev WHERE ev.`game_id`=games.game_id AND command_id=cm_out.command_id AND `type_event_id`= 13) as ownIn',
            '(SELECT COUNT(*) FROM `events` as ev WHERE ev.`game_id`=games.game_id AND command_id=cm_in.command_id AND `type_event_id`= 13) as ownOut',
            'tour',
            'the_end',
            'stb.title as stitle',
            'games.game_id'
        ])
                     ->join('inner join', 'commands as cm_in', 'cm_in.command_id=command_id_in')
                     ->join('inner join', 'commands as cm_out', 'cm_out.command_id=command_id_out')
                     ->join('left join', 'stadiums stb', 'stb.stadiums_id=games.stadiums_id')
                     ->where(['sub_tournament_id' => $id])
                     ->orderBy('tour')->asArray()->all() as $k => $game) {
            if(!isset($tours[$game['tour']]))
            {
                $tours[$game['tour']] = [];
            }
            array_push($tours[$game['tour']],[
                'game_id' => $game['game_id'],
                'tour' => $game['tour'],
                'the_end' => $game['the_end'],
                'date' => $game['dateG'],
                'score' => ($game['ownIn'] + $game['goalIn']) . ':' . ($game['goalOut'] + $game['ownOut']),
                'in' => Commands::find()->select([
                    'CONCAT(title ," (", cit.name, ")") as title',
                    'logo',
                    'command_id',
                ])->join('inner join', 'cities cit', 'cit.id=commands.city_id')
                    ->where(['command_id' => $game['command_id_in']])->one(),
                'out' => Commands::find()->select([
                    'CONCAT(title ," (", cit.name, ")") as title',
                    'logo',
                    'command_id',
                ])->join('inner join', 'cities cit', 'cit.id=commands.city_id')->where(['command_id' => $game['command_id_out']])->one()
            ]);

        }
        $ret = [];
        $index = 0;
        foreach ($tours as $k => $val){
            $ret[$index] = [
                'date' => $k . ' Tour',
                'games' => self::sortGames($val)
            ];
            $index++;
        }
        return $this->answer = $ret;
    }
    public function sortGames($players){
        for ($i=0;$i<count($players) ; $i++){
            for ($j=$i;$j<count($players) ; $j++){
                if($players[$j]['tour'] < $players[$i]['tour']){
                    $tmp = $players[$j];
                    $players[$j] = $players[$i];
                    $players[$i] = $tmp;
                    $i=0;
                }
            }
        }
        return $players;
    }

    public function actionListplayers($id)
    {

        $tmpPl = [];
        foreach (Games::find()->where(['sub_tournament_id' => $id])->all() as $s => $game) {
            foreach (EventsInGame::find()->where(['game_id' => $game->game_id, 'type_event_id' => 1])->andWhere('events.player_id != 0')->all() as $v => $g) {
                if (isset($tmpPl[$g->player_id])) {
                    $tmpPl[$g->player_id] += 1;
                } else {
                    $tmpPl[$g->player_id] = 1;
                }
            }
        }

        $index = 0;
        $playersTOP=[];
        $posT = PositionInField::find()->select(['type' , 'desc'])->orderBy('type')->asArray()->all();
        foreach ($posT as $k => $type){
            $playersTOP[$type['type']] = [];
        };
        foreach ($tmpPl as $k => $goal) {
            $pl = Players::find()
                ->select(['CONCAT(name, " " , surename) as name', 'photo' ,'ps.type' , 'players.player_id as plId' , 'plt.position_id'])
                ->innerJoin('pl_to_com as plt', 'plt.player_id=players.player_id')
                ->innerJoin( 'position as ps' , 'ps.position_id=plt.position_id')
                ->where(['players.player_id' => $k])->asArray()->one();
            array_push($playersTOP[$pl['type']], [
                'player' => $pl,
                'command' => (Commands::find()->select(['title' , 'plt.number'])
                    ->innerJoin('pl_to_com as plt', 'plt.player_id=' . $k)
                    ->where('plt.command_id=commands.command_id')->asArray()->one())
                ,
                'goal' => $goal]);
        }

        $ret = [];
        $index = $tp = 0;
        foreach ($playersTOP as $k => $val){
            if(count($val) != 0) {
                $ret[$index++] = [
                    'type' => $k,
                    'desc' => trim($posT[$tp]['desc']),
                    'players' => $this->sortPlayers($val)
                ];
            }
            $tp++;
        }
        return $this->answer = $ret;
    }

    public function sortPlayers($players){
        for ($i=0;$i<count($players) ; $i++){
            for ($j=$i;$j<count($players) ; $j++){
                if($players[$j]['goal'] > $players[$i]['goal']){
                    $tmp = $players[$j];
                    $players[$j] = $players[$i];
                    $players[$i] = $tmp;
                    $i=0;
                }
            }
        }
        return $players;
    }

    private function getCommands($id)
    {
        if($com = CommandPosInTour::find()
            ->select([
                'com.command_id as comId',
                'position',
                'pts',
                'scored as goals',
                '(scored - missed) as disgoals',
                'com_pos_in_tour.status',
                'com.title',
                'com.logo',
                'com.command_id',

            ])
            ->join('inner join' , 'commands as com' , 'com.command_id=com_pos_in_tour.command_id')
            ->where(['sub_tournament_id' => $id ])
            ->orderBy('pts DESC')
            ->asArray()->all()){
            foreach ($com as $k => $cm){
                $com[$k]['games'] = Games::find()->where(['sub_tournament_id' => $id , 'the_end' => 1])->andWhere('`command_id_in`='.$cm['comId'].'  or `command_id_out`=' . $cm['comId'])->asArray()->count();
            }
        }

        return $com;
    }
    public function actionIndex()
    {
        $Country = [];

    }
}
