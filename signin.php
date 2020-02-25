<?php 
include_once("init.php");
//logoutUser(); //when arriving on this page, always unset the cookie
if(isset($_POST['btn-signin'])) {
	$usn = filterText($_POST['username']);
	$pwd = filterText($_POST['actpwd']);
	$DBH = new Signin();
	//$DBH->insertNewAccount("Dan", "Greenfield", "dangre00@gmail.com", "dan", "asdf");
	if(!empty($usn) && !empty($pwd)) {
		$accountVerified = $DBH->verifyAccount($usn, $pwd);
	}
	if ($accountVerified == true) {
		loginUser($accountVerified);
	} else {
		$showmodalJS = "$('#modalerror').modal('show');";
	}
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

    <title>ofPartner Organizational Assessment Services</title>

    <!-- Bootstrap Core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/css/scrolling-nav.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="/css/custom.css">

</head>

<!-- The #page-top ID is part of the scrolling feature - the data-spy and data-target are part of the built-in Bootstrap scrollspy function -->

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">

    <!-- Navigation -->
    <!--<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">-->
    <nav class="navbar-inverse" style="background-color:#343b4a;">
        <div class="container">
            <div class="navbar-header page-scroll">
                <a href="/" class="navbar-brand" alt="ofPartner OAS"><img src="/images/logo2.png" width="183" height="34" /></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->

            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Intro Section -->
    <section id="signin" class="signin">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">


    <div class="container">    
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
            <div class="panel panel-primary" >
                    <div class="panel-heading">
                        <div class="panel-title">Sign In</div>
                        <!--<div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>-->
                    </div>     

                    <div style="padding-top:30px" class="panel-body" >

                        <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
                            
                        <form id="loginform" class="form-horizontal" role="form" method="post">
                                    
                            <div style="margin-bottom: 25px" class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                        <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="User Name">                                        
                                    </div>
                                
                            <div style="margin-bottom: 25px" class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                        <input id="login-password" type="password" class="form-control" name="actpwd" placeholder="Password">
                                    </div>
                                    

                                
                            <!--<div class="input-group">
                                      <div class="checkbox">
                                        <label>
                                          <input id="login-remember" type="checkbox" name="remember" value="1"> Remember me
                                        </label>
                                      </div>
                                    </div>-->


                                <div style="margin-top:10px" class="form-group">
                                    <!-- Button -->

                                    <div class="col-sm-12 controls">
                                      <a id="btn-cancel" href="/" class="btn btn-default">Cancel</a>
                                      <input type="submit" id="btn-signin" class="btn btn-primary pull-right" name="btn-signin" value="Sign In" />

                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="col-md-12 control">
                                        <div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
                                            For assistance signing in, please email <a href="mailto:info@ofpartner.com">info@ofpartner.com</a> or call 312.720.6145
                                        </div>
                                    </div>
                                </div>    
                            </form>     



                        </div>                     
                    </div>  
        </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="modalerror" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Error Signing In</h4>
              </div>
				<div class='modal-body' id="modalerrorbody"> We could not sign you in with the information provided. Please try again.</div>
                <div class='modal-footer'>
                	<button type='button' class='btn btn-primary' data-dismiss='modal'>OK</button>
                </div>
            </div><!-- /.modal-content -->
        </div>
    </div>
    
    <div class="modal fade" id="modalsignout" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Signed Out</h4>
              </div>
				<div class='modal-body' id="modalerrorbody"> You are now signed out. Please click OK to sign back in.</div>
                <div class='modal-footer'>
                	<button type='button' class='btn btn-primary' data-dismiss='modal'>OK</button>
                </div>
            </div><!-- /.modal-content -->
        </div>
    </div>

    <!-- jQuery -->
    <script src="/js/jquery-1.12.4.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="/js/bootstrap.min.js"></script>

    <!-- Scrolling Nav JavaScript -->
    <script src="/js/jquery.easing.min.js"></script>
    <script src="/js/scrolling-nav.js"></script>
<script>
$(document).ready(function(){
	<?php echo $showmodalJS; //if error show JS that triggers bootstrap modal event?>
});
</script>
</body>

</html>
