<!-- 
予約削除の処理専用ページ
指定したIDで予約があるかをSQLから検索して予約があれば削除する仕様
-->
<?php 
  session_start();  
  require_once('../read.php');

  if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])){
    $delete_id = h($_GET['id']);
    $success = isInt($delete_id);
    if(!$success){
      die('指定された予約は存在しません。');
    }else{
      $db = dbConnect();
      // urlパラメータとSessionのメールアドレスに対して予約の有無を確認
      $count = $db->prepare('SELECT COUNT(*) FROM reserve WHERE id=?');
      if(!$count){
        die($db->error);
      }
      $count->bind_param('i', $delete_id);
      $success = $count->execute();
      $count->bind_result($result);
      $count->fetch();
      // 0件だった場合はエラー表示
      if($count === 0){
        die('指定された予約は存在しません。');
      }else{
        // 予約が存在する場合は予約を削除する
        $db = dbConnect();
        $delete = $db->prepare('DELETE FROM reserve WHERE id=? LIMIT 1');
        if(!$delete){
          die($db->error);
        }
        $delete->bind_param('i', $delete_id);
        $success = $delete->execute();
        if(!$success){
          die($db->error);
        }else{          
          header('Location: calendar.php');  // 元のページへ戻す
          exit();
        }
      }
    }
  }
  ?>