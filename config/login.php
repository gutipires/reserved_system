<?php 
require_once('../read.php');
session_start();
session_regenerate_id();

$error = [];
if(isset($_SESSION['id']) && $_SESSION['password']){
  $id = $_SESSION['id'];
  $password - $_SESSION['password'];
}else{
  $id = '';
  $password = '';
}

// フォームの値を取得
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $id = filter_input(INPUT_POST,'id', FILTER_SANITIZE_SPECIAL_CHARS);
  if($email === ''){
    $error['id'] = 'blank';
  }else{
    $_SESSION['id'] = $id;
  }

  $password = filter_input(INPUT_POST,'password', FILTER_SANITIZE_SPECIAL_CHARS);
  if($email === ''){
    $error['password'] = 'blank';    
  }else{
    $_SESSION['password'] = $password;
  }

  if(empty($error)){
    $db = dbConnect();
    $users = $db->prepare('SELECT password FROM members WHERE member_id=?');
    if(!$users){
      die($db->error);
    }
    $users->bind_param('s', $password);
    $success = $users->execute();
    if(!$success){
      die($db->error);
    }
    $users->bind_result($pass);
    $users->fetch();
    $login = password_verify($password, $pass);
    if(!$login){
      $error['login'] = 'ng';
    }else{
      header('Location: reserve.php');
      exit();
    }
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
  <title>Login</title>
</head>
<body>
  <!-- navi begin -->
  <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
    <div class="container-fluid">
      <a class="navbar-brand" href="../index.php">Top Page</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
  <!-- navi end -->

  <div class="container">
    <div class="row py-5 gy-3">
      <div class="col">
        <form action="" method="post">
          <div class="mb-3 row">
            <label for="staticEmail" class="col-sm-2 col-form-label">ID</label>
            <div class="col-sm-4 px-0">
              <input type="text" class="form-control" id="staticid" name="id" value="<?php echo $id;?>">
            </div>
            <?php if(isset($error['id']) && $error['id'] === 'blank'): ?>
              <p class="error">IDを入力してください</p>
            <?php endif; ?>
          </div>
      
          <div class="mb-3 row">
            <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-4 px-0">
              <input type="password" class="form-control" id="inputPassword" name="password" value=<?php echo $password;?>>
            </div>
            <?php if(isset($error['password']) && $error['password'] === 'blank'): ?>
              <p class="error">パスワードを入力してください</p>
            <?php endif; ?>
            <?php if(isset($error['login']) && $error['login'] === 'ng'): ?>
              <p class="error">IDまたはパスワードが違います</p>
            <?php endif; ?>
          </div>
          <input type="submit" class="btn btn-primary btn-lg" value="Login">
        </form>
      </div>
   </div>
  </div>  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>