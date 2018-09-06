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
    $key_data = 0;
  }else{
     if ($key_value < $key_data)
     {
       echo 'This key is expired. please use the "Check key" function to find out the reason.';
       exit();
     }
     echo "This is a Usage based key. (" . $key_data . "/" . $key_value . ")";
     exit();
  }



}elseif($key_type == "TIME"){//Per Time
if ($key_data == "")  {//Not used yet
  echo "The key was not used yet, it will last for " . $key_value . " day/s";
  exit();
}else{
  if (time() > strtotime("+ " . $key_value . " days",$key_data)){
    echo 'This key is expired. <br />';
    echo 'it was valid for ' . $key_value . " day/s.<br />";
  }
  echo "It first got used on the " . date("d:m:y",$key_data);
exit();
}
}

$key_check->close();
}
 ?>
<html>
<style>
#navbar {
   position:fixed;
   bottom:10px;
   height:20px;
   width:100%;

}
</style>
<center>
  <form method="POST">
    <input type="text" name="key"> </br></br>
    <input type="submit" name="submit" value="Check"> </br></br>
  </form>

<div id="navbar">
  <a href="./index.php">[>>Scan<<]</a>
  <a href="./check.php">[>>Check key<<]</a>
  <a href="./contact.php">[>>Contact<<]</a>

</div>
