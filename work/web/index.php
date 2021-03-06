<?php
  //ok
  $tab = "TOP - ";
  $intro = "読書を記録し、読みやすく身になる書籍を共有するサイトです";

  require("../../sec_info.php");
  require("../app/function.php");
  include("../app/_parts/_header.php");
?>


<!-- 遷移メッセージがある場合はここで表示 -->
<?php 
  if(isset($_SESSION['message'])){
    echo '<p class="message">'.$_SESSION["message"].'</p>';
    $_SESSION['message'] = "";
  }
?>
<h2>ようこそ</h2>

<!-- <h3>本を検索する</h3> -->

<!-- 検索フォーム -->
<form action="" method="post">
  <input type="text" name="search" placeholder="タイトル" style="margin-left:35px;" >
  <input type="submit" name="btn_search" class="btn_search" value="検索"><br>
</form>
<?php
  //検索ボタンが押された時
  if(isset($_POST['btn_search'])){
    $search = $_POST['search'];
    //データベースからタイトルに検索ワードが含まれる本を探す
    $sql = 'SELECT * FROM Books WHERE title like ?';
    // $stmt = $pdo->query($sql);
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, '%' .addcslashes($search, '\_%'). '%', PDO::PARAM_STR);
    $stmt->execute();    
    $results = $stmt->fetchAll();
    echo "<p>検索ワード：".h($search)."</p>";
?>
<!-- 検索結果を表示 -->
<ul class="book_list">
<?php
    if(empty($results)){echo "0件";}
    foreach ($results as $row){
      $title = $row['title'];
      $auther = $row['auther'];
      echo '<li><a href="book.php?title='.h($title).'&auther='.h($auther).'" class="link1">'.h($title).' ['.h($auther).']</a></li>'; 
    }
  }
?>
</ul>


<h3>新着レビュー</h3>

<?php
  $sql = 'SELECT * FROM Data WHERE public = 1 ORDER BY post_id DESC LIMIT 5';
  $stmt = $pdo->query($sql);
  $results = $stmt->fetchAll();
  foreach ($results as $row){
    ?>
    <div class="box">
      <?php
      //タイトル
      $title = $row['title'];
      $auther = $row['auther'];
      echo '書籍：　<a href="book.php?title='.h($title).'&auther='.h($auther).'" class="link1">'.h($title).'</a><br>';
      //著者
      echo '著者：　'.h($auther).'<br>';
      //コメント
      if($row['first']==1){echo '<div class="small">学習目標：<br></div>';}
      echo "<p style='white-space: pre-wrap ';>".h($row['comment'])."</p>";
      //投稿者
      echo '<div class="small">投稿者：　'.h($row['name']);
      //時刻
      $date = date_create($row['post_at']);
      echo "　　投稿日時：　".date_format($date, 'Y/m/d H:i:s');
      //読書の状態
      if($row['fin']==1){echo "<div class='fin'>　役に立った！　</div>";}
      elseif($row['dis']==1){echo "<div class='fail'>　挫折・不満..　</div>";}
      echo '</div>';
      ?>
    </div>
    <?php
  }
?>






  
  

  
  


<p><a href="#top" class="link2">先頭へ戻る</a></p>

<?php
  include("../app/_parts/_footer.php");
?>