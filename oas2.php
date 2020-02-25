<?php
ob_start();
include_once("fct/fctTakeSurvey.php");
include_once("fct/fctFunctions.php");
//include_once("class/clsTakesurvey.php");
//include_once("class/clsHash.php");
$surveyIDUnhashed = trim($_GET["sv"]);
$pageID = isset($_GET['rq']) ? trim($_GET['rq']) : null;
$surveyID = unhashit($surveyIDUnhashed);
//Validate Survey
$surveyArray = surveyInfo($surveyID);
$surveyID = $surveyArray['survey_id'];
$surveyName = $surveyArray['survey_name'];
$surveyActive = $surveyArray['survey_active'];
if($surveyID == false) {
	header('Location:/404.html');
	exit();
}
//Validate Respondent
$accessCode = trim($_GET["ac"]);
$singleRespondentArray = singleRespondentArray($surveyID, $accessCode);
$respID = $singleRespondentArray['resp_id'];
$respEmail = $singleRespondentArray['resp_email'];
$respFirst = $singleRespondentArray['resp_first'];
$respLast = $singleRespondentArray['resp_last'];
$respAlt = $singleRespondentArray['resp_alt'];
$lastPageIDSubmitted = $singleRespondentArray['last_page_id'];
$settings = surveySettings($surveyID);
$logoSplash = $settings['logo_splash'];
$logoSurvey = $settings['logo_survey'];
$showProgressBar = $settings['show_progress_bar'];
$weeklyHoursText = $settings['weekly_hours_text'];
$annualLegalHoursText = $settings['annual_legal_hours_text'];
if($respID == false) { //access code is not valid. preclude survey
	echo "The survey is temporarily down for maintenance. Please try again in 30 minutes. We apologize for the inconvenience.";
	exit();
}
if($surveyActive == false) {
	header('Location:/inactive.html');
	exit();
}
if(empty($pageID) && $settings['show_splash_page'] == false) {
	header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=B");
	exit();
}
//SURVEY INFO
$beginButton = "Begin Survey";
if(!empty($lastPageIDSubmitted)) {
	$beginButton = "Resume Survey";
}
//BACK BUTTON CLICKED
if(isset($_POST['btnBack'])) {
	$nextPageID = getNextPage($respID, $surveyID, $pageID, false, false);
	header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $nextPageID);
	exit();
}
//NEXT BUTTON CLICKED
if(isset($_POST['btnNext'])) {
	$nextPageID = getNextPage($respID, $surveyID, $pageID, true, false);
	header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $nextPageID);
	exit();
}
//BEGIN / RESUME BUTTON CLICKED
if(isset($_POST['btnResume'])) {
	$resumePageID = getNextPage($respID, $surveyID, $lastPageIDSubmitted, true, true); //jumps one page beyond last submitted
	insertStartDT($surveyID, $respID);
	header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $resumePageID);
	exit();
}
//RESET BUTTON CLICKED
if(isset($_POST['btnReset'])) {
	deleteRespondentSurvey($surveyID, $respID);
	$resumePageID = getNextPage($respID, $surveyID, NULL, true, false); //jumps one page beyond last submitted
	header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $resumePageID);
	exit();
}
//START FROM BEGINNING BUTTON CLICKED
if(isset($_POST['btnStartFromBeginning'])) {
	$resumePageID = getNextPage($respID, $surveyID, NULL, true, false); //jumps one page beyond last submitted
	header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $resumePageID);
	exit();
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

    <title><?php echo $surveyName; ?></title>

    <!-- Bootstrap Core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
	<link href="/css/scrolling-nav.css" rel="stylesheet">
    <link href="/css/custom.css" rel="stylesheet">
    <!-- Custom CSS -->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="/css/survey.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> 
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <script src="/js/jquery-1.12.4.min.js"></script>
</head>

<!-- The #page-top ID is part of the scrolling feature - the data-spy and data-target are part of the built-in Bootstrap scrollspy function -->
<body>

<?php if(empty($_GET['rq']) && $settings['show_splash_page'] == true) { //splash page if there is no page ID present ?>
    <nav class="navbar transparent navbar-inverse navbar-fixed-top" id="my-navar">
        <div class="container">
            <div class="navbar-header page-scroll">
                <span class="navbar-brand page-scroll"><?php if(!empty($logoSplash)) { echo "<img src='$logoSplash' />"; } ?></span>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
            
            	<? if(!empty($settings['contact_phone'])) { ?>
                <a href="#" class="page-scroll btn navbar-btn navbar-right navphone">
                <span class="glyphicon glyphicon-earphone"></span> <?php echo $settings['contact_phone']; ?></a>
                <? } ?>
                
                <? if(!empty($settings['contact_email'])) { ?>
                <a href="mailto:<?php echo $settings['contact_email']; ?>?subject=RE: <?php echo $surveyName; ?>" class="page-scroll btn navbar-btn navbar-right navphone"><span class="glyphicon glyphicon-envelope"></span> <?php echo $settings['contact_email']; ?></a>
                <? } ?>
                
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Intro Section -->
    <section id="intro" class="survey-splash">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="white"><?php echo $surveyName; ?></h1>
                    <h3 class="white2"><?php echo $settings['splash_page']; ?></h3>
                    <div>&nbsp;</div><div>&nbsp;</div>
                    <a class="btn-cst1 page-scroll" href="?sv=<?php echo $surveyIDUnhashed; ?>&ac=<?php echo $accessCode; ?>&rq=B">Continue</a>
                </div>
            </div>
        </div>
    </section>
    <footer class="panel-footer text-center">
    <div class="container">
    <br/>
	<?php echo $settings['footer']; ?>
    </div>
    </footer>
<?php } else { ?>
	<div id="preloader"><img src="/images/preloader-lg.gif" /></div>
    <!-- Navigation -->
    <!--<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">-->
    <nav class="navbar navbar-default navbar-survey" id="my-navar">
        <div class="container">
            <div class="navbar-header">
                <div class="navbar-brand"><?php if(!empty($logoSurvey)) { echo "<img src='$logoSurvey' />"; } ?></div>
            </div>
            <div class="navbar-brand navbar-right" style="color:#CCC; font-weight:bold;"><?php echo $surveyName; ?></div>
        </div>
        <!-- /.container -->
    </nav>
    <!-- Main Content -->
    <form name="frmsrv" id="formsrv" method="post" autocomplete="off">
    <section class="main-section">
	<div class="container"><!-- start page container -->
	<? //progressBar(); ?>
		<?php 
        renderPage(); 
        ob_end_flush();
        ?>
    </div><!-- end page container -->
    </section><!-- end main-section-->
	<!-- Buttons -->
   	</form>
    <footer class="panel-footer text-center">
    <div class="container">
    <br/>
	<?php echo $settings['footer']; ?>
    </div>
    </footer>

    <div class="modal fade" id="modalerror" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Error</h4>
              </div>
				<div class='modal-body' id="modalerrorbody"></div>
                <div class='modal-footer'>
                	<button type='button' class='btn btn-primary' data-dismiss='modal'>OK</button>
                </div>
            </div><!-- /.modal-content -->
        </div>
    </div>
<?php } ?>
    <!-- jQuery -->

    <!-- Bootstrap Core JavaScript -->
    <script src="/js/bootstrap.min.js"></script>

    <!-- Scrolling Nav JavaScript -->
    <script src="/js/jquery.easing.min.js"></script>
    <script src="/js/scrolling-nav.js"></script>
<script>
$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip();
	$(".preload").click(function(){
		$("#preloader").show();
	});
});
</script>
</body>

</html>