<!-- 
functionを集約
-->
<?php 
// htmlspecialchars設定
function h($val){
  return htmlspecialchars($val, ENT_QUOTES | ENT_HTML5);
}

// DB接続
function dbConnect(){
  $db = new mysqli('localhost', 'root', 'root', 'reserve');
  if($db->connect_errno){
    die($db->connect_error);
  }
  return $db;
}

// 予約件数を取得
function reserveCount($date){
  // 予約状況を呼び出し
  $db = dbConnect();
  $reservation = $db->prepare('SELECT COUNT(DISTINCT email) FROM reserve WHERE reserve=?');
  if(!$reservation){
    die($db->error);
  }

  $reservation->bind_param('s',$date);
  $success = $reservation->execute();

  if(!$success){
    die($db->error);
  }
  $reservation->bind_result($cnt);
  $reservation->fetch();
  return $cnt;
}

// 整数チェック
function isInt($val){
  if(!preg_match('/^0$|^-?[1-9][0-9]*$/', $val)){
    return false;
  }else{
    return true;
  }
}
// 電話番号チェック
function isTel($val){
  if(!preg_match('/^(0{1}\d{1,4}-{0,1}\d{1,4}-{0,1}\d{4})$/', $val)){
    return false;
  }else{
    return true;
  }
}

// 日付チェック
function isYmd($date){
  if(!preg_match('/^[1-9]{1}[0-9]{0,3}\/[0-9]{1,2}\/[0-9]{1,2}$/', $date)){
    return false;
  }
  list($y, $m, $d) = explode('/', $date);
  if(!checkdate($m, $d, $y)){
    return false;
  }
  return true;
}


// config情報を取得
function getConfig(){
  $config[] = [];
  $db = DbConnect();
  $config_sql = $db->prepare('SELECT reserve_begin, reserve_end, start, end, frame, full, little, holiday FROM config');
  $success = $config_sql->execute();
  if(!$success){
    die($db->error);
  }
  $config_sql->bind_result($reserve_begin, $reserve_end, $begin, $end, $frame, $full, $little, $holiday);
  $config_sql->fetch();
  $config['reserve_begin'] = $reserve_begin;
  $config['reserve_end'] = $reserve_end;
  $config['start'] = $begin;
  $config['end'] = $end;
  $config['frame'] = $frame;
  $config['full'] = $full;
  $config['little'] = $little;
  $config['holiday'] = $holiday;

  return $config;
}

// 休日を配列で返す
function getWeekday($val){
  $holiday = [];
  $weekdays = str_split($val);
  foreach($weekdays as $weekday){
    switch ($weekday){
    case 0:
      $holiday[] = '日曜';
      break;
    case 1:
      $holiday[] = '月曜';
      break;
    case 2:
      $holiday[] = '火曜';
      break;
    case 3:
      $holiday[] = '水曜';
      break;
    case 4:
      $holiday[] = '木曜';
      break;
    case 5:
      $holiday[] = '金曜';
      break;
    case 6:
      $holiday[] = '土曜';
      break;
    case 7:
      $holiday[] = '祝日';
      break;
    }
  }
  $date = implode(' ',$holiday);
  return $date;
}

function getTime($today){
  // 設定データを取得
  $config = getConfig();
  // // 予約開始時刻と予約終了時刻を取得
  $reserve_begin = new DateTime($today . $config['start']);
  //$start = $reserve_begin->format("Y-m-d h:i");
  $reserve_end = new DateTime($today . $config['end']);
  //$end = $reserve_end->format("Y-m-d h:i");

  // 予約可能時間を分単位で差分取得
  $reserve_time = $reserve_end->getTimestamp() - $reserve_begin->getTimestamp();
  // 予約時間枠で割り算をして予約枠を算出
  return $reserve_time / intval($config['frame']) / 60;
}

// 予約可能時間帯を配列で返す
function timeArray(){
  $config = getConfig();

  // 基準日時を作成
  $now = date("Y-m-d ");
  $time = new DateTime($now . $config['start']);
  // 予約時間枠を取得
  $frame = getTime($now);

  for($i = 0; $i < $frame; $i++){
    if($i > 0){
      $time->modify('+' . $config['frame'] . 'minute');
    }
    $array[] = $time->format("H:i");
  }
  return $array;
}

// HTTP でデータを取得
function httpGet($url)
{
  $option = [
    CURLOPT_RETURNTRANSFER => true, // 文字列として返す
    CURLOPT_TIMEOUT => 10, // タイムアウト時間 (秒)
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, $option);

  $data = curl_exec($ch);
  $info = curl_getinfo($ch);
  $errorNo = curl_errno($ch);

  // OK 以外はエラーなので空白配列を返す
  if ($errorNo !== CURLE_OK) {
    // CURLE_OPERATION_TIMEDOUT: タイムアウト
    return [];
  }

  if ($info['http_code'] !== 200) {
    return false;
  }

  return $data;
}

//祝祭日データを配列で取得。
function getHolidays() {
  // 祝祭日データ URL
  $url = 'https://www8.cao.go.jp/chosei/shukujitsu/syukujitsu.csv';
  
  // csv取得
  $csv = file_get_contents($url);
  if (!$csv) {
      throw new Exception("祝日データ取得に失敗しました。");
  }
  // CSVの文字コードを変換
  $csv = mb_convert_encoding($csv, 'UTF-8', 'SJIS-win');

  //-- 正規表現で抜き出す
  if (!preg_match_all('/(\d{4})\/(\d{1,2})\/(\d{1,2}),(.+)/u', $csv, $matchAllAry, PREG_SET_ORDER)){
    die('Error');
  }
  // 月日と祝日名称なら一マスの中に改行が入ることはないだろう、という想定で explode 関数による行分割
  $csvLines = explode("\n", $csv);

  // @var array 返り値用の配列 祝日の定義を格納する */
  $holiday = [];
  $i = 0;
  foreach($csvLines as $line) {
    $i++;
    if($i === 1 || empty($line)){
      continue; // 1行目と空行はスキップ
    }
    // CSV として行をセルに分割
    $cells  = str_getcsv($line);
    // 配列に追加
    $success = isYmd($cells[0]);  // 日付形式確認
    if(!$success){
      continue;
    }
    $holiday[] = [
      // MySQLの日時形式に変換して配列に格納
      date("Y-m-d", strtotime($cells[0])),
      $cells[1]
    ];
  }

  // 現在から2年間分の年末年始を追加
  $currentYear = intval(date('Y'));
  $year_end = [];
  for ($i = 0; $i < 2; $i++) { // 2年間
    $year = $currentYear + $i;
    $date = strtotime("$year-12-29"); // 12月29日から
    for ($day = 0; $day < 6; $day++) { // 1月3日まで6日間
      $dateStr = date('Y-m-d', $date);
      $year_end[] = [$dateStr, '年末年始'];
      $date = strtotime("+1 day", $date);
    }
  }

  // 祝日と年末年始休暇を合体
  $holidays = array_merge($holiday, $year_end);
  foreach($holidays as $value){
    // 重複登録確認
    $db = dbConnect();
    $sql_multi = $db->prepare('SELECT COUNT(*) FROM holiday WHERE date=?');
    $sql_multi->bind_param('s',$value[0]);
    $success = $sql_multi->execute();
    if(!$success){
        die($db->error);
    }
    $sql_multi->bind_result($cnt);
    $sql_multi->fetch();
    
    if($cnt === 0){
      $db = dbConnect();
      $sql = $db->prepare('INSERT INTO holiday (date, holiday) VALUES (?,?)');
      $sql->bind_param('ss',$value[0], $value[1]);
      $success = $sql->execute();
      if(!$success){
          die($db->error);
      }
    }
  }

}

// MySQLの祝日データを配列で返す
function loadholidays(){
  $holidays = [];
  $db = DbConnect();
  $sql = $db->prepare('SELECT date FROM holiday');
  $success = $sql->execute();
  if(!$success){
    die($db->error);
  }
  $sql->bind_result($date);
  while($sql->fetch()){
    $holidays[] = $date;
  }
  return $holidays;
}

?>