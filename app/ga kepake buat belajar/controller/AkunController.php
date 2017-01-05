<?php
class AkunController extends BaseController {
	const INT_PROFIL_DIRI_SENDIRI = -99999;
	const STRERROR_USER_BELOM_LOGIN_TAPI_AKSES_PROFIL_SELF = "Halaman berikut khusus untuk pengguna Foorenzy";
	const STRERROR_USER_DENGAN_IDTERSEBUT_TIDAK_ADA = "Tidak ditemukan Pengguna dengan ID Tersebut";
	const INT_USER_DENGAN_IDTERSEBUT_TIDAK_ADA = -99998;
	const INT_GANTI_DATA_PROFIL_DIPERBOLEHKAN = 11111;
	const STRERROR_UBAHPROFIL_ADA_DATA_GA_VALID = "Data yang dimasukan tidak sesuai dengan format kami. Harap ikuti Format yang sudah kami siapkan pada Javascript";
	const STRERROR_KESEMPATAN_VERIFY_HP_SUDAH_HABIS = "Anda sudah menggunakan semua kesempatan untuk hari ini. Coba lagi hari esok";
	const STRERROR_KHU_TIDAK_DITEMUKAN = "Link Berikut Tidak Valid atau sudah Kadaluarsa";
	

	
	public function showProfil($idTargetUser = self::INT_PROFIL_DIRI_SENDIRI){
		if(!Auth::check())
			if($idTargetUser == self::INT_PROFIL_DIRI_SENDIRI)
				return HSFoorenzy::showHalamanError(self::STRERROR_USER_BELOM_LOGIN_TAPI_AKSES_PROFIL_SELF);
				
		if($this->checkIsUserDenganIdTersebutExists($idTargetUser) == false){	
			if($idTargetUser != self::INT_PROFIL_DIRI_SENDIRI){
				return HSFoorenzy::showHalamanError(self::STRERROR_USER_DENGAN_IDTERSEBUT_TIDAK_ADA);
			}
		}

		$data = array("idTargetUser" => $idTargetUser);
		return View::make("profil.showProfil", $data);
	}
	
	public function getDataProfil(){
		$idTargetUser = Input::get("idTargetUser");

		if(Auth::check()){
			$iduser = Auth::id();
			if($idTargetUser == $iduser || $idTargetUser == self::INT_PROFIL_DIRI_SENDIRI){
				return $this->getDataProfil_ForSelf();
			}
		}
		return $this->getDataProfil_ForOther($idTargetUser);
	}
	
	public function getSkill(){
		return file_get_contents(storage_path()."/dataServer/skill.txt");
	}

	public function showUbahProfil(){
		return View::make("profil.ubahprofil");
	}

	public function getDataUbahProfil(){
		$user = Auth::user();
		$data = array(
			"namalengkap" => $user->nama,
			"namasingkat" => $user->namasingkat,
			"alamat" => $user->alamatdefault,
			"telp" => $user->hpdefault,
		);
		return json_encode($data);
	}

	public function doGantiDataProfil(){
		$nl = Input::get("namalengkap");
		$ns = Input::get("namasingkat");
		$al = Input::get("alamat");

		$returnNya = $this->checkInputanGantiDataProfilValid($nl, $ns, $al);
		if($returnNya == self::INT_GANTI_DATA_PROFIL_DIPERBOLEHKAN){
			$user = Auth::User();
			$user->ubahDataProfil($nl, $ns, $al);
			return Redirect::back();
		}
		return Redirect::to("/ubahprofil")->with("errorsnya",$returnNya);
	}

	public function doGantiGambarProfPic(){
		$user = Auth::user();
		$idUser = $user->id;

		$fui = new FoorenzyUploadImg;

		$fui->init(Input::file('gbrProfPic'));
		if($fui->checkImage()){

			$destinationPathWithoutFolder = public_path()."/foto/user";

			$destinationPath = $fui->createFolderForId_To_DestinationPathWithoutFolder($idUser, $destinationPathWithoutFolder);

			$newName = $fui->moveImage_And_getNewName($destinationPath, $idUser);

			$user->changeProfPicImageTo($newName);

			return $idUser . "/" . $newName;
		}else{
			return "GAGAL";
		}
	}
	
	public function doGantiNomorHpUser(){
		$nohpbaru = Input::get("nohpbaru");
		if($this->validasiFormatNomorHpBaruValid($nohpbaru)){
			return $this->buatKonfirmasiUntukPenggantianHpUser($nohpbaru);
		}
		return Redirect::to("/ubahprofil")->with("errorsnya",self::STRERROR_UBAHPROFIL_ADA_DATA_GA_VALID);
	}
	
	public function showKonfirmasiPesanan($idKHU){
		$khu = KonfHpUser::isObjectAdaBerdasarkan_And_Get("id",$idKHU);
		if($khu !== FALSE){
			$returnCheckOrNoHp = $khu->checkKHU_isValid_AndGetHp();
			if($khu->checkReturnCheck_CKHUISVAGH_IsOkay($returnCheckOrNoHp)){
				$arrNya = array(
					"hp" => $returnCheckOrNoHp,
					"idKHU" => $idKHU,
					"nyawasisa" => $khu->getNyawaSisa()
				);
				return View::make("akun.konfhpuser", $arrNya);
			}
			return HSFoorenzy::showHalamanError($returnCheckOrNoHp);
		}
		return HSFoorenzy::showHalamanError(self::STRERROR_KHU_TIDAK_DITEMUKAN);
	}

	public function doConfirmPenggantianHpUser(){
		$idKHU = Input::get("idkhu");
		$khu = KonfHpUser::isObjectAdaBerdasarkan_And_Get("id",$idKHU);
		if($khu !== FALSE){
			$returnCheckOrNoHp = $khu->checkKHU_isValid_AndGetHp();
			
			if($khu->checkReturnCheck_CKHUISVAGH_IsOkay($returnCheckOrNoHp)){
				$konfAsli = $khu->kodekonfnya;
				$konfDimasukan = Input::get("konfirmasi");

				$khu->setUdahInput();

				if($konfAsli == $konfDimasukan){
					return $this->doSomethingForPenggantianHpUser($khu);					
				}else{
					return Redirect::to("/khu/gagalkonf/".$idKHU);
				}
			}
			return HSFoorenzy::showHalamanError($returnCheckOrNoHp);
		}
		return HSFoorenzy::showHalamanError(self::STRERROR_KHU_TIDAK_DITEMUKAN);
	}

	public function doKirimUlangPenggantianHpUserKeNoBaru(){
		$idKHU = Input::get("idkhu");
		$noHpBaru = Input::get("hpbaru");

		$khu = KonfHpUser::isObjectAdaBerdasarkan_And_Get("id",$idKHU);
		if($khu !== FALSE){
			
			$returnCheckOrNoHp = $khu->checkKHU_isValidEvenSudahPernah_AndGetHp();

			
			if($khu->checkReturnCheck_CKHUISVAGH_IsOkay($returnCheckOrNoHp)){

				if($khu->isForPesanan()){
					return $this->buatKonfirmasiUntukPenggantianHpUserForPesanan($noHpBaru, $khu);
				}else{
					return $this->buatKonfirmasiUntukPenggantianHpUser($noHpBaru);
				}

			}
			return HSFoorenzy::showHalamanError($returnCheckOrNoHp);
		}
		return HSFoorenzy::showHalamanError(self::STRERROR_KHU_TIDAK_DITEMUKAN);
	}

	public function showGagalKonfirmasi($idKHU){
		$khu = KonfHpUser::isObjectAdaBerdasarkan_And_Get("id",$idKHU);
		if($khu !== FALSE){
			
			$returnCheckOrNoHp = $khu->checkKHU_isValidEvenSudahPernah_AndGetHp();

			
			if($khu->checkReturnCheck_CKHUISVAGH_IsOkay($returnCheckOrNoHp)){
				$arrNya = array(
					"hp" => $returnCheckOrNoHp,
					"idKHU" => $idKHU,
				);
				return View::make("pesan.konfhpusergagal", $arrNya);
			}
			return HSFoorenzy::showHalamanError($returnCheckOrNoHp);
		}
		return HSFoorenzy::showHalamanError(self::STRERROR_KHU_TIDAK_DITEMUKAN);
	}

	public function doKirimUlang_GantiNomorHpUser(){
		$idKHU = Input::get("idkhu");
		$khu = KonfHpUser::isObjectAdaBerdasarkan_And_Get("id",$idKHU);
		if($khu !== FALSE){
			
			$returnCheckOrNoHp = $khu->checkKHU_isValidEvenSudahPernah_AndGetHp();

			if($khu->checkReturnCheck_CKHUISVAGH_IsOkay($returnCheckOrNoHp)){

				if($this->validasiFormatNomorHpBaruValid($returnCheckOrNoHp)){
					if($khu->isForPesanan()){
						return $this->buatKonfirmasiUntukPenggantianHpUserForPesanan($returnCheckOrNoHp, $khu);
					}else{
						return $this->buatKonfirmasiUntukPenggantianHpUser($returnCheckOrNoHp);
					}
				}
				return Redirect::to("/ubahprofil")->with("errorsnya",self::STRERROR_UBAHPROFIL_ADA_DATA_GA_VALID);

			}
			return HSFoorenzy::showHalamanError($returnCheckOrNoHp);
		}
		return HSFoorenzy::showHalamanError(self::STRERROR_KHU_TIDAK_DITEMUKAN);
	}

	public function doSubmitKritikDanSaran_HasUser(){
		$kdans = Input::get("kdans");
		if(KritikSaran::validateKritikDanSaran($kdans)){
			$ks = new KritikSaran;
			$ks->addKritikSaranWithUser($kdans);
			return "SUKSES";
		}
		return "GAGAL";
	}

	public function doSubmitKritikDanSaran_NoUser(){
		$kdans = Input::get("kdans");
		$nama = Input::get("nama");
		$email = Input::get("email");
		if(KritikSaran::validateKritikDanSaran($kdans)){
			$ks = new KritikSaran;
			$ks->addKritikSaranNoUser($kdans, $nama, $email);
			return "SUKSES";
		}
		return "GAGAL";
	}

	public function showPesanDariServer(){
		return View::make("akun.pesandariserver");
	}


	/*-----------------------------------------------------private----------------------------------*/
	private function checkIsUserDenganIdTersebutExists($idTargetUser){
		return User::isObjectAdaBerdasarkan("id",$idTargetUser);
	}



	private function getDataProfil_ForSelf(){
		$user = Auth::user();
		$data = array(
			"dirisendiri" => true,

			"barPoinExp" => $user->getExpBar(),
			"barPoinBeli" => $user->getPBBar(),
			"textPoinExp" => $user->getExpText(),
			"textPoinBeli" => $user->getPBText(),
			"kupon" => $user->getKupon(),
			"namasingkat" => $user->namasingkat,
			"alamat" => $user->alamatdefault,
			"telp" => $user->hpdefault,

			"pathProfPic" => $user->getLinkProfPic(),
			"level" => $user->getLevel(),
			"namalengkap" => $user->nama,
			"tglgabung" => $user->jamgabung,
		);
		return json_encode($data);
	}
	private function getDataProfil_ForOther($idTargetUser){
		if($this->checkIsUserDenganIdTersebutExists($idTargetUser)){
			$user = User::whereRaw("id=?",array($idTargetUser))->first();
			$data = array(
				"dirisendiri" => false,

				"pathProfPic" => $user->getLinkProfPic(),
				"level" => $user->getLevel(),
				"namalengkap" => $user->nama,
				"tglgabung" => $user->jamgabung,
			);
			return json_encode($data);
		}
		return self::INT_USER_DENGAN_IDTERSEBUT_TIDAK_ADA;
	}



	private function checkInputanGantiDataProfilValid($nl, $ns, $al){
		if(strlen($nl) > 50){
		}else if(strlen($ns) > 10){
		}else if(strlen($al) > 150){
		}else{
			if(strlen($nl) >= 3){
				if(strlen($ns) >= 3){
					if(strlen($al) >= 5){
						return self::INT_GANTI_DATA_PROFIL_DIPERBOLEHKAN;
					}
				}
			}
		}
		return self::STRERROR_UBAHPROFIL_ADA_DATA_GA_VALID;
	}



	private function validasiFormatNomorHpBaruValid($nohpbaru){
		if(strlen($nohpbaru) <= 20 && strlen($nohpbaru) >= 5)
			if(substr($nohpbaru,0,1) == "0") 
				return true;
		return false;	
	}
	private function buatKonfirmasiUntukPenggantianHpUser($noHpBaru){
		$user = Auth::user();
		return KonfHpUser::KonfirmasiPenggantianHpUser($user->id, $noHpBaru);
	}
	private function buatKonfirmasiUntukPenggantianHpUserForPesanan($noHpBaru, $khuLama){
		$user = Auth::user();
		return KonfHpUser::KonfirmasiPenggantianHpUserForPesanan($user->id, $noHpBaru, $khuLama);
	}
	private function getUdahBerapaBanyakKonfirmasiHpUserHariIni($userid){
		$khu = KonfHpUser::whereRaw("iduser = ? and tgljam = ?", array($userid, FoorenzyTimeFormat::FzyDateVersion(2)))->get();
		$byk = sizeof($khu);
		return $byk;
	}
	



	



	private function doSomethingForPenggantianHpUser($khu){
		$user = Auth::user();
		if($khu->isForPesanan()){
			$pesanan = Pesanan::whereRaw("id=?",array($khu->idpesanan))->first();
			$user->setHpDefault($pesanan->hp);

			if($pesanan->kirimkanSecaraFisikKeResto()){
				$pesanan->setPesanan_SiapDikirim();
				return Redirect::to("/pesan/selesai");
			}else{
				return HSFoorenzy::showHalamanError("Gagal untuk mengirim pesanan anda.");
			}

		}else{
			$user->setHpDefault($khu->hpuserdariprofil);
			return Redirect::to("/ubahprofil");
		}
	}




	
	/*-----------------------------------------------------unknown----------------------------------*/



	/*public function lihatPesanDariServer(){
		$a = StatusDariFoorenzy::IdDescending()->take(20)->get();
		$user = Auth::user();
		$data = array(
			"res" => $a,
			"lastBaca" => $user->getLastBacaStatusDF(),
		);

		return View::make("akun.pesandariserver", $data);
	}*/
	/*public function UbahStatusDariFoorenzy(){
		$user = Auth::user();
		$user->bykstatdf = $user->getstatdfdariserver();
		$thestime = date("Y-m-d");
		$strWaktu1 = date("Y-m-d",strtotime("1 days",strtotime($thestime)));
		$user->lastbacastatusdf = $strWaktu1;
		$user->save();

		return $strWaktu1;
	}
	public function PesanServerDataKe(){
		$dataPerPage = Input::get("dpp");
		$pageSekarang = Input::get("ps");
		$mulai = ($pageSekarang - 1) * $dataPerPage;
		$res = StatusDariFoorenzy::IdDescending()
			->take($dataPerPage)
			->skip($mulai)
			->get();

		return json_encode($res);
	}*/
	
	

	

	

	
	
	

	

	
}
