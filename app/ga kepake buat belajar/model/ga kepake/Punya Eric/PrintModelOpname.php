<?php
class PrintModelOpname extends PrintModel{

	/*-----------------------start field dan comment-----------------------
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function stokGudang($jsonNya, $tipeExcel = self::CONST_TIPE_EXCEL_STORE){
			$obj = json_decode($jsonNya);
			
			$arrMargin = array(
				self::CONST_MARGIN_PAGE_LEFT, 
				self::CONST_MARGIN_PAGE_TOP, 
				self::CONST_MARGIN_PAGE_RIGHT, 
				self::CONST_MARGIN_PAGE_BOTTOM
			);

			//$orientation = self::CONST_ORIENTATION_LANDSCAPE;
			$orientation = self::CONST_ORIENTATION_PORTRAIT;

			$paperSize = PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_2;

			$ex = Excel::create("excel", function($excel) use($obj, $arrMargin, $orientation, $paperSize){
				$excel->setCreator("Yogie Soesanto")->setCompany("Bee Bee Toys");
				$excel->sheet("Sheet 1", function($sheet) use($obj, $arrMargin, $orientation, $paperSize){
					$this->sheet = $sheet;
					$sheet->setPaperSize( $paperSize );
					$sheet->setOrientation( $orientation );
					$sheet->setPageMargin( $arrMargin );
					
					$this->createData($obj);
				});
			});
			if($tipeExcel == self::CONST_TIPE_EXCEL_STORE)
				$ex->store("xlsx", public_path("/HasilPrint"));
			else
				$ex->download("xlsx");

			return self::CONST_PRINT_READY;
		}
	/*----------------------------protected logic--------------------------*/
		
	/*----------------------------private logic----------------------------*/
		private function createData($obj){
			$this->sheet->setWidth('A', 4.5);
			$this->sheet->setWidth('B', 16);
			$this->sheet->setWidth('F', 6);
			$this->sheet->setWidth('G', 4.5);
			$this->sheet->setWidth('H', 16);
			
			$row = 1;
			$this->buatTanggal($row);

			$byk = sizeof($obj);
			$row = 4;
			for($i = 0 ; $i < $byk ; $i++){
				$row = $this->buatPerSupplier($obj[$i], $row);
			}
			
		}
		private function buatPerSupplier($data, $row){
			$row = $this->wrapperNamaSupplier($data->ns, $row);

			$koloms = array("A", "B", "D", "G", "H", "J");
			$kolomsAkhir = array("A", "C", "E", "G", "I", "K");
			
			$row = $this->buatJudulDetail($row, $koloms, $kolomsAkhir);

			$row = $this->buatDetailBarang($row, $koloms, $kolomsAkhir, $data->brgs);

			return $row;
		}
		private function wrapperNamaSupplier($namaSupplier, $row){
			$tempRow = "A".$row;
			$this->mergeSatuBaris($row);
			$this->hAlign("center", $tempRow);
			$this->tulis($namaSupplier, $tempRow);
			$this->fontSize(14, $tempRow);
			$this->fontWeight("150", $tempRow);
			$this->border("thick", $tempRow.":"."K".$row);
			$this->vAlign("center", $tempRow);

			//$this->bgColor("#000000", $tempRow);
			//$this->fontColor("#ffffff", $tempRow);

			$row+=1;
			return $row;
		}
		private function buatTanggal($row){
			$tf = new TF;
			$textTanggal = "Tanggal : " . $tf->FzyDateVersion(3);

			$tempRow = "A".$row;
			$this->mergeSatuBaris($row);
			$this->tulis( $textTanggal ,$tempRow);
			$this->fontSize(14, $tempRow);
			$this->fontWeight("150", $tempRow);
			$this->hAlign("center", $tempRow);
		}
		private function buatJudulDetail($row, $koloms, $kolomsAkhir){
			
			$tempCell = $koloms[0] . $row;
			$tempAkhirCell = $kolomsAkhir[0] . $row;
			$this->tulis( "No.", $tempCell);
			$this->fontSize(11, $tempCell);
			$this->hAlign("center", $tempCell);
			$this->border("thin", $tempCell);
			$this->fontWeight("150", $tempCell);

			$tempCell = $koloms[1] . $row;
			$tempAkhirCell = $kolomsAkhir[1] . $row;
			$this->tulis( "Nama Barang", $tempCell);
			$this->merge($tempCell, $tempAkhirCell);
			$this->fontSize(11, $tempCell);
			$this->hAlign("center", $tempCell);
			$this->border("thin", $tempCell, $tempAkhirCell);
			$this->fontWeight("150", $tempCell);

			$tempCell = $koloms[2] . $row;
			$tempAkhirCell = $kolomsAkhir[2] . $row;
			$this->tulis( "Stok", $tempCell);
			$this->merge($tempCell, $tempAkhirCell);
			$this->fontSize(11, $tempCell);
			$this->hAlign("center", $tempCell);
			$this->border("thin", $tempCell, $tempAkhirCell);
			$this->fontWeight("150", $tempCell);



			$tempCell = $koloms[3] . $row;
			$tempAkhirCell = $kolomsAkhir[3] . $row;
			$this->tulis( "No.", $tempCell);
			$this->fontSize(11, $tempCell);
			$this->hAlign("center", $tempCell);
			$this->border("thin", $tempCell);
			$this->fontWeight("150", $tempCell);

			$tempCell = $koloms[4] . $row;
			$tempAkhirCell = $kolomsAkhir[4] . $row;
			$this->tulis( "Nama Barang", $tempCell);
			$this->merge($tempCell, $tempAkhirCell);
			$this->fontSize(11, $tempCell);
			$this->hAlign("center", $tempCell);
			$this->border("thin", $tempCell, $tempAkhirCell);
			$this->fontWeight("150", $tempCell);

			$tempCell = $koloms[5] . $row;
			$tempAkhirCell = $kolomsAkhir[5] . $row;
			$this->tulis( "Stok", $tempCell);
			$this->merge($tempCell, $tempAkhirCell);
			$this->fontSize(11, $tempCell);
			$this->hAlign("center", $tempCell);
			$this->border("thin", $tempCell, $tempAkhirCell);
			$this->fontWeight("150", $tempCell);

			$row += 1;
			return $row;

		}
		private function buatDetailBarang($row, $koloms, $kolomsAkhir, $brgs){
			$byk = sizeof($brgs);
			$bykBarisPerBaris = 1;
			for($i = 0 ; $i < $byk ; $i++){	
				if($i % 2 == 0){
					$j = $i + 1;
					if($j < $byk){
						$nabarj = strlen($brgs[$j]->nb);
						$nabari = strlen($brgs[$i]->nb);
						if($nabarj > self::CONST_MAKS_LENGTH_NAMA_BARANG || $nabari > self::CONST_MAKS_LENGTH_NAMA_BARANG){
							$bykBarisPerBaris = 2;
							$rowAkhir = $row + 1;
						}
						else{
							$bykBarisPerBaris = 1;
							$rowAkhir = $row;
						}
					}else{
						$nabari = strlen($brgs[$i]->nb);
						if($nabari > self::CONST_MAKS_LENGTH_NAMA_BARANG){
							$bykBarisPerBaris = 2;
							$rowAkhir = $row + 1;
						}else{
							$bykBarisPerBaris = 1;
							$rowAkhir = $row;
						}
					}
				}
				$namabarang = $brgs[$i]->nb;
				$stokbarang = $brgs[$i]->sb;
				if($i % 2 == 0){
					$tempCell = $koloms[0] . $row;
					$tempAkhirCell = $kolomsAkhir[0] . $rowAkhir;
					$this->tulis( ($i + 1), $tempCell);
					$this->merge($tempCell, $tempAkhirCell);
					$this->fontSize(10, $tempCell);
					$this->hAlign("center", $tempCell);
					$this->border("thin", $tempCell, $tempAkhirCell);
					$this->vAlign("center", $tempCell);
					$this->wrapText($tempCell, $tempAkhirCell);

					$tempCell = $koloms[1] . $row;
					$tempAkhirCell = $kolomsAkhir[1] . $rowAkhir;
					$this->tulis($namabarang, $tempCell);
					$this->merge($tempCell, $tempAkhirCell);
					$this->fontSize(10, $tempCell);
					$this->hAlign("left", $tempCell);
					$this->border("thin", $tempCell, $tempAkhirCell);
					$this->vAlign("center", $tempCell);
					$this->wrapText($tempCell, $tempAkhirCell);

					$tempCell = $koloms[2] . $row;
					$tempAkhirCell = $kolomsAkhir[2] . $rowAkhir;
					$this->tulis($stokbarang, $tempCell);
					$this->merge($tempCell, $tempAkhirCell);
					$this->fontSize(10, $tempCell);
					$this->hAlign("center", $tempCell);
					$this->border("thin", $tempCell, $tempAkhirCell);
					$this->vAlign("center", $tempCell);
					$this->wrapText($tempCell, $tempAkhirCell);

				}else{
					$tempCell = $koloms[3] . $row;
					$tempAkhirCell = $kolomsAkhir[3] . $rowAkhir;
					$this->tulis( ($i + 1), $tempCell);
					$this->merge($tempCell, $tempAkhirCell);
					$this->fontSize(10, $tempCell);
					$this->hAlign("center", $tempCell);
					$this->border("thin", $tempCell, $tempAkhirCell);
					$this->vAlign("center", $tempCell);
					$this->wrapText($tempCell, $tempAkhirCell);

					$tempCell = $koloms[4] . $row;
					$tempAkhirCell = $kolomsAkhir[4] . ($rowAkhir);
					$this->tulis($namabarang, $tempCell);
					$this->merge($tempCell, $tempAkhirCell);
					$this->fontSize(10, $tempCell);
					$this->hAlign("left", $tempCell);
					$this->border("thin", $tempCell, $tempAkhirCell);
					$this->vAlign("center", $tempCell);
					$this->wrapText($tempCell, $tempAkhirCell);

					$tempCell = $koloms[5] . $row;
					$tempAkhirCell = $kolomsAkhir[5] . $rowAkhir;
					$this->tulis($stokbarang, $tempCell);
					$this->merge($tempCell, $tempAkhirCell);
					$this->fontSize(10, $tempCell);
					$this->hAlign("center", $tempCell);
					$this->border("thin", $tempCell, $tempAkhirCell);
					$this->vAlign("center", $tempCell);
					$this->wrapText($tempCell, $tempAkhirCell);
				}
				if($i % 2 == 1){
					$row += $bykBarisPerBaris;
				}

			}
			$rowAkhir += 2;
			return $rowAkhir;
		}

}
