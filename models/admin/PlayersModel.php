<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 23.07.2018
 * Time: 16:30
 */

namespace app\models\admin;


use yii\base\Model;

class PlayersModel extends Model
{
    public $name;
    public $surename;
    public $patronymic;
    public $photo;
    public $number;
    public $position_id;
    public $birthday;
    public $stature;
    public $weight;
    public $FB;
    public $VK;
    public $phone;


    public function rules()
    {
        //`name`, `surename`, `patronymic`, `photo_id`, `number`, `position_id`, `birthday`, `stature`, `weight`, `FB`, `VK`, `phone`,
        return [
            [[`name`, `surename`], 'required'],
            [[`name`, `surename`, `patronymic`], 'string', 'min' => 2, 'max' => 255],
            [['FB' , 'VK'] , 'string'],
            ['birthday', 'date'],
            [[`stature`, `weight`],'double'],
            ['photo', 'string'],
            [['phone' , 'number'] , 'integer']
        ];
    }
    public function save(){
        $player = new Players();
        foreach ($this as $k => $val){
            $player->$k = $val;
        }
        return $player->save();
    }
}