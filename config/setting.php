<?php 
session_start();
require_once('../read.php');
$error[] = ['data' => '', 'blank' => ''];

// if(!isset($_SESSION['change'])){
//   header('Location: config.php');
//   exit();
// }

// 予約の設定情報を取得
// 設定データを取得
$config = getConfig();

// 設定変更をした時の処理
if($_SERVER['REQUEST_METHOD'] === 'POST'){

  // 基本設定の値取得
  $config['reserve_begin'] = filter_input(INPUT_POST, 'reserve_begin', FILTER_SANITIZE_NUMBER_INT);
  $config['reserve_end'] = filter_input(INPUT_POST, 'reserve_end', FILTER_SANITIZE_NUMBER_INT);
  $config['start'] = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_SPECIAL_CHARS);
  $config['end'] = filter_input(INPUT_POST, 'end', FILTER_SANITIZE_SPECIAL_CHARS);
  $config['frame'] = filter_input(INPUT_POST, 'frame', FILTER_SANITIZE_NUMBER_INT);
  $config['full'] = filter_input(INPUT_POST, 'full', FILTER_SANITIZE_NUMBER_INT);
  $config['little'] = filter_input(INPUT_POST, 'little', FILTER_SANITIZE_NUMBER_INT);

  // 配列の初期化 
  for($i = 0; $i <= 7; $i++){ // 祝日は7で受け取る
    $checked["holiday"][$i]="";
  }
  if(isset($_POST["holiday"])){
    $i = 0;
    foreach((array)$_POST["holiday"] as $val){
      $checked["holiday"][$i] = strval($val);
      $i++;
    }
    $config['holiday'] = implode($checked['holiday']);
  }
  

  // 人数指定の入力値が不正だった場合の処理
  $success = isInt($config['full']);
  if(!$success){
    $error['data'] = 'injustice';
  }
  $success = isInt($config['little']);
  if(!$success){
    $config['little'] = 0;  // 0で登録する
  }

  // エラー判定をしてエラーがなかったらDBに登録
  if(in_array('-', $config) && $error['data'] === 'injustice'){
    $error['blank'] = 'blank';
  }else{
    $db = dbConnect();
    // 設定を上書きする

    $config_sql = $db->prepare('UPDATE config 
    SET reserve_begin=?, reserve_end=?, start=?,end=?,frame=?,full=?,little=?, holiday=?');
    
    $config_sql->bind_param('iissiiis', 
      $config['reserve_begin'], $config['reserve_end'], $config['start'], $config['end'], $config['frame'], $config['full'], $config['little'], $config['holiday']);
    $success = $config_sql->execute();
    if(!$success){
      die($db->error);
    }

    header('Location: setting.php');
    echo '<script>alert("設定完了");</script>';
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="../css/setting.css">
  <title>Setting</title>
</head>
<body>
  <!-- navi begin -->
  <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
    <div class="container-fluid">
      <a class="navbar-brand" href="../index.php">Top Page</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="calendar.php">Reserve</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="config.php">Config</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- navi end -->

  <div class="container">
    <div class="row">
      <div class="col px-3">
        <div class="change">
          <form action="" method="post">
            <div>
              <p>
                <label for="reserve_begin">予約開始月を指定する：</label>
                <select id="reserve_begin" name="reserve_begin">
                  <option>-</option> 
                  <?php if($config['reserve_begin'] === 0): ?>
                    <option value="0" selected>当月から</option>
                  <?php else: ?>
                    <option value="0">当月から</option>
                  <?php endif; ?>

                  <?php for($i = 1; $i <= 12; $i++): ?>
                    <?php if($config['reserve_begin'] === $i): ?>
                      <option value="<?php echo $i ?>" selected><?php echo $i; ?>ヶ月先から</option>
                    <?php else: ?>
                      <option value="<?php echo $i ?>"><?php echo $i; ?>ヶ月先から</option>
                    <?php endif; ?>
                  <?php endfor; ?>
                </select>
              </p>
            </div>
            <div>
              <p>
                <label for="reserve_end">予約表示上限月を指定する：</label>
                <select id="reserve_end" name="reserve_end">
                  <option>-</option> 
                  <?php for($i = 1; $i <= 12; $i++): ?>
                    <?php if($config['reserve_end'] === $i): ?>
                      <option value="<?php echo $i ?>" selected>＋<?php echo $i; ?>ヶ月後</option>
                    <?php else: ?>
                      <option value="<?php echo $i ?>">＋<?php echo $i; ?>ヶ月後</option>
                    <?php endif; ?>
                    <?php endfor; ?>
                </select>
              </p>
            </div>
            <div>
              <p>
                <label>受付開始時間を指定する：</label>
                <select name="start">
                  <?php 
                  $create_time = date("Y-m-d ") . '00:00';
                  $time = new DateTime($create_time);
                  for($i = 0; $i < 96; $i++):
                    if($i > 0){
                      $time->modify('+15 minute');
                    }
                    $start = $time->format("H:i:s");
                    if($config['start'] === $start):
                    ?>
                      <option value="<?php echo $start; ?>" selected><?php echo $time->format("h:i"); ?></option>
                    <?php else: ?>
                      <option value="<?php echo $start; ?>"><?php echo $time->format("H:i"); ?></option>
                    <?php endif;?>
                  <?php endfor; ?>
                  ?>
                </select>
              </p>
            </div>
            <div>
              <p>
                <label>受付終了時間を指定する：</label>
                <select name="end">
                <!-- 選択肢は00:00~23:30を作成する -->
                <?php 
                  $create_time = date("Y-m-d ") . '00:00';
                  $time = new DateTime($create_time);
                  for($i = 0; $i < 96; $i++):
                    if($i > 0){
                      $time->modify('+15 minute');
                    }
                    $end = $time->format("H:i:s");
                    if($config['end'] === $end):
                  ?>
                      <option value="<?php echo $end; ?>" selected><?php echo $time->format("H:i"); ?></option>
                    <?php else: ?>
                      <option value="<?php echo $end; ?>"><?php echo $time->format("H:i"); ?></option>
                    <?php endif;
                  endfor;  
                  ?>
                </select>
              </p>
            </div>
            <div>
              <p>
                <label for="frame">予約時間枠を指定する：</label>
                <select id="frame" name="frame">
                  <option>-</option> 
                  <!-- 選択肢15分、30分を作成 -->
                  <?php for($i = 1; $i <= 2; $i++):
                    if($i * 15 === $config['frame']):  
                  ?>
                      <option value="<?php echo $i * 15;?>" selected><?php echo $i * 15 ?>分</option>
                    <?php else: ?>
                      <option value="<?php echo $i * 15;?>" ><?php echo $i * 15 ?>分</option>
                    <?php endif; ?>
                  <?php endfor; ?>

                  <!-- 選択肢60分〜900分を作成 -->
                  <?php for($i = 1; $i <= 15; $i++):
                    if($i * 60 === $config['frame']):  
                  ?>
                      <option value="<?php echo $i * 60;?>" selected><?php echo $i * 60 ?>分</option>
                    <?php else: ?>
                      <option value="<?php echo $i * 60;?>" ><?php echo $i * 60 ?>分</option>
                    <?php endif; ?>
                  <?php endfor; ?>
                </select>
              </p>
            </div>
            <div>
              <?php if(isset($error['data']) && $error['data'] === 'injustice'): ?>
                <h5 style = color:red>※1以上の整数を入力してください</h5>
              <?php endif; ?>
              <p>予約上限人数：
                <input class="full" type="text" name="full" value="<?php echo $config['full']; ?>">
              </p>
            </div>
            <div>
              <p>予約枠「△」の閾値：
                <input class="little" type="text" name="little" value="<?php echo $config['little']; ?>">
                <br><br>※△を表示させる場合は整数を入力
              </p>
            </div>
            <div>
              休日の指定
              <div class="container">
                <div class="row py-1">
                  <div class="col py-3">
                    <input type="checkbox" id="0" name="holiday[]" value="0">
                    <label for="0">日</label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="1" name="holiday[]" value="1">
                    <label for="1">月</label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="2" name="holiday[]" value="2">
                    <label for="2">火</label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="3" name="holiday[]" value="3">
                    <label for="3">水</label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="4" name="holiday[]" value="4">
                    <label for="4">木</label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="5" name="holiday[]" value="5">
                    <label for="5">金</label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="6" name="holiday[]" value="6">
                    <label for="6">土<label>
                  </div>
                  <div class="col py-3">
                    <input type="checkbox" id="7" name="holiday[]" value="7">
                    <label for="7">祝</label>
                  </div>
                </div>
              </div>
            </div>
            <?php if(isset($error['blank']) && $error['blank'] === 'blank'): ?>
              <h5 style = color:red>※未選択箇所があります。全てを設定してください。</h5>
            <?php endif; ?>
            <input type="submit" class="btn btn-primary btnset" value="設定する">
            <a class="btn btn-primary btnset" href="config.php" role="button">戻る</a>
         </form>
        </div>
      </div>

      <!-- 設定された内容を表示 -->
      <div class="col px-5">
        <p>現在の設定内容</p>
        <p>
          予約開始月：<?php if($config['reserve_begin'] === 0){
            echo '当月から';
          }else{
            echo $config['reserve_begin'] . 'ヶ月先から';
          };?>
        </p>
        <p>
          予約表示上限月：＋<?php echo $config['reserve_end'];?>ヶ月後
        </p>
        <p>
          受付開始時間：<?php echo $config['start'];?>
        </p>
        <p>
          受付終了時間：<?php echo $config['end'];?>
        </p>
        <p>
          受付時間枠：<?php echo $config['frame'];?> 分
        </p>
        <p>
          予約上限人数：<?php echo $config['full'];?>人
        </p>
        <p>
          残り僅かの閾値：<?php echo $config['little'];?>人
        </p>
        <?php $holiday =  getWeekday($config['holiday']);?>
        <p>休日：<?php echo $holiday;?></p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>