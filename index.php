<?php

session_start();
header("Cache-Control: no-cache, must-revalidate");
ob_start();
error_reporting (E_ALL ^ E_NOTICE);
require "functions.php";

$PHP_SELF = "index.php";
$is_loged_in = FALSE;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Login/Check/Logout
// Login
if($action == "login")
{
  $result = FALSE;
  $is_loged_in = FALSE;
  $md5_password = md5($password);

  $full_member_db = file("data/users.php");
  foreach($full_member_db as $member_db_line)
    {
      if(!eregi("<\?",$member_db_line))
        {
          $member_db = explode("|",$member_db_line);
          if(strtolower($member_db[2]) == strtolower($username) && $member_db[3] == $md5_password) { $result = TRUE; break; }
        }
    }

  if($result == TRUE)
    {
      $_SESSION['joined']	= "$member_db[0]";
      $_SESSION['permission']	= "$member_db[1]";
      $_SESSION['user']		= "$member_db[2]";
      $_SESSION['md5_password']	= "$member_db[3]";
      $_SESSION['email']	= "$member_db[4]";
      $_SESSION['url']		= "$member_db[5]";
      $_SESSION['age']		= "$member_db[6]";
      $_SESSION['location']	= "$member_db[7]";
      $_SESSION['lastvisit']	= "$member_db[8]";

      // Modify Last time loged in ////////////////////
      $all_users_db = file("data/users.php");
      $old_users_db        = $all_users_db;
      $modified_users = fopen("data/users.php", "w");
      foreach($old_users_db as $old_users_db_line)
        {
          $old_users_db_arr = explode("|", $old_users_db_line);
          if($member_db[0] != $old_users_db_arr[0]) { fwrite($modified_users, "$old_users_db_line"); }
          else
            {
              $time = time() + ($config_date_adjust * 3600);
              fwrite($modified_users, "$old_users_db_arr[0]|$old_users_db_arr[1]|$old_users_db_arr[2]|$old_users_db_arr[3]|$old_users_db_arr[4]|$old_users_db_arr[5]|$old_users_db_arr[6]|$old_users_db_arr[7]|$time|$old_users_db_arr[9]|$old_users_db_arr[10]|\n");
            }
        }
      fclose($modified_users);
      /////////////////////////////////////////////////

      $is_loged_in = TRUE;
      header("location: $PHP_SELF?id=modules/online&action=add&add_ip=$_SESSION[user]");
    }
  else 
    {
      $is_loged_in = FALSE;
      header("location: $PHP_SELF?login_failed=1");
    }
}

// Check if user is loged in
if($is_guest == TRUE) { $is_loged_in = FALSE; }
elseif($is_user == TRUE) { $is_loged_in = TRUE; }
elseif($is_moderator == TRUE) { $is_loged_in = TRUE; }
elseif($is_administrator == TRUE) { $is_loged_in = TRUE; }

// Logout
if($action == "logout")
{
  @session_destroy();
  @session_unset();

  header("location: $PHP_SELF?id=modules/online&action=remove&remove_ip=$_SESSION[user]");
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Check if users/guests are online and if they are write them or remove them

$users_timeout_seconds = 60;
$ip = ipCheck();
$referer = $_SERVER['HTTP_REFERER'];
$lang = @explode(",", @getenv("HTTP_ACCEPT_LANGUAGE"));
$lang = @strtolower($lang[0]);
$file = file("data/online.php");
$past = time()-$users_timeout_seconds;
$now = time();


if(isset($_SESSION[user])) { $write = "$_SESSION[user]|$_SESSION[email]|$_SESSION[permission]|$_SESSION[joined]|$now|$ip|$_SESSION[location]|member||\n"; }
if(!isset($_SESSION[user])) { $write = "guest|$ip|$now|$referer|$lang||\n"; }

for($i=0;$i<count($file);$i++)
  {
    $visitdata = explode("|", $file[$i]);
    if($visitdata[4] > $past && $visitdata[0] != $_SESSION[user]) { $write .= "$visitdata[0]|$visitdata[1]|$visitdata[2]|$visitdata[3]|$visitdata[4]|$visitdata[5]|$visitdata[6]||\n"; }
    if($visitdata[2] > $past && $visitdata[1] != ipCheck()) { $write .= "$visitdata[0]|$visitdata[1]|$visitdata[2]|$visitdata[3]|$visitdata[4]||\n"; }
  }
if($ofile = @fopen("data/online.php","w"))
  {
    @fputs ($ofile, $write);
    @fclose($ofile);
  }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Show number of total members

$file = file("data/users.php"); 
$TotalMembersCount = 0; 

for($line = 0; $line < sizeof($file); $line++) $TotalMembersCount++;

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Show last registered user

$file = file("data/users.php");
$totalLines = sizeof($file);

for($line = 0; $line < $totalLines; $line++) $lastmember_arr = explode("|", $file[$line]);

if($TotalMembersCount == 0) $LastRegisteredMemberName = '';
else
  {
    $LastRegisteredMemberName = $lastmember_arr[2];
    $LastRegisteredMemberId = $lastmember_arr[0];
  }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Show number of guests/members online

$file = file("data/online.php"); 
$OnlineGuestsCount = "0";
$OnlineMembersCount = "0";

for($line = 0; $line < sizeof($file); $line++) { if(@"guest" == substr($file[$line], 0, 5)) $OnlineGuestsCount++; else $OnlineMembersCount++; }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Site Design Header

echo'
<html>
<head>
	<meta name="description" content="srdav, dayanandvihar, wartex, wartex2016, nayamamarshe, brainescape, game zone">
    	<meta name="author" content="nayam">
        <meta http-equiv="cache-control" content="no-cache">
        <meta http-equiv="pragma" content="no-cache">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="expires" content="0">
<meta name="theme-color" content="#6630AD" />

	<title>Brain-Escape</title>
	<link rel="shortcut icon" href="assets/images/open.png">
	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<link href="assets/css/nayam.css" rel="stylesheet">
	<link href="assets/css/bootstrap-sortable.css" rel="stylesheet">
	<script src="assets/js/jquery-1.10.2.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/moment.min.js"></script>
	<script src="assets/js/bootstrap-sortable.js"></script>
</head>

<body>
    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="http://brainescape.ml">Brain Escape</a>
        </div>
        <div class="navbar-collapse collapse">
         <ul class="nav navbar-nav navbar-right">
            <li><a href="http://wartex.club" target="_default">Wartex</a></li>
            <li><a href="index.php#disqus_thread" target="_self">Hints (Bottom of the main Page)</a></li>
         </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div> 
<br>
<div class="container">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12">
			<div class="alert alert-warning text-center">'; echo message(); echo'</div>
			<div class="col-lg-3 col-md-3 col-sm-12">';

		        if($is_loged_in == FALSE)
		        {
		          echo'
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title text-center">Login</h3>
					</div>
					<div class="panel-body text-center">
						<form method="post" action="'.$PHP_SELF.'">
							<div class="input-group">
								<span class="input-group-addon">Username</span>
								<input type="text" id="user" name="username" class="form-control" placeholder="Username">
							</div><br>
							<div class="input-group">
								<span class="input-group-addon">Password</span>
								<input type="password" name="password" class="form-control" placeholder="Password">
							</div><br>
							<input class="btn btn-success" type="button" name="register" value="Register" onclick=window.location.href="'.$PHP_SELF.'?id=modules/register">
							<input class="btn btn-primary" type="submit" name="submit" value="Login">
							<input type="hidden" name="action" value="login">
						</form>
					</div>
				</div>';
		        }

		        elseif($is_loged_in == TRUE)
		        {
		          echo'
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title text-center">Control Panel</h3>
					</div>
					<div class="panel-body text-center">';
                
						  if($is_user == TRUE)
						    { 
						      echo'
							<ul class="list-group">
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/main">Play</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/panel">Account</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/leader&action=view_leader">Leaderboard</li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?action=logout">Logout</a></li>
							</ul>';
						    }
						  if($is_moderator == TRUE)
						    {
						      echo'
							<ul class="list-group">
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/main">Info</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/panel">Account</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/leaderA&action=view_leader">Leaderboard</li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?action=logout">Logout</a></li>
							</ul>';
						    }
						  if($is_administrator == TRUE)
						    {
						      echo'
							<ul class="list-group">
                                                                Make sure to Read the rules first ;)
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/main">Info</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/panel">Account</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/leaderA&action=view_leader">Leaderboard</li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/users">Users list</a></li>
								<li class="list-group-item"><a href="'.$PHP_SELF.'?action=logout">Logout</a></li>
							</ul>';
						    }
			echo'
					</div>
				</div>';
			if($is_moderator == TRUE || $is_administrator == TRUE)
                	echo'
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title text-center">Statistics</h3>
					</div>
					<div class="panel-body text-center">
						<ul class="list-group">
							<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/users&action=view_users">Total Members: '.$TotalMembersCount.'</li>
							<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/users&action=stand_alone&userid='.$LastRegisteredMemberId.'">Last Registered: '.$LastRegisteredMemberName.'</a></li>
							<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/online&action=members">Members Online: '.$OnlineMembersCount.'</a></li>
							<li class="list-group-item"><a href="'.$PHP_SELF.'?id=modules/online&action=guests">Guests Online: '.$OnlineGuestsCount.'</a></li>
						</ul>
					</div>
				</div>';
			}
			echo'
			<div class="row">
			<iframe src="//www.facebook.com/plugins/likebox.php?href=https://m.facebook.com/TeamWartex&amp;width&amp;height=258&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:258px; width:100%" allowTransparency="true"></iframe>
			</div>
			</div>
			<div class="col-lg-9 col-md-9 col-sm-12">';
				if(isset($_GET['id']) && $is_loged_in == TRUE) 
				  { 
				    $id = $_GET['id']; 
				    if(file_exists("$id.php")) 
				      { 
					if($is_user == TRUE && $id == 'modules/main')
				    		include("modules/game.php");
					else
				    		include("$id.php");
				      } 
				     elseif(!file_exists("$id.php"))
				      { 
					echo '
						<div class="jumbotron">
						  <div class="container">
						    <h1>Error 404 </h1>Page cannot be found
						  </div>
						</div>';
				      }
				  }
				if(isset($_GET['id']) && $is_loged_in == False) 
				  { 
				    $id = $_GET['id']; 
				    if(file_exists("$id.php")) 
				      { 
					if($id == 'modules/register')
				    		include("$id.php");
					else
						include("news.php");
				      } 
				     elseif(!file_exists("$id.php"))
				      { 
					echo '
						<div class="jumbotron">
						  <div class="container">
						    <h1>Error 404 </h1>Page cannot be found
						  </div>
						</div>';
				      }
				  }
				else if(!isset($_GET['id']) || $is_loged_in == FALSE)
				  {
				    include("news.php"); 
				  } 


				echo'

			</div>
		</div>
	</div>
</div>
</div>
<!-- Enter your disqus script here -->
<!-- <script id="dsq-count-scr" src="//brainescape.disqus.com/count.js" async></script> -->
</body>
</html>';

ob_end_flush();
?>