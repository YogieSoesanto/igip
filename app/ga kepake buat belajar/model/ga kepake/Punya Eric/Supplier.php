<?php
class Supplier extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar(100)
		-tglmasukdb datetime
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mssupplier';
		const CONST_NO_ID_SUPPLIER_GIVEN = "No Id Supplier Given";
		const FAIL_BECAUSE_SUPPLIER_IS_BEING_USED = "21021";
		const SUPPLIER_NOT_FOUND = "21022";
		
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
		public function getSupplierNameByIds($textJSONIdSupplier){
			$datas = json_decode($textJSONIdSupplier);
			$byk = sizeof($datas);
			if($byk > 0){
				return DB::table($this->table)->whereIn("id", $datas)->select("id as 0","nama as 1")->get();
			}return self::CONST_NO_ID_SUPPLIER_GIVEN;
		}
		public function deleteById($idNya, $inputPassword){
			$obj = self::find($idNya);

			if($obj != null){
				if($obj->checkBolehDelete()){
					$pm = new PassModel;
					if($pm->isPasswordValidForDeleteSup($inputPassword)){
						return $obj->doDelete();
					}else return self::FAIL_DELETE_PASSWORD_NOT_MATCH;
				}else return self::FAIL_BECAUSE_SUPPLIER_IS_BEING_USED;
			}else return self::SUPPLIER_NOT_FOUND;
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
			$fbeli = new FakturBeli;
			return $fbeli->checkSupBolehDelete($this->id);
		}
		private function doDelete(){
			try{
				$this->delete();
				return self::CONST_DELETE_SUKSES;
			}catch(exception $e){}
			return self::CONST_DELETE_GAGAL;
		}
}
