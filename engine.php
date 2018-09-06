<?php
include_once "./inc/config.php";
class VmManager{
private $av = "";
private $user = "";
private $path = "";

function __construct($av_input,$user_input,$path_input)
{
	$this->av = $av_input;
	$this->user = $user_input;
	$this->path = $path_input;
}
function Execute($command)
{
	echo "[DEBUG] ". $command . PHP_EOL;
return shell_exec(chr(34) . $this->path . chr(34) . " " . $command);
}
function Start()
{
	return $this->Execute("startvm " . chr(34) . $this->av . chr(34) . " --type headless");
	while (strpos("error",strtolower($this->Cmd("echo %RANDOM%"))) != false){
		sleep(1000);
	}
}
function Stop()
{
return $this->Execute("controlvm " . chr(34) . $this->av . chr(34) . " poweroff");
}
function Screenshot($path)
{
return $this->Execute("controlvm " . chr(34) . $this->av . chr(34) . " screenshotpng " . chr(34) . $path . chr(34));
}
function SetInternet($state)
{
if ($state == false)
{
return $this->Execute("controlvm " . chr(34) . $this->av . chr(34) . " setlinkstate1 off");
}else{
return $this->Execute("controlvm " . chr(34) . $this->av . chr(34) . " setlinkstate1 on");
}
}
function CopyTo($host_path,$guest_path)
{
$host_path = str_replace("/","\\",str_replace("/","//",$host_path));
$guest_path = str_replace("/","\\",str_replace("/","//",$guest_path));
return $this->Execute("guestcontrol " . chr(34) . $this->av . chr(34) . " copyto --target-directory " . chr(34) . $guest_path . chr(34) . " " . chr(34) . $host_path . chr(34) . " --username " . $this->user);
}
function ExecuteFile($path,$cmd = "")
{
$path = str_replace("/","//",$path);
return $this->Execute("guestcontrol " . chr(34) . $this->av . chr(34) . " start --exe " . chr(34) . $path . chr(34) . " --username " . chr(34) . $this->user . chr(34) . " " . chr(34) . $cmd . chr(34));
}
function Cmd($cmd){
return $this->Execute("guestcontrol " . chr(34) . $this->av . chr(34) . " start --exe "  . chr(34) . "C:\\Windows\\System32\cmd.exe" . chr(34) . " --username " . chr(34) . $this->user . chr(34) . " -- arg0 /c start " .chr(34) . chr(34) . " " . chr(34) . $cmd . chr(34));
}
function LoadSnapshot($name)
{
return $this->Execute("snapshot " .chr(34) . $this->av . chr(34) . " restore " . chr(34) . $name . chr(34));
}
function CreateSnapshot($name)
{
return $this->Execute("snapshot " .chr(34) . $this->av . chr(34) . " take " . chr(34) . $name . chr(34));
}
}
function detect($img1_input,$img2_input){
	try{
		$img1 = imagecreatefromstring(file_get_contents($img1_input));
		$size1 = getimagesizefromstring(file_get_contents($img1_input));
		$img2 = imagecreatefromstring(file_get_contents($img2_input));
		$size2 = getimagesizefromstring(file_get_contents($img2_input));
		if ($size1[1] != $size2[1] || $size1[0] != $size2[0])
		{
			return true;
		}
		$difference = 0;
		$total = 0;
		for($j=0;$j<$size1[1];$j++){
		    for($k=0;$k<$size1[0];$k++){
		        $pix1 = imagecolorat($img2,$k,$j);
		        $pix2 = imagecolorat($img1,$k,$j);
		        if($pix1 != $pix2){
					$difference +=1;
		        }
				$total +=1;
		    }
		}
		$percentage = number_format(100 * $difference / $total, 2);
		echo "[DEBUG] Difference:" . $percentage . PHP_EOL;
		if ($percentage > 2){
			return true;//Detected
		}else{
			return false;//Undetected
		}
	}catch (Exception $e){
	return true;
	}

}
function scan($id,$info,$connection)
{
	$update_task = $connection->prepare("UPDATE `task` SET `status` = 'WORKING' WHERE `name` = ?;");
	$update_task->bind_param("s",$id);
	$update_task->Execute();
	$update_task->close();
$info_array = json_decode($info,1);
foreach($info_array as $av => $key){
$detect = false;
$vm = new VmManager($av,"TinyWin","C:\Program Files\Oracle\VirtualBox\VboxManage");
$vm->Start();
echo "Started" . PHP_EOL;
$vm->SetInternet(false);
echo "Disabled Internet" . PHP_EOL;
mkdir("./results/$id/$av/");
$vm->Screenshot(dirname(__FILE__). "/results/$id/$av/1.png");
echo "Screenshot #1" . PHP_EOL;
$vm->CopyTo(dirname(__FILE__). "/results/" . $id . ".exe","C:/Users/TinyWin/Desktop");
echo "Copied" . PHP_EOL;
echo 'Sleeping...' . PHP_EOL;
$start = date_create();
$check = date_create();
$diff = date_diff($start,$check);
while($diff->s < 30){
	echo $diff->s . PHP_EOL;

$vm->Screenshot(dirname(__FILE__). "/results/$id/$av/2.png");
if (detect(dirname(__FILE__). "/results/$id/$av/1.png",dirname(__FILE__). "/results/$id/$av/2.png") == true)
{
	$info_array[$av] = "1";
	$info = json_encode($info_array);
	$update_task = $connection->prepare("UPDATE `task` SET `info` = ? WHERE `name` = ?;");
	$update_task->bind_param("ss",$info,$id);
	$update_task->Execute();
	echo 'Detected on drop' . PHP_EOL;
	echo 'Exit.....' . PHP_EOL;
	$vm->Stop();
    $vm->LoadSnapshot("clean");
	$detect = true;
	break;
}
$check = date_create();
$diff = date_diff($start,$check);

}
if ($detect){
continue;
}



$vm->Cmd("C:/Users/TinyWin/Desktop/$id.exe");
$start = date_create();
$check = date_create();
$diff = date_diff($start,$check);
while($diff->s < 30){
	echo $diff->s . PHP_EOL;
$vm->Screenshot(dirname(__FILE__). "/results/$id/$av/3.png");
if (detect(dirname(__FILE__). "/results/$id/$av/2.png",dirname(__FILE__). "/results/$id/$av/3.png") == true)
{
	$info_array[$av] = "2";
	$info = json_encode($info_array);
	$update_task = $connection->prepare("UPDATE `task` SET `info` = ? WHERE `name` = ?;");
	$update_task->bind_param("ss",$info,$id);
	$update_task->Execute();
	echo 'Detected on Execution' . PHP_EOL;
	echo 'Exit.....' . PHP_EOL;
	$vm->Stop();
    $vm->LoadSnapshot("clean");
	$detect = true;
	break;
}
$check = date_create();
$diff = date_diff($start,$check);

}
if ($detect){
continue;
}

$vm->Stop();
$vm->LoadSnapshot("clean");
echo 'Undetected' . PHP_EOL;
echo 'Exit.....' . PHP_EOL;
$info_array[$av] = "0";
$info = json_encode($info_array);
$update_task = $connection->prepare("UPDATE `task` SET `info` = ? WHERE `name` = ?;");
$update_task->bind_param("ss",$info,$id);
$update_task->Execute();
}
$img = imagecreatefrompng('result.png');
$font = 'arial.ttf';

// Add some shadow to the text
$green = imagecolorallocate($img, 0, 255, 0);
$red = imagecolorallocate($img, 255, 0, 0);
$i=0;
foreach($info_array as $av => $key){

	if ($info_array[$av] == 0){
imagettftext($img, 10, 0, 170, 210 + ($i*24), $green, $font, "Undetected");
	}else{
		imagettftext($img, 10, 0, 170, 210 + ($i*24), $red, $font, "Detected");

	}
	$i++;
}
imagepng($img,dirname(__FILE__). "/results/$id/result.png");
$POST_DATA = array(
	'name' => $id . ".png",
	'file' => base64_encode(file_get_contents(dirname(__FILE__). "/results/$id/result.png"))
);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://avscan.space/upload.php?key=Axm,cpowAOSID90');
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $POST_DATA);
$response = curl_exec($curl);
curl_close ($curl);

$update_task = $connection->prepare("UPDATE `task` SET `status` = 'FINISHED' WHERE `name` = ?;");
$update_task->bind_param("s",$id);
$update_task->Execute();
}
while (1)
{
	echo "Looking for a task...." . PHP_EOL;
	$get_task = $connection->prepare("SELECT * FROM `task` WHERE `status` = 'QUEUED' ORDER BY `id` ASC");
	$get_task->Execute();
	$get_task->store_result();

	$get_task->bind_result($db_id,$db_name,$db_status,$db_info);

	$get_task->fetch();
	if($get_task->num_rows >=1){
		echo "Found task: " . $db_name . PHP_EOL;
		$get_task->close();
	scan($db_name,$db_info,$connection);
	}
sleep(15);
}
?>
