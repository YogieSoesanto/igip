<?php
class MsKategoriTambahan extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mskategoritambahan';
		const GetData_TIPEByName = -2;
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getNama());
			}else if($tipeGetData == self::GetData_TIPEByName){
				return $this->getNama();
			}
			return $arrRet;
		}
		public function getNama(){
			return $this->nama;
		}
		public function setNama($nama){
			$this->nama = $nama;
		}
		public function saveData($nama){
			$this->setNama($nama);
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function getIdByName($name){
			$kt = self::whereRaw("nama like ?", array($name))->first();
			if($kt == NULL){
				$kt = new MsKategoriTambahan;
				$kt->saveData($name);
			}
			return $kt->id;
		}
		public function getDatasByName($name, $formatLike = self::FormatLike_MENGANDUNG){
			if($formatLike == self::FormatLike_MENGANDUNG)
				$strLike = "%" . $name . "%";
			$datas = self::whereRaw("nama like ?", array($strLike))->get();
			$arrRet = array();
			foreach($datas as $data){
				array_push($arrRet, $data->getData(self::GetData_TIPEByName));
			}
			return $arrRet;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
