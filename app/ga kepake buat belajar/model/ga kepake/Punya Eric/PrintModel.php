<?php
class PrintModel extends BaseModel{

	/*-----------------------start field dan comment-----------------------
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = '';
		protected $sheet;

		const CONST_PRINT_READY = "1111";

		const CONST_MARGIN_PAGE_LEFT = 0.2;
		const CONST_MARGIN_PAGE_TOP = 0.15;
		const CONST_MARGIN_PAGE_RIGHT = 0;
		const CONST_MARGIN_PAGE_BOTTOM = 0.15;

		const CONST_ORIENTATION_PORTRAIT = "portrait";
		const CONST_ORIENTATION_LANDSCAPE = "landscape";
		const CONST_MAKS_LENGTH_NAMA_BARANG = 25;

		const CONST_TIPE_EXCEL_STORE = "1";
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function makeDataToko($row){
			$this->merge("A".$row , "D".$row);
			$this->tulis("PT. Bee Bee Toys", "A".$row);
			$this->fontSize("14", "A".$row);
			$this->fontWeight("bold", "A".$row);
			$row++;

			$this->merge("A".$row , "D".$row);
			$this->tulis("Jl. Terusan Bandengan Utara", "A".$row);
			$this->fontSize("10", "A".$row);
			$row++;

			$this->merge("A".$row , "D".$row);
			$this->tulis("Ruko Air Baja Blok I no 10", "A".$row);
			$this->fontSize("10", "A".$row);
			$row++;

			$this->merge("A".$row , "D".$row);
			$this->tulis("Jakarta Utara", "A".$row);
			$this->fontSize("10", "A".$row);
			$row++;

			$this->merge("A".$row , "D".$row);
			$this->tulis("021-667 123 52", "A".$row);
			$this->fontSize("10", "A".$row);		
			return 2;
		}
	/*----------------------------protected logic--------------------------*/
		protected function mergeSatuBaris($row){
			$this->merge("A".$row , "K".$row);
		}
	
		protected function merge($cellAwal, $cellAkhir){
			$this->sheet->mergeCells($cellAwal . ":" . $cellAkhir);
		}

		protected function tulis($isi, $cellAwal){
			$use = $isi;
			$this->sheet->cell( $cellAwal , function($cell) use($use){
				$cell->setValue($use);
			});
		}

		protected function fontSize($ukuran, $cellAwal, $cellAkhir = ""){
			$use = $ukuran;
			if($cellAkhir == ""){
				$this->sheet->cell($cellAwal, function($cell) use($use) {
					$cell->setFontSize($use);
				});
			}else{
				$this->sheet->cell($cellAwal . ":" . $cellAkhir, function($cell) use($use) {
					$cell->setFontSize($use);
				});
			}
		}

		protected function fontWeight($weight, $cellAwal, $cellAkhir = ""){
			$use = $weight;
			if($cellAkhir == ""){
				$this->sheet->cell($cellAwal, function($cell) use($use) {
					$cell->setFontWeight($use);
				});
			}else{
				$this->sheet->cell($cellAwal . ":" . $cellAkhir, function($cell) use($use) {
					$cell->setFontWeight($use);
				});
			}
		}

		protected function hAlign($align, $cellAwal){
			$use = $align;
			
			$this->sheet->cell($cellAwal, function($cell) use($use) {
				$cell->setAlignment($use);
			});
			
		}

		protected function bgColor($hashTagWarna, $cellAwal, $cellAkhir = ""){
			$use = $hashTagWarna;
			if($cellAkhir == ""){
				$this->sheet->cell($cellAwal, function($cell) use($use) {
					$cell->setBackground($use);
				});
			}else{
				$this->sheet->cell($cellAwal . ":" . $cellAkhir, function($cell) use($use) {
					$cell->setBackground($use);
				});
			}
		}

		protected function fontColor($hashTagWarna, $cellAwal, $cellAkhir = ""){
			$use = $hashTagWarna;
			if($cellAkhir == ""){
				$this->sheet->cell($cellAwal, function($cell) use($use) {
					$cell->setFontColor($use);
				});
			}else{
				$this->sheet->cell($cellAwal . ":" . $cellAkhir, function($cell) use($use) {
					$cell->setFontColor($use);
				});
			}
		}

		protected function border($tipeGaris, $cellAwal, $cellAkhir = "", $top = true, $right = true, $bottom = true, $left = true){
			$use = $tipeGaris;
			$use2 = array($top, $right, $bottom, $left);
			if($cellAkhir == ""){
				$this->sheet->cell($cellAwal, function($cell) use($use, $use2) {
					$cell->setBorder( ($use2[0]?$use:"none"), ($use2[1]?$use:"none"), ($use2[2]?$use:"none"), ($use2[3]?$use:"none") );
				});
			}else{
				$this->sheet->cell($cellAwal . ":" . $cellAkhir , function($cell) use($use, $use2) {
					$cell->setBorder( ($use2[0]?$use:"none"), ($use2[1]?$use:"none"), ($use2[2]?$use:"none"), ($use2[3]?$use:"none") );
				});
			}
			
		}

		protected function vAlign($align, $cellAwal){
			$use = $align;
			
			$this->sheet->cell($cellAwal, function($cell) use($use) {
				$cell->setValignment($use);
			});
			
		}

		protected function wrapText($cellAwal, $cellAkhir = ""){
			if($cellAkhir == ""){
				$this->sheet->getStyle($cellAwal)->getAlignment()->setWrapText(true);
			}else{
				$this->sheet->getStyle($cellAwal . ":" . $cellAkhir)->getAlignment()->setWrapText(true);
			}
		}
	/*----------------------------private logic----------------------------*/
		

}
