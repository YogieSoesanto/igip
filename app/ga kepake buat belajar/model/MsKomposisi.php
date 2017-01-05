<?php
class MsKomposisi extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
		-path varchar
			default null, soalnya kemungkinan besar bakal null sih
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mskomposisi';
		const GetData_TIPEByName = -2;
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getPath());
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
		public function getPath(){
			return ($this->path == "" ? "" : $this->path);
		}
		public function setPath($path){
			if($path == "") $this->path = null;
			else $this->path = $path;
		}
		public function saveData($nama){
			$this->setNama($nama);
			$this->setPath("");
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function getIdByName($name){
			$komp = self::whereRaw("nama like ?", array($name))->first();
			if($komp == NULL){
				$komp = new MsKomposisi;
				$komp->saveData($name);
			}
			return $komp->id;
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
