<?php
class KategoriMenuResto extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idresto int
		-idkm int 
			id dari table mskategorimenu
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
		const CODE_ERROR_GET_KM = "-9999";
		const CODE_ERROR_GET_KMR = "-9998";
		private $KategoriMenu;
	/*--------------------------setter dan getter--------------------------*/
		public function getData($tipeGetData = self::GetData_TIPEDEFAULT){
			$arrRet = array();
			if($tipeGetData == self::GetData_TIPEDEFAULT){
				array_push($arrRet, $this->id);
				array_push($arrRet, $this->idresto);
				array_push($arrRet, $this->idkm);
				array_push($arrRet, $this->getNama());
				array_push($arrRet, $this->getDesc());
				array_push($arrRet, $this->getUrutan());
			}else if($tipeGetData == self::GetData_TIPEManageMenu){
				$arrRet = $this->getDataMenusForManageMenu();
			}else if($tipeGetData == self::GetData_TIPEKomposisi){
				$arrRet = $this->getDataMenusForManageMenuBagianKomposisi();
			}
			return $arrRet;
		}
		public function getNama(){
			if($this->getObjKategoriMenu()){
				return $this->KategoriMenu->getNama();
			}
			return self::CODE_ERROR_GET_KM;
		}
		public function setNama($nama){
			$km = new MsKategoriMenu; $idKM = $km->getIdByName($nama);
			$this->idkm = $idKM;
		}
		public function getDesc(){
			return ($this->desc == "" ? "" : $this->desc);
		}
		public function setDesc($desc){
			if($desc == "") $this->desc = null;
			else $this->desc = $desc;
		}
		public function getUrutan(){
			return $this->urutan;
		}
		public function setUrutan($urutanBaru){
			$this->urutan = $urutanBaru;
		}
		public function saveData($idResto, $namaBaru, $descBaru, $urutanBaru){
			$this->idresto = $idResto;
			$this->setNama($namaBaru);
			$this->setDesc($descBaru);
			$this->setUrutan($urutanBaru);
			$this->save();
		}
	/*---------------------------public logic------------------------------*/
		public function gantiUrutanFromJSON($textJsonNya){
			/*bentuk json = [ {i: idKMR, u: urutanBaru}, {i: idKMR, u: urutanBaru} ]*/
			try{
				$datas = json_decode($textJsonNya);				
				foreach($datas as $data){
					$idKMR = $data->i;
					$urutanBaru = $data->u;
					$kmr = self::find($idKMR);
					if($kmr == NULL) return self::CONST_RETURN_ERROR_TEXT;
					$kmr->setUrutan($urutanBaru);
					$kmr->save();
				}
				return self::Boolean_TRUE;
			}catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function tambahData($idResto, $namaBaru, $descBaru){
			try{
				$urutanBaru = $this->getUrutanTerbesarByIdParent($idResto) + 1;
				$this->saveData($idResto, $namaBaru, $descBaru, $urutanBaru);
				return json_encode($this->getData());
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;
			}
		}
		public function gantiDataNamaAndDesc($namaBaru, $descBaru){
			try{
				$this->setNama($namaBaru);
				$this->setDesc($descBaru);
				$this->save();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;	
			}
		}
		public function gantiDataDesc($descBaru){
			try{
				$this->setDesc($descBaru);
				$this->save();
				return self::Boolean_TRUE;
			}
			catch(Exception $e){
				return self::CONST_RETURN_ERROR_TEXT;	
			}
		}
		public function deleteData(){
			try{
				$this->deleteAllDataAnak();
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
		private function getObjKategoriMenu(){
			if($this->KategoriMenu != NULL) return true;
			$km = MsKategoriMenu::find($this->idkm);
			if($km == NULL) return false;
			$this->KategoriMenu = $km; return true;
		}
		private function getDataMenusForManageMenu(){
			$arr = array();
			$menus = Menu::whereRaw("idkmr = ?", array($this->id))->orderBy("urutan", "asc")->get();
			foreach($menus as $menu){
				array_push($arr, $menu->getData(Menu::GetData_TIPEManageMenu));
			}
			return $arr;
		}
		private function getDataMenusForManageMenuBagianKomposisi(){
			$arr = array();
			$menus = Menu::whereRaw("idkmr = ?", array($this->id))->orderBy("urutan", "asc")->get();
			foreach($menus as $menu){
				array_push($arr, $menu->getData(Menu::GetData_TIPEKomposisi));
			}
			return $arr;
		}
		private function getUrutanTerbesarByIdParent($idResto){
			$urutan = DB::table($this->table)->whereRaw("idresto = ?", array($idResto))->max("urutan");
			if($urutan == NULL) return 0;
			return $urutan;
		}
		private function deleteAllDataAnak(){
			$menus = Menu::whereRaw("idkmr = ?", array($this->id))->get();
			foreach($menus as $menu){
				$menu->deleteData();
			}
		}
		private function aturUlangUrutan(){
			$idResto = $this->idresto;
			$urutan = $this->getUrutan();
			DB::table($this->table)->whereRaw("idresto = ? and urutan > ?", array($idResto, $urutan))->decrement("urutan");
		}
		
}
