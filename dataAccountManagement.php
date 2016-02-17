<?php 
	function tableExists($con, $table) {
	    $sql = "SHOW TABLES LIKE '$table'";
	    $result = mysqli_query($con, $sql);

	    if(mysqli_num_rows($result) > 0) {
	        return true;
	    } 
	    else {
	    	return false;
	    }
	}

	// generate the names of all the companies
	function importHoldingsData ($host, $db, $user, $pass, $data) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		//create table if it doesn't exist yet
		$tableExists = tableExists($con, "current_holdings");
		if (!$tableExists) {
			$createTable = "CREATE TABLE `pse_data`.`current_holdings` (
							  `quote` VARCHAR(16) NOT NULL,
							  `datebuy` DATE NOT NULL,
							  `pricebuy` FLOAT NULL,
							  `volume` INT NULL,
							  `pricestoploss` FLOAT NULL,
							  PRIMARY KEY (`quote`))
							COMMENT = 'contains the current stocks being held and the stop loss selling price'";

			$result = mysqli_query($con, $createTable);

			echo "importHoldingsData: Created table 'current_holdings' <Br><Br>";
		}

		$sql = "REPLACE INTO current_holdings (datebuy, quote, pricebuy, volume)";

		$holdingValues = " VALUES";
		
		$dataSize = count($data);
		$ctr = 0;

		foreach ($data as $holdings) {
			$holdingValues = $holdingValues . "('".$holdings['date']."','".$holdings['company']."','".$holdings['pricebuy']."','".$holdings['volume']."')";

			$ctr++;
			if ($ctr < $dataSize) {
				$holdingValues = $holdingValues . ", ";
			}
		}

		$sql = $sql . $holdingValues;

		echo "$sql";

		$result = mysqli_query($con, $sql);

		if (mysqli_affected_rows($con) < 1) {
			echo "importHoldingsData: Failed Value Insertion!<Br>";
		}

		mysqli_close($con);
	}

?>