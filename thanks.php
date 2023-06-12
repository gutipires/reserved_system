<?php 
session_start();
require_once('read.php');
$error = [];

if(isset($_SESSION['form'])){
  $form = $_SESSION['form'];
  unset($_SESSION['form']);
}else{
  header('Location:calendar.php');
  exit();
}

$date_time = new DateTime($form['reserve']);
$datetime = $date_time->format("Y/n/j（D） G:i"); // 表示用の予約日時を作成

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
  <title>Reservation completion</title>
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
  
  <div class="col py-4 px-5">
    <h2 class="thx">ご予約ありがとうございます。</h2>
    <p class="result">
      ご予約日：<?php echo $datetime;?>
    </p>
    <p class="result">
      氏名：<?php echo $form['last_name'] . ' ' . $form['first_name'];?>
    </p>
    <p class="result">
      電話番号：<?php echo $form['tel']; ?>
    </p>
    <p class="result">
      メールアドレス：<?php echo $form['email']; ?>
    </p>
    <p class="result">パスワード：【表示されません】</p>
  </div>

  <a class="btn btn-primary thxbtn" href="index.php" role="button">TOPページへ</a>
  <a class="btn btn-primary change" href="myreserve.php" role="button">予約を取り消す</a>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>