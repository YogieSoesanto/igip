<?php
class Menu extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idkmr int
			id dari kategorimenuresto
		-nama varchar
		-harga int
			kalo menu ini punya tambahan, harga disini berupa harga default
			contoh: 
			mie jumbo = 20000, mie sedang = 17000, mie diet = 15000
			maka tambahan porsi jumbo = 3000, porsi sedang = 0, porsi diet = -2000, harga = 17000
		-desc varchar
			default null, soalnya kemungkinan besar bakal null sih
		-isrecomend char 1
			di recomended dari resto ga, ga ada hubungan perhitungan like dari foorenzy
			default null, soalnya kemungkinan besar bakal null sih
		-urutan int
			kalo belom ada menu lain, maka jadi 1, kalo udah ada, maka urutan terakhir di++;
		-isavail char 1
			Kalo isAvail == null, artinya menu itu lagi ga available
			default nya 1. 
		-path varchar
			default null, soalnya kemungkinan besar bakal null sih
		-ishof char 1
			isHallOfFame kalo 1 artinya HOF
			default null, soalnya kemungkinan besar bakal null sih
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'menu';
		const GetData_TIPEManageMenu = -2;
		const GetData_TIPEKomposisi = -3;
		const CODE_ERROR_GET_MENU = "-9999";
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idkmr);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getHarga());
				array_push($arrRet, $this->getDesc());
				array_push($arrRet, $this->getIsRecomend());
				array_push($arrRet, $this->getUrutan());
				array_push($arrRet, $this->getIsAvailable());
				array_push($arrRet, $this->getPath());
				array_push($arrRet, $this->getIsHallOfFame());
			}else if($tipeGetData == self::GetData_TIPEManageMenu){
				$arrRet = $this->getData();
				array_push($arrRet, $this->getData_TambahansForManageMenu());
			}else if($tipeGetData == self::GetData_TIPEKomposisi){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getData_KomposisisForManageMenu());
			}
			return $arrRet;
		}
		public function getNama(){
			return $this->nama;
		}
		public function setNama($nama){
			$this->nama = $nama;
		}
		public function getHarga(){
			return $this->harga;
		}
		public function setHarga($harga){
			$this->harga = $harga;
		}
		public function getDesc(){
			return ($this->desc == "" ? "" : $this->desc);
		}
		public function setDesc($desc){
			if($desc == "") $this->desc = null;
			else $this->desc = $desc;
		}
		public function getIsRecomend(){
			return ($this->isrecomend == "1" ? "1" : "0");
		}
		public function setIsRecomend($rec){
			if($rec == "" || $rec == "0") $this->isrecomend = null;
			else $this->isrecomend = $rec;
		}
		public function getUrutan(){
			return $this->urutan;
		}
		public function setUrutan($urutanBaru){
			$this->urutan = $urutanBaru;
		}
		public function getIsAvailable(){
			return ($this->isavail == "1" ? "1" : "0");
		}
		public function setIsAvailable($isAv){
			if($isAv == "" || $isAv == "0") $this->isavail = null;
			else $this->isavail = $isAv;
		}
		public function getPath(){
			return ($this->path == "" ? "" : $this->path);
		}
		public function setPath($path){
			if($path == "") $this->path = null;
			else $this->path = $path;
		}
		public function getIsHallOfFame(){
			return ($this->ishof == "1" ? "1" : "0");
		}
		public function setIsHallOfFame($isHOF){
			if($isHOF == "" || $isHOF == "0") $this->ishof = null;
			else $this->ishof = $isHOF;
		}
		public function saveData($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend, $urutanBaru){
			$this->idkmr = $idKMR;
			$this->setNama($namaBaru);
			$this->setHarga($hargaBaru);
			$this->setDesc($descBaru);
			$this->setIsRecomend($isRecomend);
			$this->setUrutan($urutanBaru);
			$this->setIsAvailable("1");
			$this->setPath("");
			$this->setIsHallOfFame("0");
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function gantiUrutanFromJSON($textJsonNya){
			//bentuk json pasti [ {i: id, u: urutanBaru} , {i: id, u: urutanBaru} ]
			try{
				$datas = json_decode($textJsonNya);
				foreach($datas as $data){
					$idMenu = $data->i;
					$urutanBaru = $data->u;
					$menu = self::find($idMenu);
					if($menu == NULL) return self::CONST_RETURN_ERROR_TEXT;
					$menu->setUrutan($urutanBaru);
					$menu->save();
				}
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function duplikatData($idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
			try{
				$menu_NewHasilDuplikat = new Menu;
				$tempReturn = $menu_NewHasilDuplikat->tambahData($idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend);
				if($tempReturn == self::CONST_RETURN_ERROR_TEXT){
					return $tempReturn;
				}
				$idMenu_LamaSourceDuplikat = $this->id;
				$kts_LamaSourceDuplikat = KategoriTambahanMenu::whereRaw("idmenu = ?", array($idMenu_LamaSourceDuplikat))->get();
				foreach($kts_LamaSourceDuplikat as $kt){
					$kt->duplikatData_SamaDataBedaParent($menu_NewHasilDuplikat->id);
				}
				return json_encode($menu_NewHasilDuplikat->getData(self::GetData_TIPEManageMenu));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function tambahData($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
			try{
				$urutanBaru = $this->getUrutanTerbesarByIdParent($idKMR) + 1;
				$this->saveData($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend, $urutanBaru);
				return json_encode($this->getData(self::GetData_TIPEManageMenu));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function gantiData($namaBaru, $hargaBaru, $descBaru, $isRecomend){
			try{
				$this->setNama($namaBaru);
				$this->setHarga($hargaBaru);
				$this->setDesc($descBaru);
				$this->setIsRecomend($isRecomend);
				$this->save();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;	
			}
		}
		public function deleteData(){
			try{
				$this->deleteAllDataAnak_Tambahan();
				$this->deleteAllDataAnak_Komposisi();
				$this->deleteAllDataAnak_Gallery();
				$this->aturUlangUrutan();
				$this->delete();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getData_TambahansForManageMenu(){
			$arr = array();
			$kts = KategoriTambahanMenu::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($kts as $kt){
				array_push($arr, $kt->getData(KategoriTambahanMenu::GetData_TIPEManageMenu));
			}
			return $arr;
		}
		private function getData_KomposisisForManageMenu(){
			$arr = array();
			$kms = KomposisiMenu::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($kms as $km){
				array_push($arr, $km->getData(KomposisiMenu::GetData_TIPEAdmin));
			}
			return $arr;
		}
		private function getUrutanTerbesarByIdParent($idKMR){
			$urutan = DB::table($this->table)->whereRaw("idkmr = ?", array($idKMR))->max("urutan");
			if($urutan == NULL) return 0;
			return $urutan;
		}
		private function deleteAllDataAnak_Tambahan(){
			$kts = KategoriTambahanMenu::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($kts as $kt){
				$kt->deleteData();
			}
		}
		private function deleteAllDataAnak_Komposisi(){
			$kms = KomposisiMenu::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($kms as $km){
				$km->deleteData();
			}
		}
		private function deleteAllDataAnak_Gallery(){
			/*$gms = GalleryMenu::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($gms as $gm){
				$gm->deleteData();
			}*/
		}
		private function aturUlangUrutan(){
			$idKMR = $this->idkmr;
			$urutanMenu = $this->getUrutan();
			DB::table($this->table)->whereRaw("idkmr = ? and urutan > ?", array($idKMR, $urutanMenu))->decrement("urutan");
		}
}
