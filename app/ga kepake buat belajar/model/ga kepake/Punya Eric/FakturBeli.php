<?php
class FakturBeli extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idsupplier int
		-tanggal datetime
		-nilaitamb int
		-nilaipot int
		-namatamb varchar(50)
		-namapot varchar(50)
		-grandtotal int NULL
			grandtotal nya masih kosong awalnya, kalo dah dipanggil di function, dia bakal isi ke grandtotal, baru dah ada isinya
		-isdeleted tinyint(1)
			karena isdeleted tinyint, defaultnya selalu 0 / BOOLEAN_FALSE
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'fakturbeli';
		private $orang;
		const CONST_ORANG_IS_NOT_FOUND = "ERROR # ORANG_TIDAK_DITEMUKAN";
		const CONST_NO_FJ_WITH_THAT_ORANG_OR_DELETED = "ERROR999 # FAKTUR_JUAL_TIDAK_DITEMUKAN_KARENA_ORANG_ATAU_SUDAH_DI_DELETE";
		const CONST_NO_DFJ_WITH_THAT_BARANG = "ERROR998 # FAKTUR_JUAL_TIDAK_DITEMUKAN_KARENA_BARANG";
		const NO_FAKTUR_FOUND_FROM_THAT_TANGGAL = "5544";
		const NO_FAKTUR_FOUND_FROM_THAT_ORANG = "5555";
	/*--------------------------setter dan getter--------------------------*/
		public function getGrandTotal(){
			if($this->grandtotal == ""){
				$gt = $this->hitungGrandTotal();
				$this->grandtotal = $gt;
				$this->save();
			}
			return $this->grandtotal;
		}
		public function getNilaiTambahan(){
			return ($this->nilaitamb == "" ? 0 : $this->nilaitamb);
		}
		public function getNilaiPotongan(){
			return ($this->nilaipot == "" ? 0 : $this->nilaipot);
		}
		public function getNamaTambahan(){
			return $this->namatamb;
		}
		public function getNamaPotongan(){
			return $this->namapot;
		}
		public function getTanggalJamFaktur(){
			$tf = new TF;
			return $tf->convertTanggal(5, $this->tanggal);
		}
		public function getTanggalDoank(){
			$tf = new TF;
			return $tf->convertTanggal(2, $this->tanggal);
		}
		public function getNamaOrang(){
			if($this->getOrang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->orang->getNama();
			}return CONST_ORANG_IS_NOT_FOUND;
		}
		public function getSubTotalBarang(){
			return ($this->getGrandtotal() - $this->getNilaiTambahan() + $this->getNilaiPotongan());
		}
		public function getBanyakBarang(){
			return DetailFakturBeli::whereRaw("idfakturbeli = ?", array($this->id))->count();
		}
		public function getStatusDeleted(){
			return $this->isdeleted;
		}
	/*---------------------------public logic------------------------------*/
		/*public function getDataByOrangAndBarang($idOrang, $idBarang){
			$fj = self::whereRaw("idsupplier = ? and isdeleted = 0", array($idOrang))->IdDescending()->first();
			if($fj != NULL){
				return $fj->getDataByBarang($idBarang);
			}
			return self::CONST_NO_FJ_WITH_THAT_ORANG_OR_DELETED;
		}*/
		public function getDataByOrangAndBarang($idOrang, $idBarang){
			$fjs = self::whereRaw("idsupplier = ? and isdeleted = 0", array($idOrang))->IdDescending()->get();
			if(sizeof($fjs) == 0)
				return self::CONST_NO_FJ_WITH_THAT_ORANG_OR_DELETED;

			foreach($fjs as $fj){
				$retGetDataByBarang = $fj->getDataByBarang($idBarang);
				if($retGetDataByBarang != self::CONST_NO_DFJ_WITH_THAT_BARANG){
					return $retGetDataByBarang;
				}
			}
			return self::CONST_NO_DFJ_WITH_THAT_BARANG;
			
		}
		public function addNew($idOrang, $nilaiTambahan, $nilaiPotongan, $namaTambahan, $namaPotongan, $hariFaktur){
			try{	
				$this->idsupplier = $idOrang;
				$this->nilaitamb = $nilaiTambahan;
				$this->nilaipot = $nilaiPotongan;
				$this->namatamb = $namaTambahan;
				$this->namapot = $namaPotongan;
				$tf = new TF;
				$this->tanggal = $hariFaktur . " " . $tf->FzyDateVersion(4);
				$this->isdeleted = 0;
				$this->save();
				return $this->id;
			}
			catch(Exception $e){
			}
			return self::CONST_INSERT_GAGAL;
		}
		public function getDataHeaderHistory(){
			$arr = array(
				"0" => $this->getTanggalJamFaktur(),
				"1" => $this->getNamaOrang(),
				"2" => $this->getTextRp($this->getGrandTotal()),
				"3" => $this->getTextRp($this->getNilaiTambahan()),
				"4" => $this->getTextRp($this->getNilaiPotongan()),
				"5" => $this->getNamaTambahan(),
				"6" => $this->getNamaPotongan(),
				"7" => $this->getTextRp($this->getSubTotalBarang()),
				"8" => $this->getBanyakBarang(),
				"9" => $this->getStatusDeleted(),
				"10" => $this->id
			);
			return $arr;
		}
		public function getDataDetailHistory(){
			$arr = array();
			$dfjs = DetailFakturBeli::whereRaw("idfakturbeli = ?", array($this->id))->get();
			foreach($dfjs as $dfj){
				array_push($arr, $dfj->getDataHistory());
			}
			return $arr;
		}
		public function getDataHeaderRecreate(){
			$arr = array(
				"0" => $this->getTanggalDoank(),
				"1" => $this->idsupplier,
				"2" => $this->getNilaiTambahan(),
				"3" => $this->getNilaiPotongan(),
				"4" => $this->getNamaTambahan(),
				"5" => $this->getNamaPotongan(),
				"6" => $this->getNamaOrang(),
				"7" => $this->getStatusDeleted(),
			);
			return $arr;
		}
		public function getDataDetailRecreate(){
			$arr = array();
			$dfjs = DetailFakturBeli::whereRaw("idfakturbeli = ?", array($this->id))->get();
			foreach($dfjs as $dfj){
				array_push($arr, $dfj->getDataRecreate());
			}
			return $arr;
		}
		public function doDelete($pass){
			$pm = new PassModel;
			if($pm->isPasswordValidForDeleteFB($pass)){
				if($this->isdeleted == self::BOOLEAN_FALSE){
					$dfjs = DetailFakturBeli::whereRaw("idfakturbeli = ?", array($this->id))->get();
					foreach($dfjs as $dfj){
						$dfj->doMutasiStokFakturBeliDeleted();
					}
					$this->isdeleted = self::BOOLEAN_TRUE;
					$this->save();

					foreach($dfjs as $dfj){
						$dfj->gantiSemuaDataBarangToLastSebelumIni();
					}

					return self::CONST_DELETE_SUKSES;
				}
				return self::CONST_DELETE_GAGAL;
			}else
				return self::FAIL_DELETE_PASSWORD_NOT_MATCH;
			
		}
		public function getDataPrint(){
			return array(
				"header" => $this->getDataHeaderPrint(),
				"detail" => $this->getDataDetailPrint(),
			);
		}
		public function checkSupBolehDelete($idnya){
			$byk = DB::table($this->table)->whereRaw("idsupplier = ?", array($idnya))->take(1)->count();
			if($byk <= 0)return true;
			return false;
		}

		public function getDataHeaderHistoryByTanggal($tgl){
			$tglStart = $tgl . " 00:00:00";
			$tglEnd = $tgl . " 23:59:59";
			$fjs = FakturBeli::whereRaw("tanggal >= ? and tanggal <= ?", array($tglStart, $tglEnd))->IdDescending()->get();
			$arr = array();
			foreach($fjs as $fj){
				array_push($arr, $fj->getDataHeaderHistory());
			}
			if(sizeof($arr) == 0)return self::NO_FAKTUR_FOUND_FROM_THAT_TANGGAL;
			return json_encode($arr);
		}

		public function getDataHeaderHistoryByOrang($idC){
			$fjs = FakturBeli::whereRaw("idsupplier = ?", array($idC))->IdDescending()->get();
			$arr = array();
			foreach($fjs as $fj){
				array_push($arr, $fj->getDataHeaderHistory());
			}
			if(sizeof($arr) == 0)return self::NO_FAKTUR_FOUND_FROM_THAT_ORANG;
			return json_encode($arr);
		}
		
		public function deleteFatal($idH){
			$fj = FakturBeli::whereRaw("id=?", array($idH))->first();
			if($fj != NULL){
				$dfjs = DetailFakturBeli::whereRaw("idfakturbeli = ?", array($idH))->get();
				foreach($dfjs as $dfj){
					$dfj->delete();
				}
				$fj->delete();
			}
		}
		public function getDataByBarang($idBarang){
			$dfj = DetailFakturBeli::whereRaw("idfakturbeli = ? and idbarang = ?", array($this->id, $idBarang))->first();
			if($dfj != NULL){
				return json_encode($dfj->getDataBuatBarang());
			}
			return self::CONST_NO_DFJ_WITH_THAT_BARANG;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		
		private function hitungGrandTotal(){
			$subTotal = $this->hitungSubTotal();
			$nilaiTamb = $this->getNilaiTambahan();
			$nilaiPot = $this->getNilaiPotongan();
			return ($subTotal + $nilaiTamb - $nilaiPot);
		}
		private function hitungSubTotal(){
			$dfjs = DetailFakturBeli::whereRaw("idfakturbeli = ?", array($this->id))->get();
			$st = 0;
			foreach($dfjs as $dfj){
				$st += $dfj->getSubTotal();
			}
			return $st;
		}
		private function getOrang(){
			if($this->orang == null){
				$this->orang = Supplier::find($this->idsupplier);
				if($this->orang == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function getDataHeaderPrint(){
			return array(
				"0" => $this->getNamaOrang(),
				"1" => $this->getTanggalForPrint(),
				"2" => "Rp " . $this->getTextRp($this->getGrandTotal()),
				"3" => "Rp " . $this->getTextRp($this->getNilaiTambahan()),
				"4" => "Rp " . $this->getTextRp($this->getNilaiPotongan()),
				"5" => $this->getNamaTambahan(),
				"6" => $this->getNamaPotongan(),
				"7" => "Rp " . $this->getTextRp($this->getSubTotalBarang()),
			);
		}
		private function getDataDetailPrint(){
			$arr = array();
			$datas = DetailFakturBeli::whereRaw("idfakturbeli = ?", array($this->id))->get();
			foreach($datas as $data){
				array_push($arr, $data->getDataDetailPrint());
			}
			return $arr;
		}
		private function getTanggalForPrint(){
			$tf = new TF;
			return $tf->convertTanggal(3, $this->tanggal);
		}

}
