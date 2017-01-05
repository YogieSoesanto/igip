<?php
class ManageMenuController extends BaseController {

	public function editKategori(){
		$json = Input::get("jsonNya");
		$json = json_decode($json);

		$nama = $json->nama;
		$idkateedit = $json->idkateedit;

		if(strlen($nama) < 2 || strlen($nama) > 30){
			return "ERR1 # Panjang Karakter Nama [2 - 30]. Panjang Karakter Sekarang = " . strlen($nama);
		}else{

			$ma = MA::whereRaw("id = ?",array($idkateedit))->first();
			$ma->nama = $nama;
			$ma->save();

			return "SUKSES";

		}

	}

	public function editMenu(){
		$json = Input::get("jsonNya");
		$json = json_decode($json);

		$nama = $json->nama;
		$namaK = $json->namaK;
		$harga = $json->harga;
		$desk = $json->desk;

		$favorit = ($json->favorit == "1" ? "1" : "0");
		$cabe = ($json->cabe == "1" ? "1" : "0");
		$hapus = ($json->hapus == "1" ? "1" : "0");

		$idmenuedit = $json->idmenuedit;

		if(strlen($nama) < 2 || strlen($nama) > 30){
			return "ERR1 # Panjang Karakter Nama [2 - 30]. Panjang Karakter Sekarang = " . strlen($nama);
		}
		else if(strlen($namaK) < 2 || strlen($namaK) > 30){
			return "ERR2  # Panjang Karakter Nama Kecil [2 - 30]. Panjang Karakter Sekarang = " . strlen($namaK);
		}
		else if(strlen($desk) < 0 || strlen($desk) > 200){
			return "ERR3  # Panjang Karakter Deskripsi [0 - 200]. Panjang Karakter Sekarang = " . strlen($desk);
		}else{

			$hargaNeh = intval($harga);
			if($hargaNeh == 0 && $harga != "0"){
				return "ERR4 # Harga Bukan Berupa Angka";
			}else{
				$mb = MB::whereRaw("id = ?",array($idmenuedit))->first();
				$mb->nama = $nama;
				$mb->namakecil = $namaK;
				$mb->harga = $harga;
				$mb->deskripsidetail = $desk;

				if($favorit == "1")
					$mb->isfavorit = "1";
				else
					$mb->isfavorit = NULL;

				if($cabe == "1")
					$mb->iscabe = "1";
				else
					$mb->iscabe = NULL;

				if($hapus == "1")
					$mb->linkgambar = NULL;

				$mb->save();

				return "SUKSES";
			}
			
		}	
	}

	public function editExtra(){
		$json = Input::get("jsonNya");
		$json = json_decode($json);

		$nama = $json->nama;
		$choice = ($json->choice == "1" ? "1" : "0");
		$idextraedit = $json->idextraedit;

		if(strlen($nama) < 2 || strlen($nama) > 20){
			return "ERR1 # Panjang Karakter Nama [2 - 20]. Panjang Karakter Sekarang = " . strlen($nama);
		}else{

			
			$mc = MC::whereRaw("id = ?",array($idextraedit))->first();
			$mc->nama = $nama;
			if($choice == "1")
				$mc->ischoice = "1";
			else
				$mc->ischoice = NULL;
			$mc->save();

			return "SUKSES";
			
		}	
	}

	public function editSubExtra(){
		$json = Input::get("jsonNya");
		$json = json_decode($json);

		$nama = $json->nama;
		$harga = $json->harga;
		$idsubextraedit = $json->idsubextraedit;

		if(strlen($nama) < 2 || strlen($nama) > 25){
			return "ERR1 # Panjang Karakter Nama [2 - 25]. Panjang Karakter Sekarang = " . strlen($nama);
		}else{

			$hargaNeh = intval($harga);
			if($hargaNeh == 0 && $harga != "0"){
				return "ERR4 # Harga Bukan Berupa Angka";
			}
			else{
				$md = MD::whereRaw("id = ?",array($idsubextraedit))->first();
				$md->nama = $nama;
				$md->additionalharga = $harga;
				$md->save();

				return "SUKSES";
			}

		}
	}

	public function gantiGambarMenu(){
		$idB = Input::get("idB");

		$size = Input::file('gbrMenu')->getSize(); 
		$fileName = Input::file('gbrMenu')->getClientOriginalName();
		//$ext = substr($fileName, -3, 3);
		$titikfile = strrpos($fileName, ".");
		$nilainyaposisi = strlen($fileName) - $titikfile - 1;
		$ext = substr($fileName, -$nilainyaposisi, $nilainyaposisi);
		$newName = $idB . "." . $ext;
		if($size > 512000){
			return "GAGAL";
		}else{
			$destinationPath = "foto/menu";
			Input::file('gbrMenu')->move($destinationPath, $newName);
			$res = MB::where('id','=',$idB)->first();
			$res->linkgambar = "." . $ext;
			$res->save();
			return $newName;
		}
	}

	public function openMenuResto(){
		$idO = Session::get('idOutletGanti');
		$boleh = false;
		$outlet = Outlet::whereRaw("id = ?",array($idO))->first();
		if($outlet->isreleased == "1"){

			if(UserAdmin::whereRaw("id=?",array(Auth::id()))->first()->privilegelevel > 1){
				$boleh = true;
			}
			else{
				
			}
		}else{
			$boleh = true;
		}

		if($boleh){
			$dataMenu = $outlet->ambilMenuOutlet();
			$arrPass = array(
				"dm" => $dataMenu,
				"idO" => $idO
			);
			return View::make('admin.menu', $arrPass);
		}else{
			$data = array(
				"nr" => $outlet->nama,
				"rb" => User::find($outlet->releasedby)->nama,
			);
			return View::make("admin.sudahreleased", $data);
		}
		
	}

	public function tambahKategori(){
		$idO = Session::get('idOutletGanti');
		$val = Input::get("val");

		if(strlen($val) < 2 || strlen($val) > 30){
			return "ERR1 # Panjang Karakter Nama [2 - 30]. Panjang Karakter Sekarang = " . strlen($nama);
		}else{
			$ma = new MA;
			$ma->idoutlet = $idO;
			$ma->nama = $val;
			$ma->save();

			return $ma->id;
		}
	}

	public function tambahMenu(){
		$json = Input::get("jsonNya");

		$json = json_decode($json);

		$nama = $json->nama;
		$namasingkat = $json->namas;
		$deskripsi = $json->desk;
		$harga = $json->harga;
		$isFavorit = ($json->fav == "1" ? "1" : "0");
		$isCabe = ($json->cabe == "1" ? "1" : "0");
		$idA = $json->idkate;

		if(strlen($nama) < 2 || strlen($nama) > 30){
			return "ERR1 # Panjang Karakter Nama [2 - 30]. Panjang Karakter Sekarang = " . strlen($nama);
		}
		else if(strlen($namasingkat) < 2 || strlen($namasingkat) > 30){
			return "ERR2  # Panjang Karakter Nama Kecil [2 - 30]. Panjang Karakter Sekarang = " . strlen($namasingkat);
		}
		else if(strlen($deskripsi) > 200){
			return "ERR3  # Panjang Karakter Deskripsi [0 - 200]. Panjang Karakter Sekarang = " . strlen($deskripsi);
		}
		else{
			$hargaNeh = intval($harga);
			if($hargaNeh == 0 && $harga != "0"){
				return "ERR4 # Harga Bukan Berupa Angka";
			}

			$mb = new MB;
			$mb->ida = $idA;
			$mb->nama = $nama;
			$mb->harga = $harga;
			$mb->deskripsidetail = $deskripsi;
			
			if($isFavorit == "1")
				$mb->isfavorit = "1";

			if($isCabe == "1")
				$mb->iscabe = "1";

			$mb->namakecil = $namasingkat;
			$mb->save();

			return $mb->id;
		}
	}
	public function tambahExtra(){
		$json = Input::get("jsonNya");

		$json = json_decode($json);

		$nama = $json->nama;

		$singlechoice = ($json->sc == "1" ? "1" : "0");

		$idB = $json->idmenu;

		if(strlen($nama) < 2 || strlen($nama) > 20){
			return "ERR1 # Panjang Karakter Nama [2 - 20]. Panjang Karakter Sekarang = " . strlen($nama);
		}else{
			$mc = new MC;
			$mc->idb = $idB;
			$mc->nama = $nama;
			if($singlechoice == "1")
				$mc->ischoice = "1";
			$mc->save();
			return $mc->id;
		}
	}
	public function tambahSubExtra(){
		$json = Input::get("jsonNya");

		$json = json_decode($json);

		$nama = $json->nama;
		$harga = $json->harga;

		$idC = $json->idextra;

		if(strlen($nama) < 2 || strlen($nama) > 25){
			return "ERR1 # Panjang Karakter Nama [2 - 25]. Panjang Karakter Sekarang = " . strlen($nama);
		}else{
			$hargaNeh = intval($harga);
			if($hargaNeh == 0 && $harga != "0"){
				return "ERR4 # Harga Bukan Berupa Angka";
			}else{
				$md = new MD;
				$md->idc = $idC;
				$md->nama = $nama;
				$md->additionalharga = $harga;
				$md->save();
				return $md->id;
			}
		}
	}

	public function hapusKategori(){
		$val = Input::get("val");

		$resA = DB::select("SELECT id FROM menu_b where ida = ?", array($val));
		$bykA = sizeof($resA);
		$textInA = "";
		for($i=0;$i<$bykA;$i++){
			$textInA .= $resA[$i]->id;
			if($i < $bykA - 1)
				$textInA .= ",";
		}

		if($textInA != ""){
			$this->hapusMenuRamean($textInA);
		}
			
		DB::delete("DELETE From menu_a where id = ?", array($val));
		return "SUKSES";
	}
	public function hapusMenu(){
		$val = Input::get("val");

		$resB = DB::select("SELECT id FROM menu_c where idb = ?", array($val));
		$bykB = sizeof($resB);
		$textInB = "";
		for($i=0;$i<$bykB;$i++){
			$textInB .= $resB[$i]->id;
			if($i < $bykB - 1)
				$textInB .= ",";
		}

		if($textInB != ""){
			$this->hapusExtraRamean($textInB);
		}
			
		DB::delete("DELETE From menu_b where id = ?", array($val));
		return "SUKSES";
	}
	public function hapusExtra(){
		$val = Input::get("val");

		$resC = DB::select("SELECT id FROM menu_d where idc = ?", array($val));
		$bykC = sizeof($resC);
		$textInC = "";
		for($i=0;$i<$bykC;$i++){
			$textInC .= $resC[$i]->id;
			if($i < $bykC - 1)
				$textInC .= ",";
		}

		if($textInC != ""){
			$this->hapusSubExtraRamean($textInC);
		}
			
		DB::delete("DELETE From menu_c where id = ?", array($val));
		return "SUKSES";
	}
	public function hapusSubExtra(){
		$val = Input::get("val");
		DB::delete("DELETE From menu_d where id = ?", array($val));
		return "SUKSES";
	}

	/*start buat hapus rekursif*/
	private function hapusMenuRamean($in){
		$resB = DB::select("SELECT id FROM menu_c where idb in (".$in.")");
		$bykB = sizeof($resB);
		$textInB = "";
		for($i=0;$i<$bykB;$i++){
			$textInB .= $resB[$i]->id;
			if($i < $bykB - 1)
				$textInB .= ",";
		}

		if($textInB != ""){
			$this->hapusExtraRamean($textInB);
		}

		DB::delete("DELETE From menu_b where id in (".$in.")");
	}
	private function hapusExtraRamean($in){
		$resC = DB::select("SELECT id From menu_d where idc in (".$in.")");
		$bykC = sizeof($resC);
		$textInC = "";
		for($i=0;$i<$bykC;$i++){
			$textInC .= $resC[$i]->id;
			if($i < $bykC - 1)
				$textInC .= ",";
		}

		if($textInC != ""){
			$this->hapusSubExtraRamean($textInC);
		}

		DB::delete("DELETE From menu_c where id in (".$in.")");
	}
	private function hapusSubExtraRamean($in){
		DB::delete("DELETE From menu_d where id in (".$in.")");
	}
	/*end buat hapus rekursif*/

}
