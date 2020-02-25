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
				<ul class="nav nav-tabs">
  <li class="nav-item">
	<a class="nav-link" href="?rq=assmtopen&sid=45">Home</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="?rq=content&sid=45">Content</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="?rq=settings&sid=45">Settings</a>
  </li>
  <!--
  <li class="nav-item">
	<a class="nav-link" href="#">Appearance</a>
  </li>
  -->
  <li class="nav-item">
	<a class="nav-link" href="?rq=respondents&sid=45">Respondents</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="#">Invitations</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="?rq=reports&sid=45">X</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="?rq=rsprofile&sid=45">Profile</a>
  </li>
  <li class="nav-item active">
	<a class="nav-link" href="?rq=repuw&sid=45">Report</a>
  </li>
  <li class="nav-item">
	<a class="nav-link" href="?rq=repind&sid=45">Individual</a>
  </li>
</ul>
<div class="spacer">&nbsp;</div>

<!-- Begin Filter Options -->
<div class="row">
  <div class="col-sm-12">
  <span class="btn btn-link strong" id="show-filters">+ Show Report Filter Options</span><br /><br />
  </div>
</div> <!-- End Row -->
<div id="filter-panel">
  <div class="row form-inline">
    <div class="well">
    <p class="blue" style="font-size:18px; font-weight:300;">I want to see how the following personnel are utilized:</p>
    <select class="form-control filter-slct">
      <option value="0"> - BY EMPLOYEE ID - </option>
      <option>1</option>
      <option>2</option>
      <option>3</option>
      <option>4</option>
      <option>5</option>
    </select>
    <select class="form-control filter-slct">
      <option value="0"> - BY CATEGORY - </option>
      <option>Legal</option>
      <option>Support</option>
    </select>
    <select class="form-control filter-slct">
      <option value="0"> - BY TITLE - </option>
      <option>Accounting Coordinator</option>
      <option>Accounting Supervisor</option>
    </select>
    <select class="form-control filter-slct">
      <option value="0"> - BY DEPARTMENT - </option>
      <option>Corporate</option>
      <option>Finance</option>
      <option>Legal Personnel</option>
    </select>
    <select class="form-control filter-slct">
      <option value="0"> - BY CITY - </option>
      <option>Chicago</option>
      <option>Los Angeles</option>
      <option>New York</option>
    </select>
    <div style="margin:10px 5px 0px 0px;">
    <span id="filter-reset" class="btn btn-link strong">Reset All Filters</span>
    <span id="filter-close" class="btn btn-link strong" style="padding-left:15px;">Done</span>
    </div>
    </div>
  </div>
</div> <!-- End Filter Panel -->
<div class="spacer">&nbsp;</div>
<!-- End Filter Options -->

<table class='report-mainheader'>
<tr>
<td><img src='#' alt="COMPANY LOGO" /></td>
<td><div class='report-survey-name'>ABC Co Workplace Survey</div></td>
</tr>
<tr>
<td></td>
<td>
<div class='report-survey-name' style='font-size:14px; font-weight:normal;'># Responding: 483</div>
<div class="pull-right">
In this area, display filter selections.
<!-- <table style="width:100%;" cellpadding="0" cellspacing="0">
  <tr>
    <td>Employee ID:</td>
    <td><span class="fopt" id="fopt1"></span></td>
  </tr>
  <tr>
    <td>Category:</td>
    <td><span class="fopt" id="fopt2"></span></td>
  </tr>
  <tr>
    <td>Title:</td>
    <td><span class="fopt" id="fopt3"></span></td>
  </tr>
  <tr>
    <td>Department:</td>
    <td><span class="fopt" id="fopt4"></span></td>
  </tr>
  <tr>
    <td>City:</td>
    <td><span class="fopt" id="fopt5"></span></td>
  </tr>
</table> -->
</td>
</tr>
</table>
</div>
<div id='crumbcontainer'>
<span class='report-crumb'><a class='acrumb' href='?rq=repuw&sid=45&pid=3940&filter=&cust='>Legal & Support</a></span></div><div style='clear:both; margin-bottom:15px;'></div>
<div class='report-header blue strong'>Legal & Support</div><table class='report-table' style='margin-top:15px;'><tr>
<td><div class='strong italic'>Answering: 417</div></td>
<td></td>
<td class='report-td-calcs blue strong'>Pct</td>
<td class='report-td-calcs blue strong'>Hours</td>
<td class='report-td-calcs blue strong'>Cost to Firm</td>
<td class='report-td-calcs blue strong'>Hourly</td>
</tr>
<tr>
<td><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Support Activities</a></td>
<td class='report-td-chart'><div class='report-div-container'><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-barchart' style='width:56%;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>56.2%</td>
<td class='report-td-calcs'>662,854</td>
<td class='report-td-calcs'>$44,420,602</td>
<td class='report-td-calcs'>$67</td>
</tr>
<tr>
<td><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Legal Services</a></td>
<td class='report-td-chart'><div class='report-div-container'><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-slice'>&nbsp;</div><div class='report-div-barchart' style='width:44%;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>43.8%</td>
<td class='report-td-calcs'>299,822</td>
<td class='report-td-calcs'>$34,564,364</td>
<td class='report-td-calcs'>$115</td>
</tr>
<tr>
<td class='strong'>TOTAL:</td>
<td></td>
<td class='report-td-calcs strong'>100%</td>
<td class='report-td-calcs strong'>962,676</td>
<td class='report-td-calcs strong'>$78,984,965</td>
<td class='report-td-calcs strong'>$82</td>
</tr>
</table>

<div style='font-size:20px; color:#999; margin:10px 0px; padding-top:20px; border-top:1px solid #DDD; font-style:italic; font-weight:300;'>Cost Distribution of Legal & Support ($78,984,965):</div>
<div class='row'>
<div class='col-lg-6' style='margin-bottom:30px;'>
<div class='report-header blue strong' style='font-size:12px;'><a class='white' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Support Activities</a></div><table class='report-sub-table' style='margin-top:15px;'><tr>
<td><div class='strong italic'>Answering: 393</div></td>
<td></td>
<td class='report-td-calcs blue strong'>Pct</td>
<td class='report-td-calcs blue strong'>Cost</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Administrative Support</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:16%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>16.0%</td>
<td class='report-td-calcs'>$12,670,024</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Finance</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:10%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>9.9%</td>
<td class='report-td-calcs'>$7,792,374</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Leadership and Management</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:9%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>8.9%</td>
<td class='report-td-calcs'>$7,065,470</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Business Development</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:8%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>8.1%</td>
<td class='report-td-calcs'>$6,420,162</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Human Resources</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:6%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>6.5%</td>
<td class='report-td-calcs'>$5,128,160</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Information Technology</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:5%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>5.3%</td>
<td class='report-td-calcs'>$4,181,837</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3983&filter=&cust='>Tactical Initiatives</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:1%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3983&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>1.5%</td>
<td class='report-td-calcs'>$1,169,014</td>
</tr>
<tr>
<td class='strong'>TOTAL:</td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart bartotal' style='width:56%; height:16px;'>&nbsp;</div></div></td>
<td class='report-td-calcs strong'>56.2%</td>
<td class='report-td-calcs strong'>$44,427,040</td>
</tr>
</table>

</div><!--end column-->

<div class='col-lg-6' style='margin-bottom:30px;'>
<div class='report-header blue strong' style='font-size:12px;'><a class='white' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Legal Services</a></div><table class='report-sub-table' style='margin-top:15px;'><tr>
<td><div class='strong italic'>Answering: 171</div></td>
<td></td>
<td class='report-td-calcs blue strong'>Pct</td>
<td class='report-td-calcs blue strong'>Cost</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Transactional</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:24%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>24.2%</td>
<td class='report-td-calcs'>$19,153,736</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Litigation</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:11%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>10.6%</td>
<td class='report-td-calcs'>$8,368,966</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Bankruptcy</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:5%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>5.1%</td>
<td class='report-td-calcs'>$4,053,055</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Counseling</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:4%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>3.6%</td>
<td class='report-td-calcs'>$2,842,863</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Trademark</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:0%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>0.1%</td>
<td class='report-td-calcs'>$83,859</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Patent</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart' style='width:0%; height:16px;'><a class='report-link' href='?rq=repuw&sid=45&pid=3941&filter=&cust='>&nbsp;</a></div></div></td>
<td class='report-td-calcs'>0.1%</td>
<td class='report-td-calcs'>$61,885</td>
</tr>
<tr>
<td><div class='sub-desc'><a href='?rq=repuw&sid=45&pid=3941&filter=&cust='>Workers’ Compensation</a></div></td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div></div></td>
<td class='report-td-calcs'>0.0%</td>
<td class='report-td-calcs'>$0</td>
</tr>
<tr>
<td class='strong'>TOTAL:</td>
<td class='report-td-chart'><div class='report-div-container' style='height:25px;'><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-slice' style='height:25px;'>&nbsp;</div><div class='report-div-barchart bartotal' style='width:44%; height:16px;'>&nbsp;</div></div></td>
<td class='report-td-calcs strong'>43.8%</td>
<td class='report-td-calcs strong'>$34,564,364</td>
</tr>
</table>

</div><!--end column-->

</div><!--end row-->
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
  $("#filter-panel").hide();
  $('#show-filters').click(function () {
    $("#filter-panel").slideToggle();
  });
$('.filter-slct').on('change', function() {
  //alert( this.value );
  //$("#filter-panel").slideUp();
})
  $('#filter-close').click(function () {
    $("#filter-panel").slideUp();
  });
$('#filter-reset').click(function () {
  $(".filter-slct").each(function() { this.selectedIndex = 0 });
 });
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
</body>

</html>