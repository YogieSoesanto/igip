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
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idkomposisi);
				array_push($arrRet, $this->getNamaKomposisi());
				array_push($arrRet, $this->getPathKomposisi());
			}
			
			return $arrRet;
		}

		public function getNamaKomposisi(){
			if($this->getObjKomposisi()){
				return $this->Komposisi->getNama();
			}
			return self::CODE_ERROR_GET_KOMP;
		}

		public function getPathKomposisi(){
			if($this->getObjKomposisi()){
				return $this->Komposisi->getPath();
			}
			return self::CODE_ERROR_GET_KOMP;
		}

		

	/*---------------------------public logic------------------------------*/
		public function manageKomposisiMenu($json){
			//$json = '{"im":2,"ins":["Keju","Sapi","Jamur"],"del":[1,2]}';
			try{
				$obj = json_decode($json);
				$idMenu = $obj->im;

				$dataIns = $obj->ins;
				$dataDels = $obj->del;

				$this->manageInsertKomposisiMenu($idMenu, $dataIns);
				$this->manageDeleteKomposisiMenu($dataDels);

				return self::Boolean_TRUE;

				//return $this->getData(self::GetData_OBJKOMPOSISI);
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function addNew($idMenu, $idKomposisiDariMaster){
			$this->idkomposisi = $idKomposisiDariMaster;
			$this->idmenu = $idMenu;
			$this->save();
		}

		public function delData($idKomposisiMenu){
			$km = self::find($idKomposisiMenu);
			if($km != NULL)$km->delete();
		}

		public function deleteByIdMenu($idMenu){
			DB::table($this->table)->whereRaw("idmenu = ?", array($idMenu))->delete();
		}

		
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		

		private function manageInsertKomposisiMenu($idMenu, $dataIns){
			foreach($dataIns as $namaKomposisi){
				$komp = new Komposisi;
				$idKomposisiDariMaster = $komp->getIdKomposisiByNama($namaKomposisi);

				$km = new KomposisiMenu;
				$km->addNew($idMenu, $idKomposisiDariMaster);
			}
		}

		private function manageDeleteKomposisiMenu($dataDels){
			foreach($dataDels as $idKomposisiMenu){
				$this->delData($idKomposisiMenu);
			}
		}

		private function getObjKomposisi(){

			if($this->Komposisi != NULL) return true;

			$komp = Komposisi::find($this->idkomposisi);
			if($komp == NULL) return false;

			$this->Komposisi = $komp; return true;

		}
}
