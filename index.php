<?php
include_once './inc/config.php';
if (isset($_POST["submit"])){
  $key_check = $connection->prepare("SELECT * FROM `serial` WHERE `name` = ?");
  $key_check->bind_param("s",$_POST['key']);
  $key_check->bind_result($key_name,$key_type,$key_value,$key_data);
  $key_check->execute();
  $key_check->store_result();

  $key_check->fetch();
  if ($key_check->num_rows < 1)
  {
    echo "Invalid license key.";
    exit();
  }
if ($key_type == "USAGE"){//Per usage
  if ($key_data == ""){
     $key_data = 1;
  }else{
    $key_data +=1;
     if ($key_value < $key_data)
     {
       echo 'This key is expired. please use the "Check key" function to find out the reason.';
       exit();
     }

  }



}elseif($key_type == "TIME"){//Per Time
if ($key_data == "")  {//Not used yet
$key_data = time();
}else{
  if (time() > strtotime("+ " . $key_value . " days",time())){
    echo 'This key is expired. please use the "Check key" function to find out the reason.';
    exit();
  }
}
}

$key_check->close();
$key_insert = $connection->prepare("UPDATE `serial` SET `data` = ? WHERE `name` = ?;");
$key_insert->bind_param("ss",$key_data,$_POST['key']);
$key_insert->execute();
$key_insert->close();

  $name = "";
  while (1){
    $name = hash("sha256",random_bytes(16));
    $check = $connection->prepare('SELECT * FROM `task` WHERE `name`  = ?;');
    $check->bind_param("s",$name);
    $check->execute();
    $check->store_result();

    $check->fetch();
    if ($check->num_rows == 0){
      break;

    }
  }
  $check->close();
  $insert_task = $connection->prepare("INSERT INTO `task` (`id`,`name`,`status`,`info`) VALUES (NULL,?,'QUEUED',?);");
  $info = json_encode($av_list);
  $insert_task->bind_param("ss",$name,$info);
  $insert_task->execute();
  mkdir("./results/" . $name . "/");
  move_uploaded_file($_FILES["file"]["tmp_name"], "./results/" . $name . ".exe");
  header("Location: ./result.php?id=" . $name);
  exit();
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
                <h3>Welcome to <b>AvScan</b></br></br></br>
              </div>
              <div class="panel-body">

<form method="post" enctype="multipart/form-data" autocomplete="off">
Your file: </br>
<div class="form-group">
<input class="form-control" type="file" name="file"> </br></br>
</div>

Your key: </br>
<div class="form-group">
<input class="form-control" type="text" name="key"> </br></br>
</div>
</div>

<input type="submit" class="btn btn-block m-b-2 btn-primary" name="submit" value="scan">
</form>
</div>
</div>
</div>
</div>
</div>
</div>
</body>
