<!DOCTYPE html>
<html>
<head>
	<title>Multiview Stock Recommendations</title>

<?php 
    $serverName = $_SERVER['SERVER_NAME'];
?>

<?php if ($serverName == 'localhost'): ?>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/highstock.js"></script>
    <script src="js/highcharts-more.js"></script>
    <script src="js/exporting.js"></script>
    <script src="js/handlebars.min.js"></script>
<?php else: ?>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/stock/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<?php endif; ?>

	<script src="js/dynamicpath.js"></script>

	<!-- Bootstrap JS -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>
	<div class="container-fluid">
	  <div class="row">
	    <p><b>Multiview Stock Recommendations</b></p> 
	  </div>
	  <div class="row">
	    <div id="chart1" class="col-sm-3">
	      <h3>Column 1</h3>
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	    <div class="col-sm-3">
	      <h3>Column 2</h3>
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	    <div class="col-sm-3">
	      <h3>Column 3</h3> 
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	    <div class="col-sm-3">
	      <h3>Column 4</h3> 
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	  </div>
	</div>
</body>

<?php
    //select the company you want to view
    if(isset($_GET['company'])) {
        $company = strtolower($_GET['company']."");
    }
    else {
        //echo "ERROR: No Company was selected<Br/>";
        $company = "smc";
    }
?>

<script type="text/javascript">

</script>
</html>