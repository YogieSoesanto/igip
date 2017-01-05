<?php
class BuktiController extends BaseController {
	public function halamanPesananDanBukti(){
		return View::make('halaman.pesanandibuat');
	}
	public function getDataSekarang(){
		$ps = Input::get("ps");
		$dpp = Input::get("dpp");
		$mulai = ($ps - 1) * $dpp;

		 $res = DB::table("pesanan")
		 ->join('outlets', 'pesanan.idoutlet', '=', 'outlets.id')
         ->select('outlets.nama', 'pesanan.tgl', 'pesanan.harusdibayar','pesanan.statuspesanan', 'pesanan.id')
         ->where("pesanan.idpemesan","=",Auth::id())
         ->orderBy('pesanan.tgl', 'desc')->skip($mulai)->take($dpp)->get();

		return json_encode($res);
	}
	public function halamanUploadBukti($idpesanan){
		//$pes = Pesanan::whereRaw("id=? and idpemesan=?",array($idpesanan, Auth::id()))->first();
		$pes = DB::table("pesanan")
		->join('outlets', 'pesanan.idoutlet', '=', 'outlets.id')
		->select('outlets.nama', 'pesanan.tgl', 'pesanan.isprintedforoutlet', 'pesanan.idoutlet','pesanan.id','pesanan.buktiuploadlink', 'pesanan.statuspesanan')
		->where("pesanan.idpemesan","=",Auth::id())
		->where("pesanan.id" , "=" , $idpesanan)->first();
		if($pes == NULL){
			$pe = "ANDA TIDAK PERNAH MEMESAN DENGAN ID PESANAN SEPERTI BERIKUT";
			return View::make("halaman.error.bukti", array("data" => $pe));
		}else{
			if($pes->isprintedforoutlet == "1"){
				$pe = "PESANAN ANDA SUDAH DI LOCK OLEH PIHAK FOORENZY";
				return View::make("halaman.error.bukti", array("data" => $pe));
			}
			else if($pes->statuspesanan != "5" && $pes->statuspesanan != "6" && $pes->statuspesanan != "7" && $pes->statuspesanan != "8"){
				$pe = "Pesanan berikut tidak valid karena belum di kirim ke Resto";
				return View::make("halaman.error.bukti", array("data" => $pe));
			}
			else{
				Session::put("idPesananMauUpload", $idpesanan);
				$pos = PemesanOutletRating::whereRaw("idpesanan = ?" ,array($idpesanan))->first();

				if($pos == NULL) {
					$bintang = 3;
					$comment = "";
					$gbr = asset("assets/gbr/noimage.png");
				}else{
					$bintang = $pos->rating;
					$comment = $pos->comment;
					$gbr = asset("buktibayar/".$pes->id."".$pes->buktiuploadlink);
				}


				return View::make("halaman.buktiuploadnya" , array(
					"namaO" => $pes->nama,
					"tglO" => $pes->tgl,
					"bintang" => $bintang,
					"comment" => $comment,
					"gbr" => $gbr
				));
			}
		}
	}
	public function doUploadBukti(){
		$batasbolehupload = 1024;
		$rate = Input::get("jumlahrating");
		$comment = Input::get("komentar");
		$cbhukum = Input::get("cbhukum");

		$file = Input::file("filebukti");

		$size = Input::file('filebukti')->getSize();

		if($cbhukum != "on"){
			return Redirect::back();
		}
		else if(strlen($comment) > 300){
			return Redirect::back();
		}
		else if($size > $batasbolehupload * 1024){
			return Redirect::back();
			//file nya kegedean lebih dari 1MB
		}else{
			$fileName = Input::file('filebukti')->getClientOriginalName();
			$titikfile = strrpos($fileName, ".");
			if($titikfile === FALSE){
				return Redirect::back();
				//harusnya ga mungkin kalo file ga ada . nya
			}
			else{

				$idPesanan = Session::get("idPesananMauUpload");
				$pesanan = Pesanan::whereRaw("id=?", array($idPesanan))->first();

				$nilainyaposisi = strlen($fileName) - $titikfile;
				$ext = substr($fileName, -$nilainyaposisi, $nilainyaposisi);
				
				$destinationPath = storage_path()."/buktibayar";
				$destinationPath2 = public_path()."/buktibayar";
				
				$pesanan->buktiuploadlink = $ext;
				$pesanan->save();

				Input::file('filebukti')->move($destinationPath, $idPesanan . "" . $ext);
				File::copy($destinationPath ."/" . $idPesanan . "" . $ext, $destinationPath2 ."/" . $idPesanan . "" . $ext);
				
				$por = PemesanOutletRating::whereRaw("idpesanan=?",array($idPesanan))->first();
				if($por == NULL){
					$por = new PemesanOutletRating;
					$por->idpemesan = $pesanan->idpemesan;
					$por->idoutlet = $pesanan->idoutlet;
					$por->tgl = date("Y-m-d H:i:s");
					$por->rating = $rate;
					$por->comment = $comment;
					$por->idpesanan = $idPesanan;
					$por->save();
				}else{
					if($rate == 0 && $comment == ""){

					}else{
						$por->rating = $rate;
						$por->comment = $comment;
						$por->save();
					}
				}

				return $this->addKeuntunganSetelahUpload($idPesanan);
			}
		}
		
	}

	private function addKeuntunganSetelahUpload($idPesanan){
		$pes = Pesanan::whereRaw("id = ?", array($idPesanan))->first();
		if($pes != NULL){
			$user = Auth::user();
			$harusdibayar = $pes->harusdibayar;
			$iduser = $pes->idpemesan;
			$idoutlet = $pes->idoutlet;
			$statuspesanan = $pes->statuspesanan;

			if($user->id == $iduser){
				if($statuspesanan == 5 || $statuspesanan == 6 || $statuspesanan == 7){
					$adaRatingOutlet = false;
					$por = PemesanOutletRating::whereRaw("idpemesan = ? and idoutlet = ?", array($iduser, $idoutlet))->first();
					if($por != NULL){
						$adaRatingOutlet = true;
					}

					$adaLikeFB = false;
					$jbo = JempolBloggerOutlet::whereRaw("iduser = ? and idoutlet = ?", array($iduser, $idoutlet))->first();
					if($jbo != NULL){
						$adaLikeFB = true;
					}

					$pemb_curr = $user->pemb_curr;
					$pemb_curr = ($pemb_curr == "" ? 0 : $pemb_curr);
					$user->pemb_curr = $pemb_curr + $harusdibayar;

					$pemb_lifetime = $user->pemb_lifetime;
					$pemb_lifetime = ($pemb_lifetime == "" ? 0 : $pemb_lifetime);
					$user->pemb_lifetime = $pemb_lifetime + $harusdibayar;

					$expDidapat = floor($harusdibayar / 10000) * 10;
					$expDidapat += ( $adaRatingOutlet == true ? $_ENV['bonusngeratingoutlet'] : 0 );
					$expDidapat += ( $adaLikeFB == true ? $_ENV['bonuslikefoodblogger'] : 0 );

					$exp = $user->exp;
					$exp = ($exp == "" ? 0 : $exp);
					$exp += $expDidapat;
					$user->exp = $exp;

					$levelnya = LevelExp::whereRaw("expneeded <= ?", array($exp))->get();
					$bykLevelBerapa = sizeof($levelnya);

					$user->levelsekarang = $levelnya[$bykLevelBerapa-1]->lvl;

					$user->save();

					$pes->statuspesanan = 8;
					$pes->save();

					return $this->hitungKuponDidapat();
				}
				else
				{
					$pe = "Error Na Kenapa Disini";
					return View::make("halaman.error.bukti", array("data" => $pe));
					//return "";
				}
			}
		}
		return "GAGAL";
	}
	private function hitungKuponDidapat(){
		$user = Auth::user();

		$idreset = $user->idresetpembcurr;
		$idpembcurrbaru = file_get_contents(storage_path()."/idpembcurrbaru.txt");

		if($idpembcurrbaru == ""){
			//ini harusnya ga pernah masuk sini, soalnya pasti admin udah pernah teken button yang generate idpembcurrbaru.txt
			//cuma gw iseng aja buatnya
			file_put_contents(storage_path()."/idpembcurrbaru.txt", "1");
		}

		if($idreset != $idpembcurrbaru){
			$user->idresetpembcurr = $idpembcurrbaru;
			$user->thnextkupon = NULL;
			$user->save();
		}

		$threshold = $user->thnextkupon;
		$threshold = ($threshold == "" ? $_ENV['defaultthresholdnextkupon'] : $threshold);
		$nilai = $user->pemb_curr;
		$kupondidapat = 0;
		while($nilai >= $threshold){
			$kupondidapat += $user->tambahKuponMiliknya();
			$nilai -= $threshold;

			$threshold = $user->getNextThreshold($threshold);
		}
		$user->pemb_curr = $nilai;
		$user->thnextkupon = $threshold;
		$user->save();

		$beda = $user->thnextkupon - $user->pemb_curr;
		$text = "SUKSES, untuk pembelian kali ini, anda mendapat: " . $kupondidapat . " kupon. Kupon Anda Sekarang : " . $user->kupon . " kupon";
		$text .= "<br/>Ayo Pesan Rp $beda Lagi untuk mendapatkan kupon berikutnya";
		return $text;


	}
	public function bukti(){

		$user = Auth::user();

		//awalnya selalu buat yang udah 6 jam di jadiin status == 12 dlu
		$thestime = date("Y-m-d H:i:s");
		$waktusekarangkurang6jam = date("Y-m-d H:i:s",strtotime("-6 hours",strtotime($thestime)));
		$allPes = Pesanan::whereRaw("idpemesan = ? and statuspesanan >= 5 and statuspesanan < 10 and tgl < ?", array($user->id, $waktusekarangkurang6jam))->get();
		$jalan = false;
		foreach ($allPes as $pesNya) { $pesNya->jadiinTooLong(); $jalan = true; }
		if($jalan == true){
			$temp = $user->setBykBelomUpload();
		}
		
		//baru lakukan untuk halaman tunjukan bukti
		$res = DB::table('pesanan')
            ->join('outlets', 'outlets.id', '=', 'pesanan.idoutlet')
            ->select('pesanan.id', 'outlets.nama', 'pesanan.statuspesanan', 'pesanan.tgl', 'pesanan.harusdibayar', 'outlets.id as idresto')
            ->whereRaw("statuspesanan is not null and idpemesan = ? and statuspesanan < 10", array($user->id))
            ->orderBy('pesanan.id', 'desc')
            ->take(20)
            ->get();

		$data = array(
			"res" => $res,
		);
		return View::make("bukti.tbukti", $data);
	}

	public function buktiCekBalasan(){
		$idpes = Input::get("idP");
		return sizeof(Balasan::whereRaw("idpesanan=?",array($idpes))->get());
	}

	public function uploadbukti($idPesanan){
		Session::put("idP_UpBukti", $idPesanan);
		$pes = Pesanan::whereRaw("id=?",array($idPesanan))->first();
		if($pes == NULL){
			return "Pesanan tidak ada atau sudah dihapus.";
		}else{
			if($pes->idpemesan != Auth::id() && !$pes->pemesanIsAdminLevel(100))
				return "Pesanan berikut bukan milik anda";

			if($pes->isBelomUpload() || $pes->pemesanIsAdminLevel(100)){
				$outlet = Outlet::whereRaw("id=?",array($pes->idoutlet))->first();

				$buktiGambar = array();
				$dest = public_path()."/"."buktiupload"."/".$idPesanan;
				if (file_exists($dest)) {
					$files = File::allFiles($dest);
					foreach ($files as $file)
					{	
						$testtemp = pathinfo($file);
						array_push($buktiGambar, $testtemp['basename']);
					}
				}

				$data = array(
					"idP" => $idPesanan,
					"pB" => $pes->getPointBeliBerikut(),
					"pE" => $pes->getPointExpBerikut(),
					"noResto" => $outlet->phone,
					"gbrUU" => $buktiGambar
				);
				return View::make("bukti.uploadbukti", $data);
			}else{
				return "Pesanan sudah pernah di upload. Bisa dilihat di <a href='/dpesanan/".$idPesanan."'>Riwayat Pemesanan</a>";
			}
		}
		
	}

	public function laporinResto(){
		if(Session::has("idP_UpBukti")){
			$idPesanan = Session::get("idP_UpBukti");
			$user = Auth::user();

			$pes = Pesanan::whereRaw("id=?", array($idPesanan))->first();
			if($pes != NULL){

				if($pes->idpemesan == $user->id){
					if($pes->isBelomUpload()){


						$pes->laporkan();

						return Redirect::to("/riwayat");
					}
				}
			}
		}

		return "GAGAL";
	}

	public function uploadKondisiDua(){
		if(Session::has("idP_UpBukti")){
			$idPesanan = Session::get("idP_UpBukti");
			$user = Auth::user();

			$pes = Pesanan::whereRaw("id=?", array($idPesanan))->first();
			if($pes != NULL){
				if($pes->idpemesan == $user->id){
					if($pes->isBelomUpload()){

						if(Input::hasFile('gbr0')){
							$adaImage = true;
						}else{
							$adaImage = false;
						}

						if($adaImage){

							$bykFileGambar = $this->BolehSimpanAllImageAndSizeChecked($idPesanan);
							if($bykFileGambar == -1){
								return "Ada File yang Sizenya Kegedean, Max 1 MB";
							}

							$pes->jadiinWaiting($bykFileGambar);

							return Redirect::to("/riwayat");
						}

					}
				}
			}
		}

		return "GAGAL";
	}

	public function uploadKondisiSatu(){
		if(Session::has("idP_UpBukti")){
			$idPesanan = Session::get("idP_UpBukti");
			$user = Auth::user();

			$pes = Pesanan::whereRaw("id=?", array($idPesanan))->first();
			if($pes != NULL){
				if($pes->idpemesan == $user->id){
					if($pes->isBelomUpload()){

						$kode = Input::get("kodenya");

						if(Input::hasFile('gbr0')){
							$adaImage = true;
						}else{
							$adaImage = false;
						}

						if($adaImage){
							$bykFileGambar = $this->BolehSimpanAllImageAndSizeChecked($idPesanan);
							if($bykFileGambar == -1){
								return "Ada File yang Sizenya Kegedean, Max 1 MB";
							}
						}else{
							$bykFileGambar = -1;
						}

						$pes->cekKodeUniqueServerDanUser($kode, $adaImage, $bykFileGambar);

						return Redirect::to("/riwayat");

					}
				}
			}
		}

		return "GAGAL";
	}
	private function BolehSimpanAllImageAndSizeChecked($idPesanan){
		$bolehSimpan = true;
		$batasMax = 1024 * 1024;
		$file = array();


		for($i=0;$i<8;$i++){
			if(Input::hasFile('gbr'.$i)){
				$tempFile = Input::file('gbr'.$i);

				if($tempFile->getSize() > $batasMax)
					return -1;

				array_push($file, $tempFile);
			}
		}

		$dest = public_path()."/"."buktiupload"."/".$idPesanan;
		mkdir($dest, 0777, false);

		$bykFile = sizeof($file);
		for($i=0;$i<$bykFile;$i++){
			$fileName = $file[$i]->getClientOriginalName();
			$titikfile = strrpos($fileName, ".");
			$nilainyaposisi = strlen($fileName) - $titikfile - 1;
			$ext = substr($fileName, -$nilainyaposisi, $nilainyaposisi);

			$namabaru = $i.".".$ext;
			$file[$i]->move($dest."/", $namabaru);
		}

		return $bykFile;
	}

	public function AdminNyatakanPalsu(){
		if(Session::has("idacp")){
			$acpid = Session::get("idacp");
			$alasan = Input::get("alasan");
			$panjang = strlen($alasan);
			if($panjang > 5 && $panjang <= 999){
				$acp = AdminCekPesanan::whereRaw("id=?", array($acpid))->first();
				$acp->statusapproval = 0;
				$acp->tgl = date("Y-m-d");
				$acp->idadmin = Auth::id();
				$acp->alasan = $alasan;

				$pes = Pesanan::whereRaw("id=?", array($acp->idpesanan))->first();
				if($pes->isWaitingAdmin()){
					$acp->save();
					$pes->adminVerifyPalsu();
				}

				return Redirect::to("/adminPanel/managePesanan");
			}
		}else{
			return "GAGAL, ga ada idacp";
		}
		return "gagal palsu";
	}
	public function AdminNyatakanValid(){
		if(Session::has("idacp")){
			$acpid = Session::get("idacp");

			$acp = AdminCekPesanan::whereRaw("id=?", array($acpid))->first();
			$acp->statusapproval = 1;
			$acp->tgl = date("Y-m-d");
			$acp->idadmin = Auth::id();

			$pes = Pesanan::whereRaw("id=?", array($acp->idpesanan))->first();
			if($pes->isWaitingAdmin()){
				$acp->save();
				$pes->adminVerifyValid();
			}

			return Redirect::to("/adminPanel/managePesanan");

		}else{
			return "GAGAL, ga ada idacp";
		}

		return "gagal valid";
	}

	public function getContohBukti(){

		$dir = public_path() . "/FS/asset/bukti/contoh";
		$files = File::allFiles($dir);
		return sizeof($files);
	}

	public function uploadGambarKondisi2Atau3(){
		if(Session::has("idP_UpBukti")){
			$idPesanan = Session::get("idP_UpBukti");
			if(Input::hasFile("gbr")){
				$batasMax = 5 * 1024 * 1024;
				$file = Input::file("gbr");
				if($file->getSize() <= $batasMax){
					$urutan = Input::get("urutan");

					$dest = public_path()."/"."buktiupload"."/".$idPesanan;
					if (!file_exists($dest)) {
						mkdir($dest, 0777, false);
					}

					$fileName = $file->getClientOriginalName();
					$titikfile = strrpos($fileName, ".");
					$nilainyaposisi = strlen($fileName) - $titikfile - 1;
					$ext = substr($fileName, -$nilainyaposisi, $nilainyaposisi);

					$namabaru = $urutan.".".$ext;
					$file->move($dest."/", $namabaru);

					return asset("/buktiupload/".$idPesanan."/".$namabaru);
				}
			}
		}

		return "GAGAL";
	}

	public function uploadDataKond2(){
		if(Session::has("idP_UpBukti")){
			$idPesanan = Session::get("idP_UpBukti");

			$bykimage = Input::get("bykimage");
			if($bykimage > 0){
				$dir = public_path() . "/buktiupload/".$idPesanan;
				$files = File::allFiles($dir);
				$bykFileGambar = sizeof($files);
				if($bykFileGambar >= $bykimage){
					$pes = Pesanan::whereRaw("id=?", array($idPesanan))->first();
					$pes->jadiinWaiting($bykFileGambar);

					return Redirect::to("/riwayat");
				}
			}
		}

		return "GAGAL";
	}
	public function uploadDataKond3(){

		if(Session::has("idP_UpBukti")){
			$idPesanan = Session::get("idP_UpBukti");
			$kode = Input::get("kodenya");
			if(strlen($kode) == 3){
				$bykimage = Input::get("bykimage");
				$dir = public_path() . "/buktiupload/".$idPesanan;
				$files = File::allFiles($dir);
				$bykFileGambar = sizeof($files);
				if($bykFileGambar >= $bykimage){
					if($bykFileGambar == 0)
						$adaImage = false;
					else
						$adaImage = true;
					$pes = Pesanan::whereRaw("id=?", array($idPesanan))->first();
					$pes->cekKodeUniqueServerDanUser($kode, $adaImage, $bykFileGambar);
					return Redirect::to("/riwayat");
				}
			}
		}
		return "GAGAL";
	}
	public function ratingSubmit(){
		if(Session::has("ratIdP")){
			$idP = Session::get("ratIdP");
			$pes = Pesanan::whereRaw("id=?", array($idP))->first();
			if($pes != NULL){
				$user = Auth::user();
				if($pes->idpemesan == $user->id){

					$rat = Input::get("jumlahrating");
					$com = Input::get("com");

					$portest = PemesanOutletRating::whereRaw("idpesanan = ?", array($idP))->first();
					if($portest == NULL){
						$por = new PemesanOutletRating;
						$por->idpemesan = Auth::id();
						$por->idoutlet = $pes->idoutlet;
						$por->tgl = date("Y-m-d H:i:s");
						$por->rating = $rat;
						$por->comment = $com;
						$por->idpesanan = $idP;
						$por->save();
					}else{
						$portest->rating = $rat;
						$portest->comment = $com;
						$portest->save();
					}

					return Redirect::back();
				}
			}
		}
		return "GAGAL";
	}
	public function rating($idP){

		$pes = Pesanan::whereRaw("id=?",array($idP))->first();
		if($pes != NULL){
			$user = Auth::user();
			if($pes->idpemesan == $user->id){
				Session::put("ratIdP", $idP);

				//apakah pesanan ini udah pernah di rating
				$por = PemesanOutletRating::whereRaw("idpesanan=?", array($idP))->first();
				$dataPORLama_Spesifik = array();				
				if($por != NULL){
					array_push($dataPORLama_Spesifik, $por->rating);
					array_push($dataPORLama_Spesifik, $por->comment);
				}

				//apakah resto ini udah pernah di rating, ambil avg rat sama all commentnya
				$pors = PemesanOutletRating::whereRaw("idpemesan=? and idoutlet = ?", array($pes->idpemesan, $pes->idoutlet))->take(30)->get();
				$bykpors = sizeof($pors);
				$dataPORLama = array();
				$avgrat = -1;
				if($bykpors >= 1){
					$totrat = 0;
					foreach($pors as $por_spesifik){
						$temp = array(
							"idpor" => $por_spesifik->idpesanan,
							"comment" => $por_spesifik->comment,
							"tgl" => $por_spesifik->tgl,
						);
						$totrat += $por_spesifik->rating;
						array_push($dataPORLama, $temp);
					}
					$avgrat = number_format(($totrat / sizeof($pors)), 1, ".", ",");
				}

				//nama restonya
				$namaResto = Outlet::whereRaw("id=?", array($pes->idoutlet))->first()->nama;

				//tampung datanya
				$data = array(
					"avgrat" => $avgrat,
					"dpl" => $dataPORLama,
					"dpls" => $dataPORLama_Spesifik,
					"nResto" => $namaResto,
				);
				
				return View::make("rating.makerating", $data);
			}
		}
		return "GAGAL";
		

	}

}

