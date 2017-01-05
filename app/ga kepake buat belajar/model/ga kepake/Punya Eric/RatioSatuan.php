<?php
class RatioSatuan extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-ratiosatuankecil int
		-ratiosatuanbesar int
		-idnamasatuankecil int
		-idnamasatuanbesar int
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'msratiosatuan';
		private $namaSatuanKecil;
		private $namaSatuanBesar;

		const CONST_ERROR_NAMA_SATUAN_KECIL = "ERROR # Nama Satuan Kecil Tidak Ditemukan";
		const CONST_ERROR_NAMA_SATUAN_BESAR = "ERROR # Nama Satuan Besar Tidak Ditemukan";
		const FAIL_BECAUSE_RATIO_SATUAN_IS_BEING_USED = "16021";
		CONST RS_NOT_FOUND = "16022";
	/*--------------------------setter dan getter--------------------------*/
		public function getNamaSatuanKecil(){
			if($this->getOBJNamaSatuanKecil() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->namaSatuanKecil->getNama();
			}return self::CONST_ERROR_NAMA_SATUAN_KECIL;
		}
		public function getNamaSatuanBesar(){
			if($this->getOBJNamaSatuanBesar() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->namaSatuanBesar->getNama();
			}return self::CONST_ERROR_NAMA_SATUAN_BESAR;
		}
		public function getRatioSatuanKecil(){
			return $this->ratiosatuankecil;
		}
		public function getRatioSatuanBesar(){
			return $this->ratiosatuanbesar;
		}

	/*---------------------------public logic------------------------------*/
		public function getDataByName($name){
			$datas = self::get();
			$arr = array();
			foreach($datas as $data){
				if($data->namaBeginsWith($name)){
					array_push($arr, $data->getData());
				}
			}
			return $arr;
		}
		public function getNama(){
			return $this->getRatioSatuanBesar() . "" . $this->getNamaSatuanBesar() . " @ " . 
			$this->getRatioSatuanKecil() . "" . $this->getNamaSatuanKecil();
		}
		public function getNilaiBesarByNilaiKecil($nilaiKecil){
			return ( $nilaiKecil * $this->ratiosatuanbesar ) / $this->ratiosatuankecil;
		}
		public function addNew($idSatuanKecil, $idSatuanBesar, $ratioSatuanKecil, $ratioSatuanBesar){
			try{	
				if($this->checkNamaSudahAda($ratioSatuanKecil, $ratioSatuanBesar, $idSatuanKecil, $idSatuanBesar))return self::CONST_INSERT_GAGAL_ORANG_SUDAH_ADA;
				$this->ratiosatuankecil = $ratioSatuanKecil;
				$this->ratiosatuanbesar = $ratioSatuanBesar;
				$this->idnamasatuankecil = $idSatuanKecil;
				$this->idnamasatuanbesar = $idSatuanBesar;
				$this->save();
				return $this->id;
			}
			catch(Exception $e){
			}
			return self::CONST_INSERT_GAGAL;
		}
		public function checkNamaSatuanBolehUpdateOrDelete($idNamaSatuan){
			$byk = DB::table($this->table)->whereRaw("idnamasatuankecil = ? or idnamasatuanbesar = ?", array($idNamaSatuan, $idNamaSatuan))->take(1)->count();
			if($byk <= 0)return true;
			return false;
		}
		public function namaBeginsWith($name){
			if($name == "")return true;

			$ret = strpos($this->getNama(), $name);
			if($ret === FALSE){
				return false;
			}
			else
				if($ret == 0)return true;
			return false;
		}
		public function deleteById($idNya, $inputPassword){
			$obj = self::find($idNya);
			if($obj != NULL){
				if($obj->checkBolehDelete()){
					$pm = new PassModel;
					if($pm->isPasswordValidForDeleteRS($inputPassword)){
						return $obj->doDelete();
					}else return self::FAIL_DELETE_PASSWORD_NOT_MATCH;
				}else return self::FAIL_BECAUSE_RATIO_SATUAN_IS_BEING_USED;
			}else return self::RS_NOT_FOUND;
		}
		
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function checkNamaSudahAda($ratioSatuanKecil, $ratioSatuanBesar, $idSatuanKecil, $idSatuanBesar){
			$byk = DB::table($this->table)->whereRaw("ratiosatuankecil = ? and ratiosatuanbesar = ? and idnamasatuankecil = ? and idnamasatuanbesar = ?", array($ratioSatuanKecil, $ratioSatuanBesar, $idSatuanKecil, $idSatuanBesar))->take(1)->count();
			if($byk <= 0)return false;
			return true;
		}

		private function getData(){
			return array(
				"0" => $this->id,
				"1" => $this->getNama(),
			);
		}
		private function getOBJNamaSatuanKecil(){
			if($this->namaSatuanKecil == null){
				$this->namaSatuanKecil = NamaSatuan::find($this->idnamasatuankecil);
				if($this->namaSatuanKecil == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function getOBJNamaSatuanBesar(){
			if($this->namaSatuanBesar == null){
				$this->namaSatuanBesar = NamaSatuan::find($this->idnamasatuanbesar);
				if($this->namaSatuanBesar == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function checkBolehDelete(){
			$barang = new Barang;
			return $barang->checkRatioSatuanBolehDelete($this->id);
		}
		private function doDelete(){
			try{
				$this->delete();
				return self::CONST_DELETE_SUKSES;
			}catch(exception $e){}
			return self::CONST_DELETE_GAGAL;
		}
}
