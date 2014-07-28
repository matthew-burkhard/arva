
<?php
	$con = mysqli_connect("localhost", "arvaus5_qg", "quoteGenerator123", "arvaus5_hylite");
	if (mysqli_connect_errno($con)) {
		echo "Failed to connect to database: " . mysqli_connect_error();
	}
?>

<!DOCTYPE html>
<html>

<head>

    <title>ARVA - Quote Generator</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.2/jquery.mobile-1.4.2.min.css">
    <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.2/jquery.mobile-1.4.2.min.js"></script>
    <script type="text/javascript">
        var led_series = new Array();

         <?php
         	$rs = mysqli_query($con, "select sku from products where type = 'led'");
        	while ($row = mysqli_fetch_array($rs)) {
            	echo "led_series.push('".$row['sku']."');";
    	} ?>
        var induction_series = new Array(); 
        <?php
         	$rs = mysqli_query($con, "select sku from products where type = 'induction'");
        	while ($row = mysqli_fetch_array($rs)) {
            	echo "induction_series.push('".$row['sku']."');";
        } ?>
    </script>
    <script type="text/javascript" src="js/script.js"></script>
</head>

<body onload="generate_sections();">

    <div data-role="page">
        <form class="form-horizontal" role="form" id="data" name="data" method="post" action="generator.php" target="_blank">

            <div data-role="header">
                <h1>Arva Proposal Generator<br/>v1.0.0</h1>
                <center>
                    <input type="button" onclick="demo();" value="Demo">
                </center>
            </div>
            <div data-role="main" class="ui-content">

                <div data-role="collapsible-set">
                    <h2>General Information</h2>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Pages</h3>
                        <label for="page_1">Title</label>
                        <input type="checkbox" name="page_1" id="page_1" value="true">
                        <label for="page_2">Executive Summary</label>
                        <input type="checkbox" name="page_2" id="page_2" value="true">
                        <label for="page_5">Section Data</label>
                        <input type="checkbox" name="page_5" id="page_5" value="true">
                        <label for="page_11">Last</label>
                        <input type="checkbox" name="page_11" id="page_11" value="true">
                        <label for="page_12">Spec Sheets</label>
                        <input type="checkbox" name="page_12" id="page_12" value="true">
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Prepared For</h3>
                        <div class="ui-field-contain">
                            <label for="for_name">Name</label>
                            <input type="text" name="for_name" id="for_name">
                            <label for="for_company">Company</label>
                            <input type="text" name="for_company" id="for_company">
                            <label for="for_address">Address</label>
                            <input type="text" name="for_address" id="for_address">
                            <label for="for_city">City</label>
                            <input type="text" name="for_city" id="for_city">
                            <label for="for_state">State</label>
                            <input type="text" name="for_state" id="for_state">
                            <label for="for_zip">Zip</label>
                            <input type="text" name="for_zip" id="for_zip">
                        </div>
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Prepared By</h3>
                        <div class="ui-field-contain">
                            <label for="by_name">Name</label>
                            <input type="text" name="by_name" id="by_name">
                            <label for="by_email">Email</label>
                            <input type="email" name="by_email" id="by_email" placeholder="sales@arva.us">
                            <label for="by_phone">Phone</label>
                            <input type="text" name="by_phone" id="by_phone" placeholder="(803) 336-2230">
                        </div>
                    </div>

                </div>

                <div class="ui-field-contain">
                    <label for="num_sections">Total Sections</label>
                    <select type="text" class="form-control" id="num_sections" name="num_sections" onchange="save_section(); generate_dropdown();">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                	<label for="edit_sections">Current Section</label>
                    <select class="form-control" id="edit_sections" name="edit_sections" onchange="save_section(); load_section();">
                        <option value="1">Section 1</option>
                    </select>
                </div>

                <div data-role="collapsible-set">
                    <h2>Section Data</h2>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>General</h3>
                        <div class="ui-field-contain">
                            <label for="section_name">Section Name</label>
                            <input type="text" name="section_name" id="section_name" onkeyup="save_section(); generate_dropdown();">
                            <label for="section_electricityRate">Electricity Rate ($/kwh)</label>
                            <input type="text" name="section_electricityRate" id="section_electricityRate" placeholder="0.10">
                            <label for="section_operatingHours">Operating Hours</label>
                            <input type="text" name="section_operatingHours" id="section_operatingHours" placeholder="10000">
                        </div>
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Current Fixture</h3>
                        <div class="ui-field-contain">
                            <label for="section_current_fixture">Fixture Type</label>
                            <input type="text" name="section_current_fixture" id="section_current_fixture">
                            <label for="section_current_numFixtures">Total Fixtures</label>
                            <input type="text" name="section_current_numFixtures" id="section_current_numFixtures">
                            <label for="section_current_lamp">Current Lamp Type</label>
                            <input type="text" name="section_current_lamp" id="section_current_lamp">
                            <label for="section_current_lampLife">Bulb Rated Lamp Life (hours)</label>
                            <input type="text" name="section_current_lampLife" id="section_current_lampLife">
                            <label for="section_current_watts">Bulb Rated Watts</label>
                            <input type="text" name="section_current_watts" id="section_current_watts">
                            <label for="section_current_lampsPerFixture">Lamps per Fixture</label>
                            <input type="text" name="section_current_lampsPerFixture" id="section_current_lampsPerFixture">
                            <label for="section_current_lWatts">Luminaire Watts</label>
                            <input type="text" name="section_current_lWatts" id="section_current_lWatts">
                        </div>
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>HyLite Replacement</h3>
                        <div class="ui-field-contain">
                            <label for="section_hylite_type">Product Type</label>
                            <select name="section_hylite_type" id="section_hylite_type" onchange="generate_series();">
                                <option value="induction">Induction
                                    <option value="led">LED
                            </select>
                            <label for="section_hylite_type">Product Model</label>
                            <select name="section_hylite_sku" id="section_hylite_sku"></select>
                            <label for="section_hylite_numFixtures">Total Fixtures</label>
                            <input type="text" name="section_hylite_numFixtures" id="section_hylite_numFixtures">
                            <label for="section_hylite_lampsPerFixture">Lamps per Fixture</label>
                            <input type="text" name="section_hylite_lampsPerFixture" id="section_hylite_lampsPerFixture">
                            <label for="section_hylite_unitPrice">Unit Price ($)</label>
                            <input type="text" name="section_hylite_unitPrice" id="section_hylite_unitPrice" placeholder="99.99">
                        </div>
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Rebates / Incentives</h3>
                        <div class="ui-field-contain">
                            <label for="section_rebInc_include">Include?</label>
                            <select name="section_rebInc_include" id="section_rebInc_include">
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                            <label for="section_rebInc_total">Total Utility Rebates ($)</label>
                            <input type="text" name="section_rebInc_total" id="section_rebInc_total" placeholder="1.00">
                        </div>
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Maintenance</h3>
                        <div class="ui-field-contain">
                            <label for="section_maint_include">Include?</label>
                            <select name="section_maint_include" id="section_maint_include">
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                            <label for="section_maint_costPerBulbReplace">Labor Cost per Bulb Replacement ($)</label>
                            <input type="text" name="section_maint_costPerBulbReplace" id="section_maint_costPerBulbReplace" placeholder="1.00">
                            <label for="section_maint_costPerBulb">Cost per Replacement Bulb ($)</label>
                            <input type="text" name="section_maint_costPerBulb" id="section_maint_costPerBulb" placeholder="1.00">
                            <label for="section_maint_disposalCost">Disposal Cost per Bulb ($)</label>
                            <input type="text" name="section_maint_disposalCost" id="section_maint_disposalCost" placeholder="1.00">
                        </div>
                    </div>

                    <div data-role="collapsible" data-collapsed="true">
                        <h3>Ballast</h3>
                        <div class="ui-field-contain">
                            <label for="section_maint_ballast_include">Include?</label>
                            <select name="section_maint_ballast_include" id="section_maint_ballast_include">
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                            <label for="section_maint_ballast_lifeHours">Life of Current Ballast (hours)</label>
                            <input type="text" name="section_maint_ballast_lifeHours" id="section_maint_ballast_lifeHours" placeholder="1000">
                            <label for="section_maint_ballast_replacementCost">Cost of Replacement Ballast ($)</label>
                            <input type="text" name="section_maint_ballast_replacementCost" id="section_maint_ballast_replacementCost" placeholder="1.00">
                            <label for="section_maint_ballast_numPerLum">Ballasts per Luminaire</label>
                            <input type="text" name="section_maint_ballast_numPerLum" id="section_maint_ballast_numPerLum" placeholder="10">
                        </div>
                    </div>
                </div>
                <!--/div-->
            </div>

            <div style="position: fixed; bottom: 10px; right: 15px; display: block;">
                <input type="submit" data-role="none" value="" onclick="save_section();" style="height: 64px; width: 64px; background: url(img/pdf.png) no-repeat; background-size:100% 100%;" />
            </div>

        </form>
    </div>

</body>

</html>
