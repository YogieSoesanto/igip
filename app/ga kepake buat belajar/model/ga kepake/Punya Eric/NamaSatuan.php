<?php
class NamaSatuan extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar(30)
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'msnamasatuan';
		const FAIL_BECAUSE_NAMA_SATUAN_IS_BEING_USED = "15021";
		const NS_NOT_FOUND = "15022";
	/*--------------------------setter dan getter--------------------------*/
		public function getNama(){
			return $this->nama;
		}
		public function addNew($name){
			$test = self::whereRaw("nama like ?", array($name))->first();
			if($test == NULL){
				try{	
					$this->nama = $name;
					$this->save();
					return self::CONST_INSERT_SUKSES;
				}
				catch(Exception $e){
					return self::CONST_INSERT_GAGAL;
				}
			}else{
				return self::CONST_INSERT_GAGAL_NAMA_SUDAH_ADA;
			}
		}
	/*---------------------------public logic------------------------------*/
		public function getDataByName($nama){
			return DB::table($this->table)->select("id as 0","nama as 1")->whereRaw("nama like ?",array($this->getString_ConditionsBeginsWith($nama)))->get();
		}

		public function updateNamaSatuan($idNS, $inputNameBaru){
			$ns = self::find($idNS);
			if($ns != NULL){
				if($ns->checkBolehUpdateOrDelete()){
					$ns->doUpdate( $inputNameBaru );
				}else return self::FAIL_BECAUSE_NAMA_SATUAN_IS_BEING_USED;
			}else return self::NS_NOT_FOUND;
		}
		public function deleteById($idNya, $inputPassword){
			$obj = self::find($idNya);
			if($obj != null){
				if($obj->checkBolehUpdateOrDelete()){
					$pm = new PassModel;
					if($pm->isPasswordValidForDeleteNS ($inputPassword)){
						return $obj->doDelete();
					}else return self::FAIL_DELETE_PASSWORD_NOT_MATCH;
				}else return self::FAIL_BECAUSE_NAMA_SATUAN_IS_BEING_USED;
			}else return self::NS_NOT_FOUND;
		}
		
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function checkBolehUpdateOrDelete(){
			$rs = new RatioSatuan;
			return $rs->checkNamaSatuanBolehUpdateOrDelete($this->id);
		}
		private function doUpdate($inputNameBaru){
			try{
				$this->nama = $inputNameBaru;
				$this->save();
				return self::CONST_DELETE_SUKSES;
			}catch(exception $e){}
			return self::CONST_DELETE_GAGAL;
		}
		private function doDelete(){
			try{
				$this->delete();
				return self::CONST_DELETE_SUKSES;
			}catch(exception $e){}
			return self::CONST_DELETE_GAGAL;
		}
}
