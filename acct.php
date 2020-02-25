<?php
ob_start();
include_once("init.php");
verifySession(60 * 60);
$accountID = getAccountID();
$rqFunction = trim($_GET["rq"]);
if (!function_exists($rqFunction)) {
	header('Location:?rq=assmt');
	exit();
}
if (!function_exists($rqFunction)) {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>OAS - Logged In</title>

    <!-- Bootstrap Core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/account.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery -->
    <script src="/js/Chart221.min.js"></script>
    <script src="/js/Chart.PieceLabel.js"></script>
    <script src="/js/jquery-1.12.4.min.js"></script>

</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="?rq=assmt"><img src="/images/logo-sm.png" /></a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="?rq=assmt">Surveys</a>
                    </li>
                    <li>
                        <a href="?rq=myacct">My Account</a>
                    </li>
                    <li>
                        <a href="?rq=signout">Sign Out</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>
    <!-- Bootstrap Core JavaScript -->
    <script src="/js/bootstrap.min.js"></script>
    <!-- Page Content -->
    <div class="container" id="main-container">

        <div class="row">
            <div class="col-lg-12">
				<?php 
                $rqFunction(); 
                ?>
            </div>
        </div>
        <!-- /.row -->

    </div>
    <!-- /.container -->

    <!-- Scrolling Nav JavaScript -->
    <script src="/js/jquery.easing.min.js"></script>
    <script src="/js/scrolling-nav.js"></script>
<script>
$(document).ready(function(){
	$('#rollup').click(function () {
		$('ul.tree').hide();
		$('span.oastree').removeClass("glyphicon-chevron-down");
		$('span.oastree').addClass("glyphicon-chevron-right");
	});	
	$('#rolldown').click(function () {
		$('ul.tree').show();
		$('span.oastree').removeClass("glyphicon-chevron-right");
		$('span.oastree').addClass("glyphicon-chevron-down");
	});	
	$('label.tree-toggler').click(function () {
		$(this).parent().children('ul.tree').toggle(300);
		var slot = $(this).children('span.oastree');
		slot.toggleClass("glyphicon-chevron-down glyphicon-chevron-right");
	});	
});
</script>
<?php
$cstFunction = "cst" . trim($_GET['cst']);
if (!empty($cstFunction) && function_exists($cstFunction)) {
	$cstFunction();
}
ob_end_flush();
?>
</body>

</html>