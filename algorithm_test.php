<?php
	if(file_exists("desicion_alogrithm.json")){
		$string = file_get_contents("desicion_alogrithm.json");
		if ($string === false) {
			die("Ops! System could not able to read the Algorithm.");
		}

		$json_arr = json_decode($string, true);
		if ($json_arr === NULL) {
			die("Ops! Algorithm is Empty now.");
		}
		$input_heading = $json_arr['heading'];
		$question_name = $input_heading[count($input_heading)];
		unset($input_heading[count($input_heading)]);
		
		$data_algorithm = $json_arr['algorithm'];
		
	}else {
		die("Algorithm file not exist.");
	}
	
	function find_question_result($user_input, $alorithm_data){
		$result = 'Not Identify';
		$al_key = array_keys($alorithm_data)[0];
		if(! is_array($alorithm_data[$al_key][$user_input[$al_key]])){
			$result = $alorithm_data[$al_key][$user_input[$al_key]];
		} else {
			$next_data = $alorithm_data[$al_key][$user_input[$al_key]];
			
			$result = find_question_result($user_input, $next_data);
		}
		return $result;
	}
	
	/*
	* Execute this block when submit button
	*/
	if(isset($_POST["questionSubmit"])){
		unset($_POST['questionSubmit']);
		$user_data = [];
		foreach($_POST as $key => $val){
			$user_data[ucwords(strtolower($key))] = ucwords(strtolower($val));
		}
		
		$result = find_question_result($user_data, $data_algorithm);
	}
?>
<!DOCTYPE html>
<html>
<head>
<title> Algorithm Testing </title>
<script src="jquery-3.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" media="all" />

</head>

<body>
    <div class="outer-scontainer-left">
        <div class="row">

            <form class="form-horizontal" action="" method="post"
                name="algorithmForm" id="algorithmForm" enctype="multipart/form-data">
                <?php foreach($input_heading as $key => $name) { ?>
					<div class="input-row">
						<label for="<?php echo $name; ?>" class="col-md-4 control-label"><?php echo $name;?></label> 
						<input type="text" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo isset($_POST[$name]) ? $_POST[$name] : '';?>" style="text-transform: capitalize" required >
					</div>
				<?php } ?>
				<div class="input-row">
                    <button type="submit" id="submit" name="questionSubmit" class="btn-submit"><?php echo isset($question_name) ? $question_name . '? ' : ' ';?></button>
                </div>

            </form>
		<div> 
			<h2>
				<?php echo isset($result) ? $result : '';?>
			</h2>	
		</div>
        </div>
    </div>
</body>

</html>