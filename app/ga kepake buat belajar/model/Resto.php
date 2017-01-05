<?php
class Resto extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'resto';
		const GetData_TIPEManageMenu = -2;
		const CODE_ERROR_GET_RESTO = "-9999";
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getNama());
			}else if($tipeGetData == self::GetData_TIPEManageMenu){
				$arrRet = $this->getDataKMRsForManageMenu();
			}
			return $arrRet;
		}
		public function getNama(){
			return $this->nama;
		}
	/*---------------------------public logic------------------------------*/
		
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getDataKMRsForManageMenu(){
			$arr = array();
			$kmrs = KategoriMenuResto::whereRaw("idresto = ?", array($this->id))->orderBy("urutan", "asc")->get();
			foreach($kmrs as $kmr){
				array_push($arr, $kmr->getData());
			}
			return $arr;
		}
}
