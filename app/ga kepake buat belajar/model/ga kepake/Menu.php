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
		-urutan int
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
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
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
				array_push($arrRet, $this->getDataTambahansForManageMenu());
			}else if($tipeGetData == self::GetData_TIPEKomposisi){
				$arrRet = $this->getKomposisiMenu();
			}
			return $arrRet;
		}

		public function getIdKMR(){
			return $this->idkmr;
		}

		public function getNama(){
			return $this->nama;
		}

		public function getHarga(){
			return $this->harga;
		}

		public function getDesc(){
			return ($this->desc == "" ? "" : $this->desc);
		}

		public function getIsRecomend(){
			return ($this->isrecomend == "1" ? "1" : "0");
		}

		public function getUrutan(){
			return $this->urutan;
		}

		public function getIsAvailable(){
			return ($this->isavail == "1" ? "1" : "0");
		}

		public function getPath(){
			return ($this->path == "" ? "" : $this->path);
		}

		public function getIsHallOfFame(){
			return ($this->ishof == "1" ? "1" : "0");
		}

		

		public function editData_GantiUrutan($urutanBaru){
			$this->urutan = $urutanBaru;

			$this->save();
		}
		
	/*---------------------------public logic------------------------------*/
		public function getDataTambahan(){
			$arr = array();
			$tambs = Tambahan::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($tambs as $tamb){
				array_push($arr, $tamb->getData());
			}
			return $arr;
		}

		public function addNew($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
			
			try{
				$this->idkmr = $idKMR;
				$this->nama = $namaBaru;
				$this->harga = $hargaBaru;

				if($descBaru != "")
					$this->desc = $descBaru;

				if($isRecomend == "1")
					$this->isrecomend = "1";

				$this->urutan = ( $this->getUrutanMenuTerbesar($idKMR) + 1 );

				$this->isavail = "1";

				$this->save();

				return json_encode($this->getData(self::GetData_TIPEManageMenu));
				
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}


		}

		public function gantiUrutanFromJSON($textJsonNya){
			try{
				$datas = json_decode($textJsonNya);
				//bentuk json pasti [ {i: id, u: urutanBaru},{i: id, u: urutanBaru},{i: id, u: urutanBaru} ]
				foreach($datas as $data){
					$idMenu = $data->i;
					$urutanBaru = $data->u;
					$menu = self::find($idMenu);
					$menu->editData_GantiUrutan($urutanBaru);
				}

				return self::Boolean_TRUE;

			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}

		}

		public function editData($namaBaru, $hargaBaru, $descBaru, $isRecomend){
			try{
				$this->nama = $namaBaru;
				$this->harga = $hargaBaru;

				if($descBaru != "")
					$this->desc = $descBaru;
				else
					$this->desc = null;

				if($isRecomend == "1")
					$this->isrecomend = $isRecomend;
				else
					$this->isrecomend = null;

				$this->save();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function deleteData(){
			$this->deleteTambahanMenuBerikut();
			
			$this->deleteKomposisiMenuBerikut();
			
			$this->deleteGalleryMenuBerikut();

			$this->aturUlangUrutanPadaKMRMenuBerikut();

			$this->delete();
		}

		public function deleteByIdKMR($idKMR){
			$menus = self::whereRaw("idkmr = ?", array($idKMR))->get();
			foreach($menus as $menu){
				$menu->deleteData();
			}
		}

		public function duplikatData($idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
			try{
				$menuBaru = new Menu;
				$menuBaru->addNew($idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend);

				$tambsLama = Tambahan::whereRaw("idmenu = ?", array($this->id))->get();
				foreach($tambsLama as $dataTamabahanBuatMenuYangLama){
					$tamb = new Tambahan;
					$idKTLama = $dataTamabahanBuatMenuYangLama->getIdKT();
					$idIKTLama = $dataTamabahanBuatMenuYangLama->getIdIKT();
					if($idIKTLama == Tambahan::CODE_ERROR_GET_IKT){
						$tamb->addNew($menuBaru->id, $idKTLama);
					}else{
						$hargaIKTLama = $dataTamabahanBuatMenuYangLama->getHargaIKT();	
						$tamb->addNew($menuBaru->id, $idKTLama, $idIKTLama, $hargaIKTLama);
					}
				}
				return json_encode($menuBaru->getData(self::GetData_TIPEManageMenu));
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}


	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getUrutanMenuTerbesar($idKMR){
			$urutan = DB::table($this->table)->whereRaw("idkmr = ?", array($idKMR))->max("urutan");
			if($urutan == NULL)
				return 0;
			return $urutan;
		}

		private function deleteTambahanMenuBerikut(){
			$tamb = new Tambahan;
			$tamb->deleteByIdMenu($this->id);
		}

		private function deleteKomposisiMenuBerikut(){
			$kompMenu = new KomposisiMenu;
			$kompMenu->deleteByIdMenu($this->id);
		}

		private function deleteGalleryMenuBerikut(){

		}
		
		private function aturUlangUrutanPadaKMRMenuBerikut(){
			$idKMR = $this->getIdKMR();
			$urutanMenu = $this->getUrutan();

			DB::table($this->table)->whereRaw("idkmr = ? and urutan > ?", array($idKMR, $urutanMenu))->decrement("urutan");
		}

		private function getDataTambahansForManageMenu(){

			$arr = array();
			$tamb = new Tambahan;
			return $tamb->getDataIKT_Per_KT_ByIdMenu($this->id);
			
		}

		private function getKomposisiMenu(){
			$arr = array();
			$kms = KomposisiMenu::whereRaw("idmenu = ?", array($this->id))->get();
			foreach($kms as $km){
				array_push($arr, $km->getData());
			}
			return $arr;
		}

}
