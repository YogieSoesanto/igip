<?php
class SearchEngineController extends BaseController {

	private $batasLastUpdateForSearch;
	private $waktuSekarang;
	private $persentageForRatingBiasa;
	private $persentageForRatingFFB;
	private $persentageForCategory;
	private $arrayCategoryDanCountPesannya;
	private $arrayForQuickSortIdOutlet;
	private $arrayForQuickSortPointOutlet;
	public function adaPesananForAllOutlet(){
		/*$idOutlet = array();
		$arrayPointOutletIni = array();
		$temp = Outlet::whereRaw("isReleased is not null")->get();
		foreach($temp as $outlet){
			array_push($idOutlet, $outlet->id);
			array_push($arrayPointOutletIni, 0);
		}

		$this->arrayForQuickSortIdOutlet = $idOutlet;
		$this->arrayForQuickSortPointOutlet = $arrayPointOutletIni;

		//hapus session dulu sebelum pindah
		Session::forget("outletYangAkanDiSearchBuatUserIni");
		Session::forget("userYangLagiSearch");
		Session::forget("longitudeUserForSearch");
		Session::forget("latitudeUserForSearch");

		//buat session untuk view di chosenOutlet
		Session::put("outletChosenForViewAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForViewAfterSearch", $this->arrayForQuickSortPointOutlet);
		Session::put("outletChosenForFilterAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch", $this->arrayForQuickSortPointOutlet);
*/
		return Redirect::to("/pilihresto");
		
	}
	public function detailOutlet($idOutletDipilih){
		$outlet = Outlet::whereRaw("id=?", array($idOutletDipilih))->first();
		$data = array(
			"dataMenu" => $outlet->ambilMenuOutlet(),
			"dataProfile" => $outlet->ambilProfileOutlet()
		);
		Session::put("lagiBukaIdOutlet", $idOutletDipilih);
		//return $outlet->ambilMenuOutlet();
		return View::make("pesan.pilihmenu", $data);
	}
	public function detailOutlet2($idOutletDipilih){
		$outlet = Outlet::whereRaw("id=?", array($idOutletDipilih))->first();
		$data = array(
			"dataMenu" => $outlet->ambilMenuOutlet(),
			"dataProfile" => $outlet->ambilProfileOutlet()
		);
		Session::put("lagiBukaIdOutlet", $idOutletDipilih);
		//return $outlet->ambilMenuOutlet();
		return View::make("halaman.detailoutlet", $data);
	}

	public function terjadiFilter(){

		$budgetMin = Input::get('budgetMin');
		$budgetMax = Input::get('budgetMax');
		$khusushalal = Input::get('khusushalal');
		$khususbalas = Input::get('khususbalas');
		//$idOutlet = Session::get("outletChosenForFilterAfterSearch");
		$idOutlet = Session::get("outletChosenForViewAfterSearch");
		$matchRateIdOutlet = Session::get("tingkatKecocokanOutletChosenForViewAfterSearch");
		
		$idOutletTemp = array();
		$matchRateTemp = array();
		//echo $budgetMin . "#" . $budgetMax;

		$idBudgetMin = Budget::whereRaw("budget_min = ?", array($budgetMin))->first()->id;
		$idBudgetMax = Budget::whereRaw("budget_max = ?", array($budgetMax))->first()->id;
		

		$byk = sizeof($idOutlet);
		for($i=0;$i<$byk;$i++){
			$temp = Outlet::whereRaw('id=?',array($idOutlet[$i]))->first();
			if($temp->price_range >= $idBudgetMin && $temp->price_range <= $idBudgetMax){
				if($khususbalas == "on"){
					if($temp->nobalas != "1"){
						//kemungkinan insert nih, tapi cek halal dulu
						if($khusushalal == "on"){
							if($temp->nonhalal != "1"){
								array_push($idOutletTemp, $idOutlet[$i]);
								array_push($matchRateTemp, $matchRateIdOutlet[$i]);
							}
						}else{
							array_push($idOutletTemp, $idOutlet[$i]);
							array_push($matchRateTemp, $matchRateIdOutlet[$i]);
						}
					}
				}else{
					//jika ga khusus balas, jadi artinya boleh juga yang balas dan yang ga balas
					if($khusushalal == "on"){
						if($temp->nonhalal != "1"){
							array_push($idOutletTemp, $idOutlet[$i]);
							array_push($matchRateTemp, $matchRateIdOutlet[$i]);
						}
					}else{
						//jika ga khusus balas, juga ga khusus halal, artinya boleh balas / ga balas, dan boleh halal / ga halal (SEMUA)
						array_push($idOutletTemp, $idOutlet[$i]);
						array_push($matchRateTemp, $matchRateIdOutlet[$i]);
					}
				}
			}
		}

		$budgetMin2 = $this->kasihKoma($budgetMin);
		$budgetMax2 = $this->kasihKoma($budgetMax);
		$namaFilter = $budgetMin2."##".$budgetMax2."##".$budgetMin."##".$budgetMax."##".($khusushalal == "on"?"1" : "0")."##".($khususbalas == "on"?"1" : "0");
		Session::put("namaFilter", $namaFilter);
		
		Session::put("outletChosenForFilterAfterSearch",$idOutletTemp);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch",$matchRateTemp);
		
		$dataBudget = $this->getMinMaxBudget();
		return View::make("pesan.pilihresto", array(
			"idOutlet"=>$idOutletTemp,
			"dataMinBudget" => $dataBudget[0],
			"dataMaxBudget" => $dataBudget[1],
		));
	}

	/*public function terjadiFilter3(){
		$budgetMin = Input::get('budgetMin');
		$budgetMax = Input::get('budgetMax');
		$khusushalal = Input::get('khusushalal');
		$khususbalas = Input::get('khususbalas');
		$idOutlet = Session::get("outletChosenForViewAfterSearch");
		$matchRateIdOutlet = Session::get("tingkatKecocokanOutletChosenForViewAfterSearch");
		
		$idOutletTemp = array();
		$matchRateTemp = array();
		//echo $budgetMin . "#" . $budgetMax;

		$idBudgetMin = Budget::whereRaw("budget_min = ?", array($budgetMin))->first()->id;
		$idBudgetMax = Budget::whereRaw("budget_max = ?", array($budgetMax))->first()->id;
		

		$byk = sizeof($idOutlet);
		for($i=0;$i<$byk;$i++){
			$temp = Outlet::whereRaw('id=?',array($idOutlet[$i]))->first();
			if($temp->price_range >= $idBudgetMin && $temp->price_range <= $idBudgetMax){
				if($khususbalas == "on"){
					if($temp->nobalas != "1"){
						//kemungkinan insert nih, tapi cek halal dulu
						if($khusushalal == "on"){
							if($temp->nonhalal != "1"){
								array_push($idOutletTemp, $idOutlet[$i]);
								array_push($matchRateTemp, $matchRateIdOutlet[$i]);
							}
						}else{
							array_push($idOutletTemp, $idOutlet[$i]);
							array_push($matchRateTemp, $matchRateIdOutlet[$i]);
						}
					}
				}else{
					//jika ga khusus balas, jadi artinya boleh juga yang balas dan yang ga balas
					if($khusushalal == "on"){
						if($temp->nonhalal != "1"){
							array_push($idOutletTemp, $idOutlet[$i]);
							array_push($matchRateTemp, $matchRateIdOutlet[$i]);
						}
					}else{
						//jika ga khusus balas, juga ga khusus halal, artinya boleh balas / ga balas, dan boleh halal / ga halal (SEMUA)
						array_push($idOutletTemp, $idOutlet[$i]);
						array_push($matchRateTemp, $matchRateIdOutlet[$i]);
					}
				}
			}
		}
		$budgetMin2 = $this->kasihKoma($budgetMin);
		$budgetMax2 = $this->kasihKoma($budgetMax);
		$namaFilter = $budgetMin2."##".$budgetMax2."##".$budgetMin."##".$budgetMax."##".($khusushalal == "on"?"1" : "0")."##".($khususbalas == "on"?"1" : "0");
		Session::put("namaFilter", $namaFilter);
		
		Session::put("outletChosenForFilterAfterSearch",$idOutletTemp);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch",$matchRateTemp);
		$dataBudget = $this->getMinMaxBudget();
		return View::make("pesan.pilihresto", array(
			"idOutlet"=>$idOutletTemp,
			"dataMinBudget" => $dataBudget[0],
			"dataMaxBudget" => $dataBudget[1],
		));
	}*/
	
	public function terjadiFilter2(){
		$budgetMin = Input::get('budgetMin');
		$budgetMax = Input::get('budgetMax');
		$khusushalal = Input::get('khusushalal');
		$khususbalas = Input::get('khususbalas');
		$idOutlet = Session::get("outletChosenForViewAfterSearch");
		$matchRateIdOutlet = Session::get("tingkatKecocokanOutletChosenForViewAfterSearch");
		
		$idOutletTemp = array();
		$matchRateTemp = array();
		//echo $budgetMin . "#" . $budgetMax;

		$idBudgetMin = Budget::whereRaw("budget_min = ?", array($budgetMin))->first()->id;
		$idBudgetMax = Budget::whereRaw("budget_max = ?", array($budgetMax))->first()->id;
		

		$byk = sizeof($idOutlet);
		for($i=0;$i<$byk;$i++){
			$temp = Outlet::whereRaw('id=?',array($idOutlet[$i]))->first();
			if($temp->price_range >= $idBudgetMin && $temp->price_range <= $idBudgetMax){
				if($khususbalas == "on"){
					if($temp->nobalas != "1"){
						//kemungkinan insert nih, tapi cek halal dulu
						if($khusushalal == "on"){
							if($temp->nonhalal != "1"){
								array_push($idOutletTemp, $idOutlet[$i]);
								array_push($matchRateTemp, $matchRateIdOutlet[$i]);
							}
						}else{
							array_push($idOutletTemp, $idOutlet[$i]);
							array_push($matchRateTemp, $matchRateIdOutlet[$i]);
						}
					}
				}else{
					//jika ga khusus balas, jadi artinya boleh juga yang balas dan yang ga balas
					if($khusushalal == "on"){
						if($temp->nonhalal != "1"){
							array_push($idOutletTemp, $idOutlet[$i]);
							array_push($matchRateTemp, $matchRateIdOutlet[$i]);
						}
					}else{
						//jika ga khusus balas, juga ga khusus halal, artinya boleh balas / ga balas, dan boleh halal / ga halal (SEMUA)
						array_push($idOutletTemp, $idOutlet[$i]);
						array_push($matchRateTemp, $matchRateIdOutlet[$i]);
					}
				}
			}
		}
		$budgetMin2 = $this->kasihKoma($budgetMin);
		$budgetMax2 = $this->kasihKoma($budgetMax);
		$namaFilter = $budgetMin2."##".$budgetMax2."##".$budgetMin."##".$budgetMax."##".($khusushalal == "on"?"1" : "0")."##".($khususbalas == "on"?"1" : "0");
		Session::put("namaFilter", $namaFilter);
		
		Session::put("outletChosenForFilterAfterSearch",$idOutletTemp);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch",$matchRateTemp);
		$dataBudget = $this->getMinMaxBudget();
		return View::make("halaman.tampilinOutlet", array(
			"idOutlet"=>$idOutletTemp,
			"dataMinBudget" => $dataBudget[0],
			"dataMaxBudget" => $dataBudget[1],
		));
	}
	public function dataKe($dataPerPage, $pageSekarang){
		$idOutlet = Session::get("outletChosenForFilterAfterSearch");
		$matchRateIdOutlet = Session::get("tingkatKecocokanOutletChosenForFilterAfterSearch");
		$mulai = ($pageSekarang - 1) * $dataPerPage;
		$sampai = $pageSekarang * $dataPerPage;
		$byk = sizeof($idOutlet);

		$arrKasihKeDepan = array();


		for($i=$mulai ; $i<$byk;$i++){
			if($i == $sampai)
				break;

			$nilaiRekomendasi = $matchRateIdOutlet[$i];
			$outletNya = Outlet::whereRaw('id=?', array($idOutlet[$i]))->first();
			$arrTemp = $outletNya->giveDataForShow($nilaiRekomendasi);
			
			array_push($arrKasihKeDepan, $arrTemp);
		}
		return json_encode($arrKasihKeDepan);
	}
	public function dataKe2($dataPerPage, $pageSekarang){
		$idOutlet = Session::get("outletChosenForFilterAfterSearch");
		$matchRateIdOutlet = Session::get("tingkatKecocokanOutletChosenForFilterAfterSearch");
		$mulai = ($pageSekarang - 1) * $dataPerPage;
		$sampai = $pageSekarang * $dataPerPage;
		$byk = sizeof($idOutlet);

		$arrKasihKeDepan = array();


		for($i=$mulai ; $i<$byk;$i++){
			if($i == $sampai)
				break;

			$nilaiRekomendasi = $matchRateIdOutlet[$i];
			$outletNya = Outlet::whereRaw('id=?', array($idOutlet[$i]))->first();
			$arrTemp = $outletNya->giveDataForShow($nilaiRekomendasi);
			
			array_push($arrKasihKeDepan, $arrTemp);
		}
		return json_encode($arrKasihKeDepan);
	}
	/*public function showChosenOutlet(){
		$idOutlet = array();
		$arrayPointOutletIni = array();
		$temp = Outlet::whereRaw("isReleased is not null")->get();
		foreach($temp as $outlet){
			array_push($idOutlet, $outlet->id);
			array_push($arrayPointOutletIni, 0);
		}

		$this->arrayForQuickSortIdOutlet = $idOutlet;
		$this->arrayForQuickSortPointOutlet = $arrayPointOutletIni;

		//hapus session dulu sebelum pindah
		Session::forget("outletYangAkanDiSearchBuatUserIni");
		Session::forget("userYangLagiSearch");
		Session::forget("longitudeUserForSearch");
		Session::forget("latitudeUserForSearch");

		//buat session untuk view di chosenOutlet
		Session::put("outletChosenForViewAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForViewAfterSearch", $this->arrayForQuickSortPointOutlet);
		Session::put("outletChosenForFilterAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch", $this->arrayForQuickSortPointOutlet);


		Session::forget("namaFilter");
		$idOutlet = Session::get("outletChosenForViewAfterSearch");
		$dataBudget = $this->getMinMaxBudget();
		return View::make("pesan.pilihresto", array(
			"idOutlet"=>$idOutlet,
			"dataMinBudget" => $dataBudget[0],
			"dataMaxBudget" => $dataBudget[1],
		));
	}*/
	public function showChosenOutlet(){
		$idOutlet = array();
		$arrayPointOutletIni = array();
		$temp = Outlet::whereRaw("isReleased is not null")->get();
		foreach($temp as $outlet){
			array_push($idOutlet, $outlet->id);

			array_push($arrayPointOutletIni, 0);
		}

		Session::put("outletChosenForViewAfterSearch", $idOutlet);
		Session::put("tingkatKecocokanOutletChosenForViewAfterSearch", $arrayPointOutletIni);
		Session::put("outletChosenForFilterAfterSearch", $idOutlet);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch", $arrayPointOutletIni);

		$dataBudget = $this->getMinMaxBudget();
		return View::make("pesan.pilihresto", array(
			"idOutlet"=>$idOutlet,
			"dataMinBudget" => $dataBudget[0],
			"dataMaxBudget" => $dataBudget[1],
		));
	}
	public function showChosenOutlet2(){
		Session::forget("namaFilter");
		$idOutlet = Session::get("outletChosenForViewAfterSearch");
		$dataBudget = $this->getMinMaxBudget();
		return View::make("halaman.tampilinOutlet", array(
			"idOutlet"=>$idOutlet,
			"dataMinBudget" => $dataBudget[0],
			"dataMaxBudget" => $dataBudget[1],
		));
	}
	
	public function lakukanSearchManual($nama, $idProvinsi, $kategori){
		
		

		$kategori = str_replace("_", ",", $kategori);
		//echo $nama;
		$outlet = array();
		if($nama != "-1")
			$outlet = Outlet::where('nama', 'LIKE', '%'.$nama.'%');
		else
			$outlet = Outlet::where('id' ,'>' ,'0');

		/*$byk = sizeof($outlet);
		echo $byk;*/


		if($kategori != "-1")
			$outlet = $outlet->whereRaw('category IN ('.$kategori.')');

		if($idProvinsi != "-1"){
			$ldy = Lokasi::where("iddariyogieprov", '=', $idProvinsi)->get();
			$bykData = sizeof($ldy);
			$prov = "";
			for($i=0;$i<$bykData;$i++){
				$prov .= $ldy[$i]->id;
				if($i < $bykData -1)
					$prov .= ",";
			}
			//echo $idProvinsi."#";
			//echo $prov;
			//echo 'idLokasiDariYogie IN ('.$prov.')'."#";
			$outlet = $outlet->whereRaw('idlokasidariyogie IN ('.$prov.')');
		}
		$outlet = $outlet->whereRaw('isReleased is not null');
		$outlet = $outlet->get();
		$byk = sizeof($outlet);
		$idOutlet = array();
		for($i=0;$i<$byk;$i++){
			array_push($idOutlet, $outlet[$i]->id);
		}
		Session::put("outletYangAkanDiSearchBuatUserIni", $idOutlet);
		
		if(sizeof($idOutlet) > 0){
			return $this->mulaiSearchOutletTerbaik();
		}else{
			return $this->tampilinNamaPesanError(8700);
		}

		//return sizeof($outlet);
	}
	public function lakukanSearchOtomatis($provinsi, $kotakab, $latUser, $lngUser){
		$isAdaProv = false;
		$isAdaKK = false;
		$prov = Provinsi::whereRaw("nama like ?", array($provinsi))->first();
		if($prov != null){
			$isAdaProv = true;
		}else{
			$lala = new Provinsi;
			$lala->nama = $provinsi;
			$lala->save();
		}

		$kk = KotaKab::whereRaw("nama like ?", array($kotakab))->first();
		if($kk != null){
			$isAdaKK = true;
		}else{
			$lala2 = new KotaKab;
			$lala2->nama = $kotakab;
			$lala2->save();
		}	

		
		
		if($isAdaKK && $isAdaProv){

			$lok = Lokasi::whereRaw('iddariyogieprov = ? and iddariyogiekotakab = ?' , array($prov->iddariyogie, $kk->iddariyogie))->first();
			if($lok == null){
				return $this->tampilinNamaPesanError(8900);
			}else{
				
				//$outlet = Outlet::whereRaw('idLokasiDariYogie = ? and idLokasiDariYogie is not null' , array($lok->id))->get();
				//$outlet = DB::select("select id from outlets where idlokasidariyogie = ?" , array($lok->id));
				$outlet = DB::select("select id from outlets where isReleased is not null and idlokasidariyogie = ?" , array($lok->id));
				$byk = sizeof($outlet);

				if($byk == 0){
					return $this->tampilinNamaPesanError(8800);
				}else{
					$idOutlet = array();
					for($i=0;$i<$byk;$i++){
						array_push($idOutlet, $outlet[$i]->id);
					}
					Session::put("outletYangAkanDiSearchBuatUserIni", $idOutlet);
					Session::put("latitudeUserForSearch", $latUser);
					Session::put("longitudeUserForSearch", $lngUser);
					return $this->filterLagiDenganTerdekat();
				}
			}
		}else{
			return $this->tampilinNamaPesanError(8900);
		}
		
		/*$lokasi = Lokasi::whereRaw('Provinsi = ? and KotaKab = ?' , array($provinsi, $kotakab))->first();
		Session::put("latitudeUserForSearch", $latUser);
		Session::put("longitudeUserForSearch", $lngUser);
		if($lokasi != null){
			$nilaiKotaKab = $lokasi->nilaiDariYogie;
			$outlet = Outlet::whereRaw('idkotakabdariyogie = ?' , array($nilaiKotaKab))->get();
			$byk = sizeof($outlet);
			if($byk == 0){
				return $this->tampilinNamaPesanError(8800);
			}else{
				$idOutlet = array();
				for($i=0;$i<$byk;$i++){
					array_push($idOutlet, $outlet[$i]->id);
				}
				Session::put("outletYangAkanDiSearchBuatUserIni", $idOutlet);
				
				return $this->filterLagiDenganTerdekat();
			}
		}else{
			return $this->tampilinNamaPesanError(8900);
		}*/
	}
	private function filterLagiDenganTerdekat(){
		
		$outletBuatStartSearch = array();
		$idOutlet = Session::get("outletYangAkanDiSearchBuatUserIni");
		$byk = sizeof($idOutlet);
		$latitudeUser = Session::get("latitudeUserForSearch");
		//$latitudeUser = -6.202164077613272;
		$longitudeUser = Session::get("longitudeUserForSearch");
		//$longitudeUser = 106.80384635925293;
		for($i=0;$i<$byk;$i++){

			$outletTestNya = Outlet::whereRaw('id=?',array($idOutlet[$i]))->first();
			
			if($outletTestNya->nocircle == ""){
				$nilaiRetNya = $this->distance($outletTestNya->latitude, $outletTestNya->longitude, $latitudeUser, $longitudeUser);
				if($nilaiRetNya <= $outletTestNya->radius){
					array_push($outletBuatStartSearch, $idOutlet[$i]);
				}
			}else{
				$outletVertices = OutletVertices::whereRaw("idoutlet=?", array($idOutlet[$i]))->get();
				$bykPoly = sizeof($outletVertices);
				if($bykPoly > 2){
					$array_vertices_lng = array();
					$array_vertices_lat = array();
					for($xx=0;$xx<$bykPoly;$xx++){
						array_push($array_vertices_lat, $outletVertices[$xx]->lat);
						array_push($array_vertices_lng, $outletVertices[$xx]->lng);
					}
					$pointsPolygon = $bykPoly - 1; 			//in zero based array
					$nilaiRetNya = $this->is_in_polygon($pointsPolygon, $array_vertices_lng, $array_vertices_lat, $longitudeUser, $latitudeUser);
					if($nilaiRetNya == true){
						array_push($outletBuatStartSearch, $idOutlet[$i]);
					}
				}
			}
		}
		Session::put("outletYangAkanDiSearchBuatUserIni", $outletBuatStartSearch);
		
		//return "baba";
		return $this->mulaiSearchOutletTerbaik();
	}
	private function mulaiSearchOutletTerbaik(){
		//$this->batasLastUpdateForSearch = 0.0416;		//1 jam itu 0.0416 hari 
		$waktuSekarang = date('Y-m-d H:i:s',time());
		
		$this->waktuSekarang = $waktuSekarang;
		$thestime = date("Y-m-d H:i:s");
		$strWaktu1 = date("Y-m-d H:i:s",strtotime("-1 hours",strtotime($thestime)));
		$this->batasLastUpdateForSearch = $strWaktu1;
		

		$this->persentageForRatingBiasa = $_ENV['PERSENTAGE_SEARCH_BIASA'];
		$this->persentageForRatingFFB = $_ENV['PERSENTAGE_SEARCH_FB'];
		$this->persentageForCategory = 40;

		$this->arrayCategoryDanCountPesannya = array();

		$idOutlet = Session::get("outletYangAkanDiSearchBuatUserIni");
		$idUser = Session::get("userYangLagiSearch");
		
		//disini $this->arrayCategoryDanCountPesannya bakal keiisi
		$totalCountSemuaCategory = $this->getPointFromCategory($idUser);

		$byk = sizeof($idOutlet);

		//lagi ambil Point untuk masing-masing outlet
		$arrayPointOutletIni = array();

		for($i=0;$i<$byk;$i++){
			$idOutletTest = $idOutlet[$i];

			$arrayPointOutletIni[$i] = $this->getPointForThisOutlet($idOutletTest, $totalCountSemuaCategory);
		}
		//sorting berdasarkan point dan idoutlet
		$this->arrayForQuickSortIdOutlet = $idOutlet;
		$this->arrayForQuickSortPointOutlet = $arrayPointOutletIni;
		$this->quickSort($this->arrayForQuickSortPointOutlet);

		//hapus session dulu sebelum pindah
		Session::forget("outletYangAkanDiSearchBuatUserIni");
		Session::forget("userYangLagiSearch");
		Session::forget("longitudeUserForSearch");
		Session::forget("latitudeUserForSearch");

		//buat session untuk view di chosenOutlet
		Session::put("outletChosenForViewAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForViewAfterSearch", $this->arrayForQuickSortPointOutlet);
		Session::put("outletChosenForFilterAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForFilterAfterSearch", $this->arrayForQuickSortPointOutlet);
		//echo sizeof(Session::get("outletChosenForViewAfterSearch"));
		return $this->tampilinNamaPesanError(1);
	}
	/*
	//kemungkinan ini bisa ga dipake lagi, soalnya pake nya ajax biar isa loading dulu
	public function lakukanSearchOtomatis2($provinsi, $kotakab, $latUser, $lngUser){
		$lokasi = Lokasi::whereRaw('Provinsi = ? and KotaKab = ?' , array($provinsi, $kotakab))->first();
		Session::put("latitudeUserForSearch", $latUser);
		Session::put("longitudeUserForSearch", $lngUser);
		if($lokasi != null){
			$nilaiKotaKab = $lokasi->nilaiDariYogie;
			$outlet = Outlet::whereRaw('idkotakabdariyogie = ?' , array($nilaiKotaKab))->get();
			$byk = sizeof($outlet);
			if($byk == 0){
				return $this->adaError(8800);
			}else{
				$idOutlet = array();
				for($i=0;$i<$byk;$i++){
					array_push($idOutlet, $outlet[$i]->id);
				}
				Session::put("outletYangAkanDiSearchBuatUserIni", $idOutlet);
				return Redirect::to("/search/prestart");
			}
		}else{
			return $this->adaError(8900, $provinsi."#".$kotakab);
		}
	}*/
	/*
	//kemungkinan ini bisa ga dipake lagi, soalnya pake nya ajax biar isa loading dulu
	public function preStartSearch(){
		$outletBuatStartSearch = array();
		$idOutlet = Session::get("outletYangAkanDiSearchBuatUserIni");
		$byk = sizeof($idOutlet);
		$latitudeUser = Session::get("latitudeUserForSearch");
		//$latitudeUser = -6.202164077613272;
		$longitudeUser = Session::get("longitudeUserForSearch");
		//$longitudeUser = 106.80384635925293;
		for($i=0;$i<$byk;$i++){
			$outletTestNya = Outlet::whereRaw('id=?',array($idOutlet[$i]));
			
			if($outletTestNya->nocircle == ""){
				$nilaiRetNya = $this->distance($outletTestNya->latitude, $outletTestNya->longitude, $latitudeUser, $longitudeUser);
				if($nilaiRetNya <= $outletTestNya->radius){
					array_push($outletBuatStartSearch, $idOutlet[$i]);
				}
			}else{
				$outletVertices = OutletVertices::whereRaw("idOutlet=?", array($idOutlet[$i]))->get();
				$bykPoly = sizeof($outletVertices);
				if($bykPoly > 2){
					$array_vertices_lng = array();
					$array_vertices_lat = array();
					for($xx=0;$xx<$bykPoly;$xx++){
						array_push($array_vertices_lat, $outletVertices[$xx]->lat);
						array_push($array_vertices_lng, $outletVertices[$xx]->lng);
					}
					$pointsPolygon = $bykPoly - 1; 			//in zero based array
					$nilaiRetNya = $this->is_in_polygon($pointsPolygon, $array_vertices_lng, $array_vertices_lat, $longitudeUser, $latitudeUser);
					if($nilaiRetNya == true){
						array_push($outletBuatStartSearch, $idOutlet[$i]);
					}
				}
			}
		}
		Session::put("outletYangAkanDiSearchBuatUserIni", $outletBuatStartSearch);
		return Redirect::to("/search/start");
	}
	public function startSearch(){
		$this->batasLastUpdateForSearch = 0.0416;		//1 jam itu 0.0416 hari 

		$this->persentageForRatingBiasa = $_ENV['PERSENTAGE_SEARCH_BIASA'];
		$this->persentageForRatingFFB = $_ENV['PERSENTAGE_SEARCH_FB'];
		$this->persentageForCategory = 40;

		$this->arrayCategoryDanCountPesannya = array();

		$idOutlet = Session::get("outletYangAkanDiSearchBuatUserIni");
		$idUser = Session::get("userYangLagiSearch");
		//$idUser = 1;	


		//disini $this->arrayCategoryDanCountPesannya bakal keiisi
		$totalCountSemuaCategory = $this->getPointFromCategory($idUser);

		$byk = sizeof($idOutlet);

		//lagi ambil Point untuk masing-masing outlet
		$arrayPointOutletIni = array();
		for($i=0;$i<$byk;$i++){
			$idOutletTest = $idOutlet[$i];
			$arrayPointOutletIni[$i] = $this->getPointForThisOutlet($idOutletTest, $totalCountSemuaCategory);
		}
		//sorting berdasarkan point dan idoutlet
		$this->arrayForQuickSortIdOutlet = $idOutlet;
		$this->arrayForQuickSortPointOutlet = $arrayPointOutletIni;
		$this->quickSort($this->arrayForQuickSortPointOutlet);

		//testing munculin datanya dibawah ini
		
		//$idOutlet = $this->arrayForQuickSortIdOutlet;
		//$arrayPointOutletIni = $this->arrayForQuickSortPointOutlet;
		//for($i=0;$i<$byk;$i++){
		//	echo $idOutlet[$i] . "#";
		//	echo $arrayPointOutletIni[$i] ."<br/>";
		//}

		//hapus session dulu sebelum pindah
		Session::forget("outletYangAkanDiSearchBuatUserIni");
		Session::forget("userYangLagiSearch");
		Session::forget("longitudeUserForSearch");
		Session::forget("latitudeUserForSearch");

		//buat session untuk view di chosenOutlet
		Session::put("outletChosenForViewAfterSearch", $this->arrayForQuickSortIdOutlet);
		Session::put("tingkatKecocokanOutletChosenForViewAfterSearch", $this->arrayForQuickSortPointOutlet);
		
		//baru redirect
		return Redirect::to("/search/DataRelevanForChosenOutlet");
	}
	*/

	private function getPointFromCategory($idPemesan){
		$query = "SELECT categoryoutlet, COUNT(categoryoutlet) as byk 
		from pesanan 
		where idpemesan = ? 
		group by categoryoutlet";
		$res = DB::select($query, array($idPemesan));
		$byk = sizeof($res);
		$arrayRet = array();
		$total = 0;
		for($i=0;$i<$byk;$i++){
			//array_push($arrayRet, array(0=>$res[$i]->categoryoutlet, 1=>$res[$i]->byk));
			$arrayRet[$res[$i]->categoryoutlet] = $res[$i]->byk;
			$total += $res[$i]->byk;
		}
		$this->arrayCategoryDanCountPesannya = $arrayRet;
		return $total;
	}
	private function getPointForThisOutlet($idOutletTest, $totalCountSemuaCategory){
		
		$hasilSemua = 0;

		//mulai dari rating ffb
		//$strWaktu1 = date("Y-m-d H:i:s",strtotime($this->batasLastUpdateForSearch." days",strtotime($thestime)));
		

		//$outletTest = Outlet::whereRaw('lastUpdateRatingffb is not null and lastUpdateRatingffb + '.$this->batasLastUpdateForSearch.' > ? and id = ?', array($waktuSekarang, $idOutletTest))->first();
		$outletTest = Outlet::whereRaw('lastupdateratingffb is not null and lastupdateratingffb > ? and id = ?', array($this->batasLastUpdateForSearch, $idOutletTest))->first();
		if($outletTest == null){
			$hasilFFBForSearch = $this->getPointFromRatingFromFB($idOutletTest);
			$outletGantiRatingFFB = Outlet::whereRaw('id=?',array($idOutletTest))->first();
			$outletGantiRatingFFB->ratingffbforsearch = $hasilFFBForSearch;
			$outletGantiRatingFFB->lastupdateratingffb = $this->waktuSekarang;
			$outletGantiRatingFFB->save();
		}else{
			$hasilFFBForSearch = $outletTest->ratingffbforsearch;
			//echo $hasilFFBForSearch;
		}
		$hasilFFBForSearch = $hasilFFBForSearch * $this->persentageForRatingFFB / 100;
		$hasilSemua += $hasilFFBForSearch;

		//lanjut ke rating biasa
		// $waktuSekarang = date('Y-m-d H:i:s',time());
		// $thestime = date("Y-m-d H:i:s");
		//$strWaktu1 = date("Y-m-d H:i:s",strtotime("1 hours",strtotime($thestime)));
		//$strWaktu1 = date("Y-m-d H:i:s",strtotime($this->batasLastUpdateForSearch." days",strtotime($thestime)));
		//$outletTest = Outlet::whereRaw('lastUpdateRating is not null and lastUpdateRating + '.$this->batasLastUpdateForSearch.' > ? and id = ?', array($waktuSekarang, $idOutletTest))->first();
		$outletTest = Outlet::whereRaw('lastupdaterating is not null and lastupdaterating > ? and id = ?', array($this->batasLastUpdateForSearch, $idOutletTest))->first();
		if($outletTest == null){
			$hasilForSearch = $this->getPointFromRating($idOutletTest);
			$outletGantiRating = Outlet::whereRaw('id=?',array($idOutletTest))->first();
			$outletGantiRating->ratingforsearch = $hasilForSearch;
			$outletGantiRating->lastupdaterating = $this->waktuSekarang;
			$outletGantiRating->save();
		}else{
			$hasilForSearch = $outletTest->ratingforsearch;
		}
		$hasilForSearch = $hasilForSearch * $this->persentageForRatingBiasa / 100;
		$hasilSemua += $hasilForSearch;

		//lanjut ke category
		$outletBuatCate = Outlet::whereRaw('id=?',array($idOutletTest))->first();
		$category = $outletBuatCate->category;
		if(isset($this->arrayCategoryDanCountPesannya[$category])){
			$pointCategoryTemp = $this->arrayCategoryDanCountPesannya[$category] * 100 / $totalCountSemuaCategory;
			$pointCategory = $pointCategoryTemp * $this->persentageForCategory / 100;	
		}else{
			$pointCategory = 0;
		}
		$hasilSemua += $pointCategory;
		
		return $hasilSemua;
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
		$query = "Select coalesce(avg(rating),0) as berapa from pemesanoutletrating where idoutlet = ?";
		$res = DB::select($query, array($idOutletTest));
		$nilaiRating = floatval($res[0]->berapa);
		$nilaiRating = $nilaiRating / 5 * 100;
		return ($nilaiRating);
	}

	private function adaError($idError, $parameterIfNeeded = ""){
		if($idError == 9000)
			return "ERROR FACEBOOK, HUBUNGI PIHAK Foorenzy";
		else if($idError == 8900)
			return "ERROR Search Karena Kota atau Kabupaten " . $parameterIfNeeded . " Tidak terdaftar di Foorenzy";
		else if($idError == 8800)
			return "ERROR Karena Tidak ada Restaurant dengan Kota atau Kabupaten di tempat anda berada.";
		else if($idError == 8700)
			return "Tidak Ada Resto yang sesuai dengan Pencarian Anda.";
		return View::make("auth.adaError", array("idError" => $idError));
	}
	private function tampilinNamaPesanError($idError){
		if($idError == 9000)
			return "ERROR FACEBOOK";
		else if($idError == 8900)
			return "ERROR SEARCH, KAB KOTA TIDAK TERDAFTAR";
		else if($idError == 8800)
			return "ERROR SEARCH, NO RESTO IN KAB KOTA";
		else if($idError == 8700)
			return "ERROR NO RESTO MATCH";
		else if($idError == 1)
			return "SUKSES";
	}
	private function quickSort($array) 
	{
		$this->quickSortInDescendingOrder(0, sizeof($array) - 1);
	}
	private function quickSortInDescendingOrder ($low, $high)
	{
		$i=$low;
		$j=$high;
		$temp;
		$middle=$this->arrayForQuickSortPointOutlet[($low+$high)/2];

		while ($i<$j)
		{
			while ($this->arrayForQuickSortPointOutlet[$i]>$middle)
			{
				$i++;
			}
			while ($this->arrayForQuickSortPointOutlet[$j]<$middle)
			{
				$j--;
			}
			if ($j>=$i)
			{
				$temp=$this->arrayForQuickSortPointOutlet[$i];
				$this->arrayForQuickSortPointOutlet[$i]=$this->arrayForQuickSortPointOutlet[$j];
				$this->arrayForQuickSortPointOutlet[$j]=$temp;

				$temp=$this->arrayForQuickSortIdOutlet[$i];
				$this->arrayForQuickSortIdOutlet[$i]=$this->arrayForQuickSortIdOutlet[$j];
				$this->arrayForQuickSortIdOutlet[$j]=$temp;

				$i++;
				$j--;
			}
		}
		if ($low<$j)
		{
			$this->quickSortInDescendingOrder($low, $j);
		}
		if ($i<$high)
		{
			$this->quickSortInDescendingOrder($i, $high);
		}
	}

	public function distance($lat1, $lon1, $lat2, $lon2) {

		/*
			pada eksperimen yang gw pake
			lat1 lon1 adalah tempat gw berdiri
			gw adalah outlet yang jualan

			dan lat2 lon2 adalah tempat si calon pembeli dari outlet gw berdiri
			mreka akan membeli dari gw , dan lagi liat apakah gw bisa jual ke mreka

			dengan function ini, bakal dapet brp meter jarak dari 
			coor1 ke coor2

			jika ternyata return nya <= nilaiRadiusYangDitetapkan Outlet
			maka bisa di kirim
			dimana [nilaiRadiusYangDitetapkan] didapat dari field [radius] di table Outlet
		*/

		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		//return $lat1."#".$lon1."#".$lat2."#".$lon2."#".($miles * 1609.344);				//in Meter
		return floor(($miles * 1609.344));				//in Meter
	}
	private function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
	{
		$i = $j = $c = 0;
		for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
			if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) && ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
		}
		return $c;
	}
	private function getMinMaxBudget(){
		$budget = Budget::get();
		$byk = sizeof($budget);
		$dataBudget = array();
		$dataBudget[0] = array();
		$dataBudget[1] = array();
		for($i=0;$i<$byk;$i++){
			array_push($dataBudget[0], $budget[$i]->budget_min);
			array_push($dataBudget[1], $budget[$i]->budget_max);
		}
		return $dataBudget;
	}
	private function kasihKoma($angkaNilai){
		$angkaNilaiBaru = $angkaNilai . "";
		$bykAngkaNya = strlen($angkaNilai);
		if( $bykAngkaNya < 4 )
			return $angkaNilai;
		else
		{
			$temp = array();
			$temp2 = 0;
			for($i = $bykAngkaNya-1 ;$i >= 0 ; $i--){
				array_push($temp,$angkaNilaiBaru[$i]);
				$temp2++;
				if($temp2 == 3)
				{
					$temp2 = 0;
					if($i!=0)
						array_push($temp,".");
				}
			}
			$temp3 = "";
			$bykAngkaNya = sizeof($temp);
			for($i = $bykAngkaNya-1; $i >= 0 ; $i--){
				$temp3 .= $temp[$i];
			}
			return $temp3;
		}
	}

}
