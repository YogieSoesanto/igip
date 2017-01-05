<?php
class Customer extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar(100)
		-tglmasukdb datetime
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mscustomer';
		const FAIL_BECAUSE_CUSTOMER_IS_BEING_USED = "20021";
		const CUSTOMER_NOT_FOUND = "20022";
	/*--------------------------setter dan getter--------------------------*/
		public function getNama(){
			return $this->nama;
		}
	/*---------------------------public logic------------------------------*/
		public function getDataByName($name){
			$datas = self::whereRaw("nama like ?", array($this->getString_ConditionsBeginsWith($name)))->get();
			$arr = array();
			foreach($datas as $data){
				array_push($arr, $data->getData());
			}
			return $arr;
		}
		public function addNew($name){
			if($this->checkNamaSudahAda($name))return self::CONST_INSERT_GAGAL_ORANG_SUDAH_ADA;
			try{	
				$this->nama = $name;
				$tf = new TF;
				$this->tglmasukdb = $tf->FzyDateVersion(1);
				$this->save();
				return self::CONST_INSERT_SUKSES;
			}
			catch(Exception $e){
			}
			return self::CONST_INSERT_GAGAL;
		}
		public function checkNamaSudahAda($name){
			$byk = DB::table($this->table)->whereRaw("nama like ?", array($name))->take(1)->count();
			if($byk <= 0)return false;
			return true;
		}
		public function deleteById($idNya, $inputPassword){
			$obj = self::find($idNya);

			if($obj != null){
				if($obj->checkBolehDelete()){
					$pm = new PassModel;
					if($pm->isPasswordValidForDeleteCus($inputPassword)){
						return $obj->doDelete();
					}else return self::FAIL_DELETE_PASSWORD_NOT_MATCH;
				}else return self::FAIL_BECAUSE_CUSTOMER_IS_BEING_USED;
			}else return self::CUSTOMER_NOT_FOUND;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getData(){
			$arr = array(
				"0" => $this->id,
				"1" => $this->getNama(),
			);
			return $arr;
		}
		private function checkBolehDelete(){
			$fjual = new FakturJual;
			return $fjual->checkCustBolehDelete($this->id);
		}
		private function doDelete(){
			try{
				$this->delete();
				return self::CONST_DELETE_SUKSES;
			}catch(exception $e){}
			return self::CONST_DELETE_GAGAL;
		}

		
}
