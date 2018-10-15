<?php
// Тип содержимого
var_dump('test');
foreach (\app\models\statistic\PlayersInCommand::find()->all() as $p => $plaer){
        if($pl = \app\models\admin\Players::findOne($plaer->player_id)) {
            $plaer->position_id = $pl->position_id;
            $plaer->created_at  = strtotime('now');
            print_r($plaer->update());

        }
}
?>


