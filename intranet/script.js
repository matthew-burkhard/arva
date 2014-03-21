

security_check();

function security_check() {
	if (document.referrer.indexOf('guardian.html') < 0) {
		//window.location.replace("index.html");
	}
}

var max_sections = 6;
var current_section = 1;

function input(txt, val) {
	document.getElementsByName(txt)[0].value = val;
}
function check(txt, val) {
	document.getElementsByName(txt)[0].checked = val;
}
function output(txt) {
	return document.getElementsByName(txt)[0].value;
}
function element(txt) {
	return document.getElementsByName(txt)[0];
}
function save_data(txt) {
	return document.getElementsByName(current_section + '_' + txt)[0];
}

function section_info() {
	var info = new Array();
	var selects = document.getElementsByTagName('select');
	var inputs = document.getElementsByTagName('input');
	for(var i = 0; i < selects.length; i++) {
		if (selects[i].name.indexOf('section_') == 0)
			info[info.length] = selects[i];
	}	
	for(var i = 0; i < inputs.length; i++) {
		if (inputs[i].name.indexOf('section_') == 0)
			info[info.length] = inputs[i];
	}
	return info;
}

function generate_sections() {

	var info = section_info();
	for(var i = 1; i <= max_sections; i++) {
		for (var j = 0; j < info.length; j++) {
			var n = document.createElement('input');
			n.setAttribute('type', 'hidden');
			n.setAttribute('name', i + '_' + info[j].name);
			n.setAttribute('value', info[j].value); // sets default values for all input
			
			element('data').appendChild(n);
		}
	}
	
	for(var i = 0; i < induction_series.length; i++) {
		element('section_hylite_sku').add(new Option(induction_series[i]));
	}
}

function generate_series() {
	var source;
	if (element('section_hylite_type').value == 'led') source = led_series;
	else source = induction_series;
	element('section_hylite_sku').options.length = 0;
		for(var i = 0; i < source.length; i++) {
		element('section_hylite_sku').add(new Option(source[i]));
	}
}

function generate_dropdown() {
	var num = parseInt(element('num_sections').value);
	var edit = element('edit_sections');
	
	if (edit.length <= num) { // create new elements
		for (var i = 0; i < num; i++) {
			var number = i + 1;
			var text = '';
			if (element(number + '_section_name').value == '')
				text = '#' + number;
			else
				text = element(number + '_section_name').value;
			var op = new Option(text, number);
			if (edit[i]) edit[i] = op;
			else edit.add(op);
		}
	} else if (edit.length > num) { // remove old sections for edit
		for (var i = edit.length - 1; i > num - 1; i--)
			edit.remove(i);
	}
	edit.selectedIndex = current_section - 1;
}

function save_section() {
	var info = section_info();
	for(var i = 0; i < info.length; i++) {
		save_data(info[i].name).value = info[i].value;
	}
}

function load_section() {
	var info = section_info();
	current_section = parseInt(element('edit_sections').value);
	for(var i = 0; i < info.length; i++) {
		info[i].value = save_data(info[i].name).value;
	}
}

function allPages(isChecked) {
	for(var i = 1; i <= 11; i++)
		check('page_' + i, isChecked);
}

function random(things) {
	num = Math.floor(Math.random() * things.length);
	return things[num];
}

function demo() {
	input('for_name', 'Niki Amin');
	input('for_company', 'Arva, LLC.');
	input('for_address', '911 East White Street');
	input('for_city', 'Rock Hill');
	input('for_state', 'SC');
	input('for_zip', '29730');
	
	input('by_name', 'Matthew Burkhard');
	input('by_email', 'matthew.burkhard@gmail.com');
	input('by_phone', '(803) 493-8886');
	
	for(var i = 1; i <= 6; i++) {
		if (i == 1) {
			input(i + '_section_name', 'Warehouse');
			input(i + '_section_electricityRate', 0.08);
			input(i + '_section_operatingHours', 4380);
			
			input(i + '_section_current_fixture', 'High Bay');
			input(i + '_section_current_lamp', 'Metal Halide');
			input(i + '_section_current_numFixtures', 15);
			input(i + '_section_current_lampsPerFixture', 1);
			input(i + '_section_current_lampLife', 10000);
			input(i + '_section_current_watts', 400);
			input(i + '_section_current_lWatts', 458);
			
			input(i + '_section_rebInc_include', true);
			input(i + '_section_rebInc_utility', 500);
			
			input(i + '_section_hylite_type', 'induction');
			input(i + '_section_hylite_sku', 'HL-HN-B-150W-50K-AC');
			input(i + '_section_hylite_numFixtures', 15);
			input(i + '_section_hylite_lampsPerFixture', 1);
			input(i + '_section_hylite_unitPrice', 301.00);
			
			input(i + '_section_maint_include', true);
			input(i + '_section_maint_costPerBulbReplace', 25);
			input(i + '_section_maint_costPerBulb', 15.00);
			input(i + '_section_maint_disposalCost', 1.00);
			
			input(i + '_section_maint_ballast_include', true);
			//input(i + '_section_maint_costPerBallastReplace', 0);
			input(i + '_section_maint_ballast_lifeHours', 25000);
			input(i + '_section_maint_ballast_replacementCost', 91.00);
			input(i + '_section_maint_ballast_numPerLum', 1.00);
		}
		else if (i == 2) {
			input(i + '_section_name', 'Parking Lot');
			input(i + '_section_electricityRate', 0.095);
			input(i + '_section_operatingHours', 5000);
			
			input(i + '_section_current_fixture', 'Parking Lot Light');
			input(i + '_section_current_lamp', 'High Pressure Sodium');
			input(i + '_section_current_numFixtures', 25);
			input(i + '_section_current_lampsPerFixture', 1);
			input(i + '_section_current_lampLife', 24000);
			input(i + '_section_current_watts', 250);
			input(i + '_section_current_lWatts', 294);
			
			input(i + '_section_rebInc_include', false);
			//input(i + '_section_rebInc_utility', 500);
			
			input(i + '_section_hylite_type', 'induction');
			input(i + '_section_hylite_sku', 'HL-PN-B-200W-50K-AC');
			input(i + '_section_hylite_numFixtures', 15);
			input(i + '_section_hylite_lampsPerFixture', 1);
			input(i + '_section_hylite_unitPrice', 501.00);
			
			input(i + '_section_maint_include', true);
			input(i + '_section_maint_costPerBulbReplace', 55);
			input(i + '_section_maint_costPerBulb', 35.00);
			input(i + '_section_maint_disposalCost', 1.00);
			
			input(i + '_section_maint_ballast_include', true);
			//input(i + '_section_maint_costPerBallastReplace', 0);
			input(i + '_section_maint_ballast_lifeHours', 40000);
			input(i + '_section_maint_ballast_replacementCost', 115.00);
			input(i + '_section_maint_ballast_numPerLum', 1.00);		
		}
		else if (i == 3) {
			input(i + '_section_name', 'Warehouse 2');
			input(i + '_section_electricityRate', 0.11);
			input(i + '_section_operatingHours', 4380);
			
			input(i + '_section_current_fixture', 'High Bay');
			input(i + '_section_current_lamp', 'High Pressure Sodium');
			input(i + '_section_current_numFixtures', 48);
			input(i + '_section_current_lampsPerFixture', 1);
			input(i + '_section_current_lampLife', 24000);
			input(i + '_section_current_watts', 400);
			input(i + '_section_current_lWatts', 465);
			
			input(i + '_section_rebInc_include', false);
			input(i + '_section_rebInc_utility', 5000);
			input(i + '_section_rebInc_localState', 250);
			input(i + '_section_rebInc_federal', 0);
			
			input(i + '_section_hylite_type', 'induction');
			input(i + '_section_hylite_sku', 'HL-PN-B-200W-50K-AC');
			input(i + '_section_hylite_numFixtures', 48);
			input(i + '_section_hylite_lampsPerFixture', 1);
			input(i + '_section_hylite_unitPrice', 311.05);
			
			input(i + '_section_maint_include', true);
			input(i + '_section_maint_costPerBulbReplace', 25.00);
			input(i + '_section_maint_costPerBulb', 35.00);
			input(i + '_section_maint_disposalCost', 0);
			
			input(i + '_section_maint_ballast_include', false);
		}
		else {
			input(i + '_section_name', random(new Array('Garage', 'Parking Lot', 'Warehouse', 'Storage Center', 'Outside', 'Service Bay', 'Showroom', 'Wall Packs')));
			input(i + '_section_electricityRate', 0.12);
			input(i + '_section_operatingHours', random(new Array(1000, 2000, 3000, 4000)));
			
			input(i + '_section_current_fixture', element(i + '_section_name').value + ' Lights');
			input(i + '_section_current_lamp', random(new Array('Metal Halide', 'Fluorescent')));
			input(i + '_section_current_numFixtures', random(new Array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)));
			input(i + '_section_current_lampsPerFixture', random(new Array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)));
			input(i + '_section_current_lampLife', random(new Array(10000, 12000)));
			input(i + '_section_current_watts', random(new Array(40, 75, 100, 150, 175, 1000)));
			input(i + '_section_current_lWatts', element(i + '_section_current_watts').value * element(i + '_section_current_lampsPerFixture').value);
			
			input(i + '_section_hylite_type', random(element('section_hylite_type')).value);
			if (element(i + '_section_hylite_type').value == 'LED')
				input(i + '_section_hylite_sku', random(led_series));
			else
				input(i + '_section_hylite_sku', random(induction_series));
			input(i + '_section_hylite_numFixtures', random(new Array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)));
			input(i + '_section_hylite_lampsPerFixture', random(new Array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)));
			input(i + '_section_hylite_unitPrice', random(new Array(25, 50, 75, 100)));
			
			// for now, ignore optional sections
			input(i + '_section_rebInc_include', 'No');
			input(i + '_section_maint_include', 'No');
			input(i + '_section_maint_ballast_include', 'No');
		}
	}
	
	load_section();
	generate_dropdown();
}

function displayKeywords() {
	var keywords = document.getElementById('keywords');
	var section_select = document.getElementsByTagName('select');
	var section_info = document.getElementsByTagName('input');
	for(var i = 0; i < section_select.length; i++) {
		keywords.value += section_select[i].name + '\n';
	}
	for(var i = 0; i < section_info.length; i++) {
		keywords.value += section_info[i].name + '\n';
	}
}

function validate_input() {
	if (Number(output('section_operatingHours')) > 8760)
		document.getElementById("error_message").innerHTML = "Error: Operating Years Must Less Than 8,760!";
	else
		document.getElementById("error_message").innterHTML = "";
}