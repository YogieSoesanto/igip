<?php
class KategoriTambahanMenu extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idmenu int
		-idkt int
		-minpilih int
			minpilih dan maxpilih berguna untuk validasi pilihan user ga boleh < minpilih atau > maxpilih
		-maxpilih int 
		-maxgratis int
			ItemKategoriTambahanMenu bakal dikenakan biaya sebanyak hargabiayatambahan kalo pilihan nya > maxgratis
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'kategoritambahanmenu';
		const GetData_TIPEManageMenu = -2;
		const CODE_ERROR_GET_KT = "-9999";
		const CODE_ERROR_GET_KTM = "-9998";
		private $KategoriTambahan;
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idmenu);
				array_push($arrRet, $this->idkt);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getMinPilih());
				array_push($arrRet, $this->getMaxPilih());
				array_push($arrRet, $this->getMaxGratis());
			}else if($tipeGetData == self::GetData_TIPEManageMenu){
				$arrRet = $this->getData();
				array_push($arrRet, $this->getData_ItemTambahansForManageMenu());
			}
			return $arrRet;
		}
		public function getNama(){
			if($this->getObjKategoriTambahan()){
				return $this->KategoriTambahan->getNama();
			}
			return self::CODE_ERROR_GET_KT;
		}
		public function setNama($nama){
			$kt = new MsKategoriTambahan; $idKT = $kt->getIdByName($nama);
			$this->idkt = $idKT;
		}
		public function getMinPilih(){
			return $this->minpilih;
		}
		public function setMinPilih($minPilih){
			$this->minpilih = $minPilih;
		}
		public function getMaxPilih(){
			return $this->maxpilih;
		}
		public function setMaxPilih($maxPilih){
			$this->maxpilih = $maxPilih;
		}
		public function getMaxGratis(){
			return $this->maxgratis;
		}
		public function setMaxGratis($maxGratis){
			$this->maxgratis = $maxGratis;
		}
		public function saveData($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis){
			$this->idmenu = $idMenu;
			$this->setNama($namaBaru);
			$this->setMinPilih($minPilih);
			$this->setMaxPilih($maxPilih);
			$this->setMaxGratis($maxGratis);
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function duplikatData($idMenuBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis){
			try{
				$ktm_NewHasilDuplikat = new KategoriTambahanMenu;
				$tempReturn = $ktm_NewHasilDuplikat->tambahData($idMenuBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis);
				if($tempReturn == self::CONST_RETURN_ERROR_TEXT){
					return $tempReturn;
				}
				$idKTM_LamaSourceDuplikat = $this->id;
				$ikts_LamaSourceDuplikat = ItemKategoriTambahanMenu::whereRaw("idktm = ?", array($idKTM_LamaSourceDuplikat))->get();
				foreach($ikts_LamaSourceDuplikat as $ikt){
					$ikt->duplikatData_SamaDataBedaParent($ktm_NewHasilDuplikat->id);
				}
				return json_encode($ktm_NewHasilDuplikat->getData(self::GetData_TIPEManageMenu));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function duplikatData_SamaDataBedaParent($idMenuBaru){
			$this->duplikatData($idMenuBaru, $this->getNama(), $this->getMinPilih(), $this->getMaxPilih(), $this->getMaxGratis());
		}
		public function tambahData($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis){
			try{
				$this->saveData($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis);
				return json_encode($this->getData(self::GetData_TIPEManageMenu));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function gantiDataNamaDanMinMaxMax($namaBaru, $minPilih, $maxPilih, $maxGratis){
			try{
				$this->setNama($namaBaru);
				$this->setMinPilih($minPilih);
				$this->setMaxPilih($maxPilih);
				$this->setMaxGratis($maxGratis);
				$this->save();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function gantiDataMinMaxMax($minPilih, $maxPilih, $maxGratis){
			try{
				$this->setMinPilih($minPilih);
				$this->setMaxPilih($maxPilih);
				$this->setMaxGratis($maxGratis);
				$this->save();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function deleteData(){
			try{
				$this->deleteAllDataAnak();
				$this->delete();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getObjKategoriTambahan(){
			if($this->KategoriTambahan != NULL) return true;
			$kt = MsKategoriTambahan::find($this->idkt);
			if($kt == NULL) return false;
			$this->KategoriTambahan = $kt; return true;
		}
		private function getData_ItemTambahansForManageMenu(){
			$arr = array();
			$ikts = ItemKategoriTambahanMenu::whereRaw("idktm = ?", array($this->id))->get();
			foreach($ikts as $ikt){
				array_push($arr, $ikt->getData());
			}
			return $arr;
		}
		private function deleteAllDataAnak(){
			$ikts = ItemKategoriTambahanMenu::whereRaw("idktm = ?", array($this->id))->get();
			foreach($ikts as $ikt){
				$ikt->deleteData();
			}
		}
}
