<?php
class KomposisiMenu extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idmenu int
		-idkomposisi int
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'komposisimenu';
		private $Komposisi;

		const CODE_ERROR_GET_KOMP = "-9999";
		const GetData_TIPEAdmin = -3;
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idmenu);
				array_push($arrRet, $this->idkomposisi);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getPath());
			}else if($tipeGetData == self::GetData_TIPEAdmin){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idmenu);
				array_push($arrRet, $this->idkomposisi);
				array_push($arrRet, $this->getNama());
			}
			return $arrRet;
		}
		public function getNama(){
			if($this->getObjKomposisi()){
				return $this->Komposisi->getNama();
			}
			return self::CODE_ERROR_GET_KOMP;
		}
		public function setNama($nama){
			$km = new MsKomposisi; $idKM = $km->getIdByName($nama);
			$this->idkomposisi = $idKM;
		}
		public function getPath(){
			if($this->getObjKomposisi()){
				return $this->Komposisi->getPath();
			}
			return self::CODE_ERROR_GET_KOMP;
		}
		public function saveData($idMenu, $nama){
			$this->idmenu = $idMenu;
			$this->setNama($nama);
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function manageKomposisiMenuFromJSON($json){
			//$json = '{"im":2,"ins":["Keju","Sapi","Jamur"],"del":[1,2]}';
			try{
				$obj = json_decode($json);
				$idMenu = $obj->im;
				$arrIns = $obj->ins;
				$arrDels = $obj->del;
				$this->manageInsertKomposisiMenu($idMenu, $arrIns);
				$this->manageDeleteKomposisiMenu($arrDels);
				return json_encode($this->getAllKomposisi_ByIdMenu($idMenu));
				//return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function tambahData($idMenu, $namaKomposisi){
			try{	
				$this->saveData($idMenu, $namaKomposisi);
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function deleteData(){
			try{
				$this->delete();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function getAllKomposisi_ByIdMenu($idMenu){
			$arr = array();
			$kms = KomposisiMenu::whereRaw("idmenu = ?", array($idMenu))->get();
			foreach($kms as $km){
				array_push($arr, $km->getData(KomposisiMenu::GetData_TIPEAdmin));
			}
			return $arr;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getObjKomposisi(){
			if($this->Komposisi != NULL) return true;
			$komp = MsKomposisi::find($this->idkomposisi);
			if($komp == NULL) return false;
			$this->Komposisi = $komp; return true;
		}
		private function manageInsertKomposisiMenu($idMenu, $arrIns){
			foreach($arrIns as $namaKomposisi){		
				$km = new KomposisiMenu;
				$km->tambahData($idMenu, $namaKomposisi);
			}
		}
		private function manageDeleteKomposisiMenu($arrDels){
			foreach($arrDels as $idKomposisiMenu){
				$km = self::find($idKomposisiMenu);
				if($km != NULL)
					$km->deleteData();
			}
		}
}
