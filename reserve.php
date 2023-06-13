<!-- 
カレンダーで指定した日付から1日で予約可能な範囲を表示
４日先まで表示している。

現在の予約状況をカウントして、予約可能、予約残り僅か、予約不可の判定をしている。
休日設定している日は予約不可の判定
 -->

<?php 
session_start();
session_regenerate_id();
require_once('read.php');
// URLパラメータから日付を取得
$get_date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
$Ymd = str_replace('-', '/', $get_date);
$success = isYmd($Ymd); // 日付形式であるか正規表現チェック
if(!$success){
    header('Location: index.php');
    exit();
}
$week = ['日', '月', '火', '水', '木', '金', '土'];
// 設定データを取得
$config = getConfig();
$full = intval($config['full']);
$little = intval($config['little']);

// 取得した日付でオブジェクト作成
$date = new DateTime($get_date);  
$selected_date = $date->format("Y-m-d ");

// 予約時間枠を取得
$frame = getTime($selected_date);

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
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/reserve.css">
  <title>Reserve</title>
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


  <div class="container px-1">
    <div class="row gx-3">
      <?php for($date_count = 0; $date_count < 4; $date_count++): ?>
        <div class="col">
          <div class="d-flex flex-column bd-highlight border-primary mb-3">
            <h3>
              <?php
              // 予約開始時刻のオブジェクト作成
              $reserve_begin = new DateTime($selected_date . $config['start']);
              $reserve_begin->modify('+' . $date_count . 'days');
              echo $reserve_begin->format("Y年n月j日") . '(' . $week[$reserve_begin->format("w")] . ')';
              ?>
            </h3>
            <?php 
            $times = timeArray();
            foreach($times as $time):
              $reserve = new DateTime($reserve_begin->format("Y-m-d ") . $time);
              $reserve_day = $reserve->format("Y-m-d");
              $reserve_time = $reserve->format("Y-m-d H:i:s");
            ?>
              <div class="p-0 bd-highlight">
                <ul class="time-schedule">
                  <li>
                    <span class="time">
                      <?php echo $time; ?>
                    </span>
                    <div class="sch_box">
                      <p class="sch_title">
                        <?php 
                          // 予約件数を取得
                          $reserve_count = reserveCount($reserve_time);
                          // 予約件数と予約最大枠の差分を出して一致していれば予約不可とする。
                          $count = $reserve_count - $full;

                          // 現在時刻を取得して過去の時間の場合も予約不可の処理を入れる
                          $now = date("Y-m-d H:i");

                          // 店休日を取得して該当日を予約不可の処理を入れる
                          $weekday = $reserve->format("w");
                          $weekday = strval($weekday);
                          $rest = preg_match('/' . $weekday . '/', $config['holiday']);  // 該当日が設定した休みと一致したか確認

                          if($count === 0 || $now > $reserve_time || $rest === 1){
                            echo '<a class="ng"><i class="bi bi-x-lg"></i> : 予約不可</a>';
                          }else{
                            if(preg_match('/7/', $config['holiday']) === 1 && in_array($reserve_day, $holidays)){
                              // 祝日が店休日だった場合は予約不可にする
                              echo '<a class="ng"><i class="bi bi-x-lg"></i> : 予約不可</a>';
                            }else{
                              $count = ($full - $little) - $reserve_count;  // 残り予約数に応じて表示を切り替える
                              if($count > 0){
                                echo '<a href="user.php?datetime=' . $reserve->format("Y-m-d_H:i:s") . '" class="ok"><i class="bi bi-circle"></i> : 予約可能</a>';
                              }else{
                                echo '<a href="user.php?datetime=' . $reserve->format("Y-m-d_H:i:s") . '" class="triangle"><i class="bi bi-triangle"></i> : 残りわずか</a>';
                              }
                            }
                          }
                          ?>
                      </p>
                    </div>
                  </li>
                </ul>
              </div>
            <?php endforeach;?>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>