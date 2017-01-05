<?php
class KategoriTambahan extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-nama varchar
		-minpilih
		-maxpilih
		-maxgratis
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mskategoritambahan';
		const GetData_ByName = -2;
	/*--------------------------setter dan getter--------------------------*/
		public function getNama(){
			return $this->nama;
		}
		public function getMinPilih(){
			return $this->minpilih;
		}
		public function getMaxPilih(){
			return $this->maxpilih;
		}
		public function getMaxGratis(){
			return $this->maxgratis;
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
		public function getIDKT($namaBaru, $minPilih, $maxPilih, $maxGratis){
			$kt = self::whereRaw("nama = ? and minpilih = ? and maxpilih = ? and maxgratis = ?", array($namaBaru, $minPilih, $maxPilih, $maxGratis))->first();
			if($kt == NULL){
				$kt = new KategoriTambahan;
				$kt->addNew($namaBaru, $minPilih, $maxPilih, $maxGratis);
			}
			return $kt->id;
		}

		public function addNew($namaBaru, $minPilih, $maxPilih, $maxGratis){
			$this->nama = $namaBaru;
			$this->minpilih = $minPilih;
			$this->maxpilih = $maxPilih;
			$this->maxgratis = $maxGratis;
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
