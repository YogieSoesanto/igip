<?php
class ItemKategoriTambahanMenu extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idktm int
		-idikt int
		-hargaikt int
		-isavail char 1
			defaultnya 1 , tapi kalo null artinya dia lagi ga available
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'itemkategoritambahanmenu';
		const CODE_ERROR_GET_IKT = "-9998";
		const CODE_ERROR_GET_IKTM = "-9997";
		private $ItemKategoriTambahan;
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idktm);
				array_push($arrRet, $this->idikt);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getHargaIKT());
				array_push($arrRet, $this->getIsAvailable());
			}
			return $arrRet;
		}
		public function getNama(){
			if($this->getObjItemKategoriTambahan()){
				return $this->ItemKategoriTambahan->getNama();
			}
			return self::CODE_ERROR_GET_IKT;
		}
		public function setNama($nama){
			$ikt = new MsItemKategoriTambahan; $idIKT = $ikt->getIdByName($nama);
			$this->idikt = $idIKT;
		}
		public function getHargaIKT(){
			return $this->hargaikt;
		}
		public function setHargaIKT($harga){
			$this->hargaikt = $harga;
		}
		public function getIsAvailable(){
			return ($this->isavail == "1" ? "1" : "0");
		}
		public function setIsAvailable($isAv){
			if($isAv == "" || $isAv == "0") $this->isavail = null;
			else $this->isavail = $isAv;
		}
		public function saveData($idKTM, $namaBaru, $hargaIKT){
			$this->idktm = $idKTM;
			$this->setNama($namaBaru);
			$this->setHargaIKT($hargaIKT);
			$this->setIsAvailable("1");
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function duplikatData_SamaDataBedaParent($idKTMBaru){
			$iktm_NewHasilDuplikat = new ItemKategoriTambahanMenu;
			$iktm_NewHasilDuplikat->tambahData($idKTMBaru, $this->getNama(), $this->getHargaIKT());
		}
		public function tambahData($idKTM, $namaBaru, $hargaIKT){
			try{
				$this->saveData($idKTM, $namaBaru, $hargaIKT);
				return json_encode($this->getData());
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function gantiDataNamaDanHargaIKT($namaBaru, $hargaIKT){
			try{
				$this->setNama($namaBaru);
				$this->setHargaIKT($hargaIKT);
				$this->save();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function gantiDataHargaIKT($hargaIKT){
			try{
				$this->setHargaIKT($hargaIKT);
				$this->save();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function deleteData(){
			try{
				$this->delete();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getObjItemKategoriTambahan(){
			if($this->ItemKategoriTambahan != NULL) return true;
			$ikt = MsItemKategoriTambahan::find($this->idikt);
			if($ikt == NULL) return false;
			$this->ItemKategoriTambahan = $ikt; return true;
		}
}
