<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 19.07.2018
 * Time: 17:06
 */

namespace app\models;

/*
 * @property $the_end integer
 * */

use app\models\admin\Asgmt;
use app\models\statistic\CommandPosInTour;
use app\models\statistic\CommandToTourn;
use app\models\statistic\EventsInGame;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Games extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%games}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        /*
        foreach (CommandToTourn::find()->where(['sub_tournament_id' => $this->sub_tournament_id])->all() as $k => $g){
            if(!$com = CommandPosInTour::find()->where(['command_id' => $g->command_id , 'sub_tournament_id' => $this->sub_tournament_id])->one()){
                $com = new CommandPosInTour();
                $com->command_id = $g->command_id;
                $com->sub_tournament_id = $this->sub_tournament_id;
                $com->save();
            }
        }
*/
        if ($this->the_end == 1) {
            $in = false;
            $out = false;
            if (!$gameIn = GameStatistic::find()->where(['game_id' => $this->game_id, 'sub_tournament_id' => $this->sub_tournament_id, 'command_id' => $this->command_id_in])->one()) {
                $gameIn = new GameStatistic();
                $out = true;
            }
            if (!$gameOut = GameStatistic::find()->where(['game_id' => $this->game_id, 'sub_tournament_id' => $this->sub_tournament_id, 'command_id' => $this->command_id_out])->one()) {
                $gameOut = new GameStatistic();
                $in = true;
            }
            if ($gameOut != null && $gameIn != null) {
                $gameOut->game_id = $gameIn->game_id = $this->game_id;

                $gameOut->sub_tournament_id = $gameIn->sub_tournament_id = $this->sub_tournament_id;

                $gameOut->command_id = $this->command_id_out;
                $gameIn->command_id = $this->command_id_in;

                $gameIn->scored = EventsInGame::find()->where(['game_id' => $this->game_id,
                    'command_id' => $this->command_id_in,
                    'type_event_id' => 1])
                    ->asArray()->count() + EventsInGame::find()->where(['game_id' => $this->game_id,
                    'command_id' => $this->command_id_out,
                    'type_event_id' => 13])
                    ->asArray()->count();

                $gameOut->scored = EventsInGame::find()->where(['game_id' => $this->game_id,
                    'command_id' => $this->command_id_out,
                    'type_event_id' => 1])->asArray()->count() + EventsInGame::find()->where(['game_id' => $this->game_id,
                    'command_id' => $this->command_id_in,
                    'type_event_id' => 13])->asArray()->count();

                $gameOut->missed = $gameIn->scored;
                $gameIn->missed = $gameOut->scored;
                $in ? $gameIn->save() : $gameIn->update();
                $out ? $gameOut->save() : $gameOut->update();
                $subIn = Games::find()->where(
                    [
                        'sub_tournament_id' => (Games::findOne($this->game_id))->sub_tournament_id,
                        'the_end' => 1
                    ])
                    ->andWhere('command_id_in=' . $gameIn->command_id . ' or command_id_out=' . $gameIn->command_id)
                    ->all();

                $subOut = Games::find()->where(
                    [
                        'sub_tournament_id' => (Games::findOne($this->game_id))->sub_tournament_id,
                        'the_end' => 1
                    ])
                    ->andWhere('command_id_in=' . $gameOut->command_id . ' or command_id_out=' . $gameOut->command_id)
                    ->all();
                $gamsIn = [];

                foreach ($subIn as $k => $g) {
                    $gamsIn[$k] = $g->game_id;
                }

                $gamsOut = [];
                foreach ($subOut as $k => $g) {
                    $gamsOut[$k] = $g->game_id;
                }
                $pos = false;
                if (!$posIn = CommandPosInTour::find()->where(['command_id' => $gameIn->command_id, 'sub_tournament_id' => $gameIn->sub_tournament_id])->one()) {
                    $posIn = new CommandPosInTour();
                    $pos = true;
                }
                $posIn->sub_tournament_id = $this->sub_tournament_id;
                $posIn->command_id = $this->command_id_in;
                $posIn->scored = 0;
                $posIn->missed = 0;
                foreach (GameStatistic::find()->where(['sub_tournament_id' => $this->sub_tournament_id, 'command_id' => $this->command_id_in])->all() as $s => $stat) {
                    $posIn->scored += $stat->scored;
                    $posIn->missed += $stat->missed;
                }

                $pos ? $posIn->update(): $posIn->save();
                $pos = false;
                if (!$posOut = CommandPosInTour::find()->where(['command_id' => $gameOut->command_id, 'sub_tournament_id' => $gameOut->sub_tournament_id])->one()) {
                    $posOut = new CommandPosInTour();
                    $pos = true;
                }

                $posOut->scored = 0;
                $posOut->missed = 0;
                foreach (GameStatistic::find()->where(['sub_tournament_id' => $this->sub_tournament_id, 'command_id' => $this->command_id_out])->all() as $s => $stat) {
                    $posOut->scored += $stat->scored;
                    $posOut->missed += $stat->missed;
                }
                $posOut->sub_tournament_id = $this->sub_tournament_id;
                $posOut->command_id = $this->command_id_out;

                $pos ? $posOut->update() : $posOut->save();
                $point = 0;
                foreach (GameStatistic::find()->where(['command_id' => $posIn->command_id, 'sub_tournament_id' => $posIn->sub_tournament_id])->all() as $k => $goals) {
                    $dis = $goals->scored - $goals->missed;
                    if ($dis > 0) {
                        $point += 3;
                    } elseif ($dis == 0) {
                        $point += 1;
                    }
                }

                $posIn->pts = $point;

                $point = 0;
                foreach (GameStatistic::find()->where(['command_id' => $posOut->command_id, 'sub_tournament_id' => $posOut->sub_tournament_id])->all() as $k => $goals) {
                    $dis = $goals->scored - $goals->missed;
                    if ($dis > 0) {
                        $point += 3;
                    } elseif ($dis == 0) {
                        $point += 1;
                    }
                }

                $posOut->pts = $point;

                $posOut->update();
                $posIn->update();
            }
        }

        foreach (CommandPosInTour::find()->where(['sub_tournament_id' => $this->sub_tournament_id])->orderBy('pts DESC')->all() as $k => $comm) {
            if ($comm->old_position < $k) {
                $comm->status = 'up';
                $comm->old_position = $comm->position;
            } else if ($comm->old_position > $k) {
                $comm->status = 'down';
                $comm->old_position = $comm->position;
            } else {
                $comm->status = 'unchanged';
                $comm->old_position = $comm->position;
            }
            $comm->position = $k;
            $comm->update();

/*
            $comPosT = CommandPosInTour::find()->select(['position_id', 'pts', 'position', 'old_position', 'command_id'])->where(['sub_tournament_id' => $this->sub_tournament_id])->asArray()->orderBy('pts DESC')->all();

                    for ($i = 0; $i < count($comPosT) ; $i++ ){
                        for ($j = $i + 1; $j < count($comPosT) ; $j++ ){
                            if($comPosT[$j]['pts'] == $comPosT[$i]['pts']){
                                $gameJ  = Games::find()->where(['sub_tournament_id' => $this->sub_tournament_id])
                                    ->where('the_end=1')
                                    ->andWhere('`command_id_in`='.$comPosT[$j]['command_id'].' or `command_id_out`=' . $comPosT[$j]['command_id'])
                                    ->asArray()->count();
                                $gameI  = Games::find()->where(['sub_tournament_id' => $this->sub_tournament_id])
                                    ->where('the_end=1')
                                    ->andWhere('`command_id_in`='.$comPosT[$i]['command_id'].' or `command_id_out`=' . $comPosT[$i]['command_id'])
                                    ->asArray()->count();
                                if($gameI < $gameJ){
                                    $up = CommandPosInTour::find()->where(['sub_tournament_id' => $this->sub_tournament_id , 'command_id' => $comPosT[$j]['command_id']])->one();
                                    $ud = CommandPosInTour::find()->where(['sub_tournament_id' => $this->sub_tournament_id , 'command_id' => $comPosT[$j]['command_id']])->one();
                                    $up->old_position = $up->position;
                                    $tmp  = $up->position;
                                    $up->position = $ud->position;
                                    $ud->old_position = $ud->position;
                                    $ud->position = $tmp;
                                    $ud->update();
                                    $up->update();
                                    $i=0;
                                }
                            }
                        }
                    }
*/
        }

        return parent::update($runValidation, $attributeNames); // TODO: Change the autogenerated stub
    }

    public function delete()
    {
        if ($as = Asgmt::find()->where(['game_id' => $this->game_id])->one()) {
            $as->delete();
        }
        return parent::delete(); // TODO: Change the autogenerated stub
    }
}
