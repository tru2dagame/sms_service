<?php
if (!isset($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
	$_b = false;
} else {
	$_u = $_SERVER['PHP_AUTH_USER'];
	$_p = $_SERVER['PHP_AUTH_PW'];
	if( $_SERVER['PHP_AUTH_USER']=='tru2dagame' && $_SERVER['PHP_AUTH_PW']=='******') {
		$_b = true;
	} else {
		$_b = false;
	}
}
if(!$_b){
	header("WWW-Authenticate: Basic realm='asdf'");
	header('HTTP/1.0 401 Tru only');
	print('Sorry about that you are not my girl.');
	exit;
}

include_once('class.mysql.php');
$tb_name = "wp_sms_log";

if (isset($_POST['act']) && $_POST['act'] == 'send') {
	$ip = $_SERVER["REMOTE_ADDR"];
	$now = time();

	$mobiles = $_POST['mobile'];
	$text = $_POST['text'];

	$sms = apibus::init( "sms"); //创建短信服务对象
	$error_times = 0;
	foreach ($mobiles as $mobile) {
		$obj = $sms->send( $mobile, $text , "UTF-8"); 
		print_r( $obj ); 
		$status = 'success';

		//错误输出 Tips: 亲，如果调用失败是不收费的 
		if ( $sms->isError( $obj ) ) { 
			/* echo 'errcode:'; */
			/* print_r( $obj->ApiBusError->errcode );  */
			/* echo "<br>"; */
			/* echo 'errdesc:'; */
			/* print_r( $obj->ApiBusError->errdesc ); */
			/* echo "<br>"; */
			$status = $obj->ApiBusError->errcode . "||" . $obj->ApiBusError->errdesc;
		}
		$sql = "INSERT INTO {$tb_name} (`user`, `date`, `mobile`, `text`, `status`) VALUES
                                       ('{$ip}', '{$now}', '{$mobile}', '{$text}', '$status')";
		$db->query($sql);
		if ($status != 'success') {
			$error_times ++;
		}
	}
	
	if ($error_times == 0) {
		echo '<script type="text/javascript">
				alert("All success.");
                window.location.href="test_sms.php";
              </script>';
		exit;
	} else {
		echo "<script type='text/javascript'>
				alert('{$error_times} message(s) failed...');
                window.location.href='test_sms.php';
              </script>";
		exit;

	}

}

?>

<html xmlns="http://www.w3.org/1999/xhtml" >
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="css.css" type="text/css" media="screen" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  </head>
<script type="text/javascript">
	$(document).ready(function(){
		var _val1 = '<div class="added"><input type="text" name="mobile[]" class="mobile" value="" />&nbsp;<input type="button" class="close" value="-" /></div>';
		$(".add").click(function(){
			$(".main-input").append(_val1);
		});
		$(".close").live("click", function(){
			$(this).parent(".added").remove();
		});
	});
</script>
  <body>
	<div id="wrap">
	  <form method="post" id="sendtxt" action="">
		<input type="hidden" name="act" value="send" />
		<div id="content" class="some_class">
		  <div class="main-input">
			<div class="added">
			  <input type="text" name="mobile[]" class="mobile" value="" />
			  <input type="button" class="add" value='+' />
			</div>
		  </div>
		</div>

		<div id="text">
		  <textarea tabindex="1" title="信息输入框" name="text" node-type="textEl" style="height: 135px; width:320px; margin: 0px; padding: 0px; border: 1px solid rgb(0, 0, 0); font-size: 14px; font-family: Tahoma, 宋体; word-wrap: break-word; line-height: 18px; overflow-y: auto; overflow-x: hidden; outline: none; " range="0&amp;0"></textarea>
		</div>
		<div id="send">
		  <input type="submit" name="send" value="send" style="background-color:blue; color:white" />
		</div>
	  </form>
	</div>
  </body>
</html>



<?php

echo ':) THX...';
exit;

$mobiles = array('18616333323',
				 '18616867876',
				 '18616708787',);





$msg = "测试群发短信服务。by 涛涛。 以下为测试符号\n 123\n！@#￥。"; 
$sms = apibus::init( "sms"); //创建短信服务对象

foreach ($mobiles as $mobile) {
	$obj = $sms->send( $mobile, $msg , "UTF-8"); 
	print_r( $obj ); 
	echo '<br>';

	//错误输出 Tips: 亲，如果调用失败是不收费的 
	if ( $sms->isError( $obj ) ) { 
		print_r( $obj->ApiBusError->errcode ); 
		print_r( $obj->ApiBusError->errdesc );
	}

}

echo '测试结束。';
exit;
