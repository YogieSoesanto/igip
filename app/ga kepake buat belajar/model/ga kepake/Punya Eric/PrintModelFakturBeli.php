<?php
class PrintModelFakturBeli extends PrintModel{

	/*-----------------------start field dan comment-----------------------
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		const CONST_ID_FAKTUR_TIDAK_DITEMUKAN = "54321";
		const CONST_MAX_BARIS_PER_HALAMAN = 40;
		const CONST_MAKS_LEN_NAMA_BARANG = 37;
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function fakturJual($idF, $tipeExcel = self::CONST_TIPE_EXCEL_STORE){
			$fj = FakturBeli::find($idF);
			if($fj == NULL){
				return self::CONST_ID_FAKTUR_TIDAK_DITEMUKAN;
			}

			$obj = $fj->getDataPrint();

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
			/*$this->sheet->setWidth('A', 4.5);
			$this->sheet->setWidth('B', 16);
			$this->sheet->setWidth('F', 6);
			$this->sheet->setWidth('G', 4.5);
			$this->sheet->setWidth('H', 16);*/

	    	$this->sheet->setWidth('A', 5);
	    	$this->sheet->setWidth('E', 7);
	    	$this->sheet->setWidth('F', 8);
	    	$this->sheet->setWidth('I', 8);
	    	$this->sheet->setWidth('J', 7);

	    	$row = 1;

			//$row = $this->makeDataToko($row);

			$row = $this->makeHeaderFaktur($row, "Faktur Pembelian", $obj['header'][0], $obj['header'][1]);
		
			$row = $this->makeBodyFaktur($row, $obj);
			
			$row = $this->makeFooterFaktur($row, $obj);
		}
		private function makeHeaderFaktur($startRow, $namaFaktur, $namaPelanggan, $tanggalFaktur){
			$row = $startRow;
			$colTemp = "E";
			$colTemp2 = "H";
			$this->merge($colTemp.$row , $colTemp2.$row);
			$this->tulis($namaFaktur, $colTemp.$row);
			$this->fontSize("14", $colTemp.$row);
			$this->fontWeight("bold", $colTemp.$row);
			$this->border("thin", $colTemp.$row.":".$colTemp2.$row);
			$this->hAlign("center", $colTemp.$row.":".$colTemp2.$row);

			$row++;
			$row++;
			$colTemp = "I";
			$this->merge($colTemp.$row , "L".$row);
			$this->tulis("Nama Supplier : " . $namaPelanggan, $colTemp.$row);
			$this->fontSize("12", $colTemp.$row);
			$this->hAlign("right", $colTemp.$row.":"."L".$row);

			$row++;
			$colTemp = "I";
			$this->merge($colTemp.$row , "L".$row);
			//$tf = new TF;
			//$tanggalFaktur = $tf->convertTanggal(3, $tanggalFaktur);
			$this->tulis("Tanggal : " . $tanggalFaktur, $colTemp.$row);
			$this->fontSize("12", $colTemp.$row);
			$this->hAlign("right", $colTemp.$row.":"."L".$row);

			$row+=2;
			return $row;
		}

		private function makeBodyFaktur($sr, $dfj){

			$colAwal  = array("A", "B", "F", "H", "J", "K");
			$colAkhir = array("A", "E", "G", "I", "J", "L");

			$this->judulDariBodyFaktur($colAwal, $colAkhir, $sr);

			$sr+=1;

			$sr = $this->isiDariBodyFaktur($colAwal, $colAkhir, $sr, $dfj);
			return $sr;
			
		}
		private function judulDariBodyFaktur($colAwal, $colAkhir, $sr){
			$this->merge($colAwal[1] . $sr , $colAkhir[1] . $sr);
			$this->merge($colAwal[2] . $sr , $colAkhir[2] . $sr);
			$this->merge($colAwal[3] . $sr , $colAkhir[3] . $sr);
			$this->merge($colAwal[5] . $sr , $colAkhir[5] . $sr);

			$this->border("thin", $colAwal[0].$sr);
			$this->border("thin", $colAwal[1].$sr . ":" . $colAkhir[1] . $sr);
			$this->border("thin", $colAwal[2].$sr . ":" . $colAkhir[2] . $sr);
			$this->border("thin", $colAwal[3].$sr . ":" . $colAkhir[3] . $sr);
			$this->border("thin", $colAwal[4].$sr);
			$this->border("thin", $colAwal[5].$sr . ":" . $colAkhir[5] . $sr);

			$this->fontSize("12", $colAwal[0].$sr .":".$colAkhir[5].$sr);

			$this->fontWeight("bold", $colAwal[0].$sr .":".$colAkhir[5].$sr);

			$this->hAlign("center", $colAwal[0].$sr .":".$colAkhir[5].$sr);
			$this->vAlign("center", $colAwal[0].$sr .":".$colAkhir[5].$sr);

			$this->tulis("No", $colAwal[0].$sr);
			$this->tulis("Nama Barang", $colAwal[1].$sr);
			$this->tulis("Quantity", $colAwal[2].$sr); 
			$this->tulis("Harga", $colAwal[3].$sr); 
			$this->tulis("Disc", $colAwal[4].$sr); 
			$this->tulis("Sub Total", $colAwal[5].$sr); 

			
		}

		private function isiDariBodyFaktur($colAwal, $colAkhir, $sr, $dfj){

			$bykDataDetail = sizeof($dfj['detail']);

			//$maxBarisPerHalaman = $settingAwalFakturJual['maxBarisPerHalaman'];
			$maxBarisPerHalaman = self::CONST_MAX_BARIS_PER_HALAMAN;

			$maxDetailPerHalaman = $maxBarisPerHalaman - $sr;

			$tempBuatCekApakahBanyaknyaMelebihiMaxDetailPerHalaman = 0;
			for($i = 0 ; $i < $bykDataDetail ; $i++){
				if($i == $bykDataDetail - 1)
					$banyakBarisKepakeBuatDataRowBerikut = $this->buatBodyFakturPerBaris($dfj['detail'][$i], $colAwal, $colAkhir, $sr, $i, true);
				else
					$banyakBarisKepakeBuatDataRowBerikut = $this->buatBodyFakturPerBaris($dfj['detail'][$i], $colAwal, $colAkhir, $sr, $i, true);

				$sr+=$banyakBarisKepakeBuatDataRowBerikut;

				$tempBuatCekApakahBanyaknyaMelebihiMaxDetailPerHalaman+=$banyakBarisKepakeBuatDataRowBerikut;
				if($tempBuatCekApakahBanyaknyaMelebihiMaxDetailPerHalaman > $maxDetailPerHalaman){
					//$this->kasihGarisUntukLastBarisData($colAwal, $colAkhir, $banyakBarisKepakeBuatDataRowBerikut, $sr);
					$tempBuatCekApakahBanyaknyaMelebihiMaxDetailPerHalaman = 0;
					$maxDetailPerHalaman = $maxBarisPerHalaman;
				}
			}
			return $sr;
		}

		private function buatBodyFakturPerBaris($dataPerBaris, $colAwal, $colAkhir, $sr, $angkaLooping, $isTutup = false){
			$namaBarang = $dataPerBaris['0'];
			$qtyBarang = $dataPerBaris['1'];
			$hargaJualLengkap = $dataPerBaris['2'];
			$discJual = $dataPerBaris['3'];
			$subTotal = $dataPerBaris['4'];

			$banyakBarisKepakeBuatDataRowBerikut = 1;

			if(strlen($namaBarang) > self::CONST_MAKS_LEN_NAMA_BARANG){
				$banyakBarisKepakeBuatDataRowBerikut = 2;
				$sr2 = $sr + 1;
			}else{
				$sr2 = $sr;
			}

			

			$this->merge($colAwal[0] . $sr , $colAkhir[0] . $sr2);
			$this->merge($colAwal[1] . $sr , $colAkhir[1] . $sr2);
			$this->merge($colAwal[2] . $sr , $colAkhir[2] . $sr2);
			$this->merge($colAwal[3] . $sr , $colAkhir[3] . $sr2);
			$this->merge($colAwal[4] . $sr , $colAkhir[4] . $sr2);
			$this->merge($colAwal[5] . $sr , $colAkhir[5] . $sr2);

			$this->border("thin", $colAwal[0].$sr . ":" . $colAkhir[0] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[1].$sr . ":" . $colAkhir[1] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[2].$sr . ":" . $colAkhir[2] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[3].$sr . ":" . $colAkhir[3] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[4].$sr . ":" . $colAkhir[4] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[5].$sr . ":" . $colAkhir[5] . $sr2, false, true, $isTutup, true);

			$this->fontSize("11",$colAwal[0].$sr .":".$colAkhir[5].$sr2);

			$this->hAlign("center", $colAwal[0].$sr .":".$colAkhir[5].$sr2);
			$this->hAlign("left", $colAwal[1].$sr .":".$colAkhir[1].$sr2);
			$this->hAlign("right", $colAwal[4].$sr .":".$colAkhir[5].$sr2);

			$this->vAlign("center", $colAwal[0].$sr .":".$colAkhir[5].$sr2);

			$this->wrapText($colAwal[1].$sr, $colAkhir[1] . $sr2);

			$this->tulis( ($angkaLooping + 1)."." , $colAwal[0].$sr);
			$this->tulis( $namaBarang, $colAwal[1].$sr);
			$this->tulis( $qtyBarang , $colAwal[2].$sr); 
			$this->tulis( $hargaJualLengkap , $colAwal[3].$sr); 
			$this->tulis( $discJual, $colAwal[4].$sr); 
			$this->tulis( $subTotal, $colAwal[5].$sr); 

			return $banyakBarisKepakeBuatDataRowBerikut;
		}

		public function kasihGarisUntukLastBarisData($colAwal, $colAkhir, $banyakBarisKepakeBuatDataRowBerikut, $sr2){
			$isTutup = true;
			$sr = $sr2 - $banyakBarisKepakeBuatDataRowBerikut;
			$this->border("thin", $colAwal[0].$sr . ":" . $colAkhir[0] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[1].$sr . ":" . $colAkhir[1] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[2].$sr . ":" . $colAkhir[2] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[3].$sr . ":" . $colAkhir[3] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[4].$sr . ":" . $colAkhir[4] . $sr2, false, true, $isTutup, true);
			$this->border("thin", $colAwal[5].$sr . ":" . $colAkhir[5] . $sr2, false, true, $isTutup, true);
		}

		public function makeFooterFaktur($sr, $dfj){

			$subTotal = $dfj['header']['7'];
			$nilaiTambahan = $dfj['header']['3'];
			$nilaiPotongan = $dfj['header']['4'];
			$grandTotal = $dfj['header']['2'];

			$namaTamb = $dfj['header']['5'] . " :";
			$namaPot = $dfj['header']['6'] . " :";

			$colAwal = array("A","J");
			$colAkhir = array("I","L");

			$sr+=1;
			$this->merge($colAwal[0].$sr, $colAkhir[0].$sr);
			$this->merge($colAwal[1].$sr, $colAkhir[1].$sr);
			$this->tulis( "Sub Total :" , $colAwal[0].$sr);
			$this->tulis( $subTotal, $colAwal[1].$sr);
			$this->hAlign("right", $colAwal[0].$sr .":" . $colAkhir[1].$sr);

			$sr++;
			$this->merge($colAwal[0].$sr, $colAkhir[0].$sr);
			$this->merge($colAwal[1].$sr, $colAkhir[1].$sr);
			$this->tulis( $namaTamb  , $colAwal[0].$sr);
			$this->tulis( $nilaiTambahan, $colAwal[1].$sr);
			$this->hAlign("right", $colAwal[0].$sr .":" . $colAkhir[1].$sr);

			$sr++;
			$this->merge($colAwal[0].$sr, $colAkhir[0].$sr);
			$this->merge($colAwal[1].$sr, $colAkhir[1].$sr);
			$this->tulis( $namaPot , $colAwal[0].$sr);
			$this->tulis( $nilaiPotongan, $colAwal[1].$sr);
			$this->hAlign("right", $colAwal[0].$sr .":" . $colAkhir[1].$sr);

			$sr++;
			$this->merge($colAwal[0].$sr, $colAkhir[0].$sr);
			$this->merge($colAwal[1].$sr, $colAkhir[1].$sr);
			$this->tulis( "Grand Total :" , $colAwal[0].$sr);
			$this->tulis( $grandTotal, $colAwal[1].$sr);
			$this->hAlign("right", $colAwal[0].$sr .":" . $colAkhir[1].$sr);
			$this->fontWeight("bold", $colAwal[0].$sr .":" . $colAkhir[1].$sr);
			$this->fontSize(12, $colAwal[0].$sr .":" . $colAkhir[1].$sr);

			$sr+=2;
			$this->merge("A".$sr, "F".$sr);
			$this->tulis( "Tanda Terima, " , "A" . $sr);
			$this->hAlign("center", "A".$sr .":" . "F".$sr);
			$this->fontWeight("bold", "A".$sr .":" . "F".$sr);

			$this->merge("G".$sr, "L".$sr);
			$this->tulis( "Hormat Kami, " , "G" . $sr);
			$this->hAlign("center", "G".$sr .":" . "L".$sr);
			$this->fontWeight("bold", "G".$sr .":" . "L".$sr);

		}
}
