<?php
class ItemKategoriTambahan extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'msitemkategoritambahan';
		const GetData_ByName = -2;
	/*--------------------------setter dan getter--------------------------*/
		public function getIdIKT($namaBaru){
			$ikt = self::whereRaw("nama = ?", array($namaBaru))->first();
			if($ikt == NULL){
				$ikt = new ItemKategoriTambahan;
				$ikt->addNew($namaBaru);
			}
			return $ikt->id;
		}

		public function getNama(){
				return $this->nama;
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

		public function addNew($namaBaru){
			$this->nama = $namaBaru;
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
