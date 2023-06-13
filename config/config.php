<!-- 
本アプリケーションの主設定ページ
現在の設定状況を表示
 -->

<?php 
session_start();
require_once('../read.php');
$show = 0;
$config[] = [
  'reserve_begin' => '',
  'reserve_end' => '',
  'start' => '',
  'end' => '',
  'frame' => '',
  'holiday' => ''
];

$config = getConfig();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  <div class="config">
    <p>
      予約開始月： <?php if($config['reserve_begin'] === 0){
                          echo '当月から';
                        }else{
                          echo $config['reserve_begin'] . 'ヶ月後から';} ?>
    </p>
    <p>
      予約表示上限月： <?php echo $config['reserve_end'] . 'ヶ月後'; ?>
    </p>
    <p>
      受付開始時間： <?php echo $config['start']; ?>
    </p>
    <p>
      受付終了時間： <?php echo $config['end']; ?>
    </p>
    <p>
      予約時間枠： <?php echo $config['frame']; ?> 分単位
    </p>
    <?php $holiday =  getWeekday($config['holiday']);?>
    <p>休日：<?php echo $holiday;?></p>

    <a class="btn btn-primary" href="setting.php" role="button">設定の変更</a>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>