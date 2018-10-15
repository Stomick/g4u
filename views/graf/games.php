<?php
//echo '<pre>';
//print_r($games);
//echo '</pre>';
?>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<style>
    body{
        background: none;
    }
    h2{
        text-align: center;
    }
    .img-fluid{
        width: 50px;
        height: 50px;
    }
    .row{
        border: 1px solid grey;
    }
</style>
<div>
    <?php foreach ($games as $k => $game){
        $info = $game['info']['tour'];
        $comIn = $game['info']['in'];
        $comOut = $game['info']['out'];
        $events = $game['info']['events'];
        ?>
    <div class="container">
            <h2 >Турнир "<?= $info['title'] . '" ' .  $info['date'] . ' Тур №' . $info['tour']?> </h2>
        <div class="row">
            <div class="col-sm-5">
                <h2><?= $comIn['comm']['title']?>
                <img class="img-fluid" src="<?= $comIn['comm']['logo']?>">
                </h2>
            </div>

                <div class="col-sm-5">
                <h2><?= $comOut['comm']['title']?>
                <img class="img-fluid" src="<?= $comOut['comm']['logo']?>">
                </h2>
            </div>
            <?php
            if(count($events['in']) < count($events['out'] )){
                $ind = count($events['out']);
            }
            else{
                $ind = count($events['in']);
            }
             for ($i = 0 ; $i < $ind; $i++){
            ?>
                <div class="col-sm-6">
                    <?php if(count($events['in']) > 0 && isset($events['in'][$i])){ $ev = $events['in'][$i];?>
                        <span>`<?=$ev['minute']?></span>
                        <span><?=$ev['evTitle']?></span>
                        <span><?=$ev['plName'] == 'Неизвестен' ? '' : $ev['plName']?></span>
                        <span><?=$ev['asTitle'] == 'Неизвестен' ? '' : $ev['asTitle'] ?></span>
                        <span><?=$ev['psName'] == 'Неизвестен' ? '' : $ev['psName']?></span>
                    <?php }?>
                </div>
                <div class="col-sm-6">
                    <?php if(count($events['out']) > 0 && isset($events['out'][$i])){ $ev = $events['out'][$i];?>
                        <span>`<?=$ev['minute']?></span>
                        <span><?=$ev['evTitle']?></span>
                        <span><?=$ev['plName'] == 'Неизвестен' ? '' : $ev['plName']?></span>
                        <span><?=$ev['asTitle'] == 'Неизвестен' ? '' : $ev['asTitle'] ?></span>
                        <span><?=$ev['psName'] == 'Неизвестен' ? '' : $ev['psName']?></span>
                    <?php }?>
                </div>
            <?php }?>
        </div>
    </div>
    <?php }?>
</div>