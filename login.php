<?php
header("Content-Type: text/html; charset=\"utf-8\"");
header("Cache-Control: no-store, no-cache,  must-revalidate"); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
ini_set('display_errors', 'on');
/*
if ($_REQUEST['remote_addr']=='192.168.3.198')
{$dsn = 'oci8://persik:razvitie@'.ZAOIBM;}
else
{$dsn = 'oci8://persik:razvitie@'.ZAOWH;}
*/
//$dsn="null";
$db = MDB2::connect($dsn);
//var_dump($db);
if (PEAR::isError($db))
{
	include "m.header.php";
	include "content-div-start.php";
	//include "menu.php";
	$smarty->display("db_unreachable.html");
	include "content-div-end.php";
	include "m.footer.php";
	die();
}
$db->loadModule('Extended');
$db->loadModule('Function');
#$db->setCharset('utf8',$db);
#$db->exec("SET NAMES utf8");



isset($_REQUEST['action']) ? $action = $_REQUEST['action'] : $action = null;
isset($_SESSION['users_id']) ? null : $_SESSION['users_id'] = null;
isset($_REQUEST['username']) ? $_REQUEST['username']=trim($_REQUEST['username']) : null;
isset($_REQUEST['password']) ? $_REQUEST['password']=trim($_REQUEST['password']) : null;
isset($_POST['username']) ? $_POST['username']=trim($_POST['username']) : null;
isset($_POST['password']) ? $_POST['password']=trim($_POST['password']) : null;
if (isset($_GET['auto']))
{
/*
$_SESSION["_authsession"]["username"]=$_GET['username'];
$_SESSION["_authsession"]["password"]=$_GET['password'];
$_SESSION["_authsession"]["registered"]=true;
*/
$_REQUEST["username"]=$_GET['username'];
$_REQUEST["password"]=$_GET['password'];
$_POST["username"]=$_GET['username'];
$_POST["password"]=$_GET['password'];
}
require_once "Auth.php";
$sql = rtrim(file_get_contents('sql/current_dates.sql'));
$dates = &$db->getRow($sql);
InitRequestVar("month_list",$dates[0]);
InitRequestVar("dates_list",$dates[1]);
$now_time=date("d.m.Y h:i:s");
$smarty->assign('now_time', $now_time);
$now=date("d.m.Y");
$prev1=date("01.m.Y", strtotime('-1 month'));
$smarty->assign('now', $now);
$smarty->assign('prev1', $prev1);
setlocale(LC_TIME, "rus_RUS");
$smarty->assign('now_month', strftime("%B"));
$smarty->assign('now_year', strftime("%Y"));
function loginFunction($username = null, $status = null, &$auth = null)
{
	global $smarty;
	require_once "m.header.php";
	require_once "content-div-start.php";
	include "avtoriz.php";
	require_once "content-div-end.php";
}
$options = array(
  'dsn' => $dsn,
  'usernamecol' => 'login',
  'passwordcol' => 'password',
  'table' => 'user_list',
  'cryptType' => 'none',
  'db_fields' => "*"
  );
$a = new Auth("MDB2", $options, "loginFunction");
if (isset($_REQUEST['action']))
{
if ($_REQUEST['action'] == "logout" && $a->checkAuth())
{
	isset($_SESSION["_authsession"]["username"])?$login=$_SESSION["_authsession"]["username"]:null;
	audit('Вышел','ms');
	$a->logout();
	session_unset();
	session_destroy();
	//    $a->start();
}
}
require_once "m.header.php";
$a->start();
if ($a->getAuth())
{
	if ($action=='logout')
	{
		isset($_SESSION["_authsession"]["username"])?$login=$_SESSION["_authsession"]["username"]:null;
		audit('Вышел','ms');
		$a->logout();
		$a->start();
	}
	else
	{
		if (strpos($_SESSION["_authsession"]["username"],'ag')!==false)
		{
			$_SESSION['login']=$_SESSION["_authsession"]["username"];
			$login=$_SESSION["_authsession"]["username"];
			$smarty->assign('login', $_SESSION["_authsession"]["username"]);
			$is_super=$db->getOne("select is_super from routes_agents_pwd where login='".$_SESSION['login']."'");
			$ag_id=$db->getOne("select ag_id from routes_agents_pwd where login='".$_SESSION['login']."'");
			$ag_comm=$db->getOne("select comm from routes_agents_pwd where login='".$_SESSION['login']."'");
			$ag_name=$db->getOne("select name from routes_agents where id=".$ag_id);
			$smarty->assign('is_super', $is_super);
			$smarty->assign('ag_id', $ag_id);
			$smarty->assign('ag_name', $ag_name);
			$smarty->assign('ag_comm', $ag_comm);
			InitRequestVar("agent",$ag_id);
			InitRequestVar("dpt_id",1);
			InitRequestVar("dates_list1",$_REQUEST["dates_list"]);
			InitRequestVar("dates_list2",$_REQUEST["dates_list"]);
			InitRequestVar("select_route_numb",0);
			InitRequestVar("svms_list",0);
			//InitRequestVar("oblast");
			//InitRequestVar("city");
			//InitRequestVar("nets");
			//InitRequestVar("tp",0);
			InitRequestVar("head_agents");
			if (!isset($_REQUEST["print"]))
			{
				include "content-div-start.php";
				//include "menu.php";
				$smarty->display("server_is_down.html");
			}
			if (count(glob($action.".php"))==0)
			{
				include "main.php";
			}
			else
			{
				$start=microtime(true);
				include $action.".php";
				$end=microtime(true);
				$duration=$end-$start;
				$smarty->assign('dur',$duration);
			}
			if (!isset($_REQUEST["print"]))
			{
				include "content-div-end.php";
				//include "right-div.php";
			}
		}
	}
}
else
{
//	echo "<font color=\"#990000\">Неправильное имя пользователя или пароль</font>";
}
$db->disconnect();
include "m.footer.php";
?>
