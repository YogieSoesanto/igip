<?php
class KategoriMenuResto extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idkm int 
			id dari table kategorimenu
		-idresto int
		-desc varchar
			description kategori contohnya : kategorinya bubur, descnya Bubur di tempat kami dibuat dengan olahan yang berbeda. Disediakan hari senin selasa rabu saja.
			default null , soalnya kemungkinan besar desc bakal null sih
		-urutan int
			kalo belom ada kategori lain, maka jadi 1, kalo udah ada, maka urutan terakhir di++;
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'kategorimenuresto';
		const GetData_TIPEManageMenu = -2;
		const GetData_TIPEKomposisi = -3;
		const CODE_ERROR_GET_KM = "-9999"; //"Nama Kategori Menu Tidak Ditemukan";
		private $KM;
	/*--------------------------setter dan getter--------------------------*/
		public function editData_GantiUrutan($urutanBaru){
			$this->urutan = $urutanBaru;

			$this->save();
		}

		public function editData_WithNama($namaBaru, $descBaru){
			try{
				$km = new KategoriMenu;
				$idKM = $km->getIdByName($namaBaru);
				$this->idkm = $idKM;
				$this->desc = $descBaru;
				$this->save();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;	
			}
		}

		public function editData_WithoutNama($descBaru){
			try{
				$this->desc = $descBaru;
				$this->save();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;	
			}
		}

		public function addNew($idresto, $nama, $desc){
			try{
				$this->idresto = $idresto;

				$km = new KategoriMenu;
				$idKM = $km->getIdByName($nama);
				$this->idkm = $idKM;

				if($desc != "")
				$this->desc = $desc;

				$this->urutan = ( $this->getUrutanKMRTerbesar($idresto) + 1 );

				$this->save();

				return json_encode($this->getData());
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}

		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getDesc());
				array_push($arrRet, $this->getUrutan());
			}else if($tipeGetData == self::GetData_TIPEManageMenu){
				$arrRet = $this->getDataMenusForManageMenu();
			}else if($tipeGetData == self::GetData_TIPEKomposisi){
				$arrRet = $this->getDataMenusAndKomposisi();
			}
			return $arrRet;
		}
		public function getIdResto(){
			return $this->idresto;
		}
		
		public function getNama(){
			if($this->getObjKM()){
				return $this->KM->getNama();
			}
			return self::CODE_ERROR_GET_KM;
		}

		public function getDesc(){
			return ($this->desc == "" ? "" : $this->desc);
		}

		public function getUrutan(){
			return $this->urutan;
		}
	/*---------------------------public logic------------------------------*/
		public function gantiUrutanFromJSON($textJsonNya){
			try{
				$datas = json_decode($textJsonNya);
				//bentuk json pasti [ {i: idKMR, u: urutanBaru},{i: idKMR, u: urutanBaru},{i: idKMR, u: urutanBaru} ]
				foreach($datas as $data){
					$idKMR = $data->i;
					$urutanBaru = $data->u;
					$kmr = self::find($idKMR);
					$kmr->editData_GantiUrutan($urutanBaru);
				}
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::Boolean_FALSE;
			}
		}

		public function getDatasByIdResto($idResto){
			$arr = array();
			$datas = self::whereRaw("idresto = ?", array($idResto))->orderBy('urutan', 'asc')->get();
			foreach($datas as $data){
				array_push($arr, $data->getData());
			}
			return $arr;
		}

		public function deleteData(){
			try{
				$this->deleteMenuKMRBerikut();
				
				$this->aturUlangUrutanPadaRestoKMRBerikut();

				$this->delete();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getUrutanKMRTerbesar($idResto){
			$urutan = DB::table($this->table)->whereRaw("idresto = ?", array($idResto))->max("urutan");
			if($urutan == NULL)
				return 0;
			return $urutan;
		}

		private function getObjKM(){
			if($this->KM != NULL) return true;

			$km = KategoriMenu::find($this->idkm);
			if($km == NULL) return false;

			$this->KM = $km; return true;
		}

		private function deleteMenuKMRBerikut(){
			$menu = new Menu;
			$menu->deleteByIdKMR($this->id);
		}

		private function aturUlangUrutanPadaRestoKMRBerikut(){
			$idResto = $this->getIdResto();
			$urutan = $this->getUrutan();

			DB::table($this->table)->whereRaw("idresto = ? and urutan > ?", array($idResto, $urutan))->decrement("urutan");
		}

		private function getDataMenusForManageMenu(){
			$arr = array();
			$menus = Menu::whereRaw("idkmr = ?", array($this->id))->orderBy("urutan", "asc")->get();
			foreach($menus as $menu){
				array_push($arr, $menu->getData(Menu::GetData_TIPEManageMenu));
			}
			return $arr;
		}

		private function getDataMenusAndKomposisi(){
			$arr = array();
			$menus = Menu::whereRaw("idkmr = ?", array($this->id))->orderBy("urutan", "asc")->get();
			foreach($menus as $menu){
				$arrMenu = array(
					"id" => $menu->id,
					"km" => $menu->getData(Menu::GetData_TIPEKomposisi)
				);
				array_push($arr, $arrMenu);
			}
			return $arr;
		}

}
