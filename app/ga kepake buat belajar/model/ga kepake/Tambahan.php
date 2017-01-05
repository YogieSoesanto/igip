<?php
class Tambahan extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idmenu int
		-idkt int
		-idikt int
			bisa null
			kalo idikt = null, maka di tampilan hanya tampilin data KT saja, misalnya Saos saja, ga ada BBQ / Mayo nya
			Seharusnya idikt = null sangat jarang terjadi , terjadi hanya jika admin BARU MENGINSERT KategoriTambahan, tapi belom menginsert ItemKategoriTambahan untuk KategoriTambahan Tersebut
		-hargaikt int
			bisa null juga
			kalo idikt = null, maka hargaikt jg pasti null
		-isavail char 1
			defaultnya 1 , tapi kalo null artinya dia lagi ga available
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'tambahan';
		const GetData_TIPEKateOnly = -2;
		const GetData_TIPEItemKateOnly = -3;
		const DEFAULT_IDIKT = "null";
		const DEFAULT_HARGAIKT = "null";

		private $KategoriTambahan;
		private $ItemKategoriTambahan;
		const CODE_ERROR_GET_KT = "-9999";
		const CODE_ERROR_GET_IKT = "-9998";
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getIdMenu());

				array_push($arrRet, $this->getIdKT());
				array_push($arrRet, $this->getNamaKT());
				array_push($arrRet, $this->getMinPilihKT());
				array_push($arrRet, $this->getMaxPilihKT());
				array_push($arrRet, $this->getMaxGratisKT());

				array_push($arrRet, $this->getIdIKT());
				array_push($arrRet, $this->getNamaIKT());
				array_push($arrRet, $this->getHargaIKT());
			}
			else if($tipeGetData == self::GetData_TIPEKateOnly){
				array_push($arrRet, $this->getIdKT());
				array_push($arrRet, $this->getNamaKT());
				array_push($arrRet, $this->getMinPilihKT());
				array_push($arrRet, $this->getMaxPilihKT());
				array_push($arrRet, $this->getMaxGratisKT());
			}
			else if($tipeGetData == self::GetData_TIPEItemKateOnly){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getIdIKT()); 
				array_push($arrRet, $this->getNamaIKT());
				array_push($arrRet, $this->getHargaIKT());
				array_push($arrRet, $this->getIsAvailable());
			}
			return $arrRet;
		}
		public function getIdMenu(){
			return $this->idmenu;
		}
		public function getIdKT(){
			return $this->idkt;
		}
		public function getNamaKT(){
			if($this->getObjKategoriTambahan()){
				return $this->KategoriTambahan->getNama();
			}
			return self::CODE_ERROR_GET_KT;
		}
		public function getMinPilihKT(){
			if($this->getObjKategoriTambahan()){
				return $this->KategoriTambahan->getMinPilih();
			}
			return self::CODE_ERROR_GET_KT;
		}
		public function getMaxPilihKT(){
			if($this->getObjKategoriTambahan()){
				return $this->KategoriTambahan->getMaxPilih();
			}
			return self::CODE_ERROR_GET_KT;
		}
		public function getMaxGratisKT(){
			if($this->getObjKategoriTambahan()){
				return $this->KategoriTambahan->getMaxGratis();
			}
			return self::CODE_ERROR_GET_KT;
		}
		public function getIdIKT(){
			return ($this->idikt == "" ? self::CODE_ERROR_GET_IKT : $this->idikt);
		}
		public function getNamaIKT(){
			if($this->getObjItemKategoriTambahan()){
				return $this->ItemKategoriTambahan->getNama();
			}
			return self::CODE_ERROR_GET_IKT;
		}
		public function getHargaIKT(){
			return ($this->hargaikt == "" ? "0" : $this->hargaikt);
		}
		public function getIsAvailable(){
			return ($this->isavail == "1" ? "1" : "0");
		}
	/*---------------------------public logic------------------------------*/
		public function insertKategoriTambahan($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis){
			try{
				$KT = new KategoriTambahan;
				$idKT = $KT->getIDKT($namaBaru, $minPilih, $maxPilih, $maxGratis);

				$tamb = new Tambahan;
				return $tamb->addNew($idMenu, $idKT);
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function addNew($idMenu, $idKT, $idIKT = self::DEFAULT_IDIKT, $hargaIKT = self::DEFAULT_HARGAIKT){
			try{
				$this->idmenu = $idMenu;
				$this->idkt = $idKT;

				if($idIKT != self::DEFAULT_IDIKT)
					$this->idikt = $idIKT;

				if($hargaIKT != self::DEFAULT_HARGAIKT)
					$this->hargaikt = $hargaIKT;

				$this->isavail = 1;

				$this->save();

				$arrDataKT = $this->getData(self::GetData_TIPEKateOnly);
				array_push($arrDataKT, $this->getDataIKT_ByIdKT($idMenu, $idKT));

				return json_encode($arrDataKT);
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function updateKategoriTambahan($idKTLama, $idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis){
			try{
				$KT = new KategoriTambahan;
				$idKTBaru = $KT->getIDKT($namaBaru, $minPilih, $maxPilih, $maxGratis);

				DB::table($this->table)->whereRaw("idkt = ? and idmenu = ?", array($idKTLama, $idMenu))->update(array("idkt" => $idKTBaru));
				
				$arr = array($idKTBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis);
				return json_encode($arr);
				
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function insertItemKategoriTambahan($idMenu, $idKT, $namaBaru, $hargaIKT){
			try{
				$IKT = new ItemKategoriTambahan;
				$idIKT = $IKT->getIdIKT($namaBaru);

				$this->addNew($idMenu, $idKT, $idIKT, $hargaIKT);

				DB::table($this->table)->whereRaw("idkt = ? and idmenu = ? and idikt is null", array($idKT, $idMenu))->delete();
				
				return json_encode($this->getData(self::GetData_TIPEItemKateOnly));
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function updateItemKategoriTambahan_WithNama($namaBaru, $hargaIKT){
			try{
				$IKT = new ItemKategoriTambahan;
				$idIKT = $IKT->getIdIKT($namaBaru);

				$this->idikt = $idIKT;
				$this->hargaikt = $hargaIKT;
				$this->save();
				return json_encode(array($this->idikt, $this->hargaikt));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function updateItemKategoriTambahan_WithoutNama($hargaIKT){
			try{
				$this->hargaikt = $hargaIKT;
				$this->save();
				return json_encode(array($this->idikt, $this->hargaikt));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function deleteItemKategoriTambahan(){
			try{
				$idKT = $this->getIdKT();
				$idMenu = $this->getIdMenu();

				$byk = DB::table($this->table)->whereRaw("idmenu = ? and idkt = ?", array($idMenu, $idKT))->count();
				if($byk == 1){
					$tamb = new Tambahan;
					$tamb->addNew($idMenu, $idKT);
				}
				$this->delete();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function deleteKategoriTambahan($idMenu, $idKT){
			try{
				DB::table($this->table)->whereRaw("idmenu = ? and idkt = ?", array($idMenu, $idKT))->delete();
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function deleteByIdMenu($idMenu){
			DB::table($this->table)->whereRaw("idmenu = ?", array($idMenu))->delete();
		}

		public function duplikatKategoriTambahan($idKTLama, $idMenuLama, $idMenuBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis){
			try{	
				$KT = new KategoriTambahan;
				$idKTBaru = $KT->getIDKT($namaBaru, $minPilih, $maxPilih, $maxGratis);

				$tambs = self::whereRaw("idmenu = ? and idkt = ?", array($idMenuLama, $idKTLama))->get();
				foreach($tambs as $tamb){
					$tambBaru = new Tambahan;
					$idIKT = $tamb->getIdIKT();
					if($idIKT == self::CODE_ERROR_GET_IKT){
						$tambBaru->addNew($idMenuBaru, $idKTBaru);
					}else{
						$tambBaru->addNew($idMenuBaru, $idKTBaru, $idIKT, $tamb->getHargaIKT());					
					}
				}
				$arrDataKT = array($idKTBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis);
				array_push($arrDataKT, $this->getDataIKT_ByIdKT($idMenuBaru, $idKTBaru));
				return json_encode($arrDataKT);

			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function getDataIKT_Per_KT_ByIdMenu($idMenu){
			$idkts = DB::table($this->table)->whereRaw("idmenu = ?", array($idMenu))->select("idkt")->distinct()->get();
			$byk = sizeof($idkts);
			$arr = array();
			for($i = 0 ; $i < $byk ; $i++){
				$idKt = $idkts[$i]->idkt;

				$tamb = Tambahan::whereRaw("idmenu = ? and idkt = ?", array($idMenu, $idKt))->first();

				$arrDataKT = $tamb->getData(self::GetData_TIPEKateOnly);
				array_push($arrDataKT, $this->getDataIKT_ByIdKT($idMenu, $idKt));
				
				array_push($arr, $arrDataKT);
			}
			
			return $arr;
		}
		public function getDataIKT_ByIdKT($idMenu, $idKt){
			$arr = array();
			$tambs = Tambahan::whereRaw("idmenu = ? and idkt = ?", array($idMenu, $idKt))->get();
			foreach($tambs as $tamb){
				array_push($arr, $tamb->getData(self::GetData_TIPEItemKateOnly));
			}
			return $arr;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getObjKategoriTambahan(){

			if($this->KategoriTambahan != NULL) return true;

			$kt = KategoriTambahan::find($this->idkt);
			if($kt == NULL) return false;

			$this->KategoriTambahan = $kt; return true;

		}

		private function getObjItemKategoriTambahan(){

			if($this->ItemKategoriTambahan != NULL) return true;

			$ikt = ItemKategoriTambahan::find($this->getIdIKT());
			if($ikt == NULL) return false;

			$this->ItemKategoriTambahan = $ikt; return true;

		}


}
