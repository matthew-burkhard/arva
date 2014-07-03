<?php
	// difference between this and require_once?
	require('fpdf/fpdf.php');
	require('fpdi/fpdi.php');
	
	class FormData {
		public $use_sectionData = false;
		public $current_section = 1;
	
		private function get_data($key) {
			if ($_POST[$key] == NULL) {
				return '';
			}
			else {
				return $_POST[$key];
			}
		}
		
		private function sdata($key) {
			return $this->get_data($this->current_section . '_' . $key);
		}		
		
		public function data($key) {
			if ($this->use_sectionData) {
				return $this->sdata($key);
			}
			else {
				return $this->get_data($key);
			}
		}
		
		public function data_int($key) {
			return intval($this->data($key));
		}
		
		public function data_float($key) {
			return floatval($this->data($key));
		}
		
		public function data_bool($key) {
			return ($this->data($key) == 'true' ? true : false);
		}
		
		public function data_largeInt($key) {
			return number_format($this->data_int($key));
		}
	}
	
	class SectionInfo {
		public $include_rebInc;
		public $include_maint;
		public $include_maint_ballast;
		
		public $electricity_rate;
		public $operating_hours;
		
		public $fixture;
		public $num_fixtures;
		public $lamp;
		public $lamp_life;
		public $watts;
		public $lamps_per_fixture;
		public $lWatts;
		
		public $cost_per_lamp_replace;
		public $cost_per_lamp;
		public $cost_disposal;
		
		public $ballast_cost_per_replace;
		public $ballast_life_hours;
		public $ballast_replacement_cost;
		public $ballast_num_per_lum;
		
		public $rebates_utilities;
		public $rebates_state;
		public $rebates_federal;
		
		public $yearsOfOperation;
		public $annualUsageKwh;
		public $annualElectricityCost;
		public $lifetimeUsageKwh;
		public $lifetimeElectricityCost;
		public $lifetimeMaintCost;
		public $annualMaintCost;
		public $annualCost;
		public $lifetimeCost;

		private $hylite;
		
		function Load($a, $h) {
			$this->hylite = $h;
			$this->include_rebInc = $a->data_bool('section_rebInc_include');
			$this->include_maint = $a->data_bool('section_maint_include');
			$this->include_maint_ballast = $a->data_bool('section_maint_ballast_include');
		
			$this->electricity_rate = $a->data_float('section_electricityRate');
			$this->operating_hours = $a->data_int('section_operatingHours');
			
			$this->fixture = $a->data('section_current_fixture');
			$this->num_fixtures = $a->data_int('section_current_numFixtures');
			$this->lamp = $a->data('section_current_lamp');
			$this->lamp_life = $a->data_int('section_current_lampLife');
			$this->watts = $a->data_int('section_current_watts');
			$this->lamps_per_fixture = $a->data_int('section_current_lampsPerFixture');
			$this->lWatts = $a->data_int('section_current_lWatts');
			
			if($this->include_maint) {
				$this->cost_per_lamp_replace = $a->data_int('section_maint_costPerBulbReplace');
				$this->cost_per_lamp = $a->data_int('section_maint_costPerBulb');
				$this->cost_disposal = $a->data_int('section_maint_disposalCost');
			}
			else {
				$this->cost_per_lamp_replace = 0;
				$this->cost_per_lamp = 0;
				$this->cost_disposal = 0;
			}
			
			if($this->include_maint_ballast) {
				$this->ballast_cost_per_replace = 0; //$a->data_int('section_maint_costPerBallastReplace');
				$this->ballast_life_hours = $a->data_int('section_maint_ballast_lifeHours');
				$this->ballast_replacement_cost = $a->data_int('section_maint_ballast_replacementCost');
				$this->ballast_num_per_lum = $a->data_int('section_maint_ballast_numPerLum');				
			}
			else {
				$this->ballast_cost_per_replace = 0;
				$this->ballast_life_hours = 0;
				$this->ballast_replacement_cost = 0;
				$this->ballast_num_per_lum = 0;
			}
			
			if ($this->include_rebInc) {
				$this->rebates_utilities = $a->data_int('section_rebInc_utility');
				$this->rebates_state = $a->data_int('section_rebInc_localState');
				$this->rebates_federal = $a->data_int('section_rebInc_federal');
			}
			else {
				$this->rebates_utilities = 0;
				$this->rebates_state = 0;
				$this->rebates_federal = 0;
			}
			
			$this->yearsOfOperation = round($this->lamp_life / $this->operating_hours, 1);
			$this->annualUsageKwh = ($this->lWatts * $this->num_fixtures * $this->operating_hours) / 1000;
			$this->annualElectricityCost = $this->annualUsageKwh * $this->electricity_rate;
			
			$this->lifetimeUsageKwh = ($this->lWatts * $this->num_fixtures * $this->hylite->lamp_life_rated) / 1000;
			$this->lifetimeElectricityCost = $this->lifetimeUsageKwh * $this->electricity_rate;
			if ($this->include_maint == 0) {
				$this->lifetimeMaintCost = 0;
			}
			else {
				$this->lifetimeMaintCost = 
					(
						(
							floor($this->hylite->lamp_life_rated / $this->lamp_life) // ** ROUNDED DOWN **
							* $this->lamps_per_fixture * $this->num_fixtures
						) 
							* ($this->cost_per_lamp_replace + $this->cost_per_lamp + $this->cost_disposal)
					)
					+ (
						// ** Just like above, we've rounded down the lamp life rated / ballast life hours calculation
						($this->ballast_life_hours == 0 ? 0 : floor($this->hylite->lamp_life_rated / $this->ballast_life_hours))
						* ($this->ballast_num_per_lum * $this->num_fixtures) * ( /*$this->ballast_cost_per_replace +*/ $this->ballast_replacement_cost)
					);
			}

		}
		
		function Load_Hylite() {
			$this->annualMaintCost = $this->lifetimeMaintCost / $this->hylite->yearsOfOperation;
			$this->annualCost = $this->annualElectricityCost + $this->annualMaintCost;
			$this->lifetimeCost = $this->lifetimeElectricityCost + $this->lifetimeMaintCost;
		}
	}
	
	class HyliteProduct {
		public $type;
		public $series;
		public $desc;
		public $watts;
		public $watts_actual;
		public $lamp_life_rated;
		
		public $num_fixtures;
		public $lamps_per_fixture;
		public $price;
		public $lWatts;
		
		public $picture;
		
		public $yearsOfOperation;
		public $annualUsageKwh;
		public $annualElectricityCost;
		public $lifetimeUsageKwh;
		public $annualCost;
		public $lifetimeCost;
		
		public $savings;
		public $initial_investment;
		public $total_savings;
		public $net_investment;
		public $total_roi;
		public $payback_months;

		private $usage;
		public $co2_offset;
		public $trees;
		public $miles;

		private $current;

		function pic_url() {
			return 'img/' . $this->type . '/' . $this->picture;
			//'http://intranet.arva.us/img/'
		}
		
		function Load($a, $sku, $c) {
			$this->current = $c;
			$con = mysqli_connect("localhost", "arvaus5_qg", "quoteGenerator123", "arvaus5_hylite");
			if (mysqli_connect_errno($con)) {
				echo "Failed to connect to database: " . mysqli_connect_error();
			}
			$rs = mysqli_query($con, "select * from products where sku = '" . $sku . "'");
			if($row = mysqli_fetch_assoc($rs)) {
				$this->type = $row['type'];
				$this->series = $row['series'];
				$this->desc = $row['desc'];
				$this->watts = intval($row['watts']);
				$this->lamp_life_rated = intval($row['lamp_life_rated']);
				$this->picture = $row['picture'];
			}
			mysqli_close($con);
			
			$this->num_fixtures = $a->data_int('section_hylite_numFixtures');
			$this->lamps_per_fixture = $a->data_int('section_hylite_lampsPerFixture');
			$this->price = $a->data_float('section_hylite_unitPrice');
			
			$this->initial_investment = ($this->price * $this->num_fixtures * $this->lamps_per_fixture); 

			$this->lWatts = $this->watts * $this->lamps_per_fixture * ($this->type == 'induction' ? 1.05 : 1);
		}
		
		function Load_Current($a) {
			$this->yearsOfOperation = round($this->lamp_life_rated / $this->current->operating_hours, 1);
			$this->annualUsageKwh = (round($this->lWatts, 0) * $this->num_fixtures * $this->current->operating_hours) / 1000;
			$this->annualElectricityCost = $this->annualUsageKwh * $this->current->electricity_rate;
			$this->lifetimeUsageKwh = (round($this->lWatts, 0) * $this->num_fixtures * $this->lamp_life_rated) / 1000;
			$this->lifetimeElectricityCost = $this->lifetimeUsageKwh * $a->data_float('section_electricityRate');
			$this->annualCost = $this->annualElectricityCost;
			$this->lifetimeCost = $this->lifetimeElectricityCost;

			$this->usage = $this->current->lifetimeUsageKwh - $this->lifetimeUsageKwh;
			$this->co2_offset = $this->usage * 1.44;
			$this->trees = ($this->co2_offset / 2204.6) * 6;
			$this->miles = $this->co2_offset / 0.936;
		}

		function Load_Savings() {
			$this->savings = $this->current->annualCost - $this->annualCost;

			$this->total_savings = $this->current->lifetimeCost - $this->lifetimeCost;
			$this->net_investment = $this->initial_investment - $this->current->rebates_utilities - $this->current->rebates_state - $this->current->rebates_federal;
			$this->total_roi = 100 * (($this->total_savings - $this->net_investment) / $this->net_investment);

			$this->payback_months = round(($this->net_investment / $this->savings) * 12, 1);
		}
	}
	
	class ArvaPdf {
		private $fData;
		private $pdf;
		private $myX;
		private $myY;
		private $tableX;
		private $tableY;
		
		//private $costPerKwh = 0.11;
		
		private $num_sections = 0;
		private $debug = false;
		private $border = 0;
		//private $use_sectionData = false;
		//private $current_section = 0;
		
		public function data($key) {
			return $this->fData->data($key);
		}
		
		public function data_int($key) {
			return $this->fData->data_int($key);
		}
		
		public function data_float($key) {
			return $this->fData->data_float($key);
		}
		
		public function data_largeInt($key) {
			return $this->fData->data_largeInt($key);
		}
		
		function UsePage($pageNum) {
			if ($this->data('page_' . $pageNum) == 'true')
				return true;
			else
				return false;
		}
		
		function Start() {
			$this->fData = new FormData();
			$this->pdf = new FPDI('P', 'in', array(8.5, 11));
			$this->num_sections = $this->data('num_sections');
			if ($this->debug) {
				$this->Page_Debug();
			}
		}
		
		function NewPage($pdf_template) {
			$pageCount = $this->pdf->setSourceFile($pdf_template);
			$tplidx = $this->pdf->importPage(1);
			$this->pdf->addPage();
			$this->pdf->useTemplate($tplidx, 0, 0);
		}
		
		function Set_FontAndColor($font, $size, $r, $g, $b, $s = '') {
			$this->pdf->SetFont($font, $s, $size);
			$this->pdf->SetTextColor($r, $g, $b);
		}
		
		function Set_FontColor($r, $g, $b) {
			$this->pdf->SetTextColor($r, $g, $b);
		}
		
		function Move($x, $y) {
			$this->myX = $x;
			$this->myY = $y;
			$this->pdf->SetXY($this->myX, $this->myY);
		}
		
		function Move_WriteText($x, $y, $string) {
			$this->Move($x, $y);
			$this->pdf->Write(0, $string);
		}
		
		function Move_Write($x, $y, $dataKey, $opText = '') {
			$this->Move_WriteText($x, $y, $this->data($dataKey) . $opText);
		}
		
		function NextLine_Write($dataKey, $opText = '') {
			$this->NextLine_WriteText($this->data($dataKey) . $opText);
		}
		
		function NextLine_WriteText($text) {
			$this->myY += 0.2;
			$this->Move_WriteText($this->myX, $this->myY, $text);
		}		
		
		function NextLine_WriteAddress($cityKey, $stateKey, $zipKey) {
			$this->myY += 0.2;
			$this->Move_Write($this->myX, $this->myY,
				$this->data($cityKey) . ', ' .
				$this->data($stateKey) . ' ' .
				$this->data($zipKey));
		}
		
		function Start_Table($x, $y) {
			$this->tableX = $x;
			$this->tableY = $y;
			$this->Move($this->tableX, $this->tableY);
		}
		
		function Cell_RightText($w, $h, $text, $align = 'C', $fill = false) {
			$this->pdf->Cell($w, $h, $text, $this->border, 0, $align, $fill);
		}
		
		function Cell_Right($w, $h, $dataKey, $opText = '', $align ='C', $fill = false) {
			$this->Cell_RightText($w, $h, $this->data($dataKey) . $opText, $align, $fill);
		}

		function Cell_DownText($w, $h, $text, $align = 'C') {
			$this->pdf->Cell($w, $h, $text, $this->border, 2, $align);
		}
		
		function Cell_Down($w, $h, $dataKey, $opText = '') {
			$this->Cell_DownText($w, $h, $this->data($dataKey) . $opText);
		}
				
		function Next_Row($row, $h) { //$x, $y, $w, $h, $dataKey)
			//$this->pdf->Ln();
			$this->Move($this->tableX, $this->tableY);
			for ($i = 0; $i < $row; $i++)
			{
				$this->pdf->Cell(0.01, $h, '', 0, 2);
			}
			//$this->tableX = $this->pdf->GetX();
			//$this->tableY = $this->pdf->GetY();
			//$this->pdf->Cell($w, $h, $this->data($dataKey), $border, 0, 'C');
		}
		
		function Image($file, $x, $y, $w, $h) {
			$this->pdf->Image($file, $x, $y, $w, $h);
		}
/*
		function Image_Tall($file, $x, $y, $h) {
			$this->pdf->Image($file, $x, $y, 0, $h);
		}		
		
*/		
		function Write_Debug() {
			$this->Set_FontAndColor('Helvetica', 6, 0, 0, 0);
			for($x = 0; $x < 6.5; $x += 0.5)
			{
				for ($y = 0; $y < 9; $y += 0.5)
				{
					$this->pdf->SetXY($x, $y);
					$this->pdf->Write(0, '(' . $x . ', ' . $y . ')');
				}
			}
		}
		
		function Page_Debug() {
			$this->NewPage('pdf/01_title.pdf');
			$this->Move_WriteText(1.00, 1.00, 'Debug');
			$this->NextLine_WriteText('num_sections');
			$this->NextLine_Write('num_sections');
			$this->NextLine_WriteText('current_section');
			$this->NextLine_Write('current_section');
		}
		
		function Page_Title() {
			$this->NewPage('pdf/01_title.pdf');
			$this->Set_FontAndColor('Helvetica', 11, 0, 0, 255);
			$this->Move_Write(3.25, 6.65, 'for_name');
			$this->NextLine_Write('for_company');
			$this->NextLine_Write('for_address');
			$this->NextLine_WriteAddress('for_city', 'for_state', 'for_zip');
			
			$this->Move_Write(4.75, 8.35, 'by_name');
			$this->NextLine_Write('by_email');
			$this->NextLine_Write('by_phone');
		}
		
		function Page_Last() {
			$this->NewPage('pdf/11_lastPage.pdf');
			$this->Set_FontAndColor('Helvetica', 10, 0, 0, 255);
			$this->Move_Write(4.35, 3.30, 'by_name');
			$this->NextLine_Write('by_email');
			$this->NextLine_Write('by_phone');
			$this->NextLine_WriteText('www.arva.us');		
		}
		
		function Page_ExecutiveSummary() {
			//$this->NewPage('pdf/02_graph_' . $this->num_sections . '.pdf');
			$this->NewPage('pdf/02_execSummary.pdf');
			
			// write out the double line column headers...

			$col_width = array(
				1 => 0.78,
				2 => 0.52,
				3 => 1.08,
				4 => 0.80, 
				5 => 0.81,
				6 => 0.97,
				7 => 0.82,
				8 => 0.72,
				9 => 0.84
			);

			$this->Start_Table(0.07, 2.83);
			$this->border = 0;
			$height = 0.12;

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, 'Number', 'C');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '');
			$this->Cell_RightText($col_width[4], $height, 'Current Lamp', 'C');
			$this->Cell_RightText($col_width[5], $height, 'Current Lamp', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[6], $height, 'HyLite', 'C');
			$this->Cell_RightText($col_width[7], $height, '');
			$this->Cell_RightText($col_width[8], $height, 'HyLite Lamp', 'C');
			$this->Cell_RightText($col_width[9], $height, 'Energy Savings', 'C');			

			$this->Next_Row(1, $height);

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, 'of Lights', 'C');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '');
			$this->Cell_RightText($col_width[4], $height, 'Watts', 'C');
			$this->Cell_RightText($col_width[5], $height, 'Life (hrs.)', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[6], $height, 'Replacement', 'C');
			$this->Cell_RightText($col_width[7], $height, '');
			$this->Cell_RightText($col_width[8], $height, 'Life (hrs.)', 'C');
			$this->Cell_RightText($col_width[9], $height, '(%)', 'C');

			// print section information
			$this->Start_Table(0.07, 2.83);
			$this->border = 1;
			$height = 0.24;

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, 'Section', 'C');
			
			//$this->pdf->MultiCell(0.52, $height, 'Number\n of Lights', 'C');
			$this->Cell_RightText($col_width[2], $height, '');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, 'Current Lamps', 'C');
			$this->Cell_RightText($col_width[4], $height, '');
			$this->Cell_RightText($col_width[5], $height, '', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[6], $height, '');
			$this->Cell_RightText($col_width[7], $height, 'HyLite Watts', 'C');
			$this->Cell_RightText($col_width[8], $height, '');
			$this->Cell_RightText($col_width[9], $height, '');

			$this->Start_Table(0.07, 3.07);
			$height = 0.21;

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0);
			$this->fData->use_sectionData = true;
			for ($i = 1; $i <= $this->num_sections; $i++)
			{
				$this->fData->current_section = $i; // set your section
				
				if ($i % 2 == 0)
					$this->pdf->SetFillColor(241, 241, 241);
				else
					$this->pdf->SetFillColor(255, 255, 255);

				$hylite = new HyliteProduct();
				$current = new SectionInfo();
				$hylite->Load($this->fData, $this->data('section_hylite_sku'), $current);
				$current->Load($this->fData, $hylite);
				$hylite->Load_Current($this->fData);
				$current->Load_Hylite();
				$hylite->Load_Savings();
			
				$this->Set_FontColor(0, 0, 0);
				$this->pdf->SetDrawColor(0, 0, 0);
				$this->Cell_Right($col_width[1], $height, 'section_name', '', 'C', true);
				$this->Cell_RightText($col_width[2], $height, $current->num_fixtures * $current->lamps_per_fixture, 'C', true);
				
				$this->Set_FontColor(255, 0, 0);
				$this->pdf->SetDrawColor(255, 0, 0);
				$this->Cell_RightText($col_width[3], $height, $current->lamp, 'C', true);
				$this->Cell_RightText($col_width[4], $height, number_format($current->watts) . 'W', 'C', true);
				$this->Cell_RightText($col_width[5], $height, number_format($current->lamp_life), 'C', true);
				
				$this->Set_FontColor(109, 193, 15);
				$this->pdf->SetDrawColor(109, 193, 15);
				$this->Cell_RightText($col_width[6], $height, $hylite->series, 'C', true);
				$this->Cell_RightText($col_width[7], $height, number_format($hylite->watts) . 'W', 'C', true);				
				$this->Cell_RightText($col_width[8], $height, number_format($hylite->lamp_life_rated), 'C', true);
				
				//(Current annual kWh – HyLite annual kWh)/(Current annual kWh)
				$energy_savings = ($current->annualUsageKwh - $hylite->annualUsageKwh) / $current->annualUsageKwh;
				$energy_savings = round($energy_savings * 100, 0);

				$this->Cell_RightText($col_width[9], $height, $energy_savings . '%', 'C', true);
				
				$this->Next_Row($i, $height);
			}

			$this->pdf->SetFillColor(0, 0, 0);

			$total_num_lights = 0;
			$total_current_annualCost = 0;
			$total_current_lifetimeCost = 0;
			$total_hylite_annualCost = 0;
			$total_hylite_lifetimeCost = 0;
			$total_initial_investment = 0;
			$total_total_roi = 0;
			$total_payback_months = 0;

			$total_co2_offset = 0;
			$total_miles = 0;
			$total_trees = 0;

			$total_utility_rebates = 0;
			$total_local_rebates = 0;
			$total_federal_rebates = 0;

			$this->Start_Table(0.07, 4.79);
			$this->border = 0;
			$height = 0.10;

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, '');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '');
			$this->Cell_RightText($col_width[4], $height, 'Current Total', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, 'HyLite Annual', 'C');
			$this->Cell_RightText($col_width[6], $height, '');
			$this->Cell_RightText($col_width[7], $height, '');
			$this->Cell_RightText($col_width[8], $height, '');
			$this->Cell_RightText($col_width[9], $height, '');

			$this->Next_Row(1, $height);

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, '');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '');
			$this->Cell_RightText($col_width[4], $height, 'Cost of', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, 'Cost of', 'C');
			$this->Cell_RightText($col_width[6], $height, '');
			$this->Cell_RightText($col_width[7], $height, '');
			$this->Cell_RightText($col_width[8], $height, '');
			$this->Cell_RightText($col_width[9], $height, '');

			$this->Next_Row(2, $height);

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, '');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '');
			$this->Cell_RightText($col_width[4], $height, 'Ownership', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, 'Ownership', 'C');
			$this->Cell_RightText($col_width[6], $height, '');
			$this->Cell_RightText($col_width[7], $height, '');
			$this->Cell_RightText($col_width[8], $height, '');
			$this->Cell_RightText($col_width[9], $height, '');			

			$this->Start_Table(0.07, 4.79);
			$this->border = 0;
			$height = 0.15;

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, 'Number', 'C');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, 'Current Annual Cost', 'C');
			$this->Cell_RightText($col_width[4], $height, '', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, '', 'C');
			$this->Cell_RightText($col_width[6], $height, 'Hylite Total Cost of', 'C');
			$this->Cell_RightText($col_width[7], $height, 'Initial');
			$this->Cell_RightText($col_width[8], $height, '');
			$this->Cell_RightText($col_width[9], $height, 'Payback', 'C');

			$this->Next_Row(1, $height);

			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, '');
			$this->Cell_RightText($col_width[2], $height, 'of Lights', 'C');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, 'of Ownership', 'C');
			$this->Cell_RightText($col_width[4], $height, '', 'C');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, '', 'C');
			$this->Cell_RightText($col_width[6], $height, 'Ownership', 'C');
			$this->Cell_RightText($col_width[7], $height, 'Investment');
			$this->Cell_RightText($col_width[8], $height, '');
			$this->Cell_RightText($col_width[9], $height, '(Months)', 'C');

			$this->Start_Table(0.07, 4.79);
			$this->border = 1;
			// print financial information
			$height = 0.30;

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, 'Section', 'C');
			
			//$this->pdf->MultiCell(0.52, $height, "Number\n of Lights", 0);
			$this->Cell_RightText($col_width[2], $height, '');	

			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '');
			$this->Cell_RightText($col_width[4], $height, '');
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, '');
			$this->Cell_RightText($col_width[6], $height, '');
			$this->Cell_RightText($col_width[7], $height, '', 'C');
			$this->Cell_RightText($col_width[8], $height, 'ROI (%)', 'C');
			$this->Cell_RightText($col_width[9], $height, '');

			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0); 
			$this->Start_Table(0.07, 5.09);
			$height = 0.21;

			for ($i = 1; $i <= $this->num_sections; $i++)
			{
				$this->fData->current_section = $i; // set your section
				
				if ($i % 2 == 0)
					$this->pdf->SetFillColor(241, 241, 241);
				else
					$this->pdf->SetFillColor(255, 255, 255);

				$hylite = new HyliteProduct();
				$current = new SectionInfo();
				$hylite->Load($this->fData, $this->data('section_hylite_sku'), $current);
				$current->Load($this->fData, $hylite);
				$hylite->Load_Current($this->fData);
				$current->Load_Hylite();
				$hylite->Load_Savings();
			
				$this->Set_FontColor(0, 0, 0);
				$this->pdf->SetDrawColor(0, 0, 0);
				$this->Cell_Right($col_width[1], $height, 'section_name', '', 'C', true);
				$this->Cell_RightText($col_width[2], $height, $current->num_fixtures * $current->lamps_per_fixture, 'C', true);
				$total_num_lights += $current->num_fixtures * $current->lamps_per_fixture;
				
				$this->Set_FontColor(255, 0, 0);
				$this->pdf->SetDrawColor(255, 0, 0);
				$this->Cell_RightText($col_width[3], $height, '$' . number_format($current->annualCost, 2), 'C', true);
				$total_current_annualCost += $current->annualCost;
				$this->Cell_RightText($col_width[4], $height, '$' . number_format($current->lifetimeCost, 2), 'C', true);
				$total_current_lifetimeCost += $current->lifetimeCost;

				$this->Set_FontColor(109, 193, 15);
				$this->pdf->SetDrawColor(109, 193, 15);
				$this->Cell_RightText($col_width[5], $height, '$' . number_format($hylite->annualCost, 2), 'C', true);
				$total_hylite_annualCost += $hylite->annualCost;
				$this->Cell_RightText($col_width[6], $height, '$' . number_format($hylite->lifetimeCost, 2), 'C', true);
				$total_hylite_lifetimeCost += $hylite->lifetimeCost;
				$this->Cell_RightText($col_width[7], $height, '$' . number_format($hylite->initial_investment, 2), 'C', true);
				$total_initial_investment += $hylite->initial_investment;
				$this->Cell_RightText($col_width[8], $height, number_format($hylite->total_roi) . '%', 'C', true);
				//$total_total_roi += $hylite->total_roi;
				$this->Cell_RightText($col_width[9], $height, $hylite->payback_months, 'C', true);
				//$total_payback_months += $hylite->payback_months;
				
				$total_co2_offset += $hylite->co2_offset;
				$total_miles += $hylite->miles;
				$total_trees += $hylite->trees;

				$total_utility_rebates += $current->rebates_utilities;
				$total_local_rebates += $current->rebates_state;
				$total_federal_rebates += $current->rebates_federal;

				// Rebates are no longer subtracted
				//$total_initial_investment -= ($current->rebates_utilities + $current->rebates_state + $current->rebates_federal);

				$this->Next_Row($i, $height);
			}
			$this->pdf->SetFillColor(0, 0, 0);
			$this->fData->use_sectionData = false;
			
			// print summary line
			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_RightText($col_width[1], $height, 'Total');
			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0);
			$this->Cell_RightText($col_width[2], $height, $total_num_lights);
			
			$this->Set_FontColor(255, 0, 0);
			$this->pdf->SetDrawColor(255, 0, 0);
			$this->Cell_RightText($col_width[3], $height, '$' . number_format($total_current_annualCost, 2));
			$this->Cell_RightText($col_width[4], $height, '$' . number_format($total_current_lifetimeCost, 2));
			
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_RightText($col_width[5], $height, '$' . number_format($total_hylite_annualCost, 2));
			$this->Cell_RightText($col_width[6], $height, '$' . number_format($total_hylite_lifetimeCost, 2));
			$this->Cell_RightText($col_width[7], $height, '$' . number_format($total_initial_investment, 2));

			// now subtract the rebates
			$total_initial_investment -= ($total_utility_rebates + $total_local_rebates + $total_federal_rebates);

			$total_total_roi = ((($total_current_lifetimeCost - $total_hylite_lifetimeCost) - $total_initial_investment) / $total_initial_investment) * 100;
			$this->Cell_RightText($col_width[8], $height, number_format($total_total_roi) . '%');
			$total_payback_months = ($total_initial_investment / ($total_current_annualCost - $total_hylite_annualCost)) * 12;
			$this->Cell_RightText($col_width[9], $height, number_format($total_payback_months));

			$this->Start_Table(0.46, 6.50);
			$this->Set_FontColor(0, 0, 0);
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Cell_DownText(1.60, $height, 'Utility Rebates');
			$this->Cell_DownText(1.60, $height, 'Local & State Incentives');
			$this->Cell_DownText(1.60, $height, 'Federal Incentives');
			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0, 'B');
			$this->Cell_DownText(1.60, $height, 'Net Incentives');
			$this->Set_FontAndColor('Helvetica', 7, 0, 0, 0);

			// print utility rebates, local & state incentives, federal incentives and net investment
			$this->Start_Table(4.95, 6.50);
			$this->Set_FontColor(109, 193, 15);
			$this->pdf->SetDrawColor(109, 193, 15);
			$this->Cell_DownText(0.80, $height, '$' . number_format($total_utility_rebates, 2));
			//$this->Next_Row(1, $height);
			$this->Cell_DownText(0.80, $height, '$' . number_format($total_local_rebates, 2));
			//$this->Next_Row(2, $height);
			$this->Cell_DownText(0.80, $height, '$' . number_format($total_federal_rebates, 2));
			//$this->Next_Row(3, $height);
			$this->Cell_DownText(0.80, $height, '$' . number_format($total_initial_investment, 2));
			//$this->Cell_DownText(0.80, $height, '$' . number_format($total_utility_rebates + $total_local_rebates + $total_federal_rebates));
			
			// turn border back off
			$this->border = 0;

			// environmental impact
			$width = 0.65;
			$this->Start_Table(2.00, 8.10);
			$this->Set_FontAndColor('Helvetica', 8, 109, 193, 15);
			$this->Cell_DownText($width, $height, number_format($total_co2_offset));
			$this->Cell_DownText($width, $height, number_format($total_miles));
			$this->Cell_DownText($width, $height, number_format($total_trees));

			// financial summary
			$width = 0.75;
			$height = 0.18;
			$this->Start_Table(4.65, 8.10);
			$this->Set_FontAndColor('Helvetica', 8, 109, 193, 15);
			$this->Cell_DownText($width, $height, '$' . number_format($total_current_annualCost - $total_hylite_annualCost, 2));
			$this->Cell_DownText($width, $height, '$' . number_format($total_current_lifetimeCost - $total_hylite_lifetimeCost, 2));
			$this->Cell_DownText($width, $height, number_format($total_total_roi) . '%');
			$this->Cell_DownText($width, $height, number_format($total_payback_months));
			$this->Cell_DownText($width, $height, '$' . number_format(($total_current_annualCost - $total_hylite_annualCost) / 365, 2));			
			//$this->Cell_DownText($width, $height, '$' . number_format(round($total_current_annualCost - $total_hylite_annualCost / 365, 2), 2));
			
			// net investment
			$this->Start_Table(5.70, 8.10);
			$this->Set_FontAndColor('Helvetica', 18, 109, 193, 15);
			$this->Cell_DownText(1.50, 0.55, '$' . number_format($total_initial_investment, 2));
		}
		
		function Page_Sections() {
			$this->fData->use_sectionData = true;
			for ($i = 1; $i <= $this->num_sections; $i++) {
				$this->fData->current_section = $i;
				$this->Page_Section();
			}
			$this->fData->use_sectionData = false;
		}
		
		function Page_Section() {
			
			$hylite = new HyliteProduct();
			$current = new SectionInfo();
			$hylite->Load($this->fData, $this->data('section_hylite_sku'), $current);
			$current->Load($this->fData, $hylite);
			$hylite->Load_Current($this->fData);
			$current->Load_Hylite();
			$hylite->Load_Savings();
			
			if ($current->include_rebInc) {
				$this->NewPage('pdf/05_section_investment.pdf');
			}
			else {
				$this->NewPage('pdf/05_section.pdf');
			}

			// $this->Set_FontAndColor('Helvetica', 10, 255, 0, 0);
			// $this->Move_Write(0, 0, '');
			// $this->NextLine_WriteText($current->include_rebInc);
			// $this->NextLine_WriteText($current->include_maint);
			// $this->NextLine_WriteText($current->include_maint_ballast);

			//Need to fit image into a 110x126 box (1.10w x 1.25h)
			//But, really, we want to fit the image into a 1.00x1.15 shape

			//$this->Set_FontAndColor('Helvetica', 12, 255, 0, 0);
			//$this->Move_WriteText(0, 0.2, '');

			// Also removing this code, as it's not needed for pictures anymore...
			/*
			$size = getimagesize($hylite->pic_url());
			$orig_width = $size[0];
			$orig_height = $size[1];
			$x = 6.15;
			$y = 2.40;
			$desired_w = 1.18;
			$desired_h = 1.35;
			$width = 1.00;
			$height = 1.00;
			$wide = ($orig_width > $orig_height ? true : false);
			$long = !$wide;
			if ($wide) {
				$width = $desired_w;
				$height = ($orig_height * (($desired_w * 100) / $orig_width)) / 100;
				if ($height > $desired_h)
					$height = $desired_h;
				$y += ($desired_h - $height) / 2;
				$x += 0.05;
			}
			else if ($long) {
				$height = $desired_h;
				$width = ($orig_width * (($desired_h * 100) / $orig_height)) / 100;
				if ($width > $desired_w)
						$width = $desired_w;
				$x += ($desired_w - $width) / 2;
			}
			*/

			//$this->Set_FontAndColor('Helvetica', 10, 255, 0, 0);
			//$this->Start_Table(6.18, 2.40);
			//$this->Cell_RightText(1.18, 1.35, "TEST1");
			//$this->Cell_RightText(1.18, 1.35, "TEST2");

			// $this->NextLine_WriteText('ow: ' . $orig_width);
			// $this->NextLine_WriteText('oh: ' . $orig_height);
			//$this->NextLine_WriteText('x: ' . $x);
			//$this->NextLine_WriteText('y: ' . $y);
			// $this->NextLine_WriteText('w: ' . $width);
			// $this->NextLine_WriteText('h: ' .$height);
			// $this->NextLine_WriteText('wide: ' . $wide);
			// $this->NextLine_WriteText('long: ' . $long);
		
			// Removed placing image...
			//$this->Image($hylite->pic_url(), $x, $y, $width, $height);

			// write text
			$this->Set_FontAndColor('Helvetica', 18, 51, 102, 255);
			$this->Move_WriteText(5.45, 0.80, 'Section ' . $this->fData->current_section);

			$this->Set_FontAndColor('Helvetica', 16, 51, 102, 255);
			$this->Move_Write(0.50, 1.50, 'section_name');
			//$this->Start_Table(0.50, 1.25);
			//$this->Cell_DownText(2.00, 0.50, 'Section Name', 'L');
			
			// write current information
			$this->Set_FontAndColor('Helvetica', 10, 255, 0, 0);
			$this->Start_Table(3.05, 1.25);
			$width = 1.28;
			$height = 0.25;
			$this->Cell_Down($width, $height, 'section_current_fixture');
			$this->Cell_Down($width, $height, 'section_current_lamp');
			
			$left = 2.89;
			$this->Start_Table($left, 2.00);
			$width = 1.59;
			$height = 0.23;
			$this->Cell_DownText($width, $height, $current->watts . 'W');
			$this->Cell_DownText($width, $height, $current->lamps_per_fixture);
			$this->Cell_DownText($width, $height, $current->lWatts . 'W');
			$this->Cell_DownText($width, $height, $current->num_fixtures);
			$this->Cell_DownText($width, $height, number_format($current->lamp_life));
			$this->Cell_DownText($width, $height, $current->yearsOfOperation);
			
			// annual cost of ownership
			$this->Start_Table($left, 3.60);
			$this->Cell_DownText($width, $height, number_format($current->annualUsageKwh));
			$this->Cell_DownText($width, $height, '$' . number_format($current->annualElectricityCost, 2));
			$this->Cell_DownText($width, $height, '$' . number_format($current->annualMaintCost, 2));
			//$current_annualCost = $current->annualElectricityCost + $current->annualMaintCost;
			$this->Set_FontAndColor('Helvetica', 10, 255, 0, 0, 'B');
			$this->Cell_DownText($width, $height, '$' . number_format($current->annualCost, 2));
			$this->Set_FontAndColor('Helvetica', 10, 255, 0, 0);
			
			// total cost of ownership
			$this->Start_Table($left, 4.75);
			$this->Cell_DownText($width, $height, number_format($current->lifetimeUsageKwh, 2));
			$this->Cell_DownText($width, $height, '$' . number_format($current->lifetimeElectricityCost, 2));
			$this->Cell_DownText($width, $height += 0.03, '$' . number_format($current->lifetimeMaintCost, 2));
			//$current_lifetimeCost = $current_lifetimeElectricityCost + $current_lifetimeMaintCost;
			$this->Set_FontAndColor('Helvetica', 10, 255, 0, 0, 'B');
			$this->Cell_DownText($width, $height, '$' . number_format($current->lifetimeCost, 2));
			$this->Set_FontAndColor('Helvetica', 10, 255, 0, 0);

			// write hylite information
			$this->Set_FontColor(109, 193, 15);
			$this->Start_Table(4.55, 1.25);
			$width = 1.28;
			$height = 0.25;
			$this->Cell_DownText($width, $height, 'HyLite ' . ucwords($hylite->type));
			$this->Cell_DownText($width, $height, $hylite->series);
			
			$left = 4.53;
			$this->Start_Table($left, 2.00);
			$width = 1.40;
			$height = 0.23;
			$this->Cell_DownText($width, $height, $hylite->watts . 'W');
			$this->Cell_DownText($width, $height, $hylite->lamps_per_fixture);
			$this->Cell_DownText($width, $height, round($hylite->lWatts, 0) . 'W');
			$this->Cell_DownText($width, $height, $hylite->num_fixtures);
			$this->Cell_DownText($width, $height, number_format($hylite->lamp_life_rated));
			$this->Cell_DownText($width, $height, $hylite->yearsOfOperation);
			
			// annual cost of ownership
			$this->Start_Table($left, 3.60);
			$this->Cell_DownText($width, $height, number_format($hylite->annualUsageKwh));
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->annualElectricityCost, 2));
			$this->Cell_DownText($width, $height, '$0');
			//$hylite_annualCost = $hylite_annualElectricityCost;
			$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15, 'B');
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->annualCost, 2));
			$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15);
			
			// total cost of ownership
			$this->Start_Table($left, 4.75);
			$this->Cell_DownText($width, $height, number_format($hylite->lifetimeUsageKwh));
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->lifetimeElectricityCost, 2));
			$this->Cell_DownText($width, $height += 0.03, '$0');
			//$hylite_lifetimeCost = $hylite_lifetimeElectricityCost;
			$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15, 'B');
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->lifetimeCost, 2));
			$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15);

			// hylite savings information
			$width = 2.25;
			$height = 0.23;
			$align = 'L';
			$left = 0.52;
			$down = 6.10;
			$this->Set_FontAndColor('Helvetica', 10, 51, 102, 255);
			$this->Start_Table($left, $down);
			//$savings = $current->annualCost - $hylite->annualCost;
			$this->Cell_DownText($width, $height, 'Annual Savings with HyLite:', $align);
			$yearOne = ($hylite->yearsOfOperation > 10 ? 5 : 2);
			$yearTwo = ($hylite->yearsOfOperation > 10 ? 10 : 5);
			$this->Cell_DownText($width, $height, $yearOne . ' Year Savings with HyLite:', $align);
			$this->Cell_DownText($width, $height, $yearTwo . ' Year Savings with HyLite:', $align);
			$this->Cell_DownText($width, $height, 'Total Savings with HyLite:', $align);
			$this->Cell_DownText($width, $height, 'Daily Cost of Waiting:', $align);
			
			// next row
			$this->Start_Table($left, $down);
			$this->Cell_RightText($width, $height, '');
			$width = 0.95;
			$align = 'R';
			$this->Set_FontColor(109, 193, 15);
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->savings, 2), $align);
			$this->Cell_DownText($width, $height, '$' . number_format($yearOne * $hylite->savings, 2), $align);
			$this->Cell_DownText($width, $height, '$' . number_format($yearTwo * $hylite->savings, 2), $align);
			//$total_savings = $current->lifetimeCost - $hylite->lifetimeCost;
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->total_savings, 2), $align);
			$this->Cell_DownText($width, $height, '$' . round(floatval($hylite->savings) / floatval(365), 2), $align);
			
			// environmental impact
			$width = 2.25;
			$height = 0.21;
			$align = 'L';
			$down = 7.65;
			$this->Set_FontColor(51, 102, 255);
			$this->Start_Table($left, $down);
			$this->Cell_DownText($width, $height, 'Total Pounds of CO2 Offset:', $align);
			$this->Cell_DownText($width, $height, 'Trees Planted Equivalent:', $align);
			$this->Cell_DownText($width, $height, 'Auto Miles Driven Equivalent:', $align);
			
			// next row
			$this->Start_Table($left, $down);
			$this->Cell_RightText($width, $height, '');
			$width = 0.95;
			$align = 'R';
			$this->Set_FontColor(109, 193, 15);
			/*
			$usage = $current->lifetimeUsageKwh - $hylite->lifetimeUsageKwh;
			$co2_offset = $usage * 1.44;
			$trees = ($co2_offset / 2204.6) * 6;
			$miles = $co2_offset / 0.936;
			*/
			$this->Cell_DownText($width, $height, number_format($hylite->co2_offset), $align);
			$this->Cell_DownText($width, $height, number_format($hylite->trees), $align);
			$this->Cell_DownText($width, $height, number_format($hylite->miles), $align);

			//$initial_investment = ($hylite->price * $hylite->num_fixtures * $hylite->lamps_per_fixture);
			//$net_investment = $initial_investment - $current->rebates_utilities - $current->rebates_state - $current->rebates_federal;
			
			if ($current->include_rebInc) {
				// investment
				$width = 2.25;
				$height = 0.23;
				$align = 'L';
				$left = 4.02;
				$down = 6.08;
				$this->Set_FontAndColor('Helvetica', 10, 51, 102, 255);
				$this->Start_Table($left, $down);
				$this->Cell_DownText($width, $height, 'Initial Investment:', $align);
				$this->Cell_DownText($width, $height, '   Utility Rebates:', $align);
				$this->Cell_DownText($width, $height, '   Local & State:', $align);
				$this->Cell_DownText($width, $height, '   Federal Incentives:', $align);
				$this->Set_FontAndColor('Helvetica', 10, 51, 102, 255, 'B');
				$this->Cell_DownText($width, $height, 'Net Investment:', $align);
				$this->Set_FontAndColor('Helvetica', 10, 51, 102, 255);
				
				// next row
				$this->Start_Table($left, $down);
				$this->Cell_RightText($width, $height, '');
				$width = 0.95;
				$align = 'R';
				$this->Set_FontColor(109, 193, 15);
				$this->Cell_DownText($width, $height, '$' . number_format($hylite->initial_investment, 2), $align);
				$this->Set_FontColor(255, 0, 0);
				$this->Cell_DownText($width, $height, '$' . number_format($current->rebates_utilities, 2), $align);
				$this->Cell_DownText($width, $height, '$' . number_format($current->rebates_state, 2), $align);
				$this->Cell_DownText($width, $height, '$' . number_format($current->rebates_federal, 2), $align);
				$this->Set_FontColor(109, 193, 15);
				$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15, 'B');
				$this->Cell_DownText($width, $height, '$' . number_format($hylite->net_investment, 2), $align);
				$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15);
			}
			
			if ($current->include_rebInc) {
				$left = 4.02;
				$down = 7.65;
			}
			else {
				$left = 4.10;
				$down = 6.10;
			}

			// return on investment
			$width = 2.25;
			$height = 0.21;
			$align = 'L';
			$this->Set_FontAndColor('Helvetica', 10, 51, 102, 255);
			$this->Start_Table($left, $down);
			if (!$current->include_rebInc) {
				$this->Cell_DownText($width, $height, 'Initial Investment:', $align);	
			}
			$this->Cell_DownText($width, $height, 'Total Savings with HyLite:', $align);
			$this->Cell_DownText($width, $height, 'Net Gain from Investment:', $align);
			$this->Cell_DownText($width, $height, $yearOne . ' Year Return On Investment:', $align);
			$this->Cell_DownText($width, $height, $yearTwo . ' Year Return On Investment:', $align);
			$this->Cell_DownText($width, $height, 'Total Return on Investment:', $align);
			$this->Cell_DownText($width, $height, 'Payback Period (Months):', $align);
			
			// next row
			$this->Start_Table($left, $down);
			$this->Cell_RightText($width, $height, '');
			$width = 0.95;
			$align = 'R';
			$this->Set_FontAndColor('Helvetica', 10, 109, 193, 15, 'B');
			if (!$current->include_rebInc) {
				$this->Cell_DownText($width, $height, '$' . number_format($hylite->initial_investment), $align);
			}
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->total_savings, 2), $align);
			$this->Cell_DownText($width, $height, '$' . number_format($hylite->total_savings - $hylite->net_investment, 2), $align);
			$this->Cell_DownText($width, $height, number_format(100 * ((($yearOne * $hylite->savings) - $hylite->net_investment) / $hylite->net_investment)) . '%', $align);
			$this->Cell_DownText($width, $height, number_format(100 * ((($yearTwo * $hylite->savings) - $hylite->net_investment) / $hylite->net_investment)) . '%', $align);
			$this->Cell_DownText($width, $height, number_format($hylite->total_roi) . '%', $align);
			$this->Cell_DownText($width, $height, $hylite->payback_months, $align);
		}
		
		function Page_Quote() {
			//$this->NewPage('pdf/07_quote_' . $this->num_sections . '.pdf');
			$this->NewPage('pdf/07_quote.pdf');
			
			$this->Set_FontAndColor('Helvetica', 18, 0, 0, 0);
			$this->Move_Write(0.60, 1.75, 'for_company');
			
			// top quote table
			// $height = 0.18;
			// $this->Start_Table(0.19, 2.68);
			// $this->Set_FontAndColor('Helvetica', 10, 0, 0, 0);
			// $this->Cell_RightText(0.80, $height, date("m/d/y"));
			// $this->Cell_Right(1.50, $height, 'by_name');
			
			// new quote table...
			$height = 0.18;
			$this->Start_Table(0.19, 2.49);
			$this->border = 1;
			$this->pdf->SetDrawColor(0, 0, 0);
			$this->Set_FontAndColor('Helvetica', 10, 0, 0, 0, 'B');
			$this->pdf->SetFillColor(241, 241, 241);
			$this->Cell_RightText(0.80, $height, 'Date', 'C', true);
			$this->Cell_RightText(1.50, $height, 'Sales Rep.', 'C', true);
			$this->Cell_RightText(1.40, $height, 'Terms', 'C', true);
			$this->Set_FontAndColor('Helvetica', 10, 0, 0, 0);
			$this->Next_Row(1, $height);
			$this->Cell_RightText(0.80, $height, date("m/d/y"));
			$this->Cell_Right(1.50, $height, 'by_name');
			$this->Cell_RightText(1.40, $height, 'See Below');

			$total_price = 0;
			
			// section table
			$height = 0.22;
			$this->Start_Table(0.19, 3.34);
			
			$this->Set_FontAndColor('Helvetica', 8, 0, 0, 0, 'B');
			$this->pdf->SetFillColor(241, 241, 241);
			$this->Cell_RightText(0.48, $height, 'Qty', 'C', true);
			$this->Cell_RightText(1.80, $height, 'Model', 'C', true);
			$this->Cell_RightText(3.52, $height, 'Description', 'C', true);
			$this->Cell_RightText(0.68, $height, 'Unit Price', 'C', true);
			$this->Cell_RightText(0.70, $height, 'Total', 'C', true);
			$this->Next_Row(1, $height);

			$this->Set_FontAndColor('Helvetica', 8, 0, 0, 0);
			$this->fData->use_sectionData = true;
			for ($i = 1; $i <= $this->num_sections; $i++)
			{
				$this->fData->current_section = $i; // set your section
				
				$hylite = new HyliteProduct();
				$current = new SectionInfo();
				$hylite->Load($this->fData, $this->data('section_hylite_sku'), $current);
				$current->Load($this->fData, $hylite);
				$hylite->Load_Current($this->fData);
				$current->Load_Hylite();
				$hylite->Load_Savings();
				
				$total_quantity = $hylite->num_fixtures * $hylite->lamps_per_fixture;
				$line_price = $total_quantity * $hylite->price;
				$total_price += $line_price;
				
				$this->Cell_RightText(0.48, $height, $total_quantity);
				$this->Cell_RightText(1.80, $height, $this->data('section_hylite_sku'), 'L');
				$this->Cell_RightText(3.52, $height, $hylite->desc, 'L');
				$this->Cell_RightText(0.68, $height, '$' . number_format($hylite->price, 2));
				$this->Cell_RightText(0.70, $height, '$' . number_format($line_price, 2), 'R');
				$this->Next_Row($i + 1, $height);
			}
			$this->fData->use_sectionData = false;
			
			
			$width = 1.60;
			$this->Start_Table(4.00, 5.17);

			$this->Set_FontAndColor('Helvetica', 10, 0, 0, 0, 'B');
			$this->pdf->SetFillColor(241, 241, 241);
			$this->Cell_RightText($width, $height, 'Subtotal:', 'L', true);
			$this->Cell_RightText($width, $height, '$' . number_format($total_price, 2), 'C', true);

			$this->Next_Row(1, $height);
			$this->Set_FontAndColor('Helvetica', 10, 0, 0, 0);
			$this->pdf->SetFillColor(0, 0, 0);
			$this->Cell_RightText($width, $height, 'Subtotal:', 'L');
			$this->Cell_RightText($width, $height, '-');

			$this->Next_Row(2, $height);
			$this->Cell_RightText($width, $height, 'Tax:', 'L');
			$this->Cell_RightText($width, $height, '-');

			$this->Next_Row(3, $height);
			$this->Cell_RightText($width, $height, 'Shipping and Handling:', 'L');
			$this->Cell_RightText($width, $height, '-');

			$this->Next_Row(4, $height);
			$this->Cell_RightText($width, $height, 'Miscellaneous:',  'L');
			$this->Cell_RightText($width, $height, '-');

			$this->Next_Row(5, $height);
			$this->Set_FontAndColor('Helvetica', 10, 0, 0, 0, 'B');
			$this->pdf->SetFillColor(241, 241, 241);
			$this->Cell_RightText($width, $height, 'Total:', 'L', true);
			$this->Cell_RightText($width, $height, '$' . number_format($total_price, 2), 'C', true);

			$this->border = 0;
		}

		function Page_Assumptions() {
			$this->NewPage('pdf/10_assumptions.pdf');
			$this->Move(0.50, 1.75);

			$this->fData->use_sectionData = true;
			for ($i = 1; $i <= $this->num_sections; $i++)
			{				
				$this->fData->current_section = $i; // set your section
				$hylite = new HyliteProduct();
				$current = new SectionInfo();
				$hylite->Load($this->fData, $this->data('section_hylite_sku'), $current);
				$current->Load($this->fData, $hylite);
				$hylite->Load_Current($this->fData);
				$current->Load_Hylite();
				$hylite->Load_Savings();

				if ($i <= 3)
					$this->Move($this->myX - 0.05, $this->myY);
				else if ($i == 4)
					$this->Move($this->myX + 3.25, 1.75);
				else
					$this->Move($this->myX - 0.05, $this->myY);

				$this->Set_FontAndColor('Helvetica', 12, 0, 0, 255);
				$this->NextLine_WriteText('Section ' . $i);
				$this->Set_FontAndColor('Helvetica', 10, 0, 0, 0);
				$this->Move($this->myX + 0.05, $this->myY);
				$this->NextLine_WriteText('$' . $current->electricity_rate . '/kWh');
				$this->NextLine_WriteText(number_format($current->operating_hours) . ' Operating Hours Per Year');
				$this->NextLine_WriteText('Rated Lamp Life of Current Bulb: ' . number_format($current->lamp_life) . ' hours');
				$this->NextLine_WriteText('Rated Lamp Life of Current Ballast: ' . number_format($current->ballast_life_hours) . ' hours');
				$this->NextLine_WriteText('Labor and Equipment Cost per Bulb Rep: $' . $current->cost_per_lamp_replace);
				$this->NextLine_WriteText('Cost for Replacement Bulb: $' . $current->cost_per_lamp);
				$this->NextLine_WriteText('Disposal Cost per Bulb: $' . $current->cost_disposal);
				//$this->NextLine_WriteText('Labor and Equipment Cost per Ballast Rep: $'	. $current->ballast_replacement_cost);
				$this->NextLine_WriteText('Cost to Replace Ballast: $' . $current->ballast_replacement_cost);
						//$current->ballast_cost_per_replace);
				$this->NextLine_WriteText('');
			}

			$this->fData->use_sectionData = false;
		}
		
		function Page($page_num) {
			
			switch($page_num)
			{
			
				// Title Page
				case 1:
					$this->Page_Title();
				break;
				
				// Executive Summary
				case 2:
					$this->Page_ExecutiveSummary();
				break;
				
				// Induction Benefits
				case 3:
					$this->NewPage('pdf/03_inductionInfo.pdf');
				break;
				
				// LED Benefits
				case 4:
					$this->NewPage('pdf/04_ledInfo.pdf');
				break;
				
				// Section(s) Page
				case 5:
					$this->Page_Sections();
				break;
				
				// Savings Comparisons
				case 6:
					$this->NewPage('pdf/06_savingsComparisonsDemo.pdf');
				break;
				
				// Quote
				case 7:
					$this->Page_Quote();
				break;
				
				// Induction Pictures
				case 8:
					$this->NewPage('pdf/08_inductionPictures.pdf');
				break;
				
				// LED Pictures
				case 9:
					$this->NewPage('pdf/09_ledPictures.pdf');
				break;
				
				// Assumptions
				case 10:
					$this->Page_Assumptions();
				break;
				
				// Last Page
				case 11:
					$this->Page_Last();
				break;
			}
			
			if ($debug)
				$this->Write_Debug();
			
		}
		
		function Finish() {
			$this->pdf->Output();
		}
	}
	
	function Write_ArvaPdf() {
	
		$output = new ArvaPdf();
		$output->Start();

		for ($page = 1; $page <= 11; $page++) {
			if ($output->UsePage($page)) {
				$output->Page($page);
			}
		}
		
		$output->Finish();
		
	}
	
	Write_ArvaPdf();
?>