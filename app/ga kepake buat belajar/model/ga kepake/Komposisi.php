<?php
class Komposisi extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
		-path varchar
			default null, soalnya kemungkinan besar bakal null sih

	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mskomposisi';
		const GetData_ByName = -2;
	/*--------------------------setter dan getter--------------------------*/
		public function getNama(){
			return $this->nama;
		}
		public function getPath(){
			return ($this->path == "" ? "" : $this->path);
		}
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getNama());
			}else if($tipeGetData == self::GetData_ByName){
				return $this->getNama();
			}
			return $arrRet;
		}
	/*---------------------------public logic------------------------------*/
		public function getIdKomposisiByNama($nama){
			$komp = self::whereRaw("nama like ?", array($nama))->first();
			if($komp == NULL){
				$komp = new Komposisi;
				$komp->addNew($nama);
			}
			return $komp->id;
		}

		public function addNew($nama){
			$this->nama = $nama;
			$this->save();
		}
		public function getDatasByName($nama, $formatLike = self::FormatLike_MENGANDUNG){
			if($formatLike == self::FormatLike_MENGANDUNG)
				$strLike = "%" . $nama . "%";

			//$datas = self::whereRaw("nama like ?", array($strLike))->get();
			$datas = self::distinct()->select("nama")->whereRaw("nama like ?", array($strLike))->get();
			$arrRet = array();
			foreach($datas as $data){
				array_push($arrRet, $data->getData(self::GetData_ByName));
			}
			return $arrRet;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
