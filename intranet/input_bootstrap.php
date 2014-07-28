<?php
	$con = mysqli_connect("localhost", "arvaus5_qg", "quoteGenerator123", "arvaus5_hylite");
	if (mysqli_connect_errno($con)) {
		echo "Failed to connect to database: " . mysqli_connect_error();
	}
?>
<html>
<head>
<title>ARVA - Quote Generator</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<link href="bootstrap-3.1.1-dist/css/bootstrap.min.css" rel="stylesheet">
<script src="bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
<script type="text/javascript">
var led_series = new Array();
	<?php
	$rs = mysqli_query($con, "select sku from products where type = 'led'");
	while($row = mysqli_fetch_array($rs)) {
		echo "led_series.push('" . $row['sku'] . "');";
	}
	?>
	var induction_series = new Array();
	<?php
	$rs = mysqli_query($con, "select sku from products where type = 'induction'");
	while($row = mysqli_fetch_array($rs)) {
		echo "induction_series.push('" . $row['sku'] . "');";
	}
	?>
</script>
<script type="text/javascript" src="js/script.js"></script>
<style>
	/*#sticky { padding: 0.5ex; width: 600px; background-color: #000; color: #fff; font-size: 2em; border-radius: 0.5ex; } */
	#sticky.stick { position: fixed; top: 0; z-index: 10000; border-radius: 0 0 0.5em 0.5em; margin-left:auto; margin-right: auto; }
	div.outbox {
		margin-left:0px;
		margin-right:0px;
		border:2px solid #a1a1a1;
		padding:10px 40px;
		background:#dddddd;
		width:80%;
		min-width:500px;
		border-radius:25px;	
	}
	div.navigation {
		position: fixed; 
		top: 10px; 
		right: 15px; 
		display: block; 
		width: 200px;

		border:2px solid #a1a1a1;
		padding:10px 40px;
		background:#dddddd;
		border-radius:25px;	
	}	
</style>
</head>
<body onload="generate_sections();">
<form class="form-horizontal" role="form" name="data" method="post" action="generator.php" target="_blank">

	<div id="sticky-anchor"></div>
	<div id="sticky" class="outbox"> <!-- was outbox class -->
		<div style="text-align: center; font-weight: bold;">
			Arva Proposal Generator | v1.0.0
		</div>
		<div class="form-group">
			<div><b>Document Type</b><br />
				<select id="doc_type" name="doc_type">
					<option value="proposal">Standard Proposal</option>
					<option value="energy_savings">Energy Savings</option>
					<option value="quote">Quote</option>
				</select>
			</div>
			<div><label>Total Sections</label></div>
			<div>
				<select type="text" class="form-control" id="inputEmail3" name="num_sections" onchange="save_section(); generate_dropdown();">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
				</select>
			</div>
			<div><label>Currently Editing</label></div>
			<div>
				<select class="form-control" id="inputEmail3" name="edit_sections" onchange="save_section(); load_section();">
					<option value="1">#1</option>
				</select>
			</div>
			<br />
			<div><label>Pages</label><a onclick="allPages(true);"> (All</a> | <a onclick="allPages(false);">None)</a></div>
			<div>
				<input type="checkbox" name="page_1" value="true">Title Page<br />
				<input type="checkbox" name="page_2" value="true">Executive Summary<br />
				<!--input type="checkbox" name="page_3" value="true">Induction Benefits<br /-->
				<input type="hidden" name="page_3" value="false">
				<!--input type="checkbox" name="page_4" value="true">LED Benefits<br /-->
				<input type="hidden" name="page_4" value="false">
				<!-- sections -->
				<input type="checkbox" name="page_5" value="true">Section Information<br />
				<!--input type="checkbox" name="page_6" value="true">Savings Comparisons (DEMO)<br /-->
				<input type="hidden" name="page_6" value="false">
				<!--input type="checkbox" name="page_7" value="true">Quote<br /-->
				<input type="hidden" name="page_7" value="false">
				<!--input type="checkbox" name="page_8" value="true">Induction Examples<br /-->
				<input type="hidden" name="page_8" value="false">
				<!--input type="checkbox" name="page_9" value="true">LED Examples<br /-->
				<input type="hidden" name="page_9" value="false">
				<!--input type="checkbox" name="page_10" value="true">Assumptions<br /-->
				<input type="hidden" name="page_10" value="false">
				<input type="checkbox" name="page_11" value="true">Last Page<br />
				<input type="checkbox" name="page_12" value="true">Spec Sheets<br />
			</div>
		</div>		
	</div>

	<br />

	<div class="outbox">
		<div style="text-align: center; font-weight: bold;">Prepared For</div>
		<div class="form-group">
			<div ><label>Name</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="for_name" name="for_name" placeholder=""></div>
			<div><label>Company</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="for_company" name="for_company" placeholder=""></div>
			<div><label>Address</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="for_address" name="for_address" placeholder=""></div>	
			<div><label>City</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="for_city" name="for_city" placeholder=""></div>	
			<div><label>State</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="for_state" name="for_state" placeholder=""></div>							
			<div><label>Zip</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#</span><input type="text" class="form-control" id="for_zip" name="for_zip" placeholder="29730"></div>		
		</div>
	</div>

	<br />

	<div class="outbox">
		<div style="text-align: center; font-weight: bold;">Prepared By</div>
		<div class="form-group">
			<div><label>Name</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="by_name" name="by_name" placeholder=""></div>
			<div><label>Email</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="by_email" name="by_email" placeholder="example@arva.com"></div>
			<div><label>Phone</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="by_phone" name="by_phone" placeholder="(803) 336-2230"></div>		
		</div>
	</div>

	<br />

	<div class="outbox">
		<div style="text-align: center; font-weight: bold;"><h3>Section Data</h3></div>
		<div style="text-align: center; font-weight: bold;">General</div>
		<div class="form-group">
			<div><label>Section Name</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="section_name" name="section_name" placeholder="" onkeyup="save_section(); generate_dropdown();"></div>
			<div><label>Electricity Rate</label></div>
			<div class="col-sm-12">
					<span class="input-group-addon" style="width:10px;">$(/kwh)</span>
					<input type="text" class="form-control" id="section_electricityRate" name="section_electricityRate" placeholder="1.00">
			</div>
			<div><label>Operating Hours</label></div>
			<div class="col-sm-12">
					<span class="input-group-addon" style="width:10px;">#(per year)</span>
					<input type="text" class="form-control" id="section_operatingHours" name="section_operatingHours" placeholder="10000" onblur="validate_input();">
					<div id="error_message" style="color:rgb(255, 0, 0);text-transform:bold;"></div>
			</div>
		</div>
		<div style="text-align: center; font-weight: bold;">Current Fixture</div>
		<div class="form-group" >
			<div><label>Fixture Type</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="section_current_fixture" name="section_current_fixture"></div>
			<div><label># of Fixtures</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="section_current_numFixtures" name="section_current_numFixtures" placeholder="10"></div>
			<div><label>Current Lamp Type</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="section_current_lamp" name="section_current_lamp" placeholder=""></div>
			<div><label>Bulb Rated Lamp Life</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#(hours)</span><input type="text" class="form-control" id="section_current_lampLife" name="section_current_lampLife" placeholder="1000"></div>
			<div><label>Bulb Rated Watts</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#(watts)</span><input type="text" class="form-control" id="section_current_watts" name="section_current_watts" placeholder="1000"></div>
			<div><label># of Lamps / Fixture</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#</span><input type="text" class="form-control" id="section_current_lampsPerFixture" name="section_current_lampsPerFixture" placeholder="10"></div>			
			<div><label>Luminaire Watts</label></div>
			<div class="col-sm-12"><input type="text" class="form-control" id="section_current_lWatts" name="section_current_lWatts" placeholder="watts"></div>						
		</div>
		<div style="text-align: center; font-weight: bold;">Rebates and Incentives</div>
		<div class="form-group" >
			<div><b>Include Incentives and Rebates?</b>
				<select name="section_rebInc_include">
				<option value="true">Yes</option>
				<option value="false">No</option>
				</select>
			</div>
			<div><label>Total Utility Rebates</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_rebInc_total" name="section_rebInc_total"  placeholder="1.00"></div>
			<!--div><label>Total Utility Rebates</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_rebInc_utility" name="section_rebInc_utility"  placeholder="1.00"></div>
			<div><label>Total Local and State Incentives</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_rebInc_localState" name="section_rebInc_localState" placeholder="1.00"></div>
			<div><label>Total Federal Incentives</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_rebInc_federal" name="section_rebInc_federal" placeholder="1.00"></div-->
		</div>
		<div style="text-align: center; font-weight: bold;">HyLite Replacement</div>
		<div class="form-group" >
			<div><b>Product Type</b>
				<select name="section_hylite_type" onchange="generate_series();">
					<option value="induction">Induction
					<option value="led">LED
				</select>
			</div>
			<div><b>Product Model</b>
				<select name="section_hylite_sku"> <!-- onchange="save_section();" -->
				</select>
			</div>						
			<div><label># of Fixtures</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#</span><input type="text" class="form-control" id="section_hylite_numFixtures" name="section_hylite_numFixtures"  placeholder="10"></div>
			<div><label># of Lamps/Fixture</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#</span><input type="text" class="form-control" id="section_hylite_lampsPerFixture" name="section_hylite_lampsPerFixture" placeholder="10"></div>
			<div><label>Unit Price</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_hylite_unitPrice" name="section_hylite_unitPrice" placeholder="1.00"></div>
		</div>
		<div style="text-align: center; font-weight: bold;">Maintenance</div>
		<div class="form-group" >
			<div><b>Include Maintenance Data?</b>
				<select name="section_maint_include">
					<option value="true">Yes</option>
					<option value="false">No</option>
				</select>
			</div>
			<div><label>Maintenance Cost per Bulb Replacement</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_maint_costPerBulbReplace" name="section_maint_costPerBulbReplace"  placeholder="1.00"></div>
			<div><label>Cost per Replacement Bulb</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_maint_costPerBulb" name="section_maint_costPerBulb" placeholder="1.00"></div>
			<div><label>Disposal Cost per Bulb</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_maint_disposalCost" name="section_maint_disposalCost" placeholder="1.00"></div>
		</div>
		<div style="text-align: center; font-weight: bold;">Ballast</div>
		<div class="form-group" >
			<div><b>Include Ballast Replacement Data?</b>
				<select name="section_maint_ballast_include">
					<option value="true">Yes</option>
					<option value="false">No</option>
				</select>
			</div>
			<div><label>Life of Current Ballast</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#(hours)</span><input type="text" class="form-control" id="section_maint_ballast_lifeHours" name="section_maint_ballast_lifeHours"  placeholder="1000"></div>
			<div><label>Cost of Replacement Ballast</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">$</span><input type="text" class="form-control" id="section_maint_ballast_replacementCostsection_maint_ballast_numPerLum" name="section_maint_ballast_replacementCost" placeholder="1.00"></div>
			<div><label># of Ballasts per Luminaire</label></div>
			<div class="col-sm-12"><span class="input-group-addon" style="width:10px;">#</span><input type="text" class="form-control" id="section_maint_ballast_numPerLum" name="section_maint_ballast_numPerLum" placeholder="10"></div>
		</div>
	</div>

	<div style="position: fixed; bottom: 10px; right: 15px; display: block;">
		<input type="submit" value="" onclick="save_section();" style="height: 64px; width: 64px; background: url(img/pdf.png) no-repeat; background-size:100% 100%;"/>
	</div>
<br /><br /><br /><br /><br /><br /><br /><br />
<input type="button" onclick="demo();" value="Demo" style="width: 50px; height: 25px;">
</form>
</body>
</html>