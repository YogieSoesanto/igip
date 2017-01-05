<?php
class BuatPesananController extends BaseController {
	public function gantiLikeReviewFB(){
		$angkaReturnOKE = 0;
		$bloggeroutletid = Input::get("idBO");
		$outletid = Session::get("lagiBukaIdOutlet");
		$jbo = JempolBloggerOutlet::whereRaw("idoutlet = ? and iduser = ?", array($outletid, Auth::id()))->first();
		if($jbo == NULL){
			$jbo = new JempolBloggerOutlet;
			$jbo->idbloggeroutlet = $bloggeroutletid;
			$jbo->iduser = Auth::id();
			$jbo->idoutlet = $outletid;
			$jbo->save();
			
			$bo = BloggerOutlet::whereRaw("id=?", array($bloggeroutletid))->first();
			$bo->jumlahlike += 1;
			$bo->save();
			$angkaReturnOKE = 1;

			DB::update("UPDATE foodbloggers set jumlahlike = (SELECT SUM(jumlahlike) FROM bloggeroutlet where idfoodblogger = ?) WHERE id = ?", array($bo->idfoodblogger, $bo->idfoodblogger));

		}else{
			if($jbo->idbloggeroutlet == $bloggeroutletid){
				$jbo->delete();
				
				$bo = BloggerOutlet::whereRaw("id=?", array($bloggeroutletid))->first();
				$bo->jumlahlike -= 1;
				$bo->save();

				DB::update("UPDATE foodbloggers set jumlahlike = (SELECT SUM(jumlahlike) FROM bloggeroutlet where idfoodblogger = ?) WHERE id = ?", array($bo->idfoodblogger, $bo->idfoodblogger));

			}else{

				//minus-in yang lama
				$bo = BloggerOutlet::whereRaw("id=?", array($jbo->idbloggeroutlet))->first();
				$bo->jumlahlike -= 1;
				$bo->save();

				DB::update("UPDATE foodbloggers set jumlahlike = (SELECT SUM(jumlahlike) FROM bloggeroutlet where idfoodblogger = ?) WHERE id = ?", array($bo->idfoodblogger, $bo->idfoodblogger));

				//tambah yang baru
				$bo = BloggerOutlet::whereRaw("id=?", array($bloggeroutletid))->first();
				$bo->jumlahlike += 1;
				$bo->save();

				DB::update("UPDATE foodbloggers set jumlahlike = (SELECT SUM(jumlahlike) FROM bloggeroutlet where idfoodblogger = ?) WHERE id = ?", array($bo->idfoodblogger, $bo->idfoodblogger));

				$jbo->idbloggeroutlet = $bloggeroutletid;
				$jbo->save();
				$angkaReturnOKE = 2;
			}
		}
		return "SUKSES#".$angkaReturnOKE;
	}
	public function getUlasan(){
		$dataPerPage = Input::get("dpp");
		$pageSekarang = Input::get("ps");

		$idOutletTest = Session::get("lagiBukaIdOutlet");
		$nilaiRating = $this->KasihDanAmbilNilaiOutletIni(1, $idOutletTest);

		//$por = PemesanOutletRating::whereRaw("idoutlet = ?", array($idOutletTest))->skip(10)->take(5);
		$mulai = ($pageSekarang - 1) * $dataPerPage;
		$por = DB::table("pemesanoutletrating")->where("idoutlet","=",$idOutletTest)->skip($mulai)->take($dataPerPage)->get();
		$byk = sizeof($por);

		$arrKasihKeDepan = array();
		for($i=0 ; $i<$byk;$i++){
			$arrTemp = array();
			//$por[$i]->idpemesan;
			$user = User::find($por[$i]->idpemesan);
			$idpemesan =  $user->id;
			$linkprofpic = $user->linkprofpic;

			$arrTemp['nama'] = $user->nama;
			$arrTemp['id'] = $idpemesan;
			$arrTemp['gbr'] = $user->getLinkProfPic();

			$arrTemp['rat'] = $por[$i]->rating;
			$arrTemp['com'] = $por[$i]->comment;
			$arrTemp['tgl'] = $por[$i]->tgl;
			array_push($arrKasihKeDepan, $arrTemp);
		}
		
		$arrKasihAjax = array("akkd" => $arrKasihKeDepan , "nr" => $nilaiRating);
		return json_encode($arrKasihAjax);
	}

	public function getUlasanFB(){
		$dataPerPage = Input::get("dpp");
		$pageSekarang = Input::get("ps");

		$idOutletTest = Session::get("lagiBukaIdOutlet");
		$nilaiRating = $this->KasihDanAmbilNilaiOutletIni(2, $idOutletTest);

		//$por = PemesanOutletRating::whereRaw("idoutlet = ?", array($idOutletTest))->skip(10)->take(5);
		$mulai = ($pageSekarang - 1) * $dataPerPage;
		$por = DB::table("bloggeroutlet")->where("idoutlet","=",$idOutletTest)->orderBy('jumlahlike', 'desc')->orderBy('id','asc')->skip($mulai)->take($dataPerPage)->get();
		$byk = sizeof($por);

		$arrKasihKeDepan = array();
		for($i=0 ; $i<$byk;$i++){
			$arrTemp = array();

			$user = User::find($por[$i]->idfoodblogger);
			$idpemesan =  $user->id;
			$linkprofpic = $user->linkprofpic;

			$arrTemp['nama'] = $user->nama;
			$arrTemp['id'] = $idpemesan;
			$arrTemp['gbr'] = ($linkprofpic == "" ? asset("FS/asset/default_user.png") : asset("foto/user/") . "/" . $linkprofpic);

			$arrTemp['rat'] = $por[$i]->ratingffb;
			$arrTemp['link'] = $por[$i]->linkreview;
			$arrTemp['tgl'] = $por[$i]->tgl;
			$arrTemp['like'] = $por[$i]->jumlahlike;
			$arrTemp['idbo'] = $por[$i]->id;
			$arrTemp['isjbo'] = 0;

			if(Auth::check()){
				$jbo = JempolBloggerOutlet::whereRaw("idbloggeroutlet = ? and iduser = ?", array($por[$i]->id, Auth::id()))->first();
				if($jbo != NULL){
					//apakah user ini like review ini
					$arrTemp['isjbo'] = 1;
				}
			}

			array_push($arrKasihKeDepan, $arrTemp);
		}
		
		$arrKasihAjax = array("akkd" => $arrKasihKeDepan , "nr" => $nilaiRating);
		return json_encode($arrKasihAjax);
	}
	public function gagalKonfirmasi($idKHU){
		$khu = KonfHpUser::whereRaw("id = ?" , array($idKHU))->first();
		if($khu != NULL){
			if($khu->idpesanan == "-1"){
				$arrNya = array(
					"hp" => $khu->hpuserdariprofil,
					"idKHU" => $idKHU,
				);
				return View::make("pesan.konfhpusergagal", $arrNya);
			}
			else{
				$pesanan = Pesanan::whereRaw("id=?",array($khu->idpesanan))->first();
				if($pesanan != NULL){
					$arrNya = array(
						"hp" => $pesanan->hp,
						"idKHU" => $idKHU,
					);
					return View::make("pesan.konfhpusergagal", $arrNya);
				}
			}
		}
		return "Data Nomor Konfirmasi tidak ditemukan.";
	}
	public function konfirmasiPesanan($idKHU){

		$khu = KonfHpUser::whereRaw("id = ?" , array($idKHU))->first();
		if($khu == NULL){
			return "LINK TIDAK VALID";
		}
		else{
			if($khu->iduser != Auth::id()){
				return "Pesanan dengan berikut bukan milik anda";
			}else{

				if($khu->idpesanan == "-1"){
					$arrNya = array(
						"hp" => $khu->hpuserdariprofil,
						"idKHU" => $idKHU,
						"nyawasisa" => (3 - Session::get("message"))
					);
					return View::make("pesan.konfhpuser", $arrNya);
				}else{
					$pesanan = Pesanan::whereRaw("id=?",array($khu->idpesanan))->first();
					if($pesanan == NULL){
						return "PESANAN TIDAK ADA";
					}else{
						$arrNya = array(
							"hp" => $pesanan->hp,
							"idKHU" => $idKHU,
							"nyawasisa" => (3 - Session::get("message"))
						);
						return View::make("pesan.konfhpuser", $arrNya);
					}
				}
			}
		}
	}
	public function konfirmasiPesanan2($idPesanan){
		$pesanan = Pesanan::whereRaw("id = ?" , array($idPesanan))->first();
		if($pesanan == NULL){
			return "ERROR ID PESANAN SALAH.";
		}
		else{
			if($pesanan->idpemesan != Auth::id()){
				return "Pesanan dengan ID Berikut bukan milik anda." . $pesanan->idpemesan . "#" . Auth::id();
			}else{
				$arrNya = array(
					"hp" => $pesanan->hp,
					"idpesanan" => $idPesanan
				);
				return View::make("halaman.pesan.confcodedikirim", $arrNya);
			}
		}
	}
	public function confirmKonfirmasiPesanan(){
		$idKHU = Input::get("idkhu");
		$khu = KonfHpUser::whereRaw("id = ?" , array($idKHU))->first();
		if($khu == NULL){
			return "Tidak ada Nomor Konfirmasi Berikut atau Sudah kadaluarsa";
		}
		else{
			if($khu->iduser != Auth::id()){
				return "Nomor Konfirmasi ini bukan milik anda";
			}else{
				$konfAsli = $khu->kodekonfnya;
				$konfDimasukan = Input::get("konfirmasi");

				if($khu->statusgagal == "1"){
					return "Anda telah gagal memasukan kode pada Nomor Konfirmasi berikut";
				}
				else if($konfAsli == $konfDimasukan){
					if($khu->idpesanan == "-1"){
						Auth::user()->hpdefault = $khu->hpuserdariprofil;
						Auth::user()->save();

						return Redirect::to("/ubahprofil");
					}else{
						$pesanan = Pesanan::whereRaw("id=?",array($khu->idpesanan))->first();
						$pesanan->statuspesanan = 5;
						$pesanan->tgl = date("Y-m-d H:i:s");
						$pesanan->save();

						$urn = new UserReadNotif;
						$urn->cekInsertBaru(Auth::id(), $pesanan->id);

						Auth::user()->hpdefault = $pesanan->hp;
						Auth::user()->save();

						return $this->kirimKeHpResto($pesanan->id, $pesanan->idoutlet);
					}
					
				}else{
					$khu->statusgagal = 1;
					$khu->save();

					return Redirect::to("/khu/gagalkonf/".$idKHU);
				}
			}
		}
		$pesanan = Pesanan::whereRaw("id = ?" , array($idPesanan))->first();
		
	}
	public function confirmKonfirmasiPesanan2(){
		$idPesanan = Input::get("idpesanan");
		$pesanan = Pesanan::whereRaw("id = ?" , array($idPesanan))->first();
		if($pesanan == NULL){
			return "ERROR ID PESANAN SALAH.";
		}
		else{
			if($pesanan->idpemesan != Auth::id()){
				return "Pesanan dengan ID Berikut bukan milik anda." . $pesanan->idpemesan . "#" . Auth::id();
			}else{
				$konfAsli = $pesanan->codeconf;
				$konfDimasukan = Input::get("konfirmasi");

				if($pesanan->statuspesanan >= 5){
					return "ERROR NIH, PESANAN UDAH DIKIRIM";
				}
				else if($konfAsli == $konfDimasukan){
					$pesanan->statuspesanan = 5;
					$pesanan->tgl = date("Y-m-d H:i:s");
					$pesanan->save();

					$urn = new UserReadNotif;
					$urn->cekInsertBaru(Auth::id(), $pesanan->id);

					Auth::user()->hpdefault = $pesanan->hp;
					Auth::user()->save();

					return $this->kirimKeHpResto($idPesanan, $pesanan->idoutlet);

					
				}else{
					$nilaiBaru = ($pesanan->statuspesanan == "" ? "0" : $pesanan->statuspesanan);

					$nilaiBaru = intval($nilaiBaru);
					if($nilaiBaru < 3){
						$nilaiBaru += 1;
						$pesanan->statuspesanan = $nilaiBaru;
						$pesanan->tgl = date("Y-m-d H:i:s");
						$pesanan->save();
						return Redirect::to("/kp/".$idPesanan)
						->withInput(Input::only('konfirmasi'))
						->withErrors([
							'error' =>  "Kode Konfirmasi Pesanan anda salah, Anda hanya memiliki " . (4 - $nilaiBaru) . " Kesempatan lagi untuk melanjutkan pesanan berikut."
						]);
					}else{
						return Redirect::to("/kp/".$idPesanan)
						->withInput(Input::only('konfirmasi'))
						->withErrors([
							'error' =>  "Kode Konfirmasi Pesanan anda salah, Pesanan anda akan dibatalkan"
						]);
					}
					
				}
			}
		}
		$pesanan = Pesanan::whereRaw("id = ?" , array($idPesanan))->first();
		
	}
	public function pesananSelesai(){
		if(Session::has("dataKasih")){
			$dataKasih = Session::get("dataKasih");
			return View::make("pesan.pesananselesai", $dataKasih);
		}else{
			return "Halaman ini sudah tidak dapat diakses.";
		}
	}
	public function pesananDikirim(){
		return View::make("halaman.pesan.pesananterkirim");
		//return "asd";
	}
	public function buatPesanan2(){
		$np = Input::get("np");	//nama pemesan
		$hp = Input::get("hp");	//hp 
		$ap = Input::get("ap");	//alamat pemesan
		$pk = Input::get("pk");	//pesanan khusus
		$pb = Input::get("pb");	//pakai berapa

		$pb = preg_replace( '/[^0-9]/', '', $pb);

		if(strlen($np) > 10){

		}else if(strlen($hp) > 20){

		}else if(strlen($ap) > 150){

		}else if(strlen($pk) > 150){

		}else{
			$idUser = Auth::id();
			$idOutlet = Session::get("lagiBukaIdOutlet");
			$cart = DB::select("SELECT a.id, idmenu, quantity, harga FROM keranjang a inner join menu_b b on a.idmenu = b.id WHERE iduser=? and idoutlet=?", array($idUser, $idOutlet));
			$byk = sizeof($cart);
			if($byk == 0){

			}else{
				$outlet = Outlet::whereRaw("id=?",array($idOutlet))->first();
				$testUser = Auth::user();
				$levelUser = intval($testUser->getLevel());

				$terakhirPesan = $testUser->tglpesanterakhir;
				$waktusekarangnih = date("Y-m-d");

				if($waktusekarangnih != $terakhirPesan){
					$testUser->tglpesanterakhir = $waktusekarangnih;
					$testUser->freqpesanhariini = 0;
					$testUser->save();
				}

				$maksimalPesananKaliLevel = 8;
				if($testUser->freqpesanhariini < ($levelUser * $maksimalPesananKaliLevel)){
					$testUser->freqpesanhariini = $testUser->freqpesanhariini + 1;
					$testUser->save();

					$pesanan = new Pesanan;
					$pesanan->idpemesan = $idUser;
					$pesanan->idoutlet = $idOutlet;
					$pesanan->categoryoutlet = $outlet->category;
					$pesanan->ap = $ap;
					$pesanan->hp = $hp;
					$pesanan->pk = $pk;

					if(strlen($pb)>0){
						$pb = intval($pb);
						$pesanan->pakeuang = $pb;
					}

					$perluKirimConfCodePesananKeUser = false;
					if($hp == $testUser->hpdefault) {
						$pesanan->statuspesanan = 5;
					}else{
						$perluKirimConfCodePesananKeUser = true;
					}

					$pesanan->save();

					//$urn = new UserReadNotif;
					//$urn->cekInsertBaru(Auth::id(), $pesanan->id);

					$total = 0;
					for($i=0;$i<$byk;$i++){

						$dpesanan = new DPesanan;
						$dpesanan->idpesanan = $pesanan->id;
						$dpesanan->qty = $cart[$i]->quantity;
						$dpesanan->idm = $cart[$i]->idmenu;
						$dpesanan->save();

						$hargaSatuan = $cart[$i]->harga;
						$idKeranjang = $cart[$i]->id;
						$detailCart = DB::select("select a.idd, additionalharga from detailkeranjang a inner join menu_d b on a.idd = b.id where idkeranjang = ?" , array($idKeranjang));
						$bykDCart = sizeof($detailCart);
						for($x=0;$x<$bykDCart;$x++){

							$hargaSatuan += intval($detailCart[$x]->additionalharga);

							$DDPesanan = new DDPesanan;
							$DDPesanan->iddetailpesanan = $dpesanan->id;
							$DDPesanan->idd = $detailCart[$x]->idd;
							$DDPesanan->addharga = $detailCart[$x]->additionalharga;
							$DDPesanan->save();
						}
						$dpesanan->hargasatuan = $hargaSatuan;
						$dpesanan->save();

						$total += intval($cart[$i]->quantity) * intval($hargaSatuan);
					}
					$pesanan->harusdibayar = $total;
					$pesanan->tgl = date("Y-m-d H:i:s");
					$pesanan->save();	

					if($testUser->namaSingkat != $np){
						$testUser->namasingkat = $np;
						$testUser->save();
					}

					return $this->selesaiPemesanan($pesanan->id, $idUser, $idOutlet, $perluKirimConfCodePesananKeUser);

				}else{
					return "Anda sudah mencapai Batas Pesanan per Hari , Yaitu : Level User * " . $maksimalPesananKaliLevel;
				}

				
			}
		}
		return "GAGAL";
	}
	public function buatPesanan(){
		$np = Input::get("np");	//nama pemesan
		$hp = Input::get("hp");	//hp 
		$ap = Input::get("ap");	//alamat pemesan
		$pk = Input::get("pk");	//pesanan khusus
		$pb = Input::get("pb");	//pakai berapa

		$pb = preg_replace( '/[^0-9]/', '', $pb);

		if(strlen($np) > 10){

		}else if(strlen($hp) > 20){

		}else if(strlen($ap) > 150){

		}else if(strlen($pk) > 150){

		}else{
			$idUser = Auth::id();
			$idOutlet = Session::get("lagiBukaIdOutlet");
			$cart = DB::select("SELECT a.id, idmenu, quantity, harga FROM keranjang a inner join menu_b b on a.idmenu = b.id WHERE iduser=? and idoutlet=?", array($idUser, $idOutlet));
			$byk = sizeof($cart);
			if($byk == 0){

			}else{
				$outlet = Outlet::whereRaw("id=?",array($idOutlet))->first();
				$testUser = Auth::user();
				$levelUser = intval($testUser->getLevel());

				$terakhirPesan = $testUser->tglpesanterakhir;
				$waktusekarangnih = date("Y-m-d");

				if($waktusekarangnih != $terakhirPesan){
					$testUser->tglpesanterakhir = $waktusekarangnih;
					$testUser->freqpesanhariini = 0;
					$testUser->save();
				}

				$maksimalPesananKaliLevel = 8;
				if($testUser->freqpesanhariini < ($levelUser * $maksimalPesananKaliLevel)){
					$testUser->freqpesanhariini = $testUser->freqpesanhariini + 1;
					$testUser->save();

					$pesanan = new Pesanan;
					$pesanan->idpemesan = $idUser;
					$pesanan->idoutlet = $idOutlet;
					$pesanan->categoryoutlet = $outlet->category;
					$pesanan->ap = $ap;
					$pesanan->hp = $hp;
					$pesanan->pk = $pk;

					
					$perluKirimConfCodePesananKeUser = false;
					if($levelUser >= 2){
						$pesanan->statuspesanan = 5;
					}else{
						$perluKirimConfCodePesananKeUser = true;
					}

					if(strlen($pb)>0){
						$pb = intval($pb);
						$pesanan->pakeuang = $pb;
					}
					$pesanan->save();		//save dulu biar dapat id pesanan buat dipake di bawah


					//$urn = new UserReadNotif;
					//$urn->cekInsertBaru(Auth::id(), $pesanan->id);

					$total = 0;
					for($i=0;$i<$byk;$i++){

						$dpesanan = new DPesanan;
						$dpesanan->idpesanan = $pesanan->id;
						$dpesanan->qty = $cart[$i]->quantity;
						$dpesanan->idm = $cart[$i]->idmenu;
						$dpesanan->save();

						$hargaSatuan = $cart[$i]->harga;
						$idKeranjang = $cart[$i]->id;
						$detailCart = DB::select("select a.idd, additionalharga from detailkeranjang a inner join menu_d b on a.idd = b.id where idkeranjang = ?" , array($idKeranjang));
						$bykDCart = sizeof($detailCart);
						for($x=0;$x<$bykDCart;$x++){

							$hargaSatuan += intval($detailCart[$x]->additionalharga);

							$DDPesanan = new DDPesanan;
							$DDPesanan->iddetailpesanan = $dpesanan->id;
							$DDPesanan->idd = $detailCart[$x]->idd;
							$DDPesanan->addharga = $detailCart[$x]->additionalharga;
							$DDPesanan->save();
						}
						$dpesanan->hargasatuan = $hargaSatuan;
						$dpesanan->save();

						$total += intval($cart[$i]->quantity) * intval($hargaSatuan);
					}
					$pesanan->harusdibayar = $total;
					$pesanan->tgl = date("Y-m-d H:i:s");
					$pesanan->save();

					if($testUser->namaSingkat != $np){
						$testUser->namasingkat = $np;
						$testUser->save();
					}

					return $this->selesaiPemesanan($pesanan->id, $idUser, $idOutlet, $perluKirimConfCodePesananKeUser);
					
				}else{
					return "Anda sudah mencapai Batas Pesanan per Hari , Yaitu : Level User * " . $maksimalPesananKaliLevel;
				}

				
			}
		}
		return "GAGAL";
	}
	/*public function cekQty($idMenu){
		$idUser = Auth::id();
		$cart = Cart::whereRaw("idUser = ? and idMenu = ?", array($idUser, $idMenu))->first();
		if($cart == NULL){
			return "NO_CART";
		}else{
			return $cart->quantity;
		}
	}
	public function getAllCartLama(){
		$idOutlet = Session::get("lagiBukaIdOutlet");
		$idUser = Auth::id();
		$cart = Cart::whereRaw("idUser = ? and idOutlet = ?", array($idUser, $idOutlet))->get();
		$byk = sizeof($cart);
		$retArray = array();
		$temp = array();
		$temp2 = array();
		for($i=0;$i<$byk;$i++){
			array_push($temp, $cart[$i]->idMenu);
			array_push($temp2, $cart[$i]->quantity);
		}
		$retArray['idM'] = $temp;
		$retArray['qty'] = $temp2;

		if(Session::has("namaPemesanSingkat"))
			$retArray['nsp'] = Session::get("namaPemesanSingkat");
		else
		{
			$user = Auth::user();
			if($user->namasingkat == ""){
				$namaSingkat = substr($user->nama, 0, 10);
			}else{
				$namaSingkat = $user->namasingkat;
			}
			Session::put("namaPemesanSingkat", $namaSingkat);
			$retArray['nsp'] = $namaSingkat;
		}
		return json_encode($retArray);
	}*/
	/*public function addToCartLama(){
		$idMenu = Input::get("idMenu"); 
		$qtyNya = Input::get("qtyNya");
		$idOutlet = Session::get("lagiBukaIdOutlet");
		$idUser = Auth::id();
		$cart = Cart::whereRaw("idUser = ? and idMenu = ?", array($idUser, $idMenu))->first();
		if($cart == NULL){
			$cart = new Cart;
			$cart->idUser = $idUser;
			$cart->idMenu = $idMenu;
			$cart->idOutlet = $idOutlet;
			$cart->quantity = $qtyNya;
			$cart->save();
		}
		else
		{
			DB::update("UPDATE Cart set quantity = ? where idUser = ? and idMenu = ?", array($qtyNya, $idUser, $idMenu));
		}
		return "SUKSES";
	}*/
	public function getAllCart(){
		$idOutlet = Session::get("lagiBukaIdOutlet");
		$idUser = Auth::id();
		
		$cart = Keranjang::whereRaw("iduser = ? and idoutlet = ?", array($idUser, $idOutlet))->get();
		$byk = sizeof($cart);
		$retArray = array();
		$temp = array();
		$temp2 = array();
		$temp3 = array();
		$temp4 = array();
		$temp5 = array();
		for($i=0;$i<$byk;$i++){
			$hargaTambah = 0;
			$namaAdditional = "";

			$query = "SELECT b.additionalharga, b.nama 
			FROM detailkeranjang a  
			inner join menu_d b on b.id = a.idd 
			where idkeranjang = ?";

			$resLagi = DB::select($query, array($cart[$i]->id));
			$bykResLagi = sizeof($resLagi);
			for($j=0;$j<$bykResLagi;$j++){
				$hargaTambah += $resLagi[$j]->additionalharga;
				$namaAdditional .= $resLagi[$j]->nama;

				if($j<$bykResLagi - 1){
					$namaAdditional .= ", ";
				}
			}


			array_push($temp5, $cart[$i]->id);
			array_push($temp, $cart[$i]->idmenu);
			array_push($temp2, $cart[$i]->quantity);
			array_push($temp3, $hargaTambah);
			array_push($temp4, $namaAdditional);
		}
		$retArray['idM'] = $temp;
		$retArray['qty'] = $temp2;
		$retArray['tTam'] = $temp4;
		$retArray['hTam'] = $temp3;
		$retArray['idK'] = $temp5;

		if(Session::has("namaPemesanSingkat")){
			$retArray['nsp'] = Session::get("namaPemesanSingkat");
			$retArray['nshp'] = Session::get("hpPemesan");
			$retArray['nsap'] = Session::get("alamatPemesan");
		}
		else
		{
			$user = Auth::user();
			if($user->namasingkat == ""){
				$namaSingkat = substr($user->nama, 0, 10);
			}else{
				$namaSingkat = $user->namasingkat;
			}
			Session::put("namaPemesanSingkat", $namaSingkat);
			Session::put("hpPemesan", $user->hpdefault);
			Session::put("alamatPemesan", $user->alamatdefault);
			$retArray['nsp'] = $namaSingkat;
			$retArray['nshp'] = $user->hpdefault;
			$retArray['nsap'] = $user->alamatdefault;
		}
		return json_encode($retArray);
	}
	public function addToCart(){
		$idMenu = Input::get("idMenu"); 
		$qtyNya = Input::get("qtyNya");
		if($qtyNya < 0){

		}
		else if($qtyNya == 0){

		}
		else{
			$jsonNya = Input::get("jsonNya");
			$arrayDM = json_decode($jsonNya);

			$bykDM = sizeof($arrayDM);
			$textInArrayDM = "";
			for($i=0;$i<$bykDM;$i++){
				$textInArrayDM .= $arrayDM[$i];
				if($i<$bykDM-1){
					$textInArrayDM .= ",";
				}
			}
		

			$idOutlet = Session::get("lagiBukaIdOutlet");
			$idUser = Auth::id();

			$arrKer = Keranjang::whereRaw("iduser = ? and idmenu = ?", array($idUser, $idMenu))->get();
			$byk = sizeof($arrKer);
			if($byk == 0){
				$ker = new Keranjang;
				$ker->iduser = $idUser;
				$ker->idmenu = $idMenu;
				$ker->idoutlet = $idOutlet;
				$ker->quantity = $qtyNya;
				$ker->save();

				for($i=0;$i<$bykDM;$i++){
					$dKer = new DKeranjang;
					$dKer->idkeranjang = $ker->id;
					$dKer->idd = $arrayDM[$i];
					$dKer->save();
				}
			}else{

				$idDitemukan = -1;
				for($i=0;$i<$byk;$i++){
					$idKerDahAda = $arrKer[$i]->id;
					$arrDKer = DKeranjang::whereRaw("idkeranjang = ?" , array($idKerDahAda))->get();
					$bykDKer = sizeof($arrDKer);
					if($bykDKer == $bykDM){
						if($bykDKer == 0){
							$idDitemukan = $idKerDahAda;
							break;
						}
						$hasil = DB::Select("Select * from detailkeranjang WHERE idkeranjang = ? and idd in (".$textInArrayDM.")", array($idKerDahAda));
						if(sizeof($hasil) == $bykDKer){
							$idDitemukan = $idKerDahAda;
							break;
						}
					}
				}

				if($idDitemukan == -1){
					$ker = new Keranjang;
					$ker->iduser = $idUser;
					$ker->idmenu = $idMenu;
					$ker->idoutlet = $idOutlet;
					$ker->quantity = $qtyNya;
					$ker->save();

					for($i=0;$i<$bykDM;$i++){
						$dKer = new DKeranjang;
						$dKer->idkeranjang = $ker->id;
						$dKer->idd = $arrayDM[$i];
						$dKer->save();
					}
				}else{
					$ker = Keranjang::whereRaw("id=?",array($idDitemukan))->first();
					$ker->quantity = $qtyNya;
					$ker->save();
				}

			}

		}
		/*$idOutlet = Session::get("lagiBukaIdOutlet");
		$idUser = Auth::id();
		$cart = Cart::whereRaw("idUser = ? and idMenu = ?", array($idUser, $idMenu))->first();
		if($cart == NULL){
			$cart = new Cart;
			$cart->idUser = $idUser;
			$cart->idMenu = $idMenu;
			$cart->idOutlet = $idOutlet;
			$cart->quantity = $qtyNya;
			$cart->save();
		}
		else
		{
			DB::update("UPDATE Cart set quantity = ? where idUser = ? and idMenu = ?", array($qtyNya, $idUser, $idMenu));
		}*/
		return "SUKSES";
	}
	/*public function UpdateCartLama(){
		$jsonString = Input::get("jsonNya");
		$json = json_decode($jsonString);

		$idUser = Auth::id();
		$idOutlet = Session::get("lagiBukaIdOutlet");
		DB::delete("Delete FROM Cart WHERE idUser = ? and idOutlet = ?", array($idUser, $idOutlet));

		$id = $json->id;
		$qty = $json->qty;
		$byk = sizeof($id);
		for($i=0;$i<$byk;$i++){
			$cart = new Cart;
			$cart->idUser = $idUser;
			$cart->idMenu = $id[$i];
			$cart->idOutlet = $idOutlet;
			$cart->quantity = $qty[$i];
			$cart->save();
		}
		return "SUKSES";
	}*/
	public function cleanCart(){
		$idUser = Auth::id();
		$idOutlet = Session::get("lagiBukaIdOutlet");
		$resLagi = DB::select("select id from keranjang where iduser = ? and idoutlet = ?", array($idUser, $idOutlet));
		$bykDeh = sizeof($resLagi);
		for($i=0;$i<$bykDeh;$i++){
			DB::Delete("DELETE FROM detailkeranjang where idkeranjang = ?", array($resLagi[$i]->id));
		}
		DB::delete("DELETE from keranjang where iduser = ? and idoutlet = ?", array($idUser, $idOutlet));
		return "SUKSES";
	}
	public function UpdateCart(){
		$jsonString = Input::get("jsonNya");
		$json = json_decode($jsonString);

		$idUser = Auth::id();
		$idOutlet = Session::get("lagiBukaIdOutlet");

		/*$query = "SELECT "
		$res = DB::Select($query,array($idUser, $idOutlet));

		DB::delete("Delete FROM Keranjang WHERE idUser = ? and idOutlet = ?", array($idUser, $idOutlet));
		*/
		$id = $json->id;
		$qty = $json->qty;
		$byk = sizeof($id);
		if($byk == 0){
			$resLagi = DB::select("select id from keranjang where iduser = ? and idoutlet = ?", array($idUser, $idOutlet));
			$bykDeh = sizeof($resLagi);
			for($i=0;$i<$bykDeh;$i++){
				DB::Delete("DELETE FROM detailkeranjang where idkeranjang ?", array($resLagi[$i]->id));
			}
			DB::delete("DELETE from keranjang where iduser = ? and idoutlet = ?", array($idUser, $idOutlet));

		}else{
			$textId = "";
			for($i=0;$i<$byk;$i++){
				$textId .= $id[$i];

				if($i < $byk-1){
					$textId .= ",";
				}
			}

			$query = "select id from keranjang where id not in (".$textId.") 
			and iduser = ? 
			and idoutlet = ?";
			$res = DB::Select($query,array($idUser, $idOutlet));

			$bykRes = sizeof($res);
			for($i=0;$i<$bykRes;$i++)
				DB::Delete("delete from detailkeranjang where idkeranjang = ?" , array($res[$i]->id));

			DB::Delete("delete from keranjang where id not in (".$textId.") 
			and iduser = ? 
			and idoutlet = ?" , array($idUser, $idOutlet));

			for($i=0;$i<$byk;$i++){
				$ker = Keranjang::whereRaw("id=?", array($id[$i]))->first();
				$ker->quantity = $qty[$i];
				$ker->save();
			}

		}
		return "SUKSES";
	}
	/*public function buatPesanan(){
		$np = Input::get("np");	//nama pemesan
		$hp = Input::get("hp");	//hp 
		$ap = Input::get("ap");	//alamat pemesan
		$pk = Input::get("pk");	//pesanan khusus
		$pb = Input::get("pb");	//pakai berapa

		$pb = preg_replace( '/[^0-9]/', '', $pb);

		if(strlen($np) > 10){

		}else if(strlen($hp) > 20){

		}else if(strlen($ap) > 150){

		}else if(strlen($pk) > 150){

		}else{
			$idUser = Auth::id();
			$idOutlet = Session::get("lagiBukaIdOutlet");
			$cart = DB::select("SELECT idmenu, quantity, harga FROM Cart a inner join Menu_B b on a.idMenu = b.id WHERE idUser=? and idOutlet=?", array($idUser, $idOutlet));
			$byk = sizeof($cart);
			if($byk == 0){

			}else{

				$outlet = Outlet::whereRaw("id=?",array($idOutlet))->first();

				$pesanan = new Pesanan;
				$pesanan->idpemesan = $idUser;
				$pesanan->idoutlet = $idOutlet;
				$pesanan->categoryoutlet = $outlet->category;
				$pesanan->ap = $ap;
				$pesanan->hp = $hp;
				$pesanan->pk = $pk;
				if(strlen($pb)>0){
					$pb = intval($pb);
					$pesanan->pakeuang = $pb;
				}
				$pesanan->save();		//save dulu biar dapat id pesanan buat dipake di bawah

				$total = 0;
				for($i=0;$i<$byk;$i++){
					$total += $cart[$i]->harga;
					$dpesanan = new DPesanan;
					$dpesanan->hargaSaatitu = $cart[$i]->harga;
					$dpesanan->qty = $cart[$i]->quantity;
					$dpesanan->idM = $cart[$i]->idMenu;
					$dpesanan->idPesanan = $pesanan->id;
					$dpesanan->save();
				}
				$pesanan->harusdibayar = $total;
				$pesanan->save();

				Auth::user()->namasingkat = $np;
				Auth::user()->save();

				$this->selesaiPemesanan($pesanan->id, $idUser, $idOutlet);
				echo "asd";
			}
		}
	}*/

	/*private function selesaiPemesanan2($idPesanan, $idUser, $idOutlet, $perluKirimConfCodePesananKeUser){
		$res = DB::select("SELECT id from keranjang where iduser = ? and idoutlet = ?", array($idUser, $idOutlet));
		$byk = sizeof($res);
		for($i = 0 ; $i < $byk ; $i++){
			DB::delete("DELETE From detailkeranjang where idkeranjang = ?",array($res[$i]->id));
		}
		DB::delete("Delete FROM keranjang WHERE iduser = ? and idoutlet = ?", array($idUser, $idOutlet));

		//Session::forget("lagiBukaIdOutlet");
		Session::forget("namaPemesanSingkat");
		Session::forget("outletChosenForFilterAfterSearch");
		Session::forget("tingkatKecocokanOutletChosenForFilterAfterSearch");
		Session::forget("outletChosenForViewAfterSearch");
		Session::forget("tingkatKecocokanOutletChosenForViewAfterSearch");
		Session::forget("namaFilter");

		if($perluKirimConfCodePesananKeUser){
			return $this->kirimConfCodePesanan($idPesanan);
		}
		else{
			$this->kirimKeHpResto($idPesanan, $idOutlet);
			return Redirect::to("/pesananDikirim");
			//return View::make("PesananSudahDiKirimKeResto");
			//return "SUKSES, HARUSNYA LEMPAR KE VIeW NIH";
		}
		
	}*/
	public function kirimKonfirmasiKeHpUser(){
		return $this->addTableKonfHpDulu(-1);
	}
	private function addTableKonfHpDulu($idPesanan){

		if($idPesanan == -1){
			$angkaNoHpBaru = Session::get("nohpbaru");
			Session::forget("nohpbaru");
		}

		$userTest = Auth::user();
		$allkhu = KonfHpUser::whereRaw("iduser = ? and tgljam = ?", array($userTest->id, date("Y-m-d")))->get();
		$byk = sizeof($allkhu);
		if($byk >= 3){
			Session::put("message", 0);
			return "GAGAL ANDA SUDAH MENGGUNAKAN SEMUA KESEMPATAN VERIFY CODE, COBA LAGI ESOK HARI UNTUK MEMVERIFY HP ANDA";
		}else{
			$khu = new KonfHpUser;
			$khu->tgljam = date("Y-m-d");
			$khu->iduser = $userTest->id;
			$khu->idpesanan	= $idPesanan;
			if($idPesanan == -1){
				$khu->hpuserdariprofil = $angkaNoHpBaru;
			}
			

			$passBaru = "";
			$bykRand = rand(4,6);
			for($i=0;$i<$bykRand;$i++){
				$temp1 = rand(65,90);
				if($temp1 < 74 || $temp1 > 81){
					
				}else{
					$temp2 = rand(1,2);
					if($temp2 == 1)
						$temp1 += rand(0,9);
					else
						$temp1 -= rand(0,9);
				}
				$passBaru .= chr($temp1) . "";
			}
			$khu->kodekonfnya = $passBaru;
			$khu->save();
			$byk++;
			Session::put("message", $byk);
			return $this->showNeedKonf($khu->id);
			//return $this->kirimConfCodePesanan2($idPesanan);
		}
	}
	private function perluKirimConfCodePesananKeUser($idPesanan){
		$userTest = Auth::user();
		$now = date("Y-m-d");
		if($now != $userTest->last_trytoverify){
			$userTest->count_trytoverify = 1;
			$userTest->last_trytoverify = $now;
			$userTest->save();
		}
		else{
			$userTest->count_trytoverify+=1;
			$userTest->save();
		}
		$maxkesempatan = 3;	
		$beda = $maxkesempatan - $userTest->count_trytoverify;
		
		if($beda >= 0){
			Session::put("message", $beda);
			//return $this->kirimConfCodePesanan($idPesanan);
			return $this->kirimConfCodePesanan2($idPesanan);
		}else{
			Session::put("message", 0);
			return "GAGAL ANDA SUDAH MENGGUNAKAN SEMUA KESEMPATAN VERIFY CODE, COBA LAGI ESOK HARI UNTUK MEMVERIFY HP ANDA";
		}
	}
	private function selesaiPemesanan($idPesanan, $idUser, $idOutlet, $perluKirimConfCodePesananKeUser){
		$res = DB::select("SELECT id from keranjang where iduser = ? and idoutlet = ?", array($idUser, $idOutlet));
		$byk = sizeof($res);
		for($i = 0 ; $i < $byk ; $i++){
			DB::delete("DELETE From detailkeranjang where idkeranjang = ?",array($res[$i]->id));
		}
		DB::delete("Delete FROM keranjang WHERE iduser = ? and idoutlet = ?", array($idUser, $idOutlet));

		//Session::forget("lagiBukaIdOutlet");
		Session::forget("namaPemesanSingkat");
		Session::forget("outletChosenForFilterAfterSearch");
		Session::forget("tingkatKecocokanOutletChosenForFilterAfterSearch");
		Session::forget("outletChosenForViewAfterSearch");
		Session::forget("tingkatKecocokanOutletChosenForViewAfterSearch");
		Session::forget("namaFilter");

		$thestime = date("Y-m-d H:i:s");
		$waktusekarangkurang6jam = date("Y-m-d H:i:s",strtotime("-6 hours",strtotime($thestime)));

		DB::Update("Update pesanan set statuspesanan = 12 where idpemesan = ? and statuspesanan >= 5 and statuspesanan < 10 and tgl < ?", array($idUser, $waktusekarangkurang6jam));		
		

		$pesanan = Pesanan::whereRaw("id=?",array($idPesanan))->first();

		if($perluKirimConfCodePesananKeUser){

			return KonfHpUser::KonfirmasiHpUserPesananBaru($pesanan);
			
			/*$khu = new KonfHpUser;
			$khu->addKonfirmasi_pesananBaru($pesanan);
			$khu->sendKodeKonfirmasiToHpUser();
			return Redirect::to("/khu/".$khu->id);*/
			

			//return $this->addTableKonfHpDulu($idPesanan);
			//return $this->perluKirimConfCodePesananKeUser($idPesanan);
			/*echo $now . "<br/>";
			echo $userTest->last_trytoverify;
			echo "<Br/>#";
			echo $userTest->count_trytoverify;*/

			
		}
		else{
			if($pesanan->kirimkanSecaraFisikKeResto()){
				$pesanan->setPesanan_SiapDikirim();
				return Redirect::to("/pesan/selesai");
			}else{
				return HSFoorenzy::showHalamanError("Gagal untuk mengirim pesanan anda.");
			}

			//return $this->kirimKeHpResto($idPesanan, $idOutlet);
			
			//return View::make("PesananSudahDiKirimKeResto");
			//return "SUKSES, HARUSNYA LEMPAR KE VIeW NIH";
		}
		
	}

	private function kirimConfCodePesanan($idPesanan){
		$passBaru = "";
		$bykRand = rand(4,6);
		for($i=0;$i<$bykRand;$i++){
			$temp1 = rand(65,90);
			if($temp1 < 74 || $temp1 > 81){
				
			}else{
				$temp2 = rand(1,2);
				if($temp2 == 1)
					$temp1 += rand(0,9);
				else
					$temp1 -= rand(0,9);
			}
			$passBaru .= chr($temp1) . "";
		}

		$pesanan = Pesanan::whereRaw("id=?", array($idPesanan))->first();
		if($pesanan == NULL){
			return "GA ADA PESANAN.";
		}else{
			$pesanan->codeconf = $passBaru;
			$pesanan->tgl = date("Y-m-d H:i:s");
			$pesanan->save();
			return $this->kirimKeHpPemesan($passBaru, $pesanan->hp, $pesanan->id);
		}
	}
	
	private function showNeedKonf($idKHU){
		$khu = KonfHpUser::whereRaw("id=?",array($idKHU))->first();
		if($khu == NULL){
			return "LINK TIDAK VALID";
		}else{

			if($khu->idpesanan == "-1"){
				//khusus konfirmasi yang dari ganti profil
				$passBaru = $khu->kodekonfnya;
				$body = "Kode Konf.\n";
				$body .="Masukan Kode ".$passBaru." pada Kotak yang disiapkan\n";
				$body .="Jika Halaman telah tertutup, Ketikan Link Berikut di browser anda : " . URL::to("/khu") . "/" . $idKHU;
				$this->SampaikanKeSiapapunLewatSMS($khu->hpuserdariprofil, $body);				
				return Redirect::to("/khu/".$idKHU);
			}else{
				//khusus konfirmasi buat pesanan
				$pesanan = Pesanan::whereRaw("id=?", array($khu->idpesanan))->first();
				if($pesanan == NULL){
					return "GA ADA PESANAN NIH DI SHOW NEED KONF";
				}else{
					$passBaru = $khu->kodekonfnya;
					$body = "Kode Konf.\n";
					$body .="Masukan Kode ".$passBaru." pada Kotak yang disiapkan\n";
					$body .="Jika Halaman telah tertutup, Ketikan Link Berikut di browser anda : " . URL::to("/khu") . "/" . $idKHU;
					$this->SampaikanKeSiapapunLewatSMS($pesanan->hp, $body);				
					return Redirect::to("/khu/".$idKHU);
				}
			}
		}
	}
	private function kirimConfCodePesanan2($idPesanan){
		$passBaru = "";
		$bykRand = rand(4,6);
		for($i=0;$i<$bykRand;$i++){
			$temp1 = rand(65,90);
			if($temp1 < 74 || $temp1 > 81){
				
			}else{
				$temp2 = rand(1,2);
				if($temp2 == 1)
					$temp1 += rand(0,9);
				else
					$temp1 -= rand(0,9);
			}
			$passBaru .= chr($temp1) . "";
		}

		$pesanan = Pesanan::whereRaw("id=?", array($idPesanan))->first();
		if($pesanan == NULL){
			return "GA ADA PESANAN.";
		}else{
			$pesanan->codeconf = $passBaru;
			$pesanan->tgl = date("Y-m-d H:i:s");
			$pesanan->save();
			//return $this->kirimKeHpPemesan($passBaru, $pesanan->hp, $pesanan->id);
			return $this->kirimKeHpPemesan2($passBaru, $pesanan->hp, $pesanan->id);
		}
	}
	public function kirimConfCodeNgulang(){
		$idKHU = Input::get("idkhu");
		$khu = KonfHpUser::whereRaw("id = ?", array($idKHU))->first();
		if($khu != NULL){
			$idPesanan = $khu->idpesanan;
			if($idPesanan == "-1"){
				Session::put("nohpbaru", $khu->hpuserdariprofil);
			}
			return $this->addTableKonfHpDulu($idPesanan);
		}
		return "ERROR , Nomor Konfirmasi tidak ditemukan";
	}
	public function kirimConfCodeNgulangNoBaru(){
		$idKHU = Input::get("idkhu");
		$hpbaru = Input::get("hpbaru");
		$khu = KonfHpUser::whereRaw("id = ?", array($idKHU))->first();
		if($khu != NULL){
			$idPesanan = $khu->idpesanan;
			if($idPesanan == "-1"){
				Session::put("nohpbaru", $hpbaru);
				return $this->addTableKonfHpDulu($idPesanan);
			}else{
				$pesanan = Pesanan::whereRaw("id=?",array($idPesanan))->first();
				if($pesanan != NULL){
					$pesanan->hp = $hpbaru;
					$pesanan->save();
					return $this->addTableKonfHpDulu($idPesanan);
				}
			}
			
		}
		return "ERROR , Terjadi Kesalahan Saat ingin mengirim ke No Hp Baru";
	}
	
	public function kirimConfCodeNgulang2(){
		$idPesanan = Input::get("idpesanan");
		return $this->perluKirimConfCodePesananKeUser($idPesanan);
	}

	private function kirimKeHpPemesan($passBaru, $hp, $idPesanan){	
		$body = "Kode Konf.\n";
		$body .="Masukan Kode ".$passBaru." pada Kotak yang disiapkan\n";
		$body .="Jika Halaman telah tertutup, Ketikan Link Berikut di browser anda : " . URL::to("/kp") . "/" . $idPesanan;

		$this->SampaikanKeSiapapunLewatSMS($hp, $body);

		return Redirect::to("/kp/".$idPesanan);
		//return View::make("hasil.codeconfdikirim", array("hp" => $pesanan->hp));
	}
	private function kirimKeHpPemesan2($passBaru, $hp, $idPesanan){	
		$body = "Kode Konf.\n";
		$body .="Masukan Kode ".$passBaru." pada Kotak yang disiapkan\n";
		$body .="Jika Halaman telah tertutup, Ketikan Link Berikut di browser anda : " . URL::to("/kp") . "/" . $idPesanan;

		$this->SampaikanKeSiapapunLewatSMS($hp, $body);
		
		return Redirect::to("/kp/".$idPesanan);
		//return View::make("hasil.codeconfdikirim", array("hp" => $pesanan->hp));
	}

	private function kirimKeHpResto($idPesanan, $idOutlet){	

		$body = "FooRenzy->\n";
		$outlet = Outlet::where("id","=",$idOutlet)->first();
		$pesanan = Pesanan::where("id","=",$idPesanan)->first();
		$user = Auth::user();
		//$body .= $outlet->nama;

		//$body .= "\nPesan: ";

		$res = DB::Select("SELECT a.id, b.namakecil, qty from detailpesanan a inner join menu_b b on a.idm = b.id where idpesanan = ?", array( $idPesanan ));;
		$byk = sizeof($res);
		for($i=0;$i<$byk;$i++){
			$body .=  $res[$i]->qty . " " . $res[$i]->namakecil;

			$res2 = DB::Select("SELECT b.nama from detaildetailpesanan a inner join menu_d b on a.idd = b.id where iddetailpesanan = ?", array($res[$i]->id));
			$byk2 = sizeof($res2);
			if($byk2 > 0)
				$body .= "(";

			for($j=0;$j<$byk2;$j++){
				$body .= $res2[$j]->nama;
				if($j < $byk2 - 1){
					$body .= " ";
				}
			}
			if($byk2 > 0)
				$body .= ")";

			if($i < $byk - 1){
				$body .=", ";
			}
		}

		if($pesanan->pk != NULL && strlen($pesanan->pk) > 3){
			$body .= ".\n".$pesanan->pk;
		}

		if($pesanan->pakeuang != NULL && $pesanan->pakeuang != ""){
			$body .= "\nPake: Rp " . $pesanan->pakeuang;
		}
		
		$body .= "\nKe: " . $pesanan->ap;
		
		$body .= "\na/n: " . $user->namasingkat;
		$body .= ", " . $pesanan->hp;

		/*
		$pesananLainNya = Pesanan::whereRaw("idoutlet = ? and statuspesanan = 5 and id != ?", array($outlet->id, $pesanan->id))->first();
		if($pesananLainNya != NULL){
			$kodekeoutlet = $this->getkodekeoutlet($outlet->id, $pesanan->id);
			$pesanan->codekeoutlet = $kodekeoutlet;
			$pesanan->save();

			$body .= "\nHarap bls sms ini dengan format \"ya#".$kodekeoutlet."\" atau \"ga#".$kodekeoutlet."#alasan\" tanpa petik.";	

		}
		else{
			$body .= "\nHarap bls sms ini dengan format \"ya\" atau \"ga#alasan\" tanpa petik.";
		}
		*/

		$metodeOutlet = $outlet->metode;
		if($metodeOutlet == 2){
			return "Lewat GCM";
		}else{
			
			$kodebonpelanggan = $this->saveKodeUniqueToPesanan($idPesanan);
			if($kodebonpelanggan == "-999"){
				return "Error, Resto menerima terlalu banyak pelanggan.";
			}else{

				$body .= ".\n";
				$body .= "Mohon menuliskan '".$kodebonpelanggan."' (Tanpa Petik) pada bon untuk pelanggan";
				$body .= ".\n";
				$body .= "Format Balasan SMS: '".$kodebonpelanggan."#ya' atau '".$kodebonpelanggan."#ga#alasan penolakan' (Tanpa Petik).";
				$noHpOutlet = $outlet->phone;
				$this->SampaikanKeSiapapunLewatSMS($noHpOutlet, $body);


				$dataKasih = array(
					"pBeli" => $pesanan->harusdibayar / 1000,
					"nHpRst" => $noHpOutlet,
					"rBalas" => $outlet->getIncludeNoBalas2(),
					"pBeliFull" => $user->getPBForNextKupon(),
					"idRest" => $outlet->id
				);
				Session::put("dataKasih", $dataKasih);

				return Redirect::to("/pesan/selesai");
				//return Redirect::to("/pesananDikirim");

			}

			

		}

	}
	
	/*private function kirimKeHpResto2($idPesanan, $idOutlet){	

		$body = "FooRenzy->";
		$outlet = Outlet::where("id","=",$idOutlet)->first();
		$pesanan = Pesanan::where("id","=",$idPesanan)->first();
		$user = Auth::user();
		//$body .= $outlet->nama;

		$body .= "\nPesan: ";

		$res = DB::Select("SELECT a.id, b.namakecil, qty from detailpesanan a inner join menu_b b on a.idm = b.id where idpesanan = ?", array( $idPesanan ));;
		$byk = sizeof($res);
		for($i=0;$i<$byk;$i++){
			$body .=  $res[$i]->qty . " " . $res[$i]->namakecil;

			$res2 = DB::Select("SELECT b.nama from detaildetailpesanan a inner join menu_d b on a.idd = b.id where iddetailpesanan = ?", array($res[$i]->id));
			$byk2 = sizeof($res2);
			if($byk2 > 0)
				$body .= "(";

			for($j=0;$j<$byk2;$j++){
				$body .= $res2[$j]->nama;
				if($j < $byk2 - 1){
					$body .= " ";
				}
			}
			if($byk2 > 0)
				$body .= ")";

			if($i < $byk - 1){
				$body .=", ";
			}
		}

		if($pesanan->pk != NULL && strlen($pesanan->pk) > 3){
			$body .= "\nTrus: " . $pesanan->pk;
		}

		if($pesanan->pakeuang != NULL && $pesanan->pakeuang != ""){
			$body .= "\nPake: Rp " . $pesanan->pakeuang;
		}
		
		$body .= "\nKe: " . $pesanan->ap;
		$body .= "\nDari: " . $pesanan->hp;
		$body .= "\nAN: " . $user->namasingkat;

		$pesananLainNya = Pesanan::whereRaw("idoutlet = ? and statuspesanan = 5 and id != ?", array($outlet->id, $pesanan->id))->first();
		if($pesananLainNya != NULL){
			$kodekeoutlet = $this->getkodekeoutlet($outlet->id, $pesanan->id);
			$pesanan->codekeoutlet = $kodekeoutlet;
			$pesanan->save();

			$body .= "\nHarap bls sms ini dengan format \"ya#".$kodekeoutlet."\" atau \"ga#".$kodekeoutlet."#alasan\" tanpa petik.";	

		}
		else{
			$body .= "\nHarap bls sms ini dengan format \"ya\" atau \"ga#alasan\" tanpa petik.";
		}
		$metodeOutlet = $outlet->metode;
		if($metodeOutlet == 2){

		}else{
			$noHpOutlet = $outlet->phone;
			$this->SampaikanKeSiapapunLewatSMS($noHpOutlet, $body);
		}

	}*/
	

	private function getkodekeoutlet($idoutlet, $idpesanan){
		
		$day = date("d");
		$month = date("m");
		$year = date("Y");
		$pesananNya = Pesanan::whereRaw("day(tgl) = ? and month(tgl) = ? and year(tgl) = ? and idoutlet = ?", array($day,$month,$year,$idoutlet))->get();
		$byk = sizeof($pesananNya);
		if($byk > 0)
			$byk -= 1;

		return $this->hitungdanambilnilaiaslikodeoutlet($byk);
	}
	private function hitungdanambilnilaiaslikodeoutlet($byk){

		$kombinasi = array("a","d","g","j","m","p","t","w");
		//adgjmptw = 8 possibilities
		//kalo ada 3 karakter , maka akan ada 8 x 8 x 8

		$bykkombinasi = sizeof($kombinasi);
		$pangkatduakombinasi = $bykkombinasi * $bykkombinasi;
		$ambilI = -1;
		for($i=0;$i<$bykkombinasi;$i++){
			$perbandingankurang = $pangkatduakombinasi * ($i + 1);
			if($byk < $perbandingankurang){
				$ambilI = $i;
				break;
			}
		}

		$karakterpertama = $kombinasi[$ambilI];

		$nilaiBedaSamaThresholdTerakhir = $byk - ($pangkatduakombinasi * $ambilI);

		$karakterkedua = $kombinasi[$nilaiBedaSamaThresholdTerakhir / $bykkombinasi];

		$karakterketiga = $kombinasi[$nilaiBedaSamaThresholdTerakhir % $bykkombinasi];

		return $karakterpertama . "" . $karakterkedua . "" . $karakterketiga;
	}

	private function SampaikanKeSiapapunLewatSMS($noHp, $bodyText){
		//$noHp = Input::get("noHp");
		//$bodyText = Input::get("bodyText");

		//$noHp = "+6281586065696";
		//$noHp = "+6281808997922";
		//$bodyText = "yank gendut u yank hahaha ini test doank yank pake GCM. buset dah ini gw ga tau gmana cara enternya, bodo amat lah ya hahahaha. btw gw mo coba nih gmana kalo lewat 160 ahahaha apa yang terjadi ya, soalnya kan kemungkinan besar ya nanti lewat lho , trus gw jg mo coba bisa spasi ga ya..\nahahahaha asik dah da spasi , semoga !!!";
		//$bodyText .= "\nini gw lagi mo coba , gimana kalo orang send nya sekitar 800 message char, soalnya bisa gempor juga ya kalo kayak gitu wakakkaka, aduh aduh , malu aku malu, pada semut merah,yang berbaris dinding, menatapku pop pabob, pabob paboppp , kepret di kepret, bau ketek di kepret, kenapa bisa begitu, oh masya allah, mana ada yang tahu , kenapa ini begini. heyyy hoooo..";
		//$bodyText = "yank gendut u yank hahaha ini test doank yank pake GCM.";

		$lastDevice = file_get_contents(storage_path()."/fileHpAdminServer/lastDevice.txt");
		$bykDevice = $_ENV['bykDevice'];
		$currDevice = intval($lastDevice) + 1;
		if($currDevice > intval($bykDevice)){
			$currDevice = 0;
		}
		file_put_contents(storage_path()."/fileHpAdminServer/lastDevice.txt", $currDevice);
		

		$API_DEVICE = $_ENV['device'.$currDevice];

		//echo $API_DEVICE;

		$message = array("noHp" => $noHp, "bodyText" => $bodyText);
		$devices = array($API_DEVICE);
		$fields = array(
			"time_to_live" 		=> 0,
			'registration_ids'  => $devices,
			'data'              => array( "message" => $message ),
		);
		$headers = array( 
			'Authorization: key=AIzaSyBgvYFOy4cCLhyEIlCbbFBynLSvx74CzWM',
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://android.googleapis.com/gcm/send"); 
		curl_setopt($ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields) );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);

	    $result = curl_exec($ch);
	    curl_close($ch);
			
	    //echo strlen($bodyText);

		$jumlahSuccess = json_decode($result)->success;
		if($jumlahSuccess == 0){
			echo "Error";
		}else{
			echo "Benar";
		}

		//var_dump($result);
		//var_dump($message);
	}

	private function KasihDanAmbilNilaiOutletIni($angka, $idOutletTest){

		$thestime = date("Y-m-d H:i:s");
		$strWaktu1 = date("Y-m-d H:i:s",strtotime("-1 hours",strtotime($thestime)));

		$waktuSekarang = date('Y-m-d H:i:s',time());

		$outletTest = Outlet::whereRaw('id = ?', array($idOutletTest))->first();
		if($outletTest != null){
			$hasilFFBForSearch = $this->getPointFromRatingFromFB($idOutletTest);
			$outletTest->ratingffbforsearch = $hasilFFBForSearch;
			$outletTest->lastupdateratingffb = $waktuSekarang;
			$outletTest->save();

			$hasilForSearch = $this->getPointFromRating($idOutletTest);
			$outletTest->ratingforsearch = $hasilForSearch;
			$outletTest->lastupdaterating = $waktuSekarang;
			$outletTest->save();
		}

		if($angka == 1)
			return $hasilForSearch;
		else 
			return $hasilFFBForSearch;
		
	}
	private function getPointFromRatingFromFB($idOutletTest){
		$query = "select a.id, coalesce(a.jumlahlike,1) as jumlahlike, b.ratingffb as ratingffb 
		from foodbloggers a inner join bloggeroutlet b on a.id = b.idfoodblogger 
		where b.idoutlet = ?";
		$res = DB::select($query, array($idOutletTest));
		$byk = sizeof($res);
		if($byk == 0)
			return 0;
		else{
			$totalMax = 0;
			for($i = 0 ; $i < $byk ; $i++){
				$totalMax += $res[$i]->jumlahlike;
			}
			$nilaiRating = 0;
			for($i = 0 ; $i < $byk ; $i++){
				$nilaiRating += floatval(($res[$i]->jumlahlike * $res[$i]->ratingffb) / $totalMax);
			}
			$nilaiRating = $nilaiRating / 5 * 100;
			return ( $nilaiRating );
		}
	}
	private function getPointFromRating($idOutletTest){
		$query = "Select coalesce( ( SUM(rating) /  COUNT(rating) ) ,0 ) as berapa from pemesanoutletrating where idoutlet = ?";
		$res = DB::select($query, array($idOutletTest));
		$nilaiRating = floatval($res[0]->berapa);
		$nilaiRating = $nilaiRating / 5 * 100;
		return ($nilaiRating);
	}

	public function generateKodeUniquePesanan($lastNo){
		//lastNo disini harusnya 0 sampai 335

		$kcup = file_get_contents(storage_path()."/dataServer/filekeycodeserver.txt");
		$tempkcup = $kcup;
		$JH = strlen($kcup);	//jumlah huruf
		$JK = $JH - 1;			//jumlah kotak
		$JKDK = $JK - 1;		//jumlah kombinasi dalam kotak
		$kodeuniquegenerated = "";


		$bantuan1 = $JK*$JKDK;
		
		$angkacharsatu = intval($lastNo / $bantuan1);
		
		$kodeuniquegenerated .= $tempkcup[$angkacharsatu];

		$tempkcup = str_replace($tempkcup[$angkacharsatu] , "" , $tempkcup);

		$nilaibantuan = $lastNo % $bantuan1;

		$angkachardua = intval($nilaibantuan / $JKDK);
		$kodeuniquegenerated .= $tempkcup[$angkachardua];
		$tempkcup = str_replace($tempkcup[$angkachardua] , "" , $tempkcup);

		$angkachartiga = $nilaibantuan % $JKDK;
		$kodeuniquegenerated .= $tempkcup[$angkachartiga];
		$tempkcup = str_replace($tempkcup[$angkachartiga] , "" , $tempkcup);

		return $kodeuniquegenerated;
		
	}

	public function saveKodeUniqueToPesanan($idPesanan){
		
		$pesanan = Pesanan::whereRaw("id=?",array($idPesanan))->first();
		$LCX = 0;

		$res = DB::Select("SELECT lastno FROM pesanan where id != ? and idoutlet = ? and statuspesanan >= 5 ORDER By id desc", array($pesanan->id, $pesanan->idoutlet));
		$byk = sizeof($res);
		$lastno = -1;
		if($byk > 0){
			$lastno = intval($res[0]->lastno) + 1;
		}else{
			$lastno = 1;
		}

		$outletkepenuhan = true;
		while($LCX < 672){
			$lastno = $lastno % 336;
			$kodeunique = $this->generateKodeUniquePesanan($lastno);
			$samaga = DB::select("Select id, tgl from pesanan where id != ? and idoutlet = ? and statuspesanan >= 5 and statuspesanan <= 10 and kodeunique = ?", array($pesanan->id, $pesanan->idoutlet, $kodeunique));

			$lanjut = true;

			$byksama = sizeof($samaga);
			if($byksama == 0){
				//ternyata ga ada yang sama kode uniquenya dengan statuspesanan belom done untuk outlet itu
				$lanjut = true;
			}else{
				for($x=0;$x<$byksama;$x++){
					$tgl = $samaga[$x]->tgl;
					$tgl = substr($tgl, 0, 19);

					$thestime = date("Y-m-d H:i:s");
					$waktusekarangkurang6jam = strtotime("-6 hours",strtotime($thestime));
					$waktuSiSama = strtotime($tgl);

					if($waktusekarangkurang6jam >= $waktuSiSama){
						//cek kalo ternyata ada yang jamnya >= 6jam dan KU nya sama, itu harus di done in
						$pesananSama = Pesanan::whereRaw("id=?",array($samaga[$x]->id))->first();
						$pesananSama->statuspesanan = 12;
						$pesananSama->save();


					}else{
						//yang tanggalnya <= 6 jam, tapi kode uniquenya sama
						if($samaga[$x]->id < $pesanan->id);
							$lanjut = false;
					}
				}
				

			}

			if($lanjut){
				$outletkepenuhan = false;
				$pesanan->lastno = $lastno;
				$pesanan->kodeunique = $kodeunique;
				$pesanan->tgl = date("Y-m-d H:i:s");
				$pesanan->save();

				break;
			}

			$lastno++;
			$LCX++;
		}

		if($outletkepenuhan){
			return "-999";
			//return "kepenuhan nih outletnya, melebihi 336 pemesanan yang belom di submit.";
		}else{
			return $pesanan->kodeunique;
			//langsung kirim ke resto nih harusnya disini
			//sukses nih save lastno sama kodeunique
		}

	}

}
