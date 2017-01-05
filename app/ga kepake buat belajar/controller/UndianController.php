<?php
class UndianController extends BaseController {

	const INT_NODATA_USER_NOT_LOGGED_IN = -99999;
	const STR_SHOW_UNDIAN_ONLY_RUANGAN = "SUOR";
	const STR_SHOW_UNDIAN_RUANGAN_WITH_KUPON_USER = "SURWKU";

	const STR_RUANG_UNDIAN_TIDAK_VALID = "Ruang Undian Tersebut Tidak Valid";
	const STR_DAFTARKAN_UNDIAN_ADA_DATA_TIDAK_VALID = "Terdapat beberapa data yang tidak sesuai dengan Database Server";
	const INT_STATE_SURUH_JADI_USER = -99998;
	const INT_STATE_RU_ABIS_SURUH_JADI_USER = -99997;
	const INT_STATE_ADA_CURRENTKUPON_LEFT_DAN_BRP_KUPON_LAMA = -99996;
	const INT_STATE_RU_ABIS_BRP_KUPON_LAMA = -99995;
	const INT_STATE_SHOW_KUPON_LAMA_DAN_CEK_KUPON_CURRENT_MILIKNYA = -99994;
	const INT_STATE_SURUH_JADI_FR = -99993;
	const INT_STATE_NO_CURRENTKUPON_LEFT_DAN_BRP_KUPON_LAMA = -99992;
	const INT_STATE_RU_ABIS_SURUH_JADI_FR = -99991;
	const INT_NO_USER_LOGGED_IN = -99990;
	const INT_MAXKUPON_UNKNOWN = -99990;

	public function showUndian(){
		//afterReady bakal panggil getDataForShowUndian()
		return View::make("undian.pilihperiode");
	}
	
	public function getDataForShowUndian(){

		$data_NotDepends_OnUserState = $this->getData_NotDepends_OnUserStateForShowUndian();

		$dataDependsOnUserState = $this->getDataDependsOnUserStateForShowUndian();

		$data = array(
			"data_NotDepends_OnUserState" => $data_NotDepends_OnUserState,
			"dataDependsOnUserState" => $dataDependsOnUserState,
		);
		return json_encode($data);
	}

	public function showRuangUndian($idundian){
		//afterReady bakal panggil getDataForDetailundian($idundian)
		if(Undian::isRuangUndianBerikutValid($idundian)){
			$data = array("idundian" => $idundian);
			return View::make("undian.detailundian", $data);
		}
		else{	
			return HSFoorenzy::showHalamanError(self::STR_RUANG_UNDIAN_TIDAK_VALID);
		}
	}
	
	public function getDataForRuangundian($idundian){
		$data_NotDepends_OnUserState = $this->getData_NotDepends_OnUserStateForRuangUndian($idundian);
		
		$dataDependsOnUserState = $this->getDataDependsOnUserStateForRuangundian($idundian, $data_NotDepends_OnUserState['headerUndian']);
		
		$data = array(
			"data_NotDepends_OnUserState" => $data_NotDepends_OnUserState,
			"dataDependsOnUserState" => $dataDependsOnUserState,
		);
		return json_encode($data);
	}

	public function doDaftarkanUndian(){
		$idundian = Input::get("idperiodeundian");
		$und = Undian::isRuangUndianValidAndGet($idundian);
		if($und !== FALSE){
			$kuponYangDiInput = Input::get("kupon");
			$user = Auth::user();
			if($this->isValid_UserTerhadapRuangUndian($user, $und)){
				$kuponUser = $user->getKupon();
				if($this->isValid_UserTerhadapKuponMiliknya($kuponUser, $kuponYangDiInput)){
					$capacityLeftForThisUndian = $und->getCapacityLeftAndSaveToTemp();
					if($capacityLeftForThisUndian >= $kuponYangDiInput){
						$this->insertKuponUndianUserIni($kuponUser, $kuponYangDiInput, $und, $user);
						return Redirect::back();
					}else{
						$pesanError = "Sayang sekali, saat ini kapasitas undian kami hanya tersisa " . $capacityLeftForThisUndian . "<br/><a href='/dundian/".$idundian."'>Kembali</a>";
						return HSFoorenzy::showHalamanError($pesanError);
					}
				}
			}
			return HSFoorenzy::showHalamanError(self::STR_DAFTARKAN_UNDIAN_ADA_DATA_TIDAK_VALID);
		}else{
			return HSFoorenzy::showHalamanError(self::STR_RUANG_UNDIAN_TIDAK_VALID);
		}
	}

	/*-----------------------------------------------------------private----------------------------------------*/
	private function getData_NotDepends_OnUserStateForShowUndian(){
		return array(
			"rUndians" => $this->getRuangUndianTerbaruForShowUndian_Sebanyak(50)
		);
	}
	private function getRuangUndianTerbaruForShowUndian_Sebanyak($bykAmbil){
		$ruangUndians = Undian::getRuangUndianTerbaruSebanyak($bykAmbil);
		return $this->getDataRuangUndianOnlyForShowUndian($ruangUndians);
	}
	private function getDataRuangUndianOnlyForShowUndian($ruangUndians){
		$dataRuangUndian = array();
		foreach($ruangUndians as $rUndian){
			array_push($dataRuangUndian, $rUndian->getDataForShowUndian());
		}
		return $dataRuangUndian;
	}
	private function getDataDependsOnUserStateForShowUndian(){

		$isUserLoggedIn = Auth::check();

		$dataKupon = $this->getKuponSiUserIfLoggedIn($isUserLoggedIn);

		$stateViewDataForAJAX = ($dataKupon == self::INT_NODATA_USER_NOT_LOGGED_IN ? self::STR_SHOW_UNDIAN_ONLY_RUANGAN : self::STR_SHOW_UNDIAN_RUANGAN_WITH_KUPON_USER);
		return array(
			"kuponUser" => $dataKupon,
			"stateView" => $stateViewDataForAJAX,
		);
	}
	private function getKuponSiUserIfLoggedIn($isUserLoggedIn){
		if($isUserLoggedIn){
			$user = Auth::user();
			$kupon = $user->getKupon();
		}else{
			$kupon = self::INT_NODATA_USER_NOT_LOGGED_IN;
		}
		return $kupon;
	}
	private function manageData_UserStateNotLoggedIn($isRuangUndianAktif){
		if($isRuangUndianAktif){ //ruang undian aktif
			return self::INT_STATE_SURUH_JADI_USER;
		}
		else{ //ruang undian udah berakhir
			return self::INT_STATE_RU_ABIS_SURUH_JADI_USER;
		}
	}
	private function manageData_UserState_DiaUser($isRuangUndianAktif, $isRuangUndianOnlyForFoodRef){
		if($isRuangUndianAktif){ //ruang undian aktif
			if($isRuangUndianOnlyForFoodRef){ //ruang undian khusus FoodRef Doank
				return self::INT_STATE_SURUH_JADI_FR;
			}else{ //ruang undian dibuka untuk semua User
				return self::INT_STATE_SHOW_KUPON_LAMA_DAN_CEK_KUPON_CURRENT_MILIKNYA;
			}
		}else{ //ruang undian udah berakhir
			if($isRuangUndianOnlyForFoodRef){ //ruang undian khusus FoodRef Doank
				return self::INT_STATE_RU_ABIS_SURUH_JADI_FR;
			}else{ //ruang undian dibuka untuk semua User
				return self::INT_STATE_RU_ABIS_BRP_KUPON_LAMA;
			}
		}
	}
	private function manageData_UserState_DiaFoodRef($isRuangUndianAktif){
		if($isRuangUndianAktif){ //ruang undian aktif
			return self::INT_STATE_SHOW_KUPON_LAMA_DAN_CEK_KUPON_CURRENT_MILIKNYA;
		}else{ //ruang undian udah berakhir
			return self::INT_STATE_RU_ABIS_BRP_KUPON_LAMA;
		}
	}
	private function manageData_UserStateLoggedIn($isRuangUndianAktif, $isRuangUndianOnlyForFoodRef){
		$user = Auth::user();
		if(!$user->isFoodRefFoorenzy()){ //dia bukan FoodRef Foorenzy, hanya user biasa
			return $this->manageData_UserState_DiaUser($isRuangUndianAktif, $isRuangUndianOnlyForFoodRef);
		}else{ //dia adalah FoodRef Foorenzy
			return $this->manageData_UserState_DiaFoodRef($isRuangUndianAktif);
		}
	}
	private function manageData_CekKuponCurrentMiliknya($user, $headerUndian){
		$data = array();
		$kuponnya = $user->getKupon();
		if($kuponnya <= 0){
			$data['maxKupon'] = $kuponnya;
			$STATE_DATA_RETURN = self::INT_STATE_NO_CURRENTKUPON_LEFT_DAN_BRP_KUPON_LAMA;
		}else{
			$data['maxKupon'] = ($kuponnya < $headerUndian['sisacapacity'] ? $kuponnya : $headerUndian['sisacapacity']);
			$STATE_DATA_RETURN = self::INT_STATE_ADA_CURRENTKUPON_LEFT_DAN_BRP_KUPON_LAMA;
		}

		$data['stateView'] = $STATE_DATA_RETURN;
		return $data;
	}
	private function getDataDependsOnUserStateForRuangundian($idundian, $headerUndian){
		$isUserLoggedIn = Auth::check();
		$isRuangUndianAktif = $this->cekIsRuangUndianAktif($headerUndian);
		$isRuangUndianOnlyForFoodRef = $this->cekIsRuangUndianOnlyForFoodRef($headerUndian);

		if($isUserLoggedIn){
			$user = Auth::user();
			$idUser = $user->id;
			$STATE_DATA_RETURN = $this->manageData_UserStateLoggedIn($isRuangUndianAktif, $isRuangUndianOnlyForFoodRef);
			$maxKupon = self::INT_MAXKUPON_UNKNOWN;
			if($STATE_DATA_RETURN == self::INT_STATE_SHOW_KUPON_LAMA_DAN_CEK_KUPON_CURRENT_MILIKNYA){
				$dataCekKuponCurrent = $this->manageData_CekKuponCurrentMiliknya($user, $headerUndian);
				$STATE_DATA_RETURN = $dataCekKuponCurrent['stateView'];
				$maxKupon = $dataCekKuponCurrent['maxKupon'];
			}
		}else{
			$idUser = self::INT_NO_USER_LOGGED_IN;
			$STATE_DATA_RETURN = $this->manageData_UserStateNotLoggedIn($isRuangUndianAktif);
			$maxKupon = self::INT_MAXKUPON_UNKNOWN;
		}

		$data = array(
			"idSiUser" 	=> $idUser,
			"stateView" => $STATE_DATA_RETURN,
			"maxKupon" => $maxKupon
		);
		return $data;
	}
	private function cekIsRuangUndianAktif($headerUndian){
		return ($headerUndian['isStatusAktif'] == Undian::INT_YES);
	}
	private function cekIsRuangUndianOnlyForFoodRef($headerUndian){
		return ($headerUndian['isFoodRefOnly'] == Undian::INT_YES);
	}
	private function getData_NotDepends_OnUserStateForRuangUndian($idundian){
		return array(
			"detailUndians" => $this->getDetailRuangUndian($idundian),
			"headerUndian" => $this->getHeaderRuangUndian($idundian),
		);
	}
	private function getHeaderRuangUndian($idundian){
		$und = Undian::whereRaw("id=?",array($idundian))->first();
		return $und->getDataHeader_ForRuangUndian($idundian);
	}
	private function getDetailRuangUndian($idundian){
		$query = "Select iduser, nama, kuponused from users inner join d_undian 
		on users.id = d_undian.iduser where idundian = ?";
		return DB::select($query, array($idundian));
	}
	private function isValid_UserTerhadapRuangUndian($user, $und){
		if($und->isStatusAktif() == "1"){
			if($und->isFoodRefOnly() == "1"){
				$fb = FoodBlogger::whereRaw("id=?",array($user->id))->first();
				if($fb != NULL) return true;
			}
			else return true;
		}
		return false;
	}
	private function isValid_UserTerhadapKuponMiliknya($kuponUser, $kuponYangDiInput){
		if($kuponYangDiInput <= 0) return false;

		if($kuponUser < $kuponYangDiInput) return false;

		return true;
	}
	private function insertKuponUndianUserIni($kuponuser, $kuponYangDiInput, $und, $user){
		$sisa = $kuponuser - $kuponYangDiInput;
		$user->kupon = $sisa;
		$user->save();

		$duNya = new DUndian;
		$duNya->add($und->id, $user->id, $kuponYangDiInput);

		$und->currcapa += $kuponYangDiInput;
		$und->save();
	}	


	/*-----------------------------------------------------------unknown----------------------------------------*/
}
