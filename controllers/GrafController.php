<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 18.07.2018
 * Time: 15:25
 */

namespace app\controllers;

use app\components\AccessType;
use app\components\CompAuthG4U;
use app\components\ErrorType;
use app\components\HelpFunction;
use app\components\SaveImage;
use app\components\UploadImage;
use app\models\admin\MergePersonal;
use app\models\admin\MergePlayers;
use app\models\admin\Personal;
use app\models\admin\Players;
use app\models\Commands;
use app\models\Games;
use app\models\Leagues;
use app\models\LikeNews;
use app\models\News;
use app\components\HttpBearerAuthG4U;
use app\models\statistic\EventsInGame;
use app\models\SubLeagues;
use app\models\Tournaments;
use app\models\User;
use Codeception\Events;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;
use linslin\yii2\curl;
use keltstr\simplehtmldom\SimpleHTMLDom as SHD;

class GrafController extends Controller
{
    private $file;
    private $filename;

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        /*$behaviors['authenticator'] = [
            'class' => CompAuthG4U::className(),
            'authMethods' => [
                HttpBearerAuthG4U::className()
            ],
        ];
*/
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/zip' => Response::FORMAT_RAW,
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
        /*
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
        */

        return parent::beforeAction($action);

    }

    public function afterAction($action, $result)
    {
        if ($this->file) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $this->filename . '"');
            header('Content-Length: ' . filesize($this->file));
            readfile($this->file);
            exit();
        } else {
            return parent::afterAction($action, $result); // TODO: Change the autogenerated stub
        }

    }


    public function actionPhoto()
    {

        //return $this->render('game');
    }

    public function actionTest()
    {

        return $this->render('test');
    }

    public function actionIndex()
    {

        $url = 'https://www.facebook.com/media/set/?set=a.308314276425970&type=1&l=f60c7ddd6d';
        $this->layout = 'clear';
        //$img = HelpFunction::getRealurl('https://www.facebook.com/media/set/?set=a.308314276425970&type=1&l=c774ff892f');

        $curl = new curl\Curl();
        //$response = json_decode($curl->get(), true);

        $options = [
            'http' => [
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            ]
        ];

        $context = stream_context_create($options);
        $html_source = SHD::file_get_html($url, false, $context);

        $element = $html_source->find('a');
        foreach ($element as $k => $el) {
                if(isset($el->attr['data-sigil']) && $el->attr['data-sigil'] == 'photoset_thumbnail') {
                    foreach (SHD::file_get_html("https://www.facebook.com/" . $el->href, false, $context)->find('meta') as $v => $t) {
                        if(isset($t->attr['property']) && $t->attr['property'] == 'og:image') {
                            echo '<pre>';
                            var_dump($t->attr['content']);
                            echo '</pre>';
                        }
                    }
                }

        }

            /*
            $img = htmlspecialchars_decode(str_replace('&#123;' , '{' , str_replace('&#125;' , '}' , $element->attr["data-store"])));


*/



        //echo($html_source);
    }

    public function actionGame($id, $test = null)
    {
        $this->layout = 'graf';
        $games = [];
        if ($game = Games::find()
            ->select([
                'from_unixtime(date / 1000, \'%d.%m.%Y\') as date',
                'command_id_in',
                'command_id_out',
                'cm_in.color_in',
                'cm_out.color_out',
                'tour',
                'lgb.title',
                'stb.title as stitle'
            ])
            ->where(['game_id' => intval($id)])
            ->join('inner join', 'commands as cm_in', 'cm_in.command_id=command_id_in')
            ->join('inner join', 'commands as cm_out', 'cm_out.command_id=command_id_out')
            ->join('inner join', 'tournaments_sub lgb', 'lgb.sub_tournament_id=games.sub_tournament_id')
            ->join('left join', 'stadiums stb', 'stb.stadiums_id=games.stadiums_id')
            ->asArray()
            ->one()) {
            $games = [
                'info' => [
                    'tour' => $game['tour'],
                    'in' => Commands::find()->select([
                        'title_min as title',
                        'logo',
                        'command_id'
                    ])->where(['command_id' => $game['command_id_in']])->asArray()->one(),
                    'out' => Commands::find()->select([
                        'title_min as title',
                        'logo',
                        'command_id'
                    ])->where(['command_id' => $game['command_id_out']])->asArray()->one()
                ],
            ];
        }
        $eventIn = EventsInGame::find()
            ->select([
                'cmd.command_id as cmId',
                'events.type_event_id as type',
                'events.minute',
                'cmd.logo',
                'events.player_id as plId',
                'plcPl.number as plNumb',
                'plcAs.number as asNumb',
                'IF(pl.name IS NOT NULL , pl.name, " ")  as plName',
                'IF(pl.surename IS NOT NULL , pl.surename, events.comment)  as plSure',
                'IF(as.name IS NOT NULL , as.name, " ")  as asName',
                'IF(as.surename IS NOT NULL , as.surename, events.comment)  as asSure',
                'as.player_id as asId',
                'et.title etTitle'

            ])
            ->join('inner join', 'commands as cmd', 'cmd.command_id=events.command_id')
            ->join('left join', 'pl_to_com as plcPl', 'plcPl.player_id=events.player_id')
            ->join('left join', 'pl_to_com as plcAs', 'plcAs.player_id=events.assist_id')
            ->join('left join', 'players as pl', 'pl.player_id=plcPl.player_id')
            ->join('left join', 'players as as', 'as.player_id=plcAs.player_id')
            ->join('left join', 'events_type as et', 'et.type_event_id=events.type_event_id')
            ->where(['game_id' => $id])->orderBy('minute')->asArray()->all();

        /*
                $eventOut = EventsInGame::find()
                    ->select([
                        'events.type_event_id as type',
                        'events.minute',
                        'cmd.logo',
                        'plcPl.number as plNumb',
                        'plcAs.number as asNumb',
                        'pl.name as plName',
                        'pl.surename as plSure',
                        'as.name as asName',
                        'as.surename as asSure',
                        'as.player_id as asId'

                    ])

                    ->join('inner join' , 'commands as cmd' , 'cmd.command_id=events.command_id')
                    ->join('inner join' , 'pl_to_com as plcPl' , 'plcPl.player_id=events.player_id')
                    ->join('left join' , 'pl_to_com as plcAs' , 'plcAs.player_id=events.assist_id')
                    ->join('inner join' , 'players as pl' , 'pl.player_id=plcPl.player_id' )
                    ->join('left join' , 'players as as' , 'as.player_id=plcAs.player_id' )
                    ->where(['game_id' => $id , 'events.command_id' => $game['command_id_out']])->orderBy('minute')->asArray()->all();
        */

        $graf = new \app\components\makeGraf\CreateGraff();
        $graf->deleteDir('./img/graf/' . $id);
        $goal = ['in' => 0, 'out' => 0];
        $tmp_in = 10;
        $tmp_out = 0;
        $files = [];
        $ind = 0;
        if ($games != null) {

            $files[$ind++] = $graf->grafGamesScore($id,
                $goal['in'], $goal['out'],
                strtoupper($games['info']['in']['title']),
                strtoupper($games['info']['out']['title']),
                0,
                $game['color_in'],
                $game['color_out']
            );
            /*
            echo '<pre>';
            var_dump($eventIn);
            echo '</pre>';
            */
            if (count($eventIn) > 0) {
                foreach ($eventIn as $k => $pl) {
                    if ($pl['etTitle'] == 'Goal') {
                        if ($pl['cmId'] == $game['command_id_in']) {
                            $goal['in']++;
                            $files[$ind++] = $graf->grafGamesScore($id,
                                $goal['in'], $goal['out'],
                                strtoupper($games['info']['in']['title']),
                                strtoupper($games['info']['out']['title']),
                                0,
                                $game['color_in'],
                                $game['color_out']
                            );
                        }
                        if ($pl['cmId'] == $game['command_id_out']) {
                            $goal['out']++;
                            $files[$ind++] = $graf->grafGamesScore($id,
                                $goal['in'], $goal['out'],
                                strtoupper($games['info']['in']['title']),
                                strtoupper($games['info']['out']['title']),
                                0,
                                $game['color_in'],
                                $game['color_out']
                            );
                        }
                        $files[$ind++] = $graf->grafGamesGoal($id,
                            $pl['logo'],
                            $pl['plNumb'],
                            $pl['asNumb'],
                            substr($pl['plName'], '0', 1) . '.' . strtolower(iconv(mb_detect_encoding($pl['plSure']), "UTF-8//IGNORE", $pl['plSure'])),
                            $pl['asName'] == null ? $pl['asName'] : substr($pl['asName'], '0', 1) . '.' . strtolower(iconv(mb_detect_encoding($pl['asSure']), "UTF-8//IGNORE", $pl['asSure'])),
                            $pl['minute'] < 10 ? '0' . $pl['minute'] : $pl['minute']);
                    }

                    if ($pl['type'] == 2 || $pl['type'] == 3) {
                        $files[$ind++] = $graf->grafGamesCart(
                            $id,
                            $pl['logo'],
                            $pl['plNumb'],
                            substr($pl['plName'], '0', 1) . '. ' . $pl['plSure'],
                            $pl['minute'],
                            $pl['type'] == 2 ? 'yelow' : 'red');
                    }
                };
            }/*
            $files[$ind++] = $graf->grafGamesResult(
                $id,
                $games['info']['in']['logo'],
                $games['info']['out']['logo'],
                $games['info']['in']['title'],
                $games['info']['out']['title'],
                $game['title'],
                $game['date'],
                intval($game['tour']),
                $game['stitle']);
*/
            $files[$ind++] = $graf->grafGames(
                $id,
                $games['info']['in']['logo'],
                $games['info']['out']['logo'],
                $games['info']['in']['title'],
                $games['info']['out']['title'],
                $game['title'],
                $game['date'],
                intval($game['tour']),
                $game['stitle']);
            $files[$ind++] = $graf->grafGamesScreen(
                $id,
                $games['info']['in']['logo'],
                $games['info']['out']['logo'],
                $games['info']['in']['title'],
                $games['info']['out']['title'],
                $game['title'],
                $game['date'],
                intval($game['tour']),
                $game['stitle']);
            $files[$ind++] = $graf->grafGamesAnons(
                $id,
                $games['info']['in']['logo'],
                $games['info']['out']['logo'],
                $games['info']['in']['title'],
                $games['info']['out']['title'],
                $game['title'],
                $game['date'],
                intval($game['tour'])
            );
        }
        if ($test == null) {
            $this->filename = $id . '.zip';
            $this->file = HelpFunction::create_zip($_SERVER['DOCUMENT_ROOT'] . '/img/graf/' . $id, $_SERVER['DOCUMENT_ROOT'] . '/img/graf/zip/' . $this->filename);
        } else {
            $this->layout = 'clear';
            return $this->render('game', ['file' => $files]);
        }
    }
}
