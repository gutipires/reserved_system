<!-- 
カレンダーを表示して予約したい日にちを選択できるように設計
祝日対応
プルダウンで月を指定することができるように設計
プルダウンの表示範囲はconfig>setting.phpの設定を反映  
-->


<?php
require_once('read.php');

// config情報を取得
$config = getConfig();
// 基準となる開始月
$base = new DateTime('+' . $config['reserve_begin'] . ' month');
$base_day = $base->format("Y-m-01");
// プルダウンの変更情報を取得 変更がなければ基準の開始月を表示
$form = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $form = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
  $Ymd = str_replace('-', '/', $form);
  $success = isYmd($Ymd);
  if(!$success){
//      header('Location: index.php');
//      exit();
  }
}else{
  $form = $base_day;
}

$week = ['日', '月', '火', '水', '木', '金', '土'];
$date = new DateTime($form);
$date->modify('+' . $config['reserve_begin'] . ' months'); //表示する年月
$start_date = $date->format('Y-m-01'); //開始の年月日
$end_date = $date->format("Y-m-t"); //終了の年月日

$week_start = new DateTime($start_date);
$start_week = $week_start->format("w");

$week_end = new DateTime($end_date);
$end_week = $week_end->format("w");
$end_week = 6 - $end_week;

// 祝日を配列に格納
$holidays = [];
$holidays =  loadHolidays();

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ReserveSystem</title>
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <!-- navi begin -->
  <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
    <div class="container-fluid">
      <a class="navbar-brand" href="calendar.php">予約システム</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="myreserve.php">MyReserve</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- navi end -->

<!-- calender -->
  <div class="container-calendar">
    <table class="table-calendar" id="calendar" data-lang="ja">
      <!-- 該当月の年月表示 -->
      <tr>
        <td colspan="7" class="center">
          <?php echo $date->format("Y年n月"); ?>
        </td>
      </tr>
<!-- カレンダー作成 -->
    <!-- header設定 -->
      <tr>
        <?php 
        foreach($week as $key => $weekday){
          if($key === 0){ 
            // 日曜日
            echo '<th class="red">' . $weekday. '</th>';
          }elseif($key === 6){
            // 土曜日
            echo '<th class="blue">' . $weekday. '</th>';
          }else{
            // 平日
            echo '<th>' . $weekday . '</th>';
          }
        }
        ?>
      </tr>
    <!-- header設定 -->

  <!-- 日付表示 -->
    <!-- 1日まで空セルにする -->
      <tr>
        <?php for($i = 0; $i < $start_week ; $i++): ?>
          <td></td>
        <?php endfor; ?>
        <!-- 1日まで空セルにする -->
        <!-- 1~月末日まで代入 -->
        <?php for($i = 1; $i <= (int)$date->format('t'); $i++): ?>
          <?php 
          // $iを日付に変換して曜日値を取得する
          $set_date = date("Y-m", strtotime($start_date)) . '-' . sprintf('%02d', $i);          
          $set_week = date("w", strtotime($set_date));
          // css用class設定
          if($set_date === date("Y-m-d")){
            $today = ' today';  // 当日の場合はclass="today"を追加
          }elseif($set_date < date("Y-m-d")){
            $today = ' past';   // 過去の場合はclass='past'を追加
          }else{
            $today = '';
          }
          // css用class設定
          
          $url = '<a href="reserve.php?date=' . $set_date . '">'; // reserve.php urlパラメータ

          // 日付入力開始
          // 土日で色を変える
          if(in_array($set_date, $holidays)){
            echo '<td class="red' . $today .'">' . $url . $i . '</a></td>';
          }elseif((int)$set_week === 0){
            echo '<td class="red' . $today .'">' . $url . $i . '</a></td>';
          }elseif((int)$set_week === 6){
            echo '<td class="blue' . $today . '">' . $url . $i . '</a></td>';
            echo '</tr>';
            echo '<tr>';
          }else{
            echo '<td class="black' . $today . '">' . $url . $i . '</a></td>';
          }
          // 日付入力終了
          ?>
       <?php endfor; ?> 

        <!-- 末日の余りを空白で埋める -->
        <?php for($i = 0; $i < $end_week; $i++): ?>
          <td></td>
        <?php endfor; ?>
      </tr>
    <!-- 1~月末日まで代入 -->
    </table>

    <div class="footer-container-calendar">
      <form action="" method="POST">
        <!-- <label for="month">月を指定する：</label> -->
        <!-- <select id="month" name="month"> -->

        <div class="dropdown">
        <a class="btn btn-info btn-lg dropdown-toggle" href="" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">月を指定</a>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <?php
            $month = new DateTime($base_day);
            for ($i = $config['reserve_begin']; $i < $config['reserve_end']; $i++) :
              // 当月ではない場合は月を加算する
              if ($i > 0) {
                $month->modify('+1 months');
              }
            ?>
              <li><a class="dropdown-item" href="index.php?date=<?php echo $month->format('Y-n-01');?>"><?php echo $month->format('Y年n月');?></a></li>
            <?php endfor; ?>
          </ul>
        </div>

      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>