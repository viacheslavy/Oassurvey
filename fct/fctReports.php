<?php
function reports() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();

	assessmentTabs($surveyID, 7);
	//echo "<h4>Reports</h4>";
	//echo "<pre>", print_r($respArray), "</pre>";
	echo "<form method='post'>";
    echo "<div class='well' style='margin-top:10px;'>\n";

	echo "<div class='row'>\n";
	echo "<div class='col-sm-12'>";
	echo "<p class='blue largetext'>I want to see how the following personnel are utilized:</p>";
	echo "</div>";//end col
	echo "</div>\n";//end row

	echo "<div class='row'>\n";
	
	echo "<div class='col-sm-4'>\n";
	echo "<div>\n";
	echo "
	  <select class='form-control' name='ddFilter' id='ddFilter'>
		<option value='0'> - SELECT PERSONNEL TYPE -</option>
		<option value='1'>By Category</option>
		<option value='2'>By Department</option>
		<option value='3'>By Title</option>
	  </select>
	  ";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col
	
	echo "<div class='col-sm-8'>";
	
	echo "
		<div id='divSubFilter1' class='divSubFilter'>
			<select class='form-control ddSubFilter' name='ddSubFilter1' id='ddSubFilter1'>
				<option value='0'>ALL CATEGORIES</option>
				<option value='1'>Legal</option>
				<option value='2'>Support</option>
				<option value='3'>Contract</option>
			</select>
		</div>
	  ";
	  
	echo "
		<div id='divSubFilter2' class='divSubFilter'>
			<select class='form-control ddSubFilter' name='ddSubFilter2' id='ddSubFilter2'>
				<option value='0'>ALL DEPARTMENTS</option>
				<option value='1'>Accounting</option>
				<option value='2'>Administration</option>
				<option value='3'>Bankruptcy and Financial Restructuring</option>
				<option value='4'>Corporate</option>
				<option value='5'>Data Processing</option>
				<option value='6'>Environmental Law and Toxic Torts</option>
				<option value='7'>Family and Personal Law</option>
				<option value='8'>Government and Regulatory Law</option>
				<option value='9'>Healthcare</option>
				<option value='10'>Library</option>
				<option value='11'>Litigation</option>
				<option value='12'>Office Services</option>
				<option value='13'>Paralegal</option>
				<option value='14'>Real Estate</option>
				<option value='15'>Secretary</option>
			</select>
		</div>
	  ";
	  
	echo "
		<div id='divSubFilter3' class='divSubFilter'>
			<select class='form-control ddSubFilter' name='ddSubFilter3' id='ddSubFilter3'>
				<option value='0'>ALL TITLES</option>
				<option value='1'>Accounting Coordinator</option>
				<option value='2'>Accounting Supervisor</option>
				<option value='3'>Accounting/Credit Manager</option>
				<option value='4'>Accounts Payable Specialist</option>
				<option value='5'>Accounts Receivable Specialist</option>
				<option value='6'>Associate</option>
				<option value='7'>Benefits Manager</option>
				<option value='8'>Billing & Collections Specialist</option>
				<option value='9'>Billing Clerk</option>
				<option value='10'>Billing Specialist</option>
				<option value='11'>Business Development Specialist</option>
				<option value='12'>Chief Financial Officer</option>
				<option value='13'>Chief Marketing Officer</option>
				<option value='14'>Client Development Executive</option>
				<option value='15'>Conflicts Coordinator</option>
				<option value='16'>Director of Facilities</option>
				<option value='17'>Director of Human Resources/Diversity</option>
				<option value='18'>Director of Litigation/Information Technology</option>
				<option value='19'>Docket Clerk</option>
				<option value='20'>Docket Coordinator</option>
				<option value='21'>Equity Partner</option>
				<option value='22'>Equity Partner (Managing Partner)</option>
				<option value='23'>Facilities Clerk</option>
				<option value='24'>Facilities Supervisor</option>
				<option value='25'>Files Clerk</option>
				<option value='26'>Files/Records Management Supervisor</option>
				<option value='27'>Financial Reporting/Payroll Manager</option>
				<option value='28'>Financial Systems Manager</option>
				<option value='29'>Hospitality Coordinator</option>
				<option value='30'>HR Specialist/Secretarial Manager</option>
				<option value='31'>Human Resources Coordinator</option>
				<option value='32'>Income Partner</option>
				<option value='33'>Intelligence Director</option>
				<option value='34'>Lead Technology Service Desk Analyst</option>
				<option value='35'>Legal Assistant</option>
				<option value='36'>Library Clerk</option>
				<option value='37'>Library Services Manager</option>
				<option value='38'>Litigation Technology Coordinator</option>
				<option value='39'>Marketing Assistant</option>
				<option value='40'>Marketing Coordinator</option>
				<option value='41'>Network Administrator</option>
				<option value='42'>Network Engineer</option>
				<option value='43'>Network Services Manager</option>
				<option value='44'>Nurse Paralegal</option>
				<option value='45'>Of Counsel</option>
				<option value='46'>Office Manager</option>
				<option value='47'>Office Services Clerk</option>
				<option value='48'>Office Services Supervisor</option>
				<option value='49'>Paralegal</option>
				<option value='50'>Paralegal Clerk</option>
				<option value='51'>Paralegal Manager</option>
				<option value='52'>Paralegal Secretary</option>
				<option value='53'>Receptionist</option>
				<option value='54'>Records Manager</option>
				<option value='55'>Secretary</option>
				<option value='56'>Senior Accounts Payable Specialist</option>
				<option value='57'>Senior Counsel</option>
				<option value='58'>Senior Executive Assistant</option>
				<option value='59'>Senior Network Administrator</option>
				<option value='60'>Senior Technology Analyst</option>
				<option value='61'>SharePoint Developer</option>
				<option value='62'>Special Assignment Secretary</option>
				<option value='63'>Special Litigation Assistant</option>
				<option value='64'>Staff Accountant</option>
				<option value='65'>Staff Attorney</option>
				<option value='66'>Technology Service Desk Analyst</option>
				<option value='67'>Technology Services Manager</option>
				<option value='68'>Training Specialist</option>
			</select>
		</div>
	  ";
	  
	echo "</div>\n";//end col
	
	echo "</div>\n";//end row
	
	echo "<div class='row' style='margin-top:25px;'>\n";
	
	echo "<div class='col-sm-6'>";
	echo '
		<div class="panel panel-primary">
		<div class="panel-heading padding-thin">Respondent Profile</div>
		<div class="panel-body">
			<canvas id="profileChart"></canvas>
		</div>
		</div>
	';
	echo "</div>";//end col
	
	echo "</div>\n";//end row
	
	echo "<div class='row' style='margin-top:25px;'>\n";
	
	echo "<div class='col-sm-6'>";
	echo '
		<div class="panel panel-primary">
		<div class="panel-heading padding-thin">Cost To Firm - Legal</div>
		<div class="panel-body">
			<canvas id="legalChart"></canvas>
		</div>
		</div>
	';
	echo "</div>";//end col
	
	echo "<div class='col-sm-6'>";
	echo '
		<div class="panel panel-primary">
		<div class="panel-heading padding-thin">Cost To Firm - Support</div>
		<div class="panel-body">
			<canvas id="supportChart"></canvas>
		</div>
		</div>
	';
	echo "</div>";//end col
	
	echo "</div>\n";//end row
	
	echo "</div>\n";//end well
	echo "</form>";
?>
<script>
$(document).ready(function(){
	$('.divSubFilter').addClass('hidden-none');
	$('#ddFilter').on('change', function() {
		$('.divSubFilter').addClass('hidden-none');
		$(".ddSubFilter").val(0);
		var thisval = this.value;
		$('#divSubFilter'+thisval).removeClass('hidden-none');
		resetdata();
	})
	$('.ddSubFilter').on('change', function() {
		var thisval = this.value;
		if(thisval == 0) {
			resetdata();
		} else {
			adddata();
		}
	})
	///////////////////////////////////////////////////////////////////
		var ctx = document.getElementById("legalChart");
		var barChartLegal = new Chart(ctx, {
			type: 'horizontalBar',
			data: {
				labels: ['Practice','Office', 'Department'],
				datasets: [
					{
						data: [11715389,8765432,1945678],
						backgroundColor: 'rgba(65, 142, 200, 0.7)',
						borderColor: 'rgba(65, 142, 200, 1)',
						borderWidth: 1
					}
				]
			},
			options: {
			tooltips: {
			  callbacks: {
					label: function(tooltipItem, data) {
						var value = data.datasets[0].data[tooltipItem.index];
						//var label = data.labels[tooltipItem.index];
						//var percentage = Math.round(value / totalSessions * 100);
						value = value.toString();
						value = value.split(/(?=(?:...)*$)/);
						value = value.join(',');
						return 'Cost to Firm:   $' + value;
					}
			  } // end callbacks:
			}, //end tooltips
				legend: {
					display: false,
				},
				scales: {
					xAxes: [{
						gridLines: {display:true},
						ticks: {
							beginAtZero: true,
							userCallback: function(value, index, values) {
								// Convert the number to a string and splite the string every 3 charaters from the end
								value = value.toString();
								value = value.split(/(?=(?:...)*$)/);
								
								// Convert the array to a string and format the output
								value = value.join(',');
								return '$' + value;
							}
						}
					}],
					yAxes: [{
							//barPercentage: 0.7
					}]
				},
				title: {
					display: false,
					text: 'Questions Ranked Highest To Lowest',
					fontSize: 16,
					fullWidth: true
				}
			}
		});
	///////////////////////////////////////////////////////////////////
		var ctx = document.getElementById("supportChart");
		var barChartSupport = new Chart(ctx, {
			type: 'horizontalBar',
			data: {
				labels: ['Practice','Office', 'Department'],
				datasets: [
					{
						data: [14715389,9765479,2745567],
						backgroundColor: 'rgba(65, 142, 200, 0.7)',
						borderColor: 'rgba(65, 142, 200, 1)',
						borderWidth: 1
					}
				]
			},
			options: {
			tooltips: {
			  callbacks: {
					label: function(tooltipItem, data) {
						var value = data.datasets[0].data[tooltipItem.index];
						//var label = data.labels[tooltipItem.index];
						//var percentage = Math.round(value / totalSessions * 100);
						value = value.toString();
						value = value.split(/(?=(?:...)*$)/);
						value = value.join(',');
						return 'Cost to Firm:   $' + value;
					}
			  } // end callbacks:
			}, //end tooltips
				legend: {
					display: false,
				},
				scales: {
					xAxes: [{
						gridLines: {display:true},
						ticks: {
							beginAtZero: true,
							userCallback: function(value, index, values) {
								// Convert the number to a string and splite the string every 3 charaters from the end
								value = value.toString();
								value = value.split(/(?=(?:...)*$)/);
								
								// Convert the array to a string and format the output
								value = value.join(',');
								return '$' + value;
							}
						}
					}],
					yAxes: [{
							//barPercentage: 0.7
					}]
				},
				title: {
					display: false,
					text: 'Questions Ranked Highest To Lowest',
					fontSize: 16,
					fullWidth: true
				}
			}
		});

////////////////////////////////////////////////////////////
	var data = [117, 141,39];
	var labels = ["Legal", "Support", "Contract"];
	var bgColor = ["#0078ff", "#ff9c00", "#00c809", "#4ACAB4", "#c0504d", "#8064a2", "#772c2a", "#f2ab71", "#2ab881", "#4f81bd", "#2c4d75"];
	var ctx = document.getElementById("profileChart");
	var pieProfile = new Chart(ctx, {
		type: 'pie',
		data: {
				labels: labels,
				datasets: [
					{
						data: data,
						backgroundColor: bgColor
					}]
		},
		options: {
			pieceLabel: {
			  // mode 'label', 'value' or 'percentage', default is 'percentage'
			  mode: 'label',
		
			  // precision for percentage, default is 0
			  precision: 0,
		
			  // font size, default is defaultFontSize
			  fontSize: 12,
		
			  // font color, default is '#fff'
			  fontColor: '#FFF',
		
			  // font style, default is defaultFontStyle
			  fontStyle: 'bold',
		
			  // font family, default is defaultFontFamily
			  fontFamily: "'Helvetica', 'Arial', sans-serif",
		
			  // draw text in arc, default is false
			  arcText: false,
		
			  // format text, work when mode is 'value'
			  format: function (value) { 
				return '$' + value;
			  }
			},
			responsive: true,
			legend: {display: false},
			tooltips: {
			  callbacks: {
					label: function(tooltipItem, data) {
						var value = data.datasets[0].data[tooltipItem.index];
						//var label = data.labels[tooltipItem.index];
						//var percentage = Math.round(value / totalSessions * 100);
						value = value.toString();
						value = value.split(/(?=(?:...)*$)/);
						value = value.join(',');
						var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
						var label = data.labels[tooltipItem.index];
						return label + ':   ' + value;
					}
			  } // end callbacks:
			} //end tooltips
		}
	});
	function resetdata() {
		pieProfile.data.datasets[0].data = [117, 141,39];
		pieProfile.update();
		barChartLegal.data.datasets[0].data = [11715389,8765432,1945678];
		barChartLegal.update();
		barChartSupport.data.datasets[0].data = [14715389,9765479,2745567];
		barChartSupport.update();
	}
	function adddata() {
		pieProfile.data.datasets[0].data = [rando(25,100),rando(25,100),rando(15,30)];
		pieProfile.update();
		barChartLegal.data.datasets[0].data = [rando(1000000,5000000),rando(500000,1000000),rando(5000,500000)];
		barChartLegal.update();
		barChartSupport.data.datasets[0].data = [rando(1000000,5000000),rando(500000,1000000),rando(5000,500000)];
		barChartSupport.update();
	}
function rando(min, max) {
    const randomBuffer = new Uint32Array(1);

    window.crypto.getRandomValues(randomBuffer);

    let randomNumber = randomBuffer[0] / (0xffffffff + 1);

    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(randomNumber * (max - min + 1)) + min;
}
});
</script>
<?php
}
?>
