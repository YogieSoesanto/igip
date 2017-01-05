<?php
class RiwayatController extends BaseController {
	public function riwayat(){
		$user = Auth::user();

		$thestime = date("Y-m-d H:i:s");
		$waktusekarangkurang6jam = date("Y-m-d H:i:s",strtotime("-6 hours",strtotime($thestime)));
		$allPes = Pesanan::whereRaw("idpemesan = ? and statuspesanan >= 5 and statuspesanan < 10 and tgl < ?", array($user->id, $waktusekarangkurang6jam))->get();
		$jalan = false;
		foreach ($allPes as $pesNya) { $pesNya->jadiinTooLong(); $jalan = true; }
		if($jalan == true){
			$temp = $user->setBykBelomUpload();
		}

		$res = DB::table('pesanan')
            ->join('outlets', 'outlets.id', '=', 'pesanan.idoutlet')
            ->select('pesanan.id', 'outlets.nama', 'pesanan.statuspesanan', 'pesanan.tgl', 'pesanan.harusdibayar', 'outlets.id as idresto')
            ->whereRaw("statuspesanan is not null and idpemesan = ?", array($user->id))
            ->orderBy('pesanan.id', 'desc')
            ->take(20)
            ->get();

        /*
       		$query = "Select pesanan.id, outlets.nama, statuspesanan, tgl, harusdibayar 
			from outlets inner join pesanan on outlets.id = pesanan.idoutlet 
			WHERE statuspesanan is not null and idpemesan = ? 
			order by pesanan.id desc";
			$res = DB::select($query, array($user->id))->take();
		*/

		$data = array(
			"res" => $res,
		);
		return View::make("riwayat.riwayat", $data);
	}
	public function dataKe(){
		$user = Auth::user();
		$dataPerPage = Input::get("dpp");
		$pageSekarang = Input::get("ps");

		$mulai = ($pageSekarang - 1) * $dataPerPage;

		$res = DB::table('pesanan')
            ->join('outlets', 'outlets.id', '=', 'pesanan.idoutlet')
            ->select('pesanan.id', 'outlets.nama', 'pesanan.statuspesanan', 'pesanan.tgl', 'pesanan.harusdibayar', 'outlets.id as idresto')
            ->whereRaw("statuspesanan is not null and idpemesan = ?", array($user->id))
            ->orderBy('pesanan.id', 'desc')
            ->skip($mulai)
            ->take($dataPerPage)
            ->get();

        return json_encode($res);
	}

	public function statusUploadBukti(){
		$user = Auth::user();

		$thestime = date("Y-m-d H:i:s");
		$waktusekarangkurang6jam = date("Y-m-d H:i:s",strtotime("-6 hours",strtotime($thestime)));
		$allPes = Pesanan::whereRaw("idpemesan = ? and statuspesanan >= 5 and statuspesanan < 10 and tgl < ?", array($user->id, $waktusekarangkurang6jam))->get();
		$jalan = false;
		foreach ($allPes as $pesNya) { $pesNya->jadiinTooLong(); $jalan = true; }
		if($jalan == true){
			$temp = $user->setBykBelomUpload();
		}

		$query = "Select pesanan.id, outlets.nama, statuspesanan, tgl, harusdibayar from outlets inner join pesanan on outlets.id = pesanan.idoutlet 
					WHERE statuspesanan >= 10 and idpemesan = ? and pesanan.id in(select idpesanan from userreadnotif where iduser=?)";
		$res = DB::select($query, array($user->id, $user->id));

		$data = array(
			"res" => $res,
		);

		return View::make("riwayat.sub", $data);
	}

	public function statusHapusNotif(){
		$ip = Input::get("ip");
		$user = Auth::user();
		$pes = Pesanan::whereRaw("id = ? and idpemesan=?", array($ip, $user->id))->first();
		if($pes != NULL){
			$urn = UserReadNotif::whereRaw("iduser=? and idpesanan=?", array($user->id, $ip))->first();			
			$urn->lakukanDelete();
		}
		return "OK";
	}
	public function statusHapusAllNotif(){
		$user = Auth::user();
		
		$urns = UserReadNotif::whereRaw("iduser=?", array($user->id))->get();
		foreach ($urns as $urn) { $urn->lakukanDelete(); }
	
		return "OK";
	}
	
	public function lihatAlasanPenolakan($idP){
		$user = Auth::user();
		$pes = Pesanan::whereRaw("id=? and idpemesan=?", array($idP, $user->id))->first();	
		if($pes != NULL){
			$acp = AdminCekPesanan::whereRaw("idpesanan = ?", array($idP))->first();
			if($acp != NULL){

				$namaext = array();

				$idP = $acp->idpesanan;
				$dir = public_path() . "/buktiupload/".$idP;
				$files = File::allFiles($dir);
				foreach ($files as $file)
				{	
					$testtemp = pathinfo($file);
					array_push($namaext, $testtemp['basename']);
				}

				$data = array(
					"res" => $acp,
					"namaext" => $namaext,
				);
				return View::make("riwayat.alasanpenolakan", $data);	
			}
		}
		return "GAGAL";
	}

	public function detailPesanan($idP){
		$user = Auth::user();
		$pes = Pesanan::whereRaw("id=?", array($idP))->first();
		if($pes != NULL){
			if($pes->idpemesan == $user->id){
				$data = array(
					"head" => $pes->getHeaderPesanan(),
					"det" => $pes->getDetailPesanan(),
					"idP" => $idP
				);
				//return $data;
				return View::make("riwayat.detailpesanan", $data);
			}
		}
		return "GAGAL";
	}

	public function cart(){
		$user = Auth::user();
		$data = array();
		$arrIdOutlet = array();
		$kers = Keranjang::whereRaw("iduser=?", array($user->id))->get();
		foreach($kers as $ker){

			$idoutlet = $ker->idoutlet;
			$hasilSearch = array_search($idoutlet, $arrIdOutlet);
			if($hasilSearch === FALSE){
				//jika idoutlet belom terdaftar pada arrIdOutlet, maka insert dulu
				array_push($arrIdOutlet, $idoutlet);

				$temp = array();
				$temp['id'] = $idoutlet;
				$temp['nama'] = Outlet::whereRaw("id=?",array($idoutlet))->first()->nama;
				$temp['menuNya'] = array();
					$namaMenu = MB::whereRaw("id=?", array($ker->idmenu))->first()->nama;
					$tempLagi = array(
						"nama" => $namaMenu,
						"qty" => $ker->quantity
					);
					array_push($temp['menuNya'], $tempLagi);

				array_push($data, $temp);
			}else{
				$namaMenu = MB::whereRaw("id=?", array($ker->idmenu))->first()->nama;
				$tempLagi = array(
					"nama" => $namaMenu,
					"qty" => $ker->quantity
				);
				array_push($data[$hasilSearch]['menuNya'], $tempLagi);


			}
		}
		//return $data;
		$data2 = array(
			"res" => $data,
		);
		return View::make("riwayat.cart" , $data2);
		
	}
	
}
