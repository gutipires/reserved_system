<!-- 
予約の際にお客様情報を入力するページ
ここで指定したメールアドレスとパスワードで
myreserve.phpで予約削除できるように設計。
URLパラメータを変更して予約不可の日程にした場合はcalendar.phpに戻す設定にしており
予約不可状態の時間帯に予約できないよう設計。
 -->
<?php 
session_start();
session_regenerate_id();
require_once('read.php');

$error = [];
$config = getConfig();
if(isset($_GET['datetime'])){ // URLパラメータがある場合の処理
  // パラメータから日付を取得して日付オブジェクトを作成
  $datetime = h($_GET['datetime']);
  $datetime = str_replace('_', ' ', $datetime);
  // パラメータの日時を正規表現チェック
  $ymd = str_replace('-', '/', $datetime);
  $ymd = mb_substr($ymd, 0, 10);
  $success = isYmd($ymd);
  if(!$success){
    header('Location: calendar.php');
    exit();
  }

  $date = new Datetime($datetime);
  $reserve = $date->format("Y-m-d H:i"); // 表示用の予約日時を作成

  // URLパラメータが予約不可だった場合の処理
  // 休日判定
  $weekday = $date->format("w");  
  $weekday = strval($weekday);
  $judge_holiday = preg_match('/' . $weekday . '/', $config['holiday']);  // 該当日が設定した休みと一致したか確認
  // 過去予約判定
  $now = date("Y-m-d H:i");
  // 時間不正判定
  $time = timeArray();
  $str_time = implode("'", $time);
  $urltime = $date->format("H:i");
  $judge_time = preg_match('/' . $urltime . '/', $str_time);

  if($now > $reserve || $judge_holiday === 1 || $judge_time === 0){
    header('Location: calendar.php');
    exit();
  }else{
    $_SESSION['reserve'] = $reserve;
  }
}
$form = [
  'last_name' => '',
  'first_name' => '',
  'tel' => '',
  'email' => '',
  'password' => '',
];

if($_SERVER['REQUEST_METHOD'] === 'POST'){ // フォームからPOSTで値を受信した時の処理
  // フォームデータを配列に格納する。エラー処理も合わせて行う
  // 予約日時
  $form['reserve'] = $_SESSION['reserve'];

  // 姓名
  $form['last_name'] = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['first_name'] = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
  if($form['last_name'] === '' || $form['first_name'] === '' ){
    $error['name'] = 'blank';
  }

  // 電話番号
  $form['tel'] = filter_input(INPUT_POST, 'tel', FILTER_SANITIZE_SPECIAL_CHARS);
  if($form['tel'] === ''){
    $error['tel'] = 'blank';
  }else{
    $success = isTel($form['tel']);
    if(!$success){
      $error['tel'] = 'ng';
    }
  }
  // メールアドレス
  $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  if($form['email'] === ''){
    $error['email'] = 'blank';
  }else{
    $form['email'] = filter_var($form['email'], FILTER_VALIDATE_EMAIL); 
    if(!$form['email']){
      $error['email'] = 'ng';
    }
  }
  // 同時刻に重複で予約していないかをemailで確認する
  $db = dbConnect();
  $count = $db->prepare('SELECT COUNT(*) FROM reserve WHERE reserve=? and email=?');
  if(!$count){
    die($db->error);
  }
  $count->bind_param('ss', $form['reserve'], $form['email']);
  $success = $count->execute();
  if(!$success){
    die($db->error);
  }
  $count->bind_result($cnt);
  $count->fetch();
  // 1件以上登録があればエラーとする
  if($cnt > 0){
    $error['email'] = 'multi';
  }

  // パスワード
  $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
  if($form['password'] === ''){
    $error['password'] = 'blank';
  }elseif(strlen($form['password']) < 4){
    $error['password'] = 'short';
  }

  // 予約件数を取得する
  $reserve_count = reserveCount($form['reserve']);
  if($reserve_count === $config['full']){
    header('Location:sorry.php');
    exit();
  }
  // エラー処理用の配列が空欄だった場合は予約をDBに登録してthanks.phpへ
  if(empty($error)){
    // セッションに登録して確認ページに遷移
    $_SESSION['form'] = $form;
    
    $db = dbConnect();
    // DBに予約内容を登録
    $register = $db->prepare('INSERT INTO reserve (reserve, last_name, first_name, tel, email, password)
                              VALUES(?,?,?,?,?,?)');
    if(!$register){
      die($db->error);
    }
    $password = password_hash($form['password'], PASSWORD_DEFAULT);
    $register->bind_param('ssssss',$form['reserve'], $form['last_name'], $form['first_name'],
                          $form['tel'], $form['email'], $password);
    $success = $register->execute();
    if(!$success){
      die($db->error);
    }
    // thanks.phpに遷移
    header('Location:thanks.php');
    exit();
  }
}

$date_time = new DateTime($_SESSION['reserve']);
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
  <title>User</title>
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


  <form class="userform" method="post">
    <div class="row gy-3">
      <h4>ご予約日時： <?php echo $datetime;?></h4>
      <?php if(isset($error['email']) && $error['email'] === 'multi'): ?>
        <p class='error'>※同じ時間帯で重複予約しています。ご自身の予約をMyReserveより確認してください。</p>
      <?php endif; ?>
      <div class="col-md-3 px-2">
        <input type="text" class="form-control" name="last_name" placeholder="姓" aria-label="姓" value="<?php echo $form['last_name'];?>">
        <?php if(isset($error['name']) && $error['name'] === 'blank'):?> 
          <p class='error'>※お名前(姓・名)を入力してください</p>
        <?php endif; ?> 
      </div>
      <div class="col-md-3 px-2">
        <input type="text" class="form-control" name="first_name" placeholder="名" aria-label="名" value="<?php echo $form['first_name'];?>">
      </div>
      <div class="w-100"></div>

      <div class="col-6 px-2">
        <input type="text" class="form-control" name="tel" placeholder="電話番号" aria-label="電話番号" value="<?php echo $form['tel'];?>">
        <?php if(isset($error['tel']) && $error['tel'] === 'blank'):?> 
          <p class='error'>※電話番号を入力してください</p>
        <?php elseif(isset($error['tel']) && $error['tel'] === 'ng'): ?>
          <p class='error'>※電話番号を正しく入力してください</p>
        <?php endif; ?> 
      </div>

      <div class="w-100"></div>
      
      <div class="col-6 px-2">
        <input type="text" class="form-control" name="email" placeholder="メールアドレス" aria-label="メールアドレス" value="<?php echo $form['email'];?>">
        <?php if(isset($error['email']) && $error['email'] === 'blank'):?> 
          <p class='error'>※メールアドレスを入力してください</p>
        <?php elseif(isset($error['email']) && $error['email'] === 'ng'): ?>
          <p class='error'>※メールアドレスを正しく入力してください</p>
        <?php endif; ?> 
      </div>

      <div class="w-100"></div>
      
      <div class="col-6 px-2">
        <input type="password" class="form-control" name="password" placeholder="パスワード" aria-label="パスワード" value="<?php echo $form['password'];?>">
        <?php if(isset($error['password']) && $error['password'] === 'blank'):?> 
          <p class='error'>※パスワードを入力してください</p>
        <?php elseif(isset($error['password']) && $error['password'] === 'short'): ?>
          <p class='error'>※4文字以上のパスワードを設定してください</p>
        <?php endif; ?> 
        <h5>※4文字以上のパスワードを設定してください。<br>
            <br>※予約を取り消す場合に必要となります。
        </h5>
      </div>
    </div>
    <input type="submit" class="btn btn-primary" value="登録する">
    <a class="btn btn-primary" href="calendar.php" role="button">戻る</a>
  </form>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>