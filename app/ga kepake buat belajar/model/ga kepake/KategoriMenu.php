<?php
class KategoriMenu extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mskategorimenu';
		const GetData_ByName = -2;
	/*--------------------------setter dan getter--------------------------*/
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

		public function addNew($nama){
			$this->nama = $nama;
			$this->save();
		}
	/*---------------------------public logic------------------------------*/	
		
		public function getIdByName($nama){
			$km = self::whereRaw("nama like ?", array($nama))->first();
			if($km == NULL){
				$km = new KategoriMenu;
				$km->addNew($nama);
			}
			return $km->id;
		}
		public function getDatasByName($nama, $formatLike = self::FormatLike_MENGANDUNG){
			if($formatLike == self::FormatLike_MENGANDUNG)
				$strLike = "%" . $nama . "%";

			$datas = self::whereRaw("nama like ?", array($strLike))->get();
			$arrRet = array();
			foreach($datas as $data){
				array_push($arrRet, $data->getData(self::GetData_ByName));
			}
			return $arrRet;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
