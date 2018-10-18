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
use app\components\UploadImage;
use app\models\admin\MergePersonal;
use app\models\admin\MergePlayers;
use app\models\admin\Personal;
use app\models\Leagues;
use app\models\LikedNews;
use app\models\News;
use app\components\HttpBearerAuthG4U;
use app\models\SubLeagues;
use app\models\User;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

class NewsController extends Controller
{
    private $answer;
    private $error = false;
    private $body;
    private $persona;
    private $message;
    private $user;
    private $locale;
    private $player;

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
                    if($pers  = MergePersonal::findOne(['user_id' => $this->user->id])){
                        $this->persona = Personal::findOne($pers->personal_id);
                    }

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

    public function actionLike(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if($like = LikedNews::find()->where(['news_id' => $this->body['id'] , 'user_id' => $this->user->id])->one()){
            return $like->delete() ? $this->answer = false : $this->error = true;
        }else {
            $like = new LikedNews();
            $like->ip = $ip;
            $like->news_id = $this->body['id'];
            $like->user_id = $this->user->id;
            return $like->save() ? $this->answer = true : $this->error = true;
        }
    }

    public function actionList($id = null,$limit = 20, $offset = 0)
    {
        $ret = [];
        if ($id) {
            if ($ret = News::find()->select([
                "news.news_id as id",
                "title",
                "text",
                "tags",
                "news.photo",
                "video",
                "CONCAT(p.surename,' ' , p.name) as name",
                "news.created_at as date"

            ])
                ->join('inner join', 'personals p', 'p.personal_id=maker')
                ->where(['news.news_id' => $id])
                //->andWhere('status = "adopted"')
                ->asArray()
                ->one()) {

                $ret['tags'] = json_decode($ret['tags']);
            }
        } else {
            foreach (News::find()->select([
                "news.news_id",
                "title",
                "text",
                "tags",
                "video",
                "maker",
                "IF(lk.like_id is null, false , true) as liked",
                "news.photo",
                "IF(utp.personal_id = maker, true ,false) as mkd",
                "CONCAT(p.surename,' ' , p.name) as name",
                "DATE_FORMAT(FROM_UNIXTIME(`news`.`created_at`), '%H:%i %d-%m-%Y') as date"
            ])
                         ->join('left join', 'personals p', 'p.personal_id=maker')
                         ->join('left join', 'user_to_personal utp', 'utp.user_id='.$this->user->id)
                         ->join('left join','liked_news lk', 'lk.user_id=' . $this->user->id .' AND lk.news_id=news.news_id')
                         ->limit($limit)
                         ->offset($offset)
                         ->where('news.league_id=' . $this->user->league_id . ' OR news.league_id=0')
                         ->asArray()
                         ->orderBy('news.created_at DESC')
                         ->all() as $k => $news) {
                $ret[$k] = $news;
                $ret[$k]['tags'] = json_decode($news['tags']);
            };
        }
        $this->answer = $ret;
    }

    public function actionDate($id)
    {
        return $this->answer = $id;
    }

    public function actionAdd()
    {
        if ($this->persona == null || $this->persona->type_id != 1) {

        }else if ($news = new News()) {

            $news->text = $this->body['text'];
            $news->tags = json_encode($this->body['tags']);
            $news->video = isset($this->body['video']) ? $this->body['video'] : null;
            $news->title = $this->body['title'];
            $this->answer= $news->photo = UploadImage::save_image($this->body['photo'] , 'news_' . date("His"), 'img/news'. date("Y-m-d").'/');

            $news->maker = $this->persona->personal_id;
            $news->league_id = $this->user->league_id;
            if($news->save()){
                $this->answer = true;
                $this->message = ErrorType::answer_true_add[$this->locale];
                return;
            } else{
                $this->error = true;
                $this->message = ErrorType::not_add[$this->locale];
                return;
            }
        }
        if($this->user->type == 'global' || $this->user->type == 'superadmin'){
            if ($news = new News()) {

                $news->text = $this->body['text'];
                $news->tags = json_encode($this->body['tags']);
                $news->video = isset($this->body['video']) ? $this->body['video'] : null;
                $news->title = $this->body['title'];
                $this->answer= $news->photo = UploadImage::save_image($this->body['photo'] , 'news_' . date("His"), 'img/news'. date("Y-m-d").'/');
                $news->maker = $this->persona->personal_id;
                $news->league_id = 0;
                $news->priority = 1;
                if($news->save()){
                    $this->answer = true;
                    $this->message = ErrorType::answer_true_add[$this->locale];
                    return;
                }else{
                    $this->error = true;
                    $this->message = ErrorType::not_add[$this->locale];
                    return;
                }
            }
        }
        $this->error = true;
        $this->message = 'You not journalist';
        return;

    }

    public function actionUpdate($id)
    {
        if ($news = News::findOne($id)) {
                if($news->maker == $this->persona->personal_id) {
                    foreach ($this->body as $k => $value) {
                        $news->$k = $value;
                    }

                    if ($news->update()) {
                        $this->answer = true;
                        $this->message = ErrorType::answer_true_update[$this->locale];
                    } else {
                        $this->error = true;
                        $this->message = "Not update";
                    }
                }else{
                    $this->error = true;
                    $this->message = "Your not a maker for this news";
                }
            }
        else {
            $this->error = true;
            $this->message = "News not found";
        }
    }

    public function actionDelete($id)
    {
        if ($news = News::findOne($id)) {
                if($news->maker == $this->persona->personal_id) {
                    if ($news->delete()) {
                        $this->answer = true;
                        $this->message = ErrorType::answer_true_delete[$this->locale];
                    } else {
                        $this->error = true;
                        $this->message = "Not delete";
                    }
                }else{
                    $this->error = true;
                    $this->message = "Your not a maker for this news";
                }
            }
        else {
            $this->error = true;
            $this->message = "News not found";
        }
    }
}
