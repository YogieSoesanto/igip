<?php

/*----------------------------------TESTING NGASAL APA AJA DISINI---------------------------------*/
/*Route::get("/yogie",function(){
	return "<form id='formProfPic' action='/ubahprofil/gantigambar' method='post' enctype='multipart/form-data'>
									<input type='hidden' name='_token' value='{{csrf_token()}}' />
									<input id='gbrProfPicNih' type='file' name='gbrProfPic' /><input type='submit'/>
								</form>";
});
*/

/*Route::get("/testsms", function(){
	$noHpBaruLagi = "089639171413";
	//$noHpBaruLagi = "081586065696";
	//$noHpBaruLagi = "08999356087";
	//$noHpBaruLagi = "081911608855";
	//$noHpBaruLagi = "081808997922";
	$balasan = strtolower("foorenzy#INFO");
	$indexKe = strpos($balasan, "foorenzy#info");

	//$skr = new SmsKeResto;
	//return $skr->kirimkanInfoUntukResto_SetelahRequest_REG_INFO($noHpBaruLagi) . "#";

	if($indexKe === FALSE)
		return false;

	if(!InfoResto::isSudahPernah($noHpBaruLagi)){
		$skr = new SmsKeResto;
		return $skr->kirimkanInfoUntukResto_SetelahRequest_REG_INFO($noHpBaruLagi) . "#";
	}
});*/
Route::get("/hehe", array(function(){
	$path = storage_path("/dataTestingSimulasiSearch/Resto.xlsx");
	try {
	    $inputFileType = PHPExcel_IOFactory::identify($path);
	    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
	    $objPHPExcel = $objReader->load($path);

	    $sheet = $objPHPExcel->getSheet(0);
	    for ($row = 1; $row <= 50; $row++) {
		    $rowData = $sheet->rangeToArray('A' . $row, NULL, TRUE, FALSE);
		    foreach($rowData[0] as $k=>$v)
		        echo "Row: ".$row."- Col: ".($k+1)." = ".$v."<br />";
		}

	} catch (Exception $e) {
	    echo $e;
	}



	/*$objPHPExcel = PHPExcel_IOFactory::load($path);
    $objWorksheet = $objPHPExcel->getActiveSheet();

    for ($row = 1; $row <= 1000; ++$row) {
         var_dump($objWorksheet->getCellByColumnAndRow(1, $row));
         echo "<br/><br/>";
    }*/
}));
Route::get("/hoho", array("uses"=>"PesanSekarangController@getBestRestoByMeta"));
Route::get("/zz", function(){
	DB::Delete("DELETE FROM ratrevupp");
	DB::Delete("DELETE FROM ratingresto");
	DB::Delete("DELETE FROM dratppm");
	return "oke";
});
Route::get("/trtrtr", function(){
	echo FoorenzyTimeFormat::FzyDateVersion(1) . "<br/>";
	$randomAsal = rand(500,1000);
	for($i=0;$i<$randomAsal;$i++){
		Meta::addNew(FoorenzyRandomText::generateTextRandomizeForURL($i));
	}
	return "selesai" . $randomAsal . "<br/>".FoorenzyTimeFormat::FzyDateVersion(1);
});
Route::get("/drdrdr", function(){
	echo FoorenzyTimeFormat::FzyDateVersion(1) . "<br/>";
	$randomAsal = rand(1,2701);
	$arrIdMetaNoDuplicate = array();
	for($i=1;$i<=$randomAsal;$i++){
		$ambilAtauEnggak = rand(0,1);
		if($ambilAtauEnggak == 1){
			array_push($arrIdMetaNoDuplicate, $i);
		}	
	}
	MetaResto::addNewResto(3,$arrIdMetaNoDuplicate);
	return "selesai" . sizeof($arrIdMetaNoDuplicate) . "<br/>".FoorenzyTimeFormat::FzyDateVersion(1);
});

Route::get("/testdah", function(){
	$rrupp = RatRevUPP::addNewAndGet(2);
	$randomAsal = rand(1,8);
	if($randomAsal <= 5){
		//insert langsung ga pake detail
		$rrupp->addDataRatingAndReview($randomAsal, FoorenzyRandomText::generateTextRandomizeForURL($randomAsal), $randomAsal % 2);
		echo "masuk jenis 1<br/>";
	}else{
		$dps = DPesanan::whereRaw("idpesanan=?",array($rrupp->idpesanan))->get();
		foreach($dps as $dp){
			$idmenu = $dp->idm;
			$dratppm = new DRatPPM;	
			$goodORBad = (rand(1,10) % 2 == 0 ? DRatPPM::INT_RATING_BAIK : DRatPPM::INT_RATING_BURUK);
			$dratppm->addNew($idmenu, $rrupp->id, $goodORBad);
		}
		$rrupp->addDataRatingAndReview(RatRevUPP::INT_RATING_FROM_DRatPPM, FoorenzyRandomText::generateTextRandomizeForURL($randomAsal), $randomAsal % 2);
		echo "masuk jenis 2<br/>";
	}
	$idresto = $rrupp->idresto;
	return $idresto . " = " . RatingResto::getNilaiPopularitas($idresto);
});
Route::get("/brzbrz", function(){
	RatingResto::doCheckPointForALL();
});
Route::get("/asd/{nama}", array("uses"=>"PesanSekarangController@getIconicRestoByName"));
Route::get("/asdasd", function(){
	return View::make("asdasd");
});
Route::get("/remot", function(){

	//$sku = new SmsKeUser;
	//return $sku->smsPointDouble("085736591735")."";
	//return $sku->pesananTelahDikirim($pes) . "";

	$skr = new SmsKeResto;
	//return $skr->kirimkanInfoUntukResto_SetelahRequest_REG_INFO("089639171413") . "";
	//return $skr->kirimkanInfoUntukResto_SetelahRequest_REG_INFO("087878595973") . "";
	return $skr->kirimkanInfoUntukResto_SetelahRequest_REG_INFO("081808997922") . "";
	//return "asd";
	//return $skr->kirimkanInfoUntukResto_SetelahRequest_REG_INFO("085691609353") . "";

	/*$pes = Pesanan::find(1);
	//$skr = new SmsKeResto;
	//return $skr->kirimkanPesanan($pes);

	$sku = new SmsKeUser;
	return $sku->pesananAdaBalasanDariResto($pes)."";*/
	//return $sku->pesananTelahDikirim($pes) . "";

	/*$hp = "089639171413";

	$text = "fzy temp" . file_get_contents(storage_path()."/dataServer/currentNumberHpSmsFoorenzy.txt");
	$a = new HPSmsFoorenzy;
	return $a->sampaikanSMS($hp, $text);*/
});


Route::get('/pesanerror', function(){

	if(Session::get('errors'))
		return Session::get('errors')->first('a');

	return Redirect::to('/');
});

/*Route::get("/alamak", function(){
	$pes = Pesanan::whereRaw("id=1")->first();
	return $pes->berikanKeuntungan();
});
*/




/*--------------------------------------PN Controller-----------------------------------------*/
Route::get('/PNASD',
	array('before'=>'auth', 'uses' => 'PNController@indexPN'));
Route::post('/PN/newpage',
	array('before'=>'auth|csrf', 'uses' => 'PNController@newPage'));
Route::post('/PN/savepage',
	array('before'=>'auth|csrf', 'uses' => 'PNController@savePage'));
Route::post('/PN/list',
	array('before'=>'auth', 'uses' => 'PNController@listPage'));
Route::get('/PN/loadpage',
	array('before'=>'auth', 'uses' => 'PNController@loadPage'));
Route::get('/PN/publish/{id}',
	array('before'=>'auth', 'uses' => 'PNController@publish'));
Route::post('/PN/upload',
	array('before'=>'auth', 'uses' => 'PNController@doUpload'));
Route::post('/PN/deleteimage',
	array('before'=>'auth', 'uses' => 'PNController@deleteImage'));





/*----------------------------------------NOT MANAGED YET---------------------------------------*/



Route::get('/pesanandanbukti',array('before' => 'auth', 'uses' => 'BuktiController@halamanPesananDanBukti'));
Route::get('/pesanandanbukti/uploadbukti/{a}',array('before' => 'auth', 'uses' => 'BuktiController@halamanUploadBukti'));
Route::post('/pesanandanbukti/getData',array('before' => 'auth|csrf', 'uses' => 'BuktiController@getDataSekarang'));
Route::post('/pesanandanbukti/doUploadBukti',array('before' => 'auth|csrf', 'uses' => 'BuktiController@doUploadBukti'));


Route::get ('/search/DataRelevanForChosenOutlet', array('uses'=>'SearchEngineController@showChosenOutlet2'));


Route::get('/changePass', array(function(){
	if(!Auth::check()){
		Session::put('tujuanKemana', '/changePass');
		return Redirect::to('/login');
	}
	return Redirect::to('/ubahkatasandi');
}));

Route::get('/syaratdanketentuan', array('before' => '', function(){
	return View::make('auth.syaratdanketentuan');
}));
Route::get('/needCekEmail', array('as'=>'cekemail', 'before' => 'guest', function(){
	$cekEmail = '';
	if(Session::has('cekEmail')){
		$cekEmail = Session::get('cekEmail');
	}
	return View::make('auth.needCekEmail', array('subject' => $cekEmail));
}));


Route::get ('/pesan/authenticate', function(){
	return Auth::check().'';
});

Route::get ('/pesan/cekQty/{idMenu}', array('before'=>'auth', 'uses'=>'BuatPesananController@cekQty'));
Route::post('/pesan/addToCart', array('before'=>'csrf|auth', 'uses'=>'BuatPesananController@addToCart'));
Route::post('/pesan/updateCart', array('before'=>'csrf|auth', 'uses'=>'BuatPesananController@UpdateCart'));
Route::post('/pesan/cleanCart', array('before'=>'csrf|auth', 'uses'=>'BuatPesananController@cleanCart'));
Route::get ('/pesan/getAllCart', array('before'=>'auth', 'uses'=>'BuatPesananController@getAllCart'));
//Route::post('/pesan/buatPesanan', array('before'=>'csrf|auth', 'uses'=>'BuatPesananController@buatPesanan'));
Route::post('/pesan/buatPesanan', array('before'=>'csrf|auth', 'uses'=>'BuatPesananController@buatPesanan2'));

Route::post('/pesan/getUlasan', array('before'=>'csrf', 'uses'=>'BuatPesananController@getUlasan'));
Route::post('/pesan/getUlasanFB', array('before'=>'csrf', 'uses'=>'BuatPesananController@getUlasanFB'));
Route::post('/pesan/gantiLikeReviewFB', array('before'=>'csrf|auth', 'uses'=>'BuatPesananController@gantiLikeReviewFB'));



Route::get ('/search/halamanPesan/{id}', array('uses'=>'SearchEngineController@detailOutlet2'));


Route::get ('/search/DataRelevanForChosenOutlet/dataKe/{dpp}/{ps}', array('uses'=>'SearchEngineController@dataKe2'));
Route::post('/search/DataRelevanForChosenOutlet/filterData', array('before'=>'csrf','uses'=>'SearchEngineController@terjadiFilter2'));
Route::get ('/search/start', array('uses' => 'SearchEngineController@startSearch'));
Route::get ('/search/prestart', array('uses' => 'SearchEngineController@preStartSearch'));
Route::get ('/search/secaraOtomatis/{a}/{b}/{c}/{d}', array('uses' => 'SearchEngineController@lakukanSearchOtomatis'));
Route::get ('/search/secaraManual/{a}/{b}/{c}', array('uses' => 'SearchEngineController@lakukanSearchManual'));
Route::get ('/lokasi/getDistance/{a}/{b}/{c}/{d}', array('uses' => 'SearchEngineController@distance'));
Route::get ('/lokasi/addlokasi/{a}/{b}', array('before' => 'guest', function($provinsi, $kotakab){
	/*
		untuk testing, buat dapetin segala jenis kabupaten dan kota
		put this code into awal.blade.php
		ke dalam function itungLatLongNya(latlng);
		di baris setelah for xx = 0 dkk

		lalu comment codingan window.locationnya 

		if(window.XMLHttpRequest){xmlhttp = new XMLHttpRequest();}else{xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');}
		xmlhttp.onreadystatechange=function(){
			if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
				var hasilNya = xmlhttp.responseText;
				x.innerHTML = adminlvl2 + '#' + adminlvl1 + '#<br/>'+latlng;
			}
		}
		xmlhttp.open('GET', '/lokasi/addlokasi/'+adminlvl1+'/'+adminlvl2);
		xmlhttp.send();
	*/
	if(Lokasi::whereRaw('provinsi = ? and kotakab = ?' , array($provinsi, $kotakab))->first() == null){
		$lokasi = new Lokasi;
		$lokasi->provinsi = $provinsi;
		$lokasi->kotakab = $kotakab;
		$lokasi->save();	
	}
	return '';
}));

/*Route::get('/hasil/needCekEmail/{subject}', array('before' => 'guest', function($subject){
	return View::make('auth.hasil.needCekEmail', array('subject'=>$subject));
}));*/



Route::get('/updateDataPerHari', array('uses'=>'AdService@updateForHome'));
Route::get('/hapusSukaSuka', array('uses'=>'AdService@hapusSukaSuka'));

Route::get('/test', array(function(){
	return View::make('pret');
}));
Route::get('/test2', array(function(){
	return 'dah';
	$mulai = 1003;
	$akhir = $mulai + 1000;
	for($i=$mulai;$i<$akhir;$i++){
		$bah = new Outlet;
		$bah->id = $i;
		$bah->idlokasidariyogie = 251;
		$bah->radius = 6000;
		$bah->latitude = -6.200351847820611;
		$bah->longitude = 106.78621888160706;
		$bah->save();
	}
	return 'SELESAI';
}));

Route::get('/test3', array(function(){
	return View::make('halaman.greget');
}));


Route::get('/test4', array('uses'=>'SocketTest@testSocket'));
Route::get('/test5', function(){
	return View::make('geje');
});
Route::get('/test6', function(){
	return View::make('testBlade');
});
Route::get('/test7', function(){
	//file_put_contents(storage_path().'/waktuRelease.txt', date('Y-m-d H:i:s'));
	echo time();
	$waktu = file_get_contents(storage_path().'/waktuRelease.txt');
	echo '<br/>'.strtotime($waktu);

});

//Route::get('/test8/{byk}', array('uses'=>'BuatPesananController@hitungdanambilnilaiaslikodeoutlet'));


Route::get('/belajartemplate', function(){
	return View::make('belajartemplate');
});




/*start yang belum ada*/
Route::get('/lihatProfile/resto/{id}', array(function($id){
	if($id == '-1')
		return 'ini ga ada';
}));
Route::get('/lihatProfile/user/{id}', array(function($id){
	if($id == '-1')
		return 'ini ga ada';
}));
Route::get('/lihatProfile/foodBlogger/{id}', array(function($id){
	if($id == '-1')
		return 'ini ga ada';
}));
Route::get('/lihatProfile/Community/{id}', array(function($id){
	if($id == '-1')
		return 'ini ga ada';
}));
Route::get('/lihatLengkap/top/resto', array(function(){
}));
Route::get('/lihatLengkap/top/userByLevel', array(function(){
}));
Route::get('/lihatLengkap/top/userByIncome', array(function(){
}));
Route::get('/lihatLengkap/top/foodBlogger', array(function(){
}));
Route::get('/lihatLengkap/top/community', array(function(){
}));
/*end yang belum ada*/


/*start buat import export dari mssql ke mysql atau sebaliknya*/
Route::get('/convertDataDB', array(function(){
	return View::make('datadb.exportimport');
}));
Route::get('/convertDataDB/gantiDB',array('uses'=>'ExportImportController@gantiDB'));
Route::get('/convertDataDB/tarik/{a}',array('uses'=>'ExportImportController@tarik'));
Route::get('/convertDataDB/simpan/{a}/{b}', array('uses'=>'ExportImportController@simpan'));

Route::get('/adminPanel',array('before'=>'authAdmin','uses'=>'AdService@homeAdmin'));
Route::get('/adminPanel/generateSkill',array('before'=>'authAdmin','uses'=>'AdService@generateSkill'));
Route::get('/adminPanel/getOutlet',array('before'=>'authAdmin','uses'=>'AdService@getOutletData'));
Route::get('/adminPanel/profileResto/{a}',array('before'=>'authAdmin','uses'=>'AdService@profileResto'));
Route::get('/adminPanel/finalize',array('before'=>'authAdmin','uses'=>'AdService@finalize'));
Route::get('/adminPanel/gdms/{a}',array('before'=>'authAdmin','uses'=>'AdService@generateDataMasterServer'));

Route::get('/adminPanel/ambilComboPK', array('before'=>'authAdmin','uses'=>'AdService@ambilComboPK'));
Route::get('/adminPanel/ambilComboRH', array('before'=>'authAdmin','uses'=>'AdService@ambilComboRH'));
Route::get('/adminPanel/ambilComboKT', array('before'=>'authAdmin','uses'=>'AdService@ambilComboKT'));

Route::get('/adminPanel/cekChangePKK/{a}/{b}', array('before'=>'authAdmin','uses'=>'AdService@cekUbahPKK'));
Route::get('/adminPanel/cekChangeHTutup/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeHTutup'));
Route::get('/adminPanel/cekChangeJam/{a}/{b}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeJam'));
Route::get('/adminPanel/cekChangeHalal/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeHalal'));
Route::get('/adminPanel/cekChangeBalas/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeBalas'));
Route::get('/adminPanel/cekChangeRenHarga/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeRenHarga'));
Route::get('/adminPanel/cekChangeKate/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeKate'));
Route::get('/adminPanel/cekChangeBiaya/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeBiaya'));
Route::get('/adminPanel/cekChangeMethod/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangeMethod'));
Route::get('/adminPanel/cekChangePajak/{a}', array('before'=>'authAdmin','uses'=>'AdService@cekChangePajak'));

Route::post('/adminPanel/uploadLogo', array('before'=>'authAdmin','uses'=>'AdService@uploadLogo'));
Route::post('/adminPanel/updateDataTab1', array('before'=>'authAdmin','uses'=>'AdService@updateDataTab1'));

Route::get('/adminPanel/mapping', array('before'=>'authAdmin','uses'=>'AdService@mapping'));
Route::post('/adminPanel/mapProv', array('before'=>'authAdmin','uses'=>'AdService@mapProv'));
Route::post('/adminPanel/mapKKab', array('before'=>'authAdmin','uses'=>'AdService@mapKKab'));

Route::get('/adminPanel/manageKate', array('before'=>'authAdmin','uses'=>'AdService@manageKate'));
Route::get('/adminPanel/managePesanan', array('before'=>'authAdmin','uses'=>'AdService@managePesanan'));
Route::get('/adminPanel/managePesanan/{a}', array('before'=>'authAdmin','uses'=>'AdService@acceptOrDeclinePesanan'));
Route::post('/adminPanel/nyatakanValid', array('before'=>'authAdmin|csrf','uses'=>'BuktiController@AdminNyatakanValid'));
Route::post('/adminPanel/nyatakanPalsu', array('before'=>'authAdmin|csrf','uses'=>'BuktiController@AdminNyatakanPalsu'));

Route::post('/adminPanel/addKate', array('before'=>'authAdmin|csrf','uses'=>'AdService@addKate'));
Route::post('/adminPanel/delKate', array('before'=>'authAdmin|csrf','uses'=>'AdService@delKate'));

Route::post('/admin/gantiURLServerSecaraPost', array('uses'=>'AdService@gantiURLServerSecaraPost'));
Route::post('/admin/ambilDataServer', array('uses'=>'AdService@ambilDataServer'));
Route::post('/admin/kirimDataKunjungan', array('uses'=>'AdService@kirimDataKunjungan'));


Route::get('/adminPanel/openMenuResto', array('before'=>'authAdmin','uses'=>'ManageMenuController@openMenuResto'));

Route::post('/adminPanel/gantiGambarMenu', array('before'=>'authAdmin','uses'=>'ManageMenuController@gantiGambarMenu'));

Route::post('/adminPanel/hapusKategori', array('before'=>'authAdmin','uses'=>'ManageMenuController@hapusKategori'));
Route::post('/adminPanel/hapusMenu', array('before'=>'authAdmin','uses'=>'ManageMenuController@hapusMenu'));
Route::post('/adminPanel/hapusExtra', array('before'=>'authAdmin','uses'=>'ManageMenuController@hapusExtra'));
Route::post('/adminPanel/hapusSubExtra', array('before'=>'authAdmin','uses'=>'ManageMenuController@hapusSubExtra'));

Route::post('/adminPanel/tambahKategori', array('before'=>'authAdmin','uses'=>'ManageMenuController@tambahKategori'));
Route::post('/adminPanel/tambahMenu', array('before'=>'authAdmin','uses'=>'ManageMenuController@tambahMenu'));
Route::post('/adminPanel/tambahExtra', array('before'=>'authAdmin','uses'=>'ManageMenuController@tambahExtra'));
Route::post('/adminPanel/tambahSubExtra', array('before'=>'authAdmin','uses'=>'ManageMenuController@tambahSubExtra'));

Route::post('/adminPanel/editKategori', array('before'=>'authAdmin','uses'=>'ManageMenuController@editKategori'));
Route::post('/adminPanel/editMenu', array('before'=>'authAdmin','uses'=>'ManageMenuController@editMenu'));
Route::post('/adminPanel/editExtra', array('before'=>'authAdmin','uses'=>'ManageMenuController@editExtra'));
Route::post('/adminPanel/editSubExtra', array('before'=>'authAdmin','uses'=>'ManageMenuController@editSubExtra'));




//Route::post('/adaSMS', array('uses'=>'AdService@adaSMS'));
Route::post('/adaSMS', array('uses'=>'AdService@adaSMS5'));



/*
	ini yang uda dibuat functionnya, tapi belom ada view nya 
	atau ada yang uda dibuat functionnya, tapi viewnya masih numpang sama orang

	1. (buatLinkShareJoinFoorenzyUser) Buat Link Referensi buat Register User udah ada, 
	tapi Share ke FB ato Share ke manapunnya belom ada

	2. (mauRegisDenganLinkShare) kalo orang link pada nomor 1, maka akan ke routes di nomor 2 ini.
	Artinya akan dibuat dibuat session idusershareberapa dan halaman register.
	Nantinya kalo ada orang register di AuthController , itu bakal cek session idusershareberapa ada ga.

	(pre-3 , jgn lupa buat upload bukti bon)

	3. (addKeuntunganSetelahUpload) kalo orang yang udah upload bukti pesan dan statusnya 5 / 6 / 7, maka dia bakal dapat keuntungan
	keuntungan2nya itu seperti exp, pemb_curr yang nantinya jadi kupon undian dkk di add disini
	terakhir ganti status pesanan tersebut jadi 8 buat bisa ditarik duitnya nanti

	4. (generateIdPembCurr) , ini viewnya numpang sama update tiap hari, buat admin bisa generate new idresetpembcurr , di tiap hari admin bisa click button, 
	kalo emang tanggal saat click button masih di dalam periode idresetpembcurr yang lama, maka ga usa generate baru
	kalo uda beda , harus generate baru idresetpembcurr dan simpan di storage/idpembcurrbaru.txt

	5. (buatPeriodeUndian), ini bener2 ga ada view nya, tapi cuma generate aja di database

	6. (joinPeriodeUndian), ini bener2 ga ada view nya, buat si user bisa join ke detail undian aja
*/
Route::get('/test9', array('before'=>'auth','uses'=>'AdService@buatLinkShareJoinFoorenzyUser'));
Route::get('/test10/{a}', array('uses'=>'AdService@mauRegisDenganLinkShare'));

//Route::get('/test11/{a}', array('before'=>'auth','uses'=>'AdService@addKeuntunganSetelahUpload'));
//test11 ini udah ada viewnya , di jalanin di BuktiController
//Route::get('/test12', array('before'=>'authAdmin','uses'=>'AdService@generateIdPembCurr'));
//test12 ini uda dijalanin koq di updatefohome, jd ga usa ada viewnya lagi

Route::get('/test13', array('before'=>'authAdmin','uses'=>'AdService@buatPeriodeUndian'));
Route::get('/test14', array('before'=>'auth','uses'=>'AdService@joinPeriodeUndian'));

Route::get('/test15',function(){
	return View::make('search.asamdigunung');
});

Route::get('/ukcup',array('uses'=>'AdService@ubahKeyCodeUniquePesanan'));

Route::get('/gkup/{a}',array('uses'=>'BuatPesananController@generateKodeUniquePesanan'));

Route::get('/test16',array(function(){
	$tgl = Pesanan::whereRaw('id=1')->first()->tgl;
	$tgl = substr($tgl, 0, 19);
	$thestime = date('Y-m-d H:i:s');

	$waktusekarangkurang6jam = strtotime('-6 hours',strtotime($thestime));
	$waktuSiSama = strtotime($tgl);
	if($waktusekarangkurang6jam >= $waktuSiSama){
		return 'done in nih' . $tgl;
	}
	else{
		return 'belom nih';
	}
	//ssms = 2015-04-21 20:55:32.343
	//mysql = 2015-05-08 07:34:23
}));

Route::get('/test18/{a}', array('uses'=>'BuatPesananController@saveKodeUniqueToPesanan'));



Route::get('/hometest20', function(){
	return View::make('homebaru');
});

Route::get('/bukti', array('before'=>'auth','uses'=>'BuktiController@bukti'));
Route::post('/bukti/cekbal', array('before'=>'auth|csrf','uses'=>'BuktiController@buktiCekBalasan'));
Route::get('/uploadbukti/{a}', array('before'=>'auth','uses'=>'BuktiController@uploadbukti'));
Route::post('/uploadbukti/getcontoh', array('before'=>'auth|csrf','uses'=>'BuktiController@getContohBukti'));
Route::post('/uploadbukti/uploadkond2atau3', array('before'=>'auth|csrf','uses'=>'BuktiController@uploadGambarKondisi2Atau3'));
Route::post('/uploadbukti/uploaddatanya_2', array('before'=>'auth|csrf','uses'=>'BuktiController@uploadDataKond2'));
Route::post('/uploadbukti/uploaddatanya_3', array('before'=>'auth|csrf','uses'=>'BuktiController@uploadDataKond3'));


Route::post('/doUpload/laporkan', array('before'=>'auth|csrf','uses'=>'BuktiController@laporinResto'));
/*Route::post('/doUpload/uploadsatu', array('before'=>'auth|csrf','uses'=>'BuktiController@uploadKondisiSatu'));
Route::post('/doUpload/uploaddua', array('before'=>'auth|csrf','uses'=>'BuktiController@uploadKondisiDua'));
*/


Route::get('/pesan', array('uses'=>'SearchEngineController@adaPesananForAllOutlet'));
Route::get ('/pilihresto', array('uses'=>'SearchEngineController@showChosenOutlet'));
Route::get ('/pilihresto/dataKe/{dpp}/{ps}', array('uses'=>'SearchEngineController@dataKe'));
//Route::post('/pilihresto/filterData', array('before'=>'csrf','uses'=>'SearchEngineController@terjadiFilter'));
Route::get('/pilihresto/filterData', array('before'=>'','uses'=>'SearchEngineController@terjadiFilter'));
Route::get ('/pilihmenu/{id}', array('uses'=>'SearchEngineController@detailOutlet'));
Route::get('/pesan/selesai', array('before'=>'auth', 'uses'=>'BuatPesananController@pesananSelesai'));

//Route::get('/khu/{a}', array('before'=>'auth', 'uses'=>'BuatPesananController@konfirmasiPesanan'));
//Route::get('/khu/gagalkonf/{a}', array('before'=>'auth', 'uses'=>'BuatPesananController@gagalKonfirmasi'));
//Route::post('/khu/cek', array('before'=>'auth', 'uses'=>'BuatPesananController@confirmKonfirmasiPesanan'));
//Route::post('/khu/kirimkodebaru', array('before'=>'auth', 'uses'=>'BuatPesananController@kirimConfCodeNgulang'));
//Route::post('/khu/kirimkodebarunobaru', array('before'=>'auth', 'uses'=>'BuatPesananController@kirimConfCodeNgulangNoBaru'));

Route::get('/kp/{a}', array('before'=>'auth', 'uses'=>'BuatPesananController@konfirmasiPesanan2'));
Route::post('/kp/cek', array('before'=>'auth|csrf','uses'=>'BuatPesananController@confirmKonfirmasiPesanan2'));
Route::post('/kp/kirimconfbaru', array('before'=>'auth|csrf','uses'=>'BuatPesananController@kirimConfCodeNgulang2'));
Route::get('/pesananDikirim', array('before'=>'auth', 'uses'=>'BuatPesananController@pesananDikirim'));


Route::get('/rating/{a}', array('before'=>'auth', 'uses'=>'BuktiController@rating'));
Route::post('/rating/submit', array('before'=>'auth|csrf', 'uses'=>'BuktiController@ratingSubmit'));










/*--------------------------------------Extra Controller-----------------------------------------*/
Route::get('/',
		array('before'=>'', 'uses'=>'ExtraController@showViewLandingPage'));

Route::get('/landing',
		array('before'=>'', 'uses'=>'ExtraController@showViewLandingPage'));

Route::get('/faq',
		array('before'=>'', 'uses'=>'ExtraController@showViewPertanyaanUmum'));

Route::get('/home',
		array('before'=>'', 'uses'=>'ExtraController@showHome'));

Route::get('/syaratdanketentuan',
		array('before'=>'', 'uses'=>'ExtraController@showSyaratDanKetentuan'));

Route::get('/onlyforguest', 
		array('before'=>'', 'uses'=>'ExtraController@showOnlyForGuest'));

Route::get('/rnf', 
		array('before'=>'', 'uses'=>'ExtraController@showRouteNotFound'));

Route::get('/rie', 
		array('before'=>'', 'uses'=>'ExtraController@showRouteInternalError'));

Route::get('/inforesto', 
		array('before'=>'', 'uses'=>'ExtraController@showInfoResto'));

Route::get('/undiangenerator', 
		array('before'=>'', 'uses'=>'ExtraController@showUndianGenerator'));


/*--------------------------------------Undian Controller-----------------------------------------*/
Route::get('/undian',
		array('before'=>'', 'uses'=>'UndianController@showUndian'));

Route::post('/undian/getData',
		array('before'=>'csrf', 'uses'=>'UndianController@getDataForShowUndian'));

Route::get('/dundian/{a}',
		array('before'=>'', 'uses'=>'UndianController@showRuangUndian'));

Route::post('/dundian/getData/{a}',
		array('before'=>'csrf', 'uses'=>'UndianController@getDataForRuangundian'));

Route::post('/undian/daftarkan',
		array('before'=>'csrf|auth','uses'=>'UndianController@doDaftarkanUndian'));





/*--------------------------------------Auth Controller-----------------------------------------*/
Route::get('/login',
		array('before'=>'guest', 'uses'=>'AuthController@showLogin'));

Route::get('/register', 
		array('before'=>'guest', 'uses'=>'AuthController@showRegister'));

Route::get ('/auth/logout', 
		array('before'=>'auth', 'uses'=>'AuthController@doLogout'));

Route::post('/auth/login', 
		array('before'=>'guest|csrf','uses'=>'AuthController@doLogin'));

Route::post('/auth/reg',
		array('before'=>'guest|csrf','uses'=>'AuthController@doRegister'));

Route::get ('/auth/linkVerif/{a}/{b}', 
		array('before'=>'guest', 'uses'=>'AuthController@doConfirmLinkVerifForRegister'));

/*
Route::get ('/auth/fb/login', 
		array('before'=>'guest', 'uses'=>'AuthController@doLoginWithFacebook'));
*/

Route::get ('/auth/fb/login2', 
		array('before'=>'guest', 'uses'=>'AuthController@doLoginWithFacebook_WithIntended'));

Route::get ('/auth/fb/callback', 
		array('before'=>'guest', 'uses'=>'AuthController@manageFacebookCallback'));

Route::get('/forgot_pass', 
		array('before'=>'guest', 'uses'=>'AuthController@showForgotPassword'));

Route::post('/auth/forgotpass', 
		array('before'=>'guest|csrf','uses'=>'AuthController@doRequestForgotPassword'));

Route::get ('/auth/forgotPassword/{a}/{b}', 
		array('before' => 'guest', 'uses'=>'AuthController@doConfirmLinkVerifForForgotPass'));

Route::get('/ubahkatasandi', 
		array('before' => 'auth', 'uses'=>'AuthController@showUbahKataSandi'));

Route::post('/ubahkatasandi/simpan', 
		array('before' => 'auth|csrf', 'uses'=>'AuthController@doUbahKataSandi'));

Route::get('/hasil/changePass', 
		array('before'=>'auth', 'uses'=>'AuthController@showHalaman_DoneChangePass'));

Route::get('/hasil/needCekEmail/{subject}', 
		array('before'=>'guest', 'uses'=>'AuthController@showHalaman_NeedCheckEmail'));
	




/*--------------------------------------Akun Controller-----------------------------------------*/
Route::get('/profil/{a?}', 
		array('before'=>'', 'uses'=>'AkunController@showProfil'));

Route::post('/profil/getData', 
		array('before'=>'csrf', 'uses'=>'AkunController@getDataProfil'));

Route::post('/profil/getSkill', 
		array('before'=>'csrf', 'uses'=>'AkunController@getSkill'));

Route::get('/ubahprofil', 
		array('before'=>'auth', 'uses'=>'AkunController@showUbahProfil'));	

Route::post('/ubahprofil/getData', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@getDataUbahProfil'));

Route::post('/ubahprofil/gantidata', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doGantiDataProfil'));

Route::post('/ubahprofil/gantigambar', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doGantiGambarProfPic'));

Route::post('/ubahprofil/gantihp', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doGantiNomorHpUser'));

Route::get('/khu/{a}', 
		array('before'=>'auth', 'uses'=>'AkunController@showKonfirmasiPesanan'));	

Route::post('/khu/cek', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doConfirmPenggantianHpUser'));

Route::post('/khu/kirimkodebarunobaru', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doKirimUlangPenggantianHpUserKeNoBaru'));

Route::get('/khu/gagalkonf/{a}', 
		array('before'=>'auth', 'uses'=>'AkunController@showGagalKonfirmasi'));

Route::post('/khu/kirimkodebaru', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doKirimUlang_GantiNomorHpUser'));

Route::post('/kritiksaran/kirimAdaUser', 
		array('before'=>'auth|csrf', 'uses'=>'AkunController@doSubmitKritikDanSaran_HasUser'));

Route::post('/kritiksaran/kirimGaAdaUser', 
		array('before'=>'csrf', 'uses'=>'AkunController@doSubmitKritikDanSaran_NoUser'));

Route::get('/psnsvr', 
		array('before'=>'auth', 'uses'=>'AkunController@showPesanDariServer'));

/*
Route::get('/psnsvr', array('before'=>'auth', 'uses'=>'AkunController@lihatPesanDariServer'));
Route::post('/psnsvr/ubahstatdaridf', array('before'=>'csrf|auth', 'uses'=>'AkunController@UbahStatusDariFoorenzy'));
Route::post('/psnsvr/dataKe', array('before'=>'csrf|auth','uses'=>'AkunController@PesanServerDataKe'));
*/







/*--------------------------------------Riwayat Controller-----------------------------------------*/
Route::get('/riwayat', array('before'=>'auth', 'uses'=>'RiwayatController@riwayat'));
Route::post('/riwayat/dataKe', array('before'=>'csrf|auth','uses'=>'RiwayatController@dataKe'));
Route::get('/statusub', array('before'=>'auth', 'uses'=>'RiwayatController@statusUploadBukti'));
Route::post('/statusub/hapusnotif', array('before'=>'csrf|auth', 'uses'=>'RiwayatController@statusHapusNotif'));
Route::post('/statusub/hapusallnotif', array('before'=>'csrf|auth', 'uses'=>'RiwayatController@statusHapusAllNotif'));
Route::get('/alasanpenolakan/{a}', array('before'=>'auth', 'uses'=>'RiwayatController@lihatAlasanPenolakan'));
Route::get('/dpesanan/{a}', array('before'=>'auth', 'uses'=>'RiwayatController@detailPesanan'));
Route::get('/cart', array('before'=>'auth', 'uses'=>'RiwayatController@cart'));






/*
Admin :  AdService + ManageMenu
Pesanan : BuatPesanan + SearchEngine
BuktiCotroller


Fitur harusnya dibagi jadi : 

1. Fitur Personal (Login, Register, ResetPassword, ChangePassword, ProfilAkun, SkillAkun, Riwayat)
2. Fitur Pemesanan (Cari, Rekomendasiin, BuatPesanan, TerimaPesanan, Pembuktian Transaksi)
3. Fitur Undian / Hadiah dari Pemesanan
4. Fitur Operator (Orang untuk Ngurusin Sistem / Operationalnya)

Urutan Kelarin : 1 -> 3 -> 4 -> 2
*/

Route::get('/cekSemua',array('before'=>'authAdmin',function(){
		
	if(UserAdmin::canOpenThis_If_PrivilegeLevelMinimal(10)){
		$user = User::whereRaw("isemailvalidated is not null and isemailvalidated <> 3")->get();
		$pesan = Pesanan::all();
		$kdans = KritikSaran::all();
		
		$text = "User : " . sizeof($user) . "<br/>";
		$text .= "Pesan : " . sizeof($pesan) . "<br/>";
		$text .= "KDANS : " . sizeof($kdans);
		
		return $text;
	}
}));
Route::get('/cekSemua/1',array('before'=>'authAdmin',function(){
	if(UserAdmin::canOpenThis_If_PrivilegeLevelMinimal(10)){
		$users = User::whereRaw("isemailvalidated is not null and isemailvalidated <> 3")->get();
		$text = "";
		$text .= "<h1>Banyak User : " . sizeof($users) . "</h1>";
		foreach($users as $user){
			$text .= $user->id . " # ";
			$text .= $user->nama . " # ";
			$text .= $user->hpdefault . " # " ;
			$text .= $user->email . " # " ;
			$text .= ($user->isemailvalidated == 2? "Register Using FB" : "Register Using Foorenzy") . " # " ;
			$text .= $user->alamatdefault . "" ;
			$text .= "<br/><br/>";
		}

		return $text;
	}
}));
Route::get('/cekSemua/2/{banyak?}',array('before'=>'authAdmin',function($banyak = -1){
	if(UserAdmin::canOpenThis_If_PrivilegeLevelMinimal(10)){
		if($banyak == -1)
			$pesans = Pesanan::IdDescending()->get();
		else
			$pesans = Pesanan::IdDescending()->take($banyak)->get();
		$text = "";
		$text .= "<h1>Banyak Pesanan : " . sizeof($pesans) . "</h1>";
		foreach($pesans as $pesan){
			$text .= $pesan->id . " # ";
			$text .= $pesan->tgl . " # ";

			$dataResto = $pesan->getDataRestoBuatSMS();
			$text .= "<br/>&nbsp&nbsp---Data Resto : " . $dataResto['nama'] . " # " . $dataResto['hp'];
			$text .= "<br/>&nbsp&nbsp---" . $pesan->getBodySMSPesanan();
			$text .= "<br/>&nbsp&nbsp---" . $pesan->getNamaStatusPesanan();
			$text .= "<br/><br/>";
		}

		return $text;
	}
}));
Route::get('/cekSemua/3/{banyak?}',array('before'=>'authAdmin',function($banyak = -1){
	if(Auth::id() == 1){
		if($banyak == -1)
			$kdans = KritikSaran::IdDescending()->get();
		else
			$kdans = KritikSaran::IdDescending()->take($banyak)->get();
		
		$text = "";
		$text .= "<h1>Banyak Kritik Dan Saran : " . sizeof($kdans) . "</h1>";
		foreach($kdans as $ks){
			$text .= $ks->id . " # " . $ks->tglmasuk;
			$text .= "<br/>&nbsp&nbsp---Id User : " . $ks->iduser;
			$text .= "<br/>&nbsp&nbsp---Nama : " . $ks->nama;
			$text .= "<br/>&nbsp&nbsp---Email : " . $ks->email;
			$text .= "<br/>&nbsp&nbsp---KDanS : " . $ks->isinya;
			$text .= "<br/><br/>";
		}

		return $text;
	}
}));


/*
Route::get('/resetUser', array('before'=>'authAdmin',function(){
	if(Auth::id() == 1){
		DB::update("update users set pemb_curr = null, pemb_lifetime = null, levelsekarang = null, exp = null, saldo = null, kupon = null, freqpesanhariini = null, tglpesanterakhir = null, thnextkupon = null, last_trytoverify = null, count_trytoverify = null, bykstatdf = null, byknotifht = null, byknotiftb = null, lastbacastatusdf = null");
		return "oke";
	}
}));
*/
