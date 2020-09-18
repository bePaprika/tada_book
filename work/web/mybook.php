<?php
  //ok
  require("../app/function.php");
  require("../../sec_info.php");

  $tab = "読書記録 ".h($_GET["title"])." - ";
  $intro = "読み切ることよりも、設定した目的を達成することを心がけましょう";
  $errors = array();
  
  include("../app/_parts/_header.php");
  createToken(); 
  validateAccount();
?>


<!-- 正しい遷移(title,autherが指定されている)か確認 -->
<?php
  if(empty($_GET)) {
    header("Location: https://tb-220261.tech-base.net/TADABON/work/web/mypage.php");
    exit();
  }

  else{
    //書籍名か著者名がない場合不正な遷移なのでマイページに飛ばす
    if ($_GET["title"] === "" || $_GET["auther"] === ""){
      $_SESSION['error'] = "該当する書籍は登録されていません";
      header("Location: https://tb-220261.tech-base.net/TADABON/work/web/mypage.php");
      exit();
    }
  }

  $title = $_GET["title"];
  $auther = $_GET["auther"];
?>


<!-- POST受信 -->
<?php
  //投稿が押されたか
  if (isset($_POST["post"])) {
    //コメントが入力されているか
    if($_POST["comment"]!=""){
      //トークンが正しいか
      if (empty($_SESSION['token']) || $_SESSION['token'] !== filter_input(INPUT_POST, 'token')) {
        $errors['double_click'] = "更新ボタンを押しても多重投稿しません";
      }
      else{
        $status = $_POST["status"];
        $comment = $_POST["comment"];
        $public = $_POST["public"];

        //状態の割り当て
        if    ($status==="0"){$fin=0;$dis=0;}
        elseif($status==="1"){$fin=1;$dis=0;}
        else                 {$fin=0;$dis=1;}
    
        //データベースに書き込む
        $sql = $pdo -> prepare("INSERT INTO Data (title,auther,first,comment,id,name,post_at,fin,dis,public)
                                VALUES(:title,:auther,0,:comment,:id,:name,now(),:fin,:dis,:public)");
        $sql -> bindParam(':title',   $title,            PDO::PARAM_STR);
        $sql -> bindParam(':auther',  $auther,           PDO::PARAM_STR);
        $sql -> bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
        $sql -> bindParam(':id',      $_SESSION['id'],   PDO::PARAM_INT);
        $sql -> bindParam(':name',    $_SESSION['name'], PDO::PARAM_STR);
        $sql -> bindParam(':fin',     $fin,              PDO::PARAM_INT);
        $sql -> bindParam(':dis',     $dis,              PDO::PARAM_INT);
        $sql -> bindParam(':public',  $public,           PDO::PARAM_INT);
        $sql -> execute();
    
        $errors['posted'] = "「".h($_GET["title"])."」に読書記録を追加しました。";
        $_SESSION['token'] = "";
      }
    }
    else{
      $errors['comment'] = "読書記録を記入してください";
    }
  }
  
?>

<?php
  //エラーがある場合ここでメッセージを表示する
  if(count($errors) > 0){
    foreach($errors as $error){ echo "<p>$error</p>"; }
  }
?>

<!-- 本のタイトルを表示 -->
<h2> <?= h($_GET["title"]).' <br>['.h($_GET["auther"]).' 著]'; ?></h2>
<?= '<p><a href="book.php?title='.h($_GET["title"]).'&auther='.h($_GET["auther"]).'" class="link2"> この本の掲示板へ </a></p>';?> 


<!-- POST送信 -->
<div class="box_form">
  <form action="" method="post">
    <laber>読書記録：<textarea name="comment" cols="60" rows="5"></textarea></laber>
    <br>
    <laber>読書状態：<input type="radio" name="status" value="0" checked>継続中</laber>
    <laber>          <input type="radio" name="status" value="1"        >役に立った！！</laber>
    <laber>          <input type="radio" name="status" value="2"        >挫折・不満..</laber>
    <br>
    <laber>公開設定：<input type="radio" name="public" value="1" checked>公開する</laber>
    <laber>          <input type="radio" name="public" value="0"        >公開しない</laber>
    <br>
    <input type="hidden" name="token" value="<?= h($_SESSION['token']);?>">
    <input type="submit" name="post" class="submit" value="投稿"><br>
  </form>
</div>

<!-- この本に対する自分のコメントを表示 -->
<h2>読書記録</h2>
<?php
  $sql = "SELECT * FROM Data WHERE id = :id AND title = :title AND auther = :auther ORDER BY post_id DESC"; 
  // $stmt = $pdo->query($sql);
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':id',     $_SESSION["id"], PDO::PARAM_INT);
  $stmt->bindParam(':title',  $_GET["title"],  PDO::PARAM_STR);
  $stmt->bindParam(':auther', $_GET["auther"], PDO::PARAM_STR);
  $stmt->execute();    
  $results = $stmt->fetchAll();
  foreach ($results as $row){
    ?>
    <div class="box">
      <?php
      //コメント
      if($row['first']==1){echo "<div class='small'>学習目標：<br></div>";}
      echo "<p style='white-space: pre-wrap ';>".h($row['comment'])."</p>";
      //時刻
      $date = date_create($row['post_at']);
      echo "<div class='small'>投稿日時：".date_format($date, 'Y/m/d　H:i:s');
      //読書の状態
      if($row['fin']==1){echo "<div class='fin'>　役に立った！　</div>";}
      elseif($row['dis']==1){echo "<div class='fail'>　挫折・不満..　</div>";}
      echo "<br></div>";
      ?>
    </div>
    <?php
  }
?>


<p><a href="#top" class="link2">先頭へ戻る</a></p>

<?php
  include("../app/_parts/_footer.php");
?>