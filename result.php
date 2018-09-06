<?php
include_once './inc/config.php';
if (isset($_GET["id"]))
{
  $check_status = $connection->prepare("SELECT * FROM `task` WHERE `name` = ?;");
  $check_status->bind_param("s",$_GET["id"]);
  $check_status->execute();
  $check_status->bind_result($db_id,$db_name,$db_status,$db_info);
  $check_status->store_result();
  $check_status->fetch();
  if ($check_status->num_rows == 0){
    exit("No task under this id.");
  }

 ?>
 <link rel="stylesheet" href="./inc/app.min.css">
 <div class="main-wrap">
   <nav class="navigation">
   <div class="container-fluid">

 <div class="navbar-inverse navbar navbar-fixed-top" >

<div class="collapse navbar-collapse" >
  <ul class="nav navbar-nav navbar-left clearfix yamm">
  </ul>
  </div>
</div>
</div>
   </nav>
 <body class="sidebar-disabled footer-disabled">


  <div class="content">

 <div class="container-fluid">

   <div class="row">
       <div class="col-md-4 col-md-offset-4">
           <div class="panel panel-default b-a-2 no-bg b-gray-dark">
               <div class="panel-heading text-center">
                 <h3>Result</br></br></br>
               </div>
               <div class="panel-body">
<?php
  if ($db_status == "FINISHED"){
    echo '<p class="text-white" style="display:inline">' .'Status:</p> <p class="text-success">Finished</p><br />';
    echo '<a target="_blank" href="bb.php?id=' . $_GET['id'] . '">Click here for your BB code</a></br>';
  }elseif($db_status == "QUEUED") {
        echo '<p class="text-white" style="display:inline">' .'Status:</p> <p class="text-danger">Queued</p><br />';
        echo ' <meta http-equiv="refresh" content="10" />';
        echo '<p class="text-white" style="display:inline">' .'This page will automaticly refresh every 10 seconds until the scan is done.</p><br />';
  }else{
    echo '<p class="text-white" style="display:inline">' .'Status:</p> <p class="text-warning">Scanning</p><br />';
    echo '<p class="text-white" style="display:inline">' .' <meta http-equiv="refresh" content="10" />';
    echo '<p class="text-white" style="display:inline">' .'This page will automaticly refresh every 10 seconds until the scan is done.</p><br />';
}

  $info = json_decode($db_info,1);
  foreach($info as $av => $value){
  if ($value == "0"){
    echo '<p class="text-white" style="display:inline">' . $av . ' =</p> <p class="text-success" style="display:inline">Undetected</p> <a href="./results/' . $_GET["id"] . '/' . $av . '/3.png">Screenshot</a>';
  }elseif ($value == "1") {
    echo '<p class="text-white" style="display:inline">' .$av . ' =</p> <p class="text-danger" style="display:inline">Detected on drop</p> <a href="./results/' . $_GET["id"] . '/' . $av . '/2.png">Screenshot</a>'  ;
  }elseif ($value == "2") {
    echo '<p class="text-white" style="display:inline">' .$av . ' =</p> <p class="text-danger" style="display:inline">Detected on execute</p> <a href="./results/' . $_GET["id"] . '/' . $av . '/3.png">Screenshot</a>'  ;

  }else{
    echo '<p class="text-white" style="display:inline">' .$av . ' =</p> <p class="text-warning" style="display:inline">waiting.....</p>';
  }
  echo '</br>';
  }

}
 ?>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

</body>
