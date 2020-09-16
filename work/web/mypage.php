<?php
 //ok
  $tab = "Mypage - ";
  $intro = "ロゴをクリックするとサイトトップに戻ります";

  require("../../sec_info.php");
  require("../app/function.php");
  include("../app/_parts/_header.php");
  
  validateAccount();
?>


<div class="container">
  <div class="index">
    <h2>マイページ</h2>
    <p>こんにちは <?= h($_SESSION["name"]) ?> さん</p>
    <h3><a href="newpost.php" class="link3">新しい本を読み始める</a></h3>
  </div>

  <!-- DBから自分の投稿だけ選択 -->
  <?php
    // 重複を許さず最近読んだ本を選択
    $sql = 'SELECT * FROM Data WHERE post_id IN(SELECT MAX(post_id) FROM Data WHERE id = :id GROUP BY title)';
    // $stmt = $pdo->query($sql);
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $_SESSION["id"], PDO::PARAM_INT);
    $stmt->execute();    
    $results = $stmt->fetchAll();
  ?>

  <!-- 自分の読書状況を表示 -->
  <?php
    //円グラフようの変数を作成
    $n1 = 0;  //継続
    $n2 = 0;  //読了
    $n3 = 0;  //挫折
    foreach ($results as $row){
      if($row['fin']==1){$n2++;}
      elseif($row['dis']==1){$n3++;}
      else{$n1++;}
    }
    //円グラフを描写
    $all = $n1 + $n2 +$n3;
    $p1 = $n1 / $all * 100;
    $p2 = ($n1+$n2) / $all * 100;
    require("chart.php");
  ?>
</div>

<h3>本の続きを記録する</h3>
<!-- 最近読んだ本の書籍名を表示 -->
<ul class="book_list">
  <?php
    foreach ($results as $row){
      $title = $row['title'];
      $auther = $row['auther'];
      echo '<li><a href="mybook.php?title='.h($title).'&auther='.h($auther).'" class="link1">'.h($title).' ['.h($auther).']</a></li>'; 
    }
  ?>
</ul>


<h3>あなたの最近の読書</h3>

<?php
  // DBから自分の投稿だけ選択
  $sql = 'SELECT * FROM Data WHERE id = :id ORDER BY post_id DESC LIMIT 5'; 
  // $stmt = $pdo->query($sql);
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':id', $_SESSION["id"], PDO::PARAM_INT);
  $stmt->execute();    
  $results = $stmt->fetchAll();

  //最近の読書記録を表示
  foreach ($results as $row){
    ?>
    <div class="box">
      <?php
      //タイトル
      $title = $row['title'];
      $auther = $row['auther'];
      echo '書籍名　　：<a href="mybook.php?title='.h($title).'&auther='.h($auther).'" class="link1">'.h($title).' ['.h($auther).']</a><br>';
      //コメント
      if($row['first']==1){echo "学習目標：<br>";}
      else{echo "コメント：<br>";}
      echo "<p style='white-space: pre-wrap';>".h($row['comment'])."</p>";
      //時刻
      $date = date_create($row['post_at']);
      echo "投稿日時：".date_format($date, 'Y/m/d　H:i:s');
      //読書の状態
      if($row['fin']==1){echo "　読了　";}
      elseif($row['dis']==1){echo "　挫折　";}
      echo "<br>";
      ?>
    </div>
    <?php
  }
?>

<p><a href="#top" class="link2">先頭へ戻る</a></p>

<?php
  include("../app/_parts/_footer.php");
?>