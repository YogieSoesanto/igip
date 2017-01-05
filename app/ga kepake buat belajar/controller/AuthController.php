<?php

class AuthController extends BaseController {
	

	private $tujuanKemana;


	const STR_SUBJECT_EMAIL_REGISTER_LINKVERIF = "Konfirmasi Email Foorenzy";
	const STR_SUBJECT_EMAIL_KONFIRMASI_LUPAKATASANDI = "Konfirmasi Lupa Kata Sandi Foorenzy";
	const STR_SUBJECT_EMAIL_KATASANDI_BERHASIL_DIRESET = "Pengubahan Password Foorenzy";
	const STRERROR_SYARAT_DAN_KETENTUAN = "Anda wajib menyetujui Syarat dan Ketentuan Foorenzy";
	const STRERROR_PASSWORD_TIDAK_VALID = "Panjang Kata Sandi yang dimasukan [6-20 Karakter] dan harus Sama dengan Konfirmasi Kata Sandi";
	const STRERROR_FORMAT_EMAIL_TIDAK_SESUAI_ATAU_SUDAH_ADA = "Email anda sudah terdafar atau Format email tidak sesuai";
	const STRERROR_EMAIL_KEPANJANGAN = "Email maksimal 50 karakter";
	const STRERROR_FORMAT_NAMA_ERROR = "Nama minimal 3 karakter dan maksimal 50 karakter";
	const STRERROR_FIELDS_EMPTY = "Semua data wajib diisi";
	const STR_NEED_DATA_FROM_FACEBOOK = "Kami membutuhkan Data (Email dan Nama) Anda yang tercatat pada Facebook sebagai Data Login Anda";
	const STR_FORGOTPASS_BOLEH_DIJALANKAN = "STR_FBD";
	const STR_NO_USER_AKTIF_UNTUK_FORGOTPASS = "STR_NUAUF";
	const STRERROR_NO_USER_AKTIF = "Tidak ditemukan <b>User Aktif</b> dengan email tersebut";
	const STRERROR_DATA_LAMA_DIINPUT_TIDAK_SESUAI_DENGAN_DATA_AKUN = "Masukan Email dan Password untuk Akun Anda secara benar";
	
	const STR_DONE_CHANGEPASS = "Kata Sandi Anda telah berubah";
	const STR_JUDUL_EMAIL_REGISTER_LINKVERIF = "Daftar Baru";
	const STR_JUDUL_EMAIL_KONFIRMASI_LUPAKATASANDI = "Lupa Kata Sandi";
	const STR_JUDUL_EMAIL_KATASANDI_BERHASIL_DIRESET = "Penggantian Kata Sandi";

	const STR_LINK_VERIF_OR_FORGOT_PASS_GAGAL = "Maaf, link ini tidak valid atau sudah kadaluarsa";
	
	

	public function showLogin(){
		return View::make("auth.halamanlogin2");
	}

	public function showRegister(){
		return View::make("auth.halamanregis2");
	}

	public function doLogout(){
		Auth::logout(); 
		return $this->intendedPage();
	}	
	
	public function doLogin(){
		$data = Input::only('email', 'password');
		if (Auth::attempt($data, Input::has('remember'))){
			return $this->cekIsUserNeedEmailValidatedBefore_ReallyLoggedIn();
		}
		else{
			return Redirect::to('/login')
					->withInput(Input::only('email', 'remember'))
					->withErrors([ 'login' => 'Email dan Kata Sandi salah.' ]);
		}
	}

	public function doRegister(){
		$data = Input::only('email', 'password' , 'nama', 'konfirmasi');
		$pesanError = $this->validasiDataKosong($data);
		if($pesanError !== self::STRERROR_FIELDS_EMPTY){
			$pesanError = $this->validasiDataNama($data['nama']);
			if($pesanError !== self::STRERROR_FORMAT_NAMA_ERROR){
				$pesanError = $this->validasiDataEmailLength($data['email']);
				if($pesanError !== self::STRERROR_EMAIL_KEPANJANGAN){
					$pesanError = $this->validasiEmailIsRightFormat($data);
					if($pesanError !== self::STRERROR_FORMAT_EMAIL_TIDAK_SESUAI_ATAU_SUDAH_ADA){
						$pesanError = $this->validasiPassword($data['password'], $data['konfirmasi']);
						if($pesanError !== self::STRERROR_PASSWORD_TIDAK_VALID){
							$pesanError = $this->validasiCentangSyaratDanKetentuan(Input::has("setuju"));
							if($pesanError !== self::STRERROR_SYARAT_DAN_KETENTUAN){
								$this->daftarkanUserBerikut($data);
	    						return $this->emailNeedValidated();
							}
						}
					}
				}
			}
		}
		return Redirect::to('/register')
		->withInput(Input::only('email', 'nama', 'setuju' ))
		->withErrors([
			'register' =>  $pesanError
		]);
	}

	public function doConfirmLinkVerifForRegister($iduser, $linkVerif){
		if(LinkVerif::checkMasihBerlaku_Lalu_Delete($iduser, $linkVerif)){
			$user = User::whereRaw('id=?',array($iduser))->first();
			$user->gabungFromLinkVerifRegister();
			Auth::login($user);
			return $this->intendedPage();
		}else{
			return HSFoorenzy::showHalamanError(self::STR_LINK_VERIF_OR_FORGOT_PASS_GAGAL);
		}
	}

/*
	public function doLoginWithFacebook(){
		Session::forget("tujuanKemana");
		$fb = new fb();
		return $fb->redirectTo_LoginHelperURL();
	}
*/
	public function doLoginWithFacebook_WithIntended(){
		Session::put("tujuanKemana", "/home");
		$fb = new fb();
		return $fb->redirectTo_LoginHelperURL();
	}

	public function manageFacebookCallback(){
		$fb = new fb();

		$this->set_HalamanTujuanKemana();

		$data = $fb->getDataUserYangDibutuhkan_DariFacebook();

		if(!isset($data['email']) || $data['email'] == "")
			return self::STR_NEED_DATA_FROM_FACEBOOK;
		else
			return $this->registerOrLoginUserUsingFB($data);
	}

	public function showForgotPassword(){
		return View::make('auth.forgotpass2');
	}

	public function doRequestForgotPassword(){
		$email = Input::get('email');
		$returnType = $this->validasiEmail_SebelumForgotPassword($email);
		if($returnType == self::STR_FORGOTPASS_BOLEH_DIJALANKAN){
			return $this->runForgotPassword_System($email);
		}else if($returnType == self::STR_NO_USER_AKTIF_UNTUK_FORGOTPASS){
			return Redirect::to('/forgot_pass')
				->withInput(Input::only('email'))
				->withErrors([ 'error' =>  self::STRERROR_NO_USER_AKTIF ]);
		}	
	}

	public function doConfirmLinkVerifForForgotPass($iduser, $forgotPassword){
		if(ForgotPass::checkMasihBerlaku_Lalu_Delete($iduser, $forgotPassword)){

			$user = User::whereRaw('id=?',array($iduser))->first();

			$newPassword = FoorenzyRandomText::generateTextRandom_BerapaHuruf($iduser, rand(5,8));

			$user->changePasswordTo($newPassword);

			$this->kirimEmail_PasswordBaru($user, $newPassword);

			return Redirect::to("/hasil/needCekEmail/".self::STR_SUBJECT_EMAIL_KATASANDI_BERHASIL_DIRESET);
		}else{
			return HSFoorenzy::showHalamanError(self::STR_LINK_VERIF_OR_FORGOT_PASS_GAGAL);
		}
	}

	public function showUbahKataSandi(){
		return View::make("auth.gantipassword");
	}

	public function doUbahKataSandi(){
		$data = Input::only('passlama', 'passbaru', 'konfirmpassword', 'email');

		$pesanError = $this->checkEmailAndPassword_ForUbahKataSandi($data['email'], $data['passlama']);
		if($pesanError !== self::STRERROR_DATA_LAMA_DIINPUT_TIDAK_SESUAI_DENGAN_DATA_AKUN){
			$pesanError = $this->validasiPassword($data['passbaru'], $data['konfirmpassword']);
			if($pesanError !== self::STRERROR_PASSWORD_TIDAK_VALID){
				Auth::user()->changePasswordTo($data['passbaru']);
				return $this->removeHalamanTujuanChangePassIfExists_And_RedirectToPasswordChanged();
			}
		}
		return Redirect::to("/ubahkatasandi")
			->withInput(Input::only('email'))
			->withErrors([ 'error' =>  $pesanError ]);
	}

	public function showHalaman_DoneChangePass(){
		return HSFoorenzy::showHalamanHasil(self::STR_DONE_CHANGEPASS);
	}

	public function showHalaman_NeedCheckEmail($subject){
		if($subject == self::STR_SUBJECT_EMAIL_KONFIRMASI_LUPAKATASANDI){
			$judul = self::STR_JUDUL_EMAIL_KONFIRMASI_LUPAKATASANDI;
			$subjectbaru = "Silakan membuka email Anda yang memiliki Subject:";
			$subjectbaru .= "<h3> '".$subject."' </h3>";
			$subjectbaru .= "Dan ikuti petunjuk yang tertera dalam pesan.";
		}
		else if($subject == self::STR_SUBJECT_EMAIL_KATASANDI_BERHASIL_DIRESET){
			$judul = self::STR_JUDUL_EMAIL_KATASANDI_BERHASIL_DIRESET;
			$subjectbaru = "Silakan membuka email Anda yang memiliki Subject:";
			$subjectbaru .= "<h3> '".$subject."' </h3>";
			$subjectbaru .= "Dan ikuti petunjuk yang tertera dalam pesan.";
		}
		else if($subject == self::STR_SUBJECT_EMAIL_REGISTER_LINKVERIF){
			$judul = self::STR_JUDUL_EMAIL_REGISTER_LINKVERIF;
			$subjectbaru = "Silakan membuka email Anda yang memiliki Subject:";
			$subjectbaru .= "<h3> '".$subject."' </h3>";
			$subjectbaru .= "Dan ikuti petunjuk yang tertera dalam pesan.";
		}
		return HSFoorenzy::showHalamanHasil($subjectbaru, $judul);
	}


	/*------------------------------------------------private---------------------------------------------------*/
	private function intendedPage(){
		if(Session::has("tujuanKemana"))
			return Redirect::to("".Session::get("tujuanKemana"));
		return Redirect::intended('/home');
	}



	private function cekIsUserNeedEmailValidatedBefore_ReallyLoggedIn(){
		$user = Auth::user();
		if($user->getStatusEmailValidated() == User::INT_EMAIL_BELUM_DIVALIDASI){
			return $this->emailNeedValidated();
		}else{
			return $this->intendedPage();
		}
	}
	private function emailNeedValidated(){
		if(Auth::check()){
			$user = Auth::user();
			if(LinkVerif::isObjectAdaBerdasarkan("idusers", $user->id) === FALSE){
				$this->buatDanKirim_RegisterLinkVerif($user->id, $user->email, $user->nama);
			}
			Auth::logout();
		}
		return Redirect::to("/hasil/needCekEmail/".self::STR_SUBJECT_EMAIL_REGISTER_LINKVERIF);
	}
	private function buatDanKirim_RegisterLinkVerif($iduser, $email, $nama){
		$linkVerif = FoorenzyRandomText::generateTextRandomizeForURL($iduser);
	    $this->simpanDBLinkVerif($iduser, $linkVerif);
	    $dataArrayEmail = array(
	    	"nama" => $nama,
	    	"linkVerif" => $iduser . "/" . $linkVerif,
	    );
	    $subject = self::STR_SUBJECT_EMAIL_REGISTER_LINKVERIF;
	    $emailFzy = new EmailFzy;
	    $emailFzy->kirim("emails.LinkVerif", $dataArrayEmail, $email, $nama, $subject);
	}
	
	private function simpanDBLinkVerif($iduser, $linkVerif){
		$lv = new LinkVerif;
		$lv->add($iduser, $linkVerif);
	}
	


	private function validasiDataKosong($data){
		if(strlen($data['email']) == 0 || strlen($data['password']) == 0 || strlen($data['nama']) == 0 )
			return self::STRERROR_FIELDS_EMPTY;
		return true;
	}
	private function validasiDataNama($dataNama){
		if(strlen($dataNama) < 3 || strlen($dataNama) > 50)
			return self::STRERROR_FORMAT_NAMA_ERROR;
		return true;
	}
	private function validasiDataEmailLength($dataEmail){
		if(strlen($dataEmail) > 50)
			return self::STRERROR_EMAIL_KEPANJANGAN;
		return true;
	}
	private function validasiEmailIsRightFormat($data){
		$validator = Validator::make($data , ['email' => 'email|unique:users']);
		if($validator->fails())
			return self::STRERROR_FORMAT_EMAIL_TIDAK_SESUAI_ATAU_SUDAH_ADA;
		return true;
	}
	private function validasiPassword($pass, $konfirmasi){
		if($this->isLengthPasswordFormatValid($pass))
			return $this->isKonfirmPasswordAndPasswordMatch($pass, $konfirmasi);
		
		return self::STRERROR_PASSWORD_TIDAK_VALID;
	}
	private function isLengthPasswordFormatValid($password){
		if(strlen($password) < 6 || strlen($password) > 20)
			return false;
		return true;
	}
	private function isKonfirmPasswordAndPasswordMatch($pass, $konf){
		return ($pass == $konf ? true : self::STRERROR_PASSWORD_TIDAK_VALID);
	}
	private function validasiCentangSyaratDanKetentuan($isCentangSyaratKetentuan){
		return ($isCentangSyaratKetentuan == true ? true : self::STRERROR_SYARAT_DAN_KETENTUAN);
	}
	private function daftarkanUserBerikut($data){
		$user = new User;
		$user->addDariRegister($data['nama'], $data['email'], $data['password']);
		$this->buatDanKirim_RegisterLinkVerif($user->id, $user->email, $user->nama);
	}

	

	private function set_HalamanTujuanKemana(){
		if(Session::has("tujuanKemana")){
			$this->tujuanKemana = Session::get("tujuanKemana");
		}
	}
	private function registerOrLoginUserUsingFB($data){
	    $testUser = User::whereRaw('email = ?', array($data['email']))->first();
	    if($testUser == null){
	    	$user = new User;
	    	$user->addDariFacebook( $data['nama'], $data['email'] );
	    	Auth::login($user);
	    }else{
	    	Auth::login($testUser);
	    }
	    return $this->intendedPage();
	}



	private function validasiEmail_SebelumForgotPassword($email){
		if(strlen($email) >= 5){
			$user = User::whereRaw("email = ?", array($email))->first();
			if($user != NULL){
				if($user->getStatusEmailValidated() != User::INT_EMAIL_BELUM_DIVALIDASI){
					return self::STR_FORGOTPASS_BOLEH_DIJALANKAN;
				}
			}
		}
		return self::STR_NO_USER_AKTIF_UNTUK_FORGOTPASS;
	}
	private function runForgotPassword_System($email){
		$user = User::whereRaw("email = ?", array($email))->first();
		$iduser = $user->id;

		$linkForgotPassword = FoorenzyRandomText::generateTextRandomizeForURL($iduser);
		
		ForgotPass::insertBaru_OR_UpdateData($iduser, $linkForgotPassword);

		$this->kirimEmailValidasiForgotPassword_KeUser($user, $linkForgotPassword);

		return Redirect::to("/hasil/needCekEmail/".self::STR_SUBJECT_EMAIL_KONFIRMASI_LUPAKATASANDI);
	}
	private function kirimEmailValidasiForgotPassword_KeUser($user, $linkForgotPassword){
		$email = $user->email;
		$nama = $user->nama;
		$dataArrayEmail =  array(
			'nama'=>$nama, 
			'linkForgotPassword' => $user->id . "/" . $linkForgotPassword
		);
		$subject = self::STR_SUBJECT_EMAIL_KONFIRMASI_LUPAKATASANDI;
		$emailFzy = new EmailFzy;
	    $emailFzy->kirim("emails.ForgotPassword", $dataArrayEmail, $email, $nama, $subject);
	}



	private function kirimEmail_PasswordBaru($user, $newPassword){
		$email = $user->email;
		$nama = $user->nama;
		$dataArrayEmail =  array(
			'nama'=>$user->nama, 
			'passwordBaru' => $newPassword
		);
		$subject = self::STR_SUBJECT_EMAIL_KATASANDI_BERHASIL_DIRESET;
		$emailFzy = new EmailFzy;
	    $emailFzy->kirim("emails.resetedPassword", $dataArrayEmail, $email, $nama, $subject);
	}



	private function checkEmailAndPassword_ForUbahKataSandi($emailYangDiInput, $passwordLamaDiinput){
		$user = Auth::user();
		if($emailYangDiInput == $user->email){
			if(Hash::check($passwordLamaDiinput, $user->password)){
				return true;
        	}
        }
        return self::STRERROR_DATA_LAMA_DIINPUT_TIDAK_SESUAI_DENGAN_DATA_AKUN;
	}
	private function removeHalamanTujuanChangePassIfExists_And_RedirectToPasswordChanged(){
		if(Session::has("tujuanKemana"))
			if(Session::get("tujuanKemana") == "/changePass")
				Session::forget("tujuanKemana");

		return Redirect::to("/hasil/changePass");
	}


	/*------------------------------------------------unknown---------------------------------------------------*/

	
}
