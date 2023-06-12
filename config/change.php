<?php 
session_start();
require_once('../read.php');
$complete = '';

// 設定データを取得
$config = getConfig();
$full = intval($config['full']);
$little = intval($config['little']);
$now = date("Y-m-d ");

// 取得した日付でオブジェクト作成
$date = new DateTime($now);  
$selected_date = $date->format("Y-m-d ");

// 予約時間枠を取得
$frame = getTime($selected_date);

if(isset($_GET['id'])){
  // URLパラメータから日付を取得
  $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve'])){
  // フォームの値を取得してDBを更新する
  $form = [];
  $form['last_name'] = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['first_name'] = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['reserve'] = filter_input(INPUT_POST, 'reserve', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['reserve'] = str_replace('T', ' ', $form['reserve']); // Tをスペースに置換
  $form['tel'] = filter_input(INPUT_POST, 'tel', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
  
  $db = dbConnect();
  $update = $db->prepare('UPDATE reserve SET last_name=?, first_name=?, reserve=?, tel=?, email=? WHERE id=?');
  if(!$update){
    die($db->error);
  }
  $update->bind_param('sssssi',$form['last_name'], $form['first_name'], $form['reserve'], $form['tel'], $form['email'], $id);
  $success = $update->execute();
  if(!$success){
    die($db->error);
  }else{
    $complete = 'ok';
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/reserve.css">
  <title>Reserve</title>
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
            <a class="nav-link" aria-current="page" href="config.php">Config</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- navi end -->

  <div class="container">
    <div class="row py-4 px-1">
      <div class="col">
        <!-- 予約内容を表示 -->
        <?php
        $db = dbConnect();
        // 指定された予約情報を取得
        $get_reserve = $db->prepare('SELECT reserve, last_name, first_name, tel, email FROM reserve WHERE id=?');
        if(!$get_reserve){
          die($db->error);
        }
        $get_reserve->bind_param('i', $id);
        $success = $get_reserve->execute();
        if(!$success){
          die($db->error);
        }
        $get_reserve->bind_result($reserve, $last_name, $first_name, $tel, $email);
        $get_reserve->fetch();
        ?>

        <!-- 各項目に予約内容を反映させる -->
        <form action="" method='POST'>
          <div class="mb-7 row py-3">
            <label for="last_name" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-5">
              <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $last_name;?>">
            </div>
            <div class="col-sm-5">
              <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $first_name;?>">
            </div>
          </div>

          <div class="mb-7 row py-3">
            <label for="reserve" class="col-sm-2 col-form-label">Date</label>
            <div class="col-sm-10">
              <?php $div = 60 * intval($config['frame']); // 設定で指定した予約枠の時間×60で、datetime-localのstep値を算出 ?>
              <input type="datetime-local" class="form-control" id="reserve" name="reserve" step="<?php echo $div;?>" value="<?php echo $reserve;?>">
            </div>
          </div>
          <div class="mb-7 row py-3">
            <label for="tel" class="col-sm-2 col-form-label">Tel</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="tel" name="tel" value="<?php echo $tel;?>">
            </div>
          </div>
          <div class="mb-7 row py-3">
            <label for="email" class="col-sm-2 col-form-label">E-Mail</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="email" name="email" value="<?php echo $email;?>">
            </div>
          </div>

          <input type="submit" class="btn btn-primary" value="予約変更">
          <a href="delete.php?id=<?php echo $id;?>" class="btn btn-primary">予約取消</a>
          <a href="calendar.php" class="btn btn-primary">戻る</a>
          <?php if($complete === 'ok'): ?>
            <p class="error">予約情報を更新しました。</p>
          <?php endif; ?>
        </form>
      </div>

      <!-- 現在の時間帯別の予約件数を表示（予約日変更対応用） -->
      <div class="col">
        <?php 
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])){
          $request = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
        }else{
          $format = new DateTime($reserve);
          $request = $format->format("Y-m-d");
        }
        ?>
        <div class="mb-7 row py-3">
          <form action="" method="post">
            <label for="date">予約状況を確認</label>
            <input type="date" name="date" value="<?php echo $request;?>">
            <input type="submit" class="btn btn-primary" value="予約表示">
          </form>
        </div>
        <div class="mb-7 row py-3">
          <h4>
            <?php
            // 予約開始時刻のオブジェクト作成
            $reserve_begin = new DateTime($request . $config['start']);
            echo $reserve_begin->format("Y年n月j日");
            ?>
          </h4>
          <div class="p-0 bd-highlight">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col">予約日時</th>
                  <th scope="col">予約人数</th>
                </tr>
              </thead>
              <?php 
              $times = timeArray();
              foreach($times as $time):
                $date_time = new DateTime($request . ' ' . $time);
                $reserve_time = $date_time->format("Y-m-d H:i:s");
                ?>
              <tbody>
                <tr>
                  <th scope="row" class="reserve_th"><?php echo $time; ?></th>
                  <?php 
                    // 予約件数を取得
                    $reserve_count = reserveCount($reserve_time);
                    $count = $reserve_count - $full;
                    if($count === 0):?>
                      <td class="reserve_td"><?php echo $reserve_count;?> 件</td>
                    <?php else: ?>
                      <td class="reserve_td"><?php echo $reserve_count;?> 件</td>
                    <?php endif; ?>
                  </td>
                </tr>
              </tbody>
              <?php endforeach;?>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>  

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>