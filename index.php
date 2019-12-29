<?php
// initial variable declare
$heading = [];
$dataset = [];

/*
* It is main function to create final algorithm
* @parameter: heading array, dataset array, total column int 
* @return: array 
* @operation: calculate entropy and calculate gain and check height gain
*  
*/
function entropy_algorithm($heading, $dataset, $ac) {
	$total_row = count($dataset);
	$total_column = $ac;
	$total_entropy = 0;
	
	// calculate total entropy of full dataset
	$actionable_data = array_values(array_unique(array_column($dataset, $total_column)));
	foreach($actionable_data as $k => $val){
		$count = count(array_filter($dataset, function ($entry) use ($val, $total_column) {
			return $entry[$total_column] == $val;
	  }));
	  $total_entropy += - $count/$total_row * log($count/$total_row, 2);
	}
	
	// sub dataset create for every unique values
	// count sub dataset total number and action count
	$final_data_set = [];
	
	foreach($heading as $key => $value){
		
		// skip fist and last index
		if(in_array($key, [0, $total_column])){
			continue;
		}
		if(! isset($final_data_set[$value])){
			$final_data_set[$value] = [];
		}
		
		foreach($dataset as $dataset_key => $dataset_value){
			$sub_data_set = $dataset_value;
			$sub_heading = $heading;
			
			// consider own attribute to next heading and dataset
			unset($sub_data_set[$key]);
			unset($sub_heading[$key]);
			
			if(!isset($final_data_set[$value][$dataset_value[$key]])){
				$final_data_set[$value][$dataset_value[$key]] = [
					'count' => 1,
					'action' => [
						$dataset_value[$total_column] => 1
					],
					'heading' => $sub_heading,
					'dataset' => [$sub_data_set]
				];
			}else{
				$final_data_set[$value][$dataset_value[$key]]['count'] += 1;
				if(isset($final_data_set[$value][$dataset_value[$key]]['action'][$dataset_value[$total_column]])){
					$final_data_set[$value][$dataset_value[$key]]['action'][$dataset_value[$total_column]] += 1;
				}else{
					$final_data_set[$value][$dataset_value[$key]]['action'][$dataset_value[$total_column]] = 1;
				}
				$final_data_set[$value][$dataset_value[$key]]['dataset'][] = $sub_data_set;
			}
		}
	}
	
	/*
	* entropy and gain calculation here
	*and also find out height gain value
	*/
	$height_gain = 0;
	$height_gain_name = '';
	$height_gain_data = [];
	
	foreach($final_data_set as $values => $data){
		$att_gain = $total_entropy;
		foreach($data as $key => $action_data){
			$values_entropy = 0;
			$total_att_values = $action_data['count'];
			if(!empty($action_data['action'])){
				foreach($action_data['action'] as $k => $action_count){
					 $values_entropy += - $action_count/$total_att_values * log($action_count/$total_att_values, 2);
				}
			}
			$final_data_set[$values][$key]['entropy'] = $values_entropy;
			$att_gain -= (($total_att_values/$total_row) * $values_entropy);
		}
		$final_data_set[$values]['gain'] = $att_gain;
		
		if($att_gain > $height_gain){
			$height_gain = $att_gain;
			$height_gain_name = $values;
		}
	}
	
	// recursive function call area
	foreach($final_data_set[$height_gain_name] as $h_key => $h_value){
		if(is_array($h_value['dataset'])){
			if(!empty($h_value['dataset'])){
				if(count(array_values(array_unique(array_column($h_value['dataset'], $total_column)))) > 1){
					// if array, then function call again
					$height_gain_data[$height_gain_name][$h_key] = entropy_algorithm($h_value['heading'], $h_value['dataset'], $ac);
				}else{
					// if not array then return value
					$height_gain_data[$height_gain_name][$h_key] = array_values(array_unique(array_column($h_value['dataset'], $total_column)))[0];
				}
			}
		}
	}
	return $height_gain_data;
}

/*
* Execute this block when click the process button.
* get csv file and divided into two array 
* one for heading its include attribute name only
* another for dataset  
*/
if (isset($_POST["import"])) {
    
    $fileName = $_FILES["file"]["tmp_name"];
	
    if ($_FILES["file"]["size"] > 0) {
        
        $file = fopen($fileName, "r");
		
		$heading_create = true;
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
			if($heading_create){
				for($i = 0; $i < count($column); $i++){
					$heading[$i] = ucfirst(strtolower(trim($column[$i])));
				}
				$heading_create = false;
			}else{
				
				for($j = 0; $j < count($column); $j++){
					$temp[$j] = ucfirst(strtolower(trim($column[$j])));
				}
				$dataset[] = $temp;
			}
        }
		$algorithm = [];
		
		if(count($dataset) > 1){
			$algorithm = entropy_algorithm($heading, $dataset, count($dataset[0]) - 1);
		}
		
		if(count($algorithm) > 0){
			if(file_exists("desicion_alogrithm.json")){
				// if exists then remove old file
				unlink("desicion_alogrithm.json");
			}
			$alo_heading = $heading;
			unset($alo_heading[0]);
			//unset($alo_heading[count($alo_heading)]);
			
			$output['heading'] = $alo_heading;
			$output['algorithm'] = $algorithm;
			
			$myfile = fopen("desicion_alogrithm.json", "w") or die("Unable to open file!");
			$txt = json_encode($output);
			
			// json file write
			fwrite($myfile, $txt);
			fclose($myfile);
		}
    }
}
?>
<!DOCTYPE html>
<html>

<head>
<title> Machine Learning </title>
<script src="jquery-3.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" media="all" />
<script type="text/javascript">
$(document).ready(function() {
    $("#frmCSVImport").on("submit", function () {

	    $("#response").attr("class", "");
        $("#response").html("");
        var fileType = ".csv";
        var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(" + fileType + ")$");
        if (!regex.test($("#file").val().toLowerCase())) {
        	    $("#response").addClass("error");
        	    $("#response").addClass("display-block");
            $("#response").html("Invalid File. Upload : <b>" + fileType + "</b> Files.");
            return false;
        }
        return true;
    });
});
</script>
</head>

<body>
    <h2>Please Upload Traning Data set in CSV file format using PHP</h2>
    
    <div id="response" class="<?php if(!empty($type)) { echo $type . " display-block"; } ?>"><?php if(!empty($message)) { echo $message; } ?></div>
    <div class="outer-scontainer-left">
        <div class="row">

            <form class="form-horizontal" action="" method="post"
                name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                <div class="input-row">
                    <label class="col-md-4 control-label">Choose CSV
                        File</label> <input type="file" name="file"
                        id="file" accept=".csv">
                    <button type="submit" id="submit" name="import"
                        class="btn-submit">Process</button>&nbsp;&nbsp;
					<?php if(file_exists("desicion_alogrithm.json")):?>	
						<a href="algorithm_test.php" target="_blank">Algorithm Test</a>	
					<?php endif;?>	
                    <br />

                </div>

            </form>

        </div>
		<?php
			
            if (count($heading) > 0 && count($dataset) > 0) {
				?>
				<table id='userTable'>
					<thead>
						<tr>
							<?php foreach($heading as $h_k => $h_v) {?>
								<th><?php echo $h_v;?></th>
							<?php } ?>

						</tr>
					</thead>
					<?php
					
						foreach($dataset as $key => $val) {
						?>
							<tbody>
							<tr>
								<?php foreach($val as $d_k => $d_v) { ?>
									<td><?php  echo $d_v; ?></td>
								<?php } ?>
							</tr>
						<?php
						}
					?>
					</tbody>
				</table>
				<?php
			}
		?>
    </div>
	<div class="outer-scontainer-right">
		<div class="row">
			<?php 
				if(!empty($algorithm)){
					echo '<pre>';
					print_r($algorithm);
					echo '</pre>';
				}				
			?>
		</div>
	</div>
</body>

</html>