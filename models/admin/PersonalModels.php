<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 23.07.2018
 * Time: 16:30
 */

namespace app\models\admin;


use yii\base\Model;

class PersonalModels extends Model
{
    public $name;
    public $surename;
    public $patronymic;
    public $photo;
    public $type_id;
    public $league_id;


    public function rules()
    {
        //`name`, `surename`, `patronymic`, `photo_id`, `number`, `position_id`, `birthday`, `stature`, `weight`, `FB`, `VK`, `phone`,
        return [
            [['name', 'surename'], 'required'],
            [['name', 'surename', 'patronymic'], 'string', 'min' => 2, 'max' => 255],
            ['photo', 'string'],
            [['type_id'] , 'integer']
        ];
    }
    public function save(){
        $player = new Personal();
        foreach ($this as $k => $val){
            $player->$k = $val;
        }
        return $player->save();
    }
}