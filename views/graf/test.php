<?php
// Тип содержимого
var_dump('test');
foreach (\app\models\statistic\PlayersInCommand::find()->all() as $p => $player){
        if($pl = \app\models\admin\Players::findOne($player->player_id)) {
            $player->position_id = $pl->position_id;
            $player->created_at  = strtotime('now');
            print_r($player->update());

        }
}
?>


