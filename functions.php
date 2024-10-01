<?php

/*
* Database Connect
*/
function dbConnect ($dataBaseName = null, $_user = null, $_password = null) {
	
	$mamp = array ("localhost", "root", "root");
	
	
	
	
	switch ($_SERVER['HTTP_HOST']) {
	
		case 'localhost:8888' : 
			$host = $mamp[0];
			$user = $mamp[1];
			$password = $mamp[2];
			break;
			
		default :
			$host = "localhost";
			$user = $_user;
			$password = $_password;
			break;
	}
	
	
	
	// For custom usernames and passwords
	if(isset($_user, $_password)) {
		$host = "localhost";
		$user = $_user;
		$password = $_password;	
	}
	
	// mysqli connection varable
	$con = mysqli_connect($host, $user, $password);
	
	if(!$con) {
		#echo "Bad username and password";
		return FALSE;	
	}
	
	// If the $dataBaseTable varable is passed 
	// and we cannot connect to db return false
	if(isset($dataBaseName)) {
		if(!(mysqli_select_db($con, $dataBaseName))) {
			return FALSE;	
		}
	}
	
	// Returns the connection varable
	return $con;
	
}

//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
/*
* Basic mysqli_query
*
*
*/
function basicQuery ($con = null, $tableParameter = null, $queryParameter = null, $echoSQL = null, $arrayType = null, $success = null) {
	
	if(!$con && $echoSQL) {
        echo "connect to the db wonderful person!\n"; 
        $php = debug_backtrace(); 
        p($php[1]['file']); echo " "; p($php[1]['line']);  l(3);
    }
	
	if(!$queryParameter && $tableParameter && $queryParameter !== 1) {
		$query = "Select * From $tableParameter";		
	} elseif ($tableParameter === 1) {
		#echo $queryParameter; l(1);
		p($queryParameter, 1); l(1);
	} else {
		$query = $queryParameter;	
	}


	// Execute the results
	$results = mysqli_query($con, $query);
	
	//p($query, 'query in basiqQuery');
	
	// Display mySQL error
	if($echoSQL && mysqli_error($con)) {
		echo "\n\n<h2>" . mysqli_error($con) . "</h2>\n\n";	
		echo "\n\n<h2>" . mysqli_info($con) . "</h2>\n\n";	
	
	} 
	if(mysqli_error($con) && $echoSQL) {
		echo "<p> <strong>Regarding:	</strong> $query</p>";
	}
	if(!mysqli_error($con) && $echoSQL && $echoSQL != 10) {
		// If we wanted to echo out 'No Errors'
	//	echo "No known errors " . time() . "";
	//	echo " - Rows effected: " . mysqli_affected_rows($con) . "\n";	
	}
	
	if(!$results && !$success) {
		return 0;	
	}
	
		
	// Determine rather the mysqli_array is assoc, index or both
	if($arrayType === 1) {
		$arrayIsType = MYSQLI_NUM;	
	} elseif ($arrayType === 2) {
		$arrayIsType = MYSQLI_ASSOC;
	} else {
		$arrayIsType = MYSQLI_BOTH;
	}
		
	// Define the returnArray
	$returnValue = array ();
	
	
	while ($rows = mysqli_fetch_array($results,$arrayIsType)) {
		$returnValue [] = $rows;		
	}
	
	if($success) {
		//echo mysqli_affected_rows($results);
		$success = mysqli_affected_rows($con);
		if(!$success) {
			$success = $results;
			return $results;
		} 
		return $success;
		
	}
	
	if($tableParameter === 2) p($returnValue);
	
    if($echoSQL == 10) $returnValue = ctsa($returnValue);
    
	return $returnValue;
	
	
}
/* SHORT CUT */
function BQ ($query = null, $dont_print_results = null) {
	global $con;
	$r = basicQuery($con, "", $query, 0, 2);
	if($dont_print_results == 0) {
		p($r);	
	}
	return $r;
}
/* SHORT CUT */
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
function basicInsert ($tableName = null, $array = null, $echoResults = null, $test = null, $success = null, $con_or = null) {
	global $con; 
    if($con_or) $con = $con_or;
	$keys = array_keys($array[0]);
	
	/*
		[0] => name
		[1] => age
		[2] => Job
	*/
	$columnNames = '';
	foreach ($keys as $b => $a) {
		$columnNames .= "`$a`";
		if($keys[$b + 1]) {
			$columnNames .= ", ";
		}
	}
	
	#echo $columnNames;
	$query = "INSERT INTO `$tableName` (" . $columnNames . ")
";
	
	$query .= " VALUES ";
	
	$count = count($array[0]);
	foreach ($array as $b => $a) {
		$query .= "(";
		
		$count = count($a); $n = 1;
		
		foreach ($a as $i) {
			
			// Comment this out for now since we are now sending all text through formatted
            if($i !== null)
			$i = mysqli_real_escape_string($con, $i);
			
			// We use this because we want to know rather or not to put backticks
			switch (gettype($i)) {
				case "string": 
						$query .= " '$i' ";
						break;
				case "integer": 
						$query .= " $i ";
						break;
                case "NULL": 
						$query .=  ' NULL ' ;
						break;
				default:
						$query .= "'$i'";
						break;
			}
			
			// Count is defined before the start of the second foreach loop
			// We use $count becasue we $i is an assocc array and we cannot use [n+1] to see if we in last loop
			if($count > $n) {
				$query .= ",";	
			} else {
				$query .= ")
";
			}
			$n++;
			
		} // End of foreach ($a as $i)
		if($array[$b + 1]) {
			$query .= ",";	
		}
		
	}
	
	if($test == 1) {
		echo $query;  l();
		return;
	}
	#echo $query;
	

	$results = basicQuery($con, 0, $query, $echoResults, 2, $success);

	if($success == 1) {
		return $results;
	} elseif ($success == 2) {
		
		



	}
	
		
	
}
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
function basicUpdate($tableName = null, $array = null, $WHERE = null, $echoResults = null, $test = null, $success = null, $conor = null) {
	global $con; 
    if($conor) $con = $conor;
    
	$query = "UPDATE $tableName";
	$query .= " SET ";
	
	$i = 0;
	$count = count($array);
	
	foreach ($array as $b => $a) {
	
            if($a === null) 
                $query .= " $b = NULL ";
            
            else {
                $a = mysqli_real_escape_string($con, $a); 
			    $query .= " `$b` = '$a' ";
            }
			
			
			if($count > $i+1) {
				$query .= ",";	
			} else {
				$query .= " WHERE $WHERE";
                if(preg_match("/id \=/", $WHERE)) {
                  $query .= " LIMIT 1";  
                }
				if($test == 1) $query .= "\n";
			}
			$i++;
			
	} 
	if($test == 1) {
		echo $query;
		return;
	}
	return basicQuery($con, 0, $query, $echoResults, 2, $success);
}
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
function convert_to_single_array($array = null, $convert_to_string = null, $remove_assoc = null) {
	$single_array = array();
	
	if($remove_assoc == 1)  {
		$array = array_values($array); # Turn the array into an array [0, 1, 2, 3...] 
	}
	foreach ($array as $b => $a) {
		foreach ($a as $d => $c) {
			$single_array[$d] = $c;	
		}
	}
	if($convert_to_string === 1) {
		$single_array = trim(implode(" ", $single_array)); 	
	}
	return $single_array;
}
function ctsa ($array = null, $convert_to_string = null, $remove_assoc = null) {
	return convert_to_single_array($array, $convert_to_string, $remove_assoc);
}
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\


//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\







function getTime ($string = null, $TIME_OVERRIDE = null) {
	if ($string == 1) {
		date_default_timezone_set('America/Los_Angeles');
		$time = date("m/d/y") . " --> " . date("h:i") . " " . date("a");	
		return $time;
	}
	if(!$TIME_OVERRIDE) $TIME_OVERRIDE = time();
	
	return date("Y-m-d H:i:s", $TIME_OVERRIDE);
	
}






function l ($a = null) {
	return;
	$c = "";
	if($a > 0) {
		for($i = 0; $i < $a; $i++) {
			$c .= "\n";
		}
		echo $c;
		return $c;
	}
	 echo "\n\n\n\n\n";
}
function p ($a = null, $pre = null) {
	#$pre = 1;
    return;
    if(isset($pre) && $pre !== 1) {
		if($pre == 10) $pretxt = 'DEBUGGING';
		else $pretxt = $pre;
        echo "\n--------------\n$pretxt\n";
    }
	if($pre == 1) echo "<pre>";
	if($a === 1) {
		if(!$_POST) var_dump($_POST);
		else print_r($_POST);
		if($pre == 1) echo "</pre>";
		return 1;
	}
	if($a === 2) {
		global $con;
		if(!$con) var_dump($con);
		else print_r($con);
		if($pre == 1) echo "</pre>";
		return 2;
	}
	if(!$a) {
		var_dump($a);if($pre == 1) {echo "</pre>"; }return;	
	}
	print_r($a);
	if($pre == 1) echo "</pre>";
    
    if(isset($pre) && $pre !== 1) {
        echo "\n--------------\n";
    }
}

function ll ($a = null, $n = null) {
	#$pre = 1;
	$pre = '';
    if(isset($n) && $n === 1) {
        echo "\n--------------\n$pre\n";
    }
    if(isset($n) && $n !== 1)
        $l_length  = $n;
    else $l_length = $n;
    
	l($l_length);
	if(!$a) {
		var_dump($a);if($pre == 1) {echo "</pre>"; }return;	
	}
	print_r($a);
    l($l_length);
    
    if(isset($pre) &&     $n === 1) {
        echo "\n--------------\n";
    }
}





//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
function generic_login ($con = null, $db_table = null, $column_name_user = null, $column_name_password = null) {
	
	session_start();
	#print_r($_SESSION);
	$session_timeout = 60 * 60 * 2;  // Timeout after 2 hours
	//p(1);
	
	// Destroy any old sessions
	if($_GET['logout']) {
		// destroy the session 
		session_destroy();
		echo "
		<!DOCTYPE html>
		<html><body>";
			if($_GET['unknow']) {
				echo "<p>Enable to proccess request at this time</p>";
				echo "<p>Please contact your system administrator for further explanation</p>";	
			} else {
			echo "<p>You have logged out</p>";
			}
			echo "<p><a href=\"{$_SERVER['SCRIPT_NAME']}\">Return</a></p>
		</body></html>
		";
		die;
		
	}
	
	
	$post = array("username" => $_POST['user'], "password" => $_POST['password']);
	if(!$column_name_user || !$column_name_password) {
		$column_name_user = "username"; $column_name_password = "password";	
	}
	
	
	// Define the base page
	$basePage = '';
	$basePage .= "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Log-in</title>
    <meta name='viewport' content='width=device-width, initial-scale=1' />";
    
// Check if the connection is HTTPS and set the protocol accordingly
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https://';
} else {
    $protocol = 'http://';
}

$basePage .= "
    <link rel='stylesheet prefetch' href='{$protocol}ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css'>
    <style>@import url({$protocol}fonts.googleapis.com/css?family=Roboto:400,100); body {background-color:;} .login-card {font-family: 'Roboto', sans-serif; padding: 40px; width: 274px; background-color: #F7F7F7; margin: 10% auto 10px; border-radius: 2px; box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3); overflow: hidden; } .login-card h1 { font-weight: 100; text-align: center; font-size: 2.3em; } .login-card input[type=submit] { width: 100%; display: block; margin-bottom: 10px; position: relative; } .login-card input[type=text], input[type=password] { height: 44px; font-size: 16px; width: 100%; margin-bottom: 10px; -webkit-appearance: none; background: #fff; border: 1px solid #d9d9d9; border-top: 1px solid #c0c0c0; /* border-radius: 2px; */ padding: 0 8px; box-sizing: border-box; -moz-box-sizing: border-box; } .login-card input[type=text]:hover, input[type=password]:hover { border: 1px solid #b9b9b9; border-top: 1px solid #a0a0a0; -moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); } .login { text-align: center; font-size: 14px; font-family: 'Arial', sans-serif; font-weight: 700; height: 36px; padding: 0 8px; /* border-radius: 3px; */ /* -webkit-user-select: none; user-select: none; */ } .login-submit { /* border: 1px solid #3079ed; */ border: 0px; color: #fff; text-shadow: 0 1px rgba(0,0,0,0.1); background-color: #4d90fe; /* background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#4d90fe), to(#4787ed)); */ } .login-submit:hover { /* border: 1px solid #2f5bb7; */ border: 0px; text-shadow: 0 1px rgba(0,0,0,0.3); background-color: #357ae8; /* background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#4d90fe), to(#357ae8)); */ } .login-card a { text-decoration: none; color: #666; font-weight: 400; text-align: center; display: inline-block; opacity: 0.6; transition: opacity ease 0.5s; } .login-card a:hover { opacity: 1; } .login-help { width: 100%; text-align: center; font-size: 12px; }</style>
</head>
<body>
<div id='login_body'>
    <div class='login-card'>
        <h1>Log-in</h1><br>
        <form id='login_form' method='post'>
            <input type='text' name='user' id='user' value='{$post['username']}' placeholder='Username' autocomplete='off'>
            <input type='password' id='password' name='password' placeholder='Password'>
            <input type='submit' name='login' class='login login-submit' value='login'>
        </form>
        <div class='login-help'>
            <a href='#'>Register</a> • <a href='#'>Forgot Password</a>
        </div>
    </div>
    <script src='{$protocol}ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
    <script src='{$protocol}ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>

    <script>
        var jmoney = 1;
        $(document).ready(function() {
            $('#login_form').submit(function (e) {
                e.preventDefault();
                var alpha = $('#user').val(), bravo = $('#password').val();
                $.post(\"{$_SERVER['SCRIPT_NAME']}\", {
                    user: alpha,
                    password: bravo	,
                    submitt: \"submit\"
                }, function(result){
                    if(result.substring(0,1) == '[')
                        result = JSON.parse(result);
                    if(typeof result == 'object') {
                        if(result[0] === 'error')
                            $(\"#login_body\").html(result[1]);
                    }
                    else location.reload();
                });
            });
        });
    </script>
</div>
</body>
</html>
";
	
	
	// Used 'submitt' b/c submit was a reserved javascript word
	if(isset($_POST['submitt']) && !($_GET['logout'])) {
		
		// Convert the password to md5
		$post['password'] = md5($post['password']);
		
		// Validate the username and password
		$login_results = convert_to_single_array(basicQuery($con, "", "SELECT $column_name_user, $column_name_password FROM $db_table WHERE $column_name_user = '{$post['username']}' AND $column_name_password = '{$post['password']}'", 0, 2));
		if($login_results[$column_name_user] !== $post['username'] || $login_results[$column_name_password] !== $post['password']) {
			
			$return_value = json_encode(array(
			"error", "$basePage Invalid Username or Invalid Password"
			));
			//$return_value = $basePage;
		}
		else {
			$_SESSION['username'] = $login_results[$column_name_user];
			//$_SESSION['owner_id'] = "";
			$_SESSION['start'] = time();
			$return_value = 1;
		}
		
	}  elseif ($_SESSION['username'] && !($_GET['logout']) && ($_SESSION['start'] + $session_timeout) > time())  {
			// Keep the sessison alive but dont reset the session varables	
			$return_value = 1;
			
	} else {
		$return_value = $basePage;	
	}
			
	if($return_value !== 1) {
		echo $return_value;
		die;
	} else {
		return 1;	
	}

	
}

function JE ($json_encode = null, $not_pretty = null, $DO_NOT_ECHO = null) {
	$json_pretty = JSON_PRETTY_PRINT; 
	if($not_pretty) $json_pretty = FALSE;
	if($DO_NOT_ECHO) return json_encode($json_encode, $json_pretty);
	else echo json_encode($json_encode, $json_pretty); return;
}
function JSON ($json_encode = null, $not_pretty = null, $DO_NOT_ECHO = null) {
    //p($json_encode);
	$json_pretty = JSON_PRETTY_PRINT|JSON_PARTIAL_OUTPUT_ON_ERROR; 
	if($not_pretty) $json_pretty = JSON_PARTIAL_OUTPUT_ON_ERROR; 
	if($DO_NOT_ECHO) return json_encode($json_encode, $json_pretty);
	else echo json_encode($json_encode, $json_pretty); return;
}
//




//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
function mres ($str = null) {
	global $con;
	return mysqli_real_escape_string($con, $str);	
}
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
//______________________________________________________________________________________________________________________________________________________\\
