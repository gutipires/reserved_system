# reserved_system
使用したもの
・HTML/CSS/PHP
・MAMP/MySQL
・Bootstrap

MAMPを使いローカルホスト上で設計しています。

作成したものは汎用型の予約サイトになります。
会員登録は基本不要で比較的カジュアルな使い方を前提にした予約サイトをテーマとして作成しました。

WEB上に入力した情報をMySQLに保存して、保存したデータベースの件数で予約の管理をしています。

calendar.phpのカレンダーから予約したい日を選択すると、reserve.phpが起動して、予約状況を画面に表示させます。

予約可能な時間帯をクリックすることで、user.phpに遷移して、お客様情報を入力して予約完了となります。
ユーザはお客様情報の際に入力したメールアドレスとパスワードを入力することで予約削除することができます。
ただし２４時間以内の予約は削除できないようにしています。

管理者はconfigフォルダ内で予約の確認・変更・削除といった予約の管理と
予約の設定の２つのメニューを用意しています。

予約の設定については時間帯を15分、30分、60分　以降60分単位で予約間隔を指定することができます。
営業時間も15分単位で指定することができます。

店休日設定も可能で、指定した曜日は予約を入れることができないよう設計しています。

入力情報を増やすことで顧客のマーケティングにも活用することができますが、今回はカジュアルな予約サイトをテーマに
作成しましたので、必要最小限の情報で予約ができるように設計しています。
