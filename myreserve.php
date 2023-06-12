<?php 
require_once('read.php');
session_start();
session_regenerate_id();
$different = '';
$error = [];
if(isset($_GET['success']) && $_GET['success'] === '1'){
  $email = $_SESSION['email'];
  $password = $_SESSION['password'];
}else{
  $email = '';
  $password = '';
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $email = filter_input(INPUT_POST,'email', FILTER_SANITIZE_EMAIL);
  if($email === ''){
    $error['email'] = 'blank';
  }else{
    $email = filter_var($email, FILTER_VALIDATE_EMAIL); 
    if(!$email){
      $error['email'] = 'ng';
    }
    $_SESSION['email'] = $email;
  }
  $password = filter_input(INPUT_POST,'password', FILTER_SANITIZE_SPECIAL_CHARS);
  if($email === ''){
    $error['email'] = 'blank';    
  }
  $_SESSION['password'] = $password;
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
  <link rel="stylesheet" href="css/style.css">
  <title>My Reserve</title>
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

  <div class="container">
    <div class="row py-5 gy-3">
      <div class="col">
        <form action="" method="post">
          <div class="mb-3 row">
            <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-7">
              <input type="text" class="form-control" id="staticEmail" name="email" value="<?php echo $email;?>">
            </div>
          </div>
      
          <div class="mb-3 row">
            <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-7">
              <input type="password" class="form-control" id="inputPassword" name="password" value=<?php echo $password;?>>
            </div>
          </div>
          <input type="submit" class="btn btn-primary" value="予約を表示する">
        </form>
      </div>

      <div class="col">
        <!-- 予約一覧を表示 -->
        <div class="mb-3 row">
          <?php
          if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(empty($error)){
              $db = dbConnect();
              $get_reserve = $db->prepare('SELECT id, reserve, password FROM reserve WHERE email=? and reserve>? ORDER BY reserve DESC');
              if(!$get_reserve){
                die($db->error);
              }
              // 現在時刻から1日前の時刻を作成
              $time = new DateTime(date("Y-m-d H:i"));
              $time->modify('+1 day');
              $date_time = $time->format("Y-m-d H:i");

              // メールアドレスが一致していて、現在時刻より24時間後の日付より先の予約を取得する
              $get_reserve->bind_param('ss', $email, $date_time);
              $success = $get_reserve->execute();
              if(!$success){
                die($db->error);
              }
              
              $get_reserve->bind_result($id, $reserve, $pass);
              ?>
              <ul class="reserve_list">
                <?php
                echo '予約内容';
                $i = 1;
                while($get_reserve->fetch()):
                  $login = password_verify($password, $pass);
                  if(!$login){
                    $different = 1;
                    continue;
                  }
                  $i++;
                  $date = new DateTime($reserve);

                ?>
                <li class="reserved">
                  <?php echo $date->format("Y/n/j(D) G:i");?><a href="delete.php?id=<?php echo $id; ?>" class="delete">予約取消</a>
                </li>
                <?php endwhile;?>
              </ul>
              <?php 
              if($i === 1){
                if($different === 1){
                  echo '入力したメールアドレスまたはパスワードが異なります。';
                }else{
                  echo '取消可能なご予約はありません。';
                }
              }
            }
          }
          ?>
        </div>
      </div>
      <?php
      if(!empty($error)){
        echo '<p class="error">メールアドレスまたはパスワードを正しく入力してください<p>';
      } 
      ?>
    </div>
  </div>  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>