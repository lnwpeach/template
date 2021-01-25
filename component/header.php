<?php
    session_start();
    date_default_timezone_set("asia/bangkok");
    include("config.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="<?php echo $APP_URL;?>/assets/plugins/bootstrap-4.5.3-dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $APP_URL;?>/assets/plugins/fontawesome/css/fontawesome.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $APP_URL;?>/assets/plugins/fontawesome/css/solid.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $APP_URL;?>/assets/plugins/fontawesome/css/brands.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $APP_URL;?>/assets/css/components.css" rel="stylesheet" type="text/css" />
<title>Template</title>
</head>

<body>

<div class="connect">
    <div class="info-mem">
        <ul>
            <li><a style="color:white;" href="documentation/">Documentation</a></li>
            <li class="info-name">สวัสดีคุณ Admin</li>
            <li class="logout"><a href="#">Logout</a></li>
        </ul>
    </div>
</div>

<div class="container-fluid">

<div class="row mt-5">
<div class="col-12 col-lg-10 col-xl-9 mb-3 content">

<nav class="navbar navbar-expand-sm navbar-dark navbar-menu">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Gen Input</a>
            </li>
        </ul>
    </div>
</nav>

<script type="text/javascript" src="<?php echo $APP_URL;?>/assets/js/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo $APP_URL;?>/assets/plugins/bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="<?php echo $APP_URL;?>/assets/plugins/bootbox/bootbox.min.js"></script>
<script type="text/javascript" src="<?php echo $APP_URL;?>/assets/plugins/alasql/alasql.min.js"></script>
<script type="text/javascript" src="<?php echo $APP_URL;?>/assets/js/peach.js"></script>

<?php 
// If use react js
if(isset($react) && $react === true) {
    if(PRODUCTION === true) {
        echo "\n";
        echo "<script src='{$APP_URL}/assets/plugins/react/react.production.min.js' crossorigin></script>\n";
        echo "<script src='{$APP_URL}/assets/plugins/react/react-dom.production.min.js' crossorigin></script>\n";
    } else {
        echo "<script src='{$APP_URL}/assets/plugins/react/react.development.js' crossorigin></script>\n";
        echo "<script src='{$APP_URL}/assets/plugins/react/react-dom.development.js' crossorigin></script>\n";
    }

    echo "<script src='{$APP_URL}/assets/plugins/react/babel.min.js'></script>\n";
    echo "<script src='{$APP_URL}/assets/plugins/react/axios.min.js'></script>\n";
}
?>