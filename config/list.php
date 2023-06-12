<?php 
session_start();
require_once('../read.php');

if(isset($_GET['datetime'])){
  // URLパラメータから日付を取得
  $get_date = filter_input(INPUT_GET, 'datetime', FILTER_SANITIZE_SPECIAL_CHARS);
  $get_date = str_replace('_', ' ', $get_date);
}else{
  header('Location: calendar.php');
  exit();
}
// 設定データを取得
$config = getConfig();
$full = intval($config['full']);
$little = intval($config['little']);

// 取得した日付でオブジェクト作成
$date = new DateTime($get_date);  
$selected_date = $date->format("Y-m-d ");

// 予約時間枠を取得
$frame = getTime($selected_date);

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
    <div class="row py-5 gy-3">
      <div class="col">
        <!-- 予約一覧を表示 -->
        <div class="mb-3 row">
          <?php
          $db = dbConnect();
          $get_reserve = $db->prepare('SELECT id, last_name, first_name FROM reserve WHERE reserve=? ORDER BY id');
          if(!$get_reserve){
            die($db->error);
          }
          $get_reserve->bind_param('s', $get_date);
          $success = $get_reserve->execute();
          if(!$success){
            die($db->error);
          }
          
          $get_reserve->bind_result($id, $last_name, $first_name);
          ?>
          <ul class="reserve_list">
            <h3><?php echo $get_date . ' 予約一覧';?></h3>
            <?php while($get_reserve->fetch()):?>
            <li class="reserved">
              <?php echo $last_name . ' ' . $first_name . ' 様';?><a href="change.php?id=<?php echo $id; ?>" class="delete">予約詳細</a>
            </li>
            <?php endwhile;?>
          </ul>
        </div>
      </div>

      <div class="col">

      </div>
    </div>
  </div>  

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>