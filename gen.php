<?php
include_once './inc/config.php';
if ($_GET["key"] == "NoLizenz"){
   if (!isset($_GET["type"])){
     echo "types: 1 = 1 scan;2 = 1 day;3 = 1 week;";
     exit();
   }
   if ($_GET["type"] == 1){
$i = 0;
     while ($i < 100){
       $name = hash("sha256",random_bytes(16));
       $check = $connection->prepare('SELECT * FROM `serial` WHERE `name`  = ?;');
       $check->bind_param("s",$name);
       $check->execute();
       $check->store_result();

       $check->fetch();

       if ($check->num_rows == 0){
         $check->close();
        $insert_task = $connection->prepare("INSERT INTO `serial` (`name`,`type`,`value`,`data`) VALUES (?,'USAGE','1','');");
        $insert_task->bind_param("s",$name);
        $insert_task->execute();
        $insert_task->close();
        echo $name . ",";
        $i +=1;
       }

     }
   }elseif($_GET["type"] ==2){
     $i = 0;
          while ($i < 100){
            $name = hash("sha256",random_bytes(16));
            $check = $connection->prepare('SELECT * FROM `serial` WHERE `name`  = ?;');
            $check->bind_param("s",$name);
            $check->execute();
            $check->store_result();

            $check->fetch();

            if ($check->num_rows == 0){
              $check->close();
             $insert_task = $connection->prepare("INSERT INTO `serial` (`name`,`type`,`value`,`data`) VALUES (?,'TIME','1','');");
             $insert_task->bind_param("s",$name);
             $insert_task->execute();
             $insert_task->close();
             echo $name . ",";
             $i +=1;
   }
}
}elseif($_GET["type"] ==3){
  $i = 0;
       while ($i < 100){
         $name = hash("sha256",random_bytes(16));
         $check = $connection->prepare('SELECT * FROM `serial` WHERE `name`  = ?;');
         $check->bind_param("s",$name);
         $check->execute();
         $check->store_result();

         $check->fetch();

         if ($check->num_rows == 0){
           $check->close();
          $insert_task = $connection->prepare("INSERT INTO `serial` (`name`,`type`,`value`,`data`) VALUES (?,'TIME','7','');");
          $insert_task->bind_param("s",$name);
          $insert_task->execute();
          $insert_task->close();
          echo $name . ",";
          $i +=1;
}
}
}
}
?>
