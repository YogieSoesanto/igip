<?php
class Barang extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar(100)
		-idsupplier int
		-idratiosatuan int
		-stokkecil int
			stokkecil ya cuma kecilnya aja, kalo stokbesar ga dicatat di DB, tapi bisa diambil nilainya lewat function getStokBesar()
		-hargamodal int
			hargamodal cuma di set saat ada pembelian ke supplier aja, kalo ga ada pembelian, harga modal jadinya null aja selamanya
		-discmodal float
			discmodal cuma di set saat ada pembelian ke supplier aja, kalo ga ada pembelian, harga modal jadinya null aja selamanya
		-ishargamodalforkecil tinyint(1)
			karena ishargamodalforkecil tinyint, defaultnya selalu 0 / BOOLEAN_FALSE
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'msbarang';
		private $supplier;
		private $ratioSatuan;
		const BOOLEAN_TRUE = 1;
		const BOOLEAN_FALSE = 0;
		const CONST_ERROR_SUPPLIER = "ERROR # Supplier Tidak ditemukan";
		const CONST_ERROR_RATIO_SATUAN = "ERROR # Ratio Satuan Tidak ditemukan";
		const FAIL_BECAUSE_BARANG_IS_BEING_USED = "17021";
		CONST BARANG_NOT_FOUND = "17022";

	/*--------------------------setter dan getter--------------------------*/
		public function getNama(){
			return $this->nama;
		}
		public function getNamaSupplier(){
			if($this->getSupplier() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->supplier->getNama();
			}return self::CONST_ERROR_SUPPLIER;
		}
		public function getNamaRatioSatuan(){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getNama();
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function getRatioSatuanKecil(){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getRatioSatuanKecil();
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function getRatioSatuanBesar(){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getRatioSatuanBesar();
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function getNamaSatuanKecil(){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getNamaSatuanKecil();
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function getNamaSatuanBesar(){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getNamaSatuanBesar();
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function getStokKecil(){
			return ($this->stokkecil == "" ? 0 : $this->stokkecil);
		}
		public function getStokBesar(){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getNilaiBesarByNilaiKecil($this->getStokKecil());
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function getHargaModal(){
			return ($this->hargamodal == "" ? 0 : $this->hargamodal);
		}
		public function getDiscModal(){
			return ($this->discmodal == "" ? 0 : $this->discmodal);
		}
		public function getIsHargaModalForKecil(){
			return $this->ishargamodalforkecil;
		}
	/*---------------------------public logic------------------------------*/
		public function getData(){
			$arr = array(
				"0" => $this->id,
				"1" => $this->getNama(),
				"2" => $this->getNamaSupplier(),
				//"3" => $this->getNamaRatioSatuan(),
				"3" => $this->getRatioSatuanKecil(),
				"4" => $this->getNamaSatuanKecil(),
				"5" => $this->getRatioSatuanBesar(),
				"6" => $this->getNamaSatuanBesar(),
				"7" => $this->getStokKecil(),
				"8" => $this->getStokBesar(),
				"9" => $this->getHargaModal(),
				"10" => $this->getDiscModal(),
				"11" => $this->getIsHargaModalForKecil(),
			);
			return $arr;
		}
		public function getDataForDetailOpname(){
			return array(
				"0" => $this->id,
				"1" => $this->getNama(),
				"2" => $this->getNamaSupplier(),
				"3" => $this->getRatioSatuanKecil(),
				"4" => $this->getNamaSatuanKecil(),
				"5" => $this->getRatioSatuanBesar(),
				"6" => $this->getNamaSatuanBesar(),
			);
		}
		public function getDataByName($name){
			$datas = self::whereRaw("nama like ?", array($this->getString_ConditionsBeginsWith($name)))->get();
			$arr = array();
			foreach($datas as $data){
				array_push($arr, $data->getData());
			}
			return $arr;
		}
		public function addNew($name, $idSupplier, $idRatioSatuan){
			try{	
				if($this->checkNamaSudahAda($name))return self::CONST_INSERT_GAGAL_ORANG_SUDAH_ADA;
				$this->nama = $name;
				$this->idsupplier = $idSupplier;
				$this->idratiosatuan = $idRatioSatuan;
				$this->ishargamodalforkecil = 0;
				$this->save();
				return self::CONST_INSERT_SUKSES;
			}
			catch(Exception $e){
			}
			return self::CONST_INSERT_GAGAL;
		}
		public function getQTYSecaraBesarByQSK($QtySecaraKecil){
			if($this->getRatioSatuan() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->ratioSatuan->getNilaiBesarByNilaiKecil($QtySecaraKecil);
			}return self::CONST_ERROR_RATIO_SATUAN;
		}
		public function doMutasiStok($valueNya, $tipeMutasi, $idFaktur){
			$stokAwal = $this->getStokKecil();
			if($tipeMutasi == DetailMutasi::CONST_TIPE_MUTASI_FAKTUR_JUAL){
				$stokAkhir = $stokAwal - $valueNya;	
			}else if($tipeMutasi == DetailMutasi::CONST_TIPE_MUTASI_FAKTUR_BELI){
				$stokAkhir = $stokAwal + $valueNya;	
			}else if($tipeMutasi == DetailMutasi::CONST_TIPE_MUTASI_STOK_OPNAME){
				$stokAkhir = $valueNya;	
			}else if($tipeMutasi == DetailMutasi::CONST_TIPE_MUTASI_FAKTUR_JUAL_DELETED){
				$stokAkhir = $stokAwal + $valueNya;	
			}else if($tipeMutasi == DetailMutasi::CONST_TIPE_MUTASI_FAKTUR_BELI_DELETED){
				$stokAkhir = $stokAwal - $valueNya;	
			}
			$this->stokkecil = $stokAkhir;
			$this->save();

			$this->addPerbedaanStokToMutasiTable($stokAwal, $stokAkhir, $tipeMutasi, $idFaktur);
		}
		public function getStokAboveZero($take, $skip){
			$arrRet = array();
			$datas = self::whereRaw("stokkecil > 0")->skip($skip)->take($take)->get();
			foreach($datas as $data){
				array_push($arrRet, $data->getDataStokAboveZero());
			}
			return $arrRet;
		}
		public function getCountStokAboveZero(){
			return self::whereRaw("stokkecil > 0")->count();
		}
		public function checkRatioSatuanBolehDelete($idnya){
			$byk = DB::table($this->table)->whereRaw("idratiosatuan = ?", array($idnya))->take(1)->count();
			if($byk <= 0)return true;
			return false;
		}
		public function deleteById($idNya, $inputPassword){
			$obj = self::find($idNya);
			if($obj != NULL){
				if($obj->checkBolehDelete()){
					$pm = new PassModel;
					if($pm->isPasswordValidForDeleteBarang($inputPassword)){
						return $obj->doDelete();
					}else return self::FAIL_DELETE_PASSWORD_NOT_MATCH;
				}else return self::FAIL_BECAUSE_BARANG_IS_BEING_USED;
			}else return self::BARANG_NOT_FOUND;
		}
		public function gantiHargaModalDanLainLain($hm, $dm, $ihmfk){
			$this->hargamodal = $hm;
			$this->discmodal = $dm;
			$this->ishargamodalforkecil = $ihmfk;
			$this->save();
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
	
		public function checkNamaSudahAda($name){
			$byk = DB::table($this->table)->whereRaw("nama like ?", array($name))->take(1)->count();
			if($byk <= 0)return false;
			return true;
		}

		private function getSupplier(){
			if($this->supplier == null){
				$this->supplier = Supplier::find($this->idsupplier);
				if($this->supplier == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function getRatioSatuan(){
			if($this->ratioSatuan == null){
				$this->ratioSatuan = RatioSatuan::find($this->idratiosatuan);
				if($this->ratioSatuan == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function addPerbedaanStokToMutasiTable($stokAwal, $stokAkhir, $tipeMutasi, $idFaktur){
			$hm = new HeaderMutasi;
			$hm->addPerbedaanStokBarangToMutasiTable($this->id, $stokAwal, $stokAkhir, $tipeMutasi, $idFaktur);
		}
		private function getDataStokAboveZero(){
			return array(
				"0" => $this->getNama(),
				"1" => $this->idsupplier,
				"2" => $this->getStokKecil(),
				"3" => $this->getNamaSatuanKecil(),
				"4" => $this->getStokBesar(),
				"5" => $this->getNamaSatuanBesar(),
			);
		}
		private function checkBolehDelete(){
			$dmutasi = new DetailMutasi;
			return $dmutasi->checkBarangBolehDelete($this->id);
		}
		private function doDelete(){
			try{
				$this->delete();
				return self::CONST_DELETE_SUKSES;
			}catch(exception $e){}
			return self::CONST_DELETE_GAGAL;
		}
}
