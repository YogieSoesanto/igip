<?php


Route::get("/z/{idResto}", function($idResto){	
	return View::make("test.managemenu", array("idResto" => $idResto));
});

Route::get("/c/{a}", function($idMenu){	
	return View::make("test.managegallery", array("idMenu" => $idMenu));
});

Route::get("/", function(){
//return phpinfo();
	return strlen("Nanti saya kasih review yang terbaik, tapi mohon lapisan bubble pembungkusnya agak tebal ya pak, takut tertimpa atau jatuh saat di JNE nya, thanks");
});

/*--------------------------------------------RESTO---------------------------------------------*/
Route::get("/test_getRestoNameById/{a}", function($idResto){
	$resto = Resto::find($idResto);
	if($resto == NULL) return Resto::CODE_ERROR_GET_RESTO;
	return $resto->getData()[1];
});
Route::post("/getRestoNameById", function(){
	$idResto = Input::get("idResto");
	$resto = Resto::find($idResto);
	if($resto == NULL) return Resto::CODE_ERROR_GET_RESTO;
	return $resto->getData()[1];
});
/*--------------------------------------------RESTO---------------------------------------------*/


/*--------------------------------------------KMR---------------------------------------------*/
Route::get("/getKMRByIdResto/{a}", function($idResto){
	$resto = Resto::find($idResto);
	if($resto == NULL) return Resto::CODE_ERROR_GET_RESTO;
	return json_encode($resto->getData(Resto::GetData_TIPEManageMenu));	
});
Route::post("/getKMRByIdResto", function(){
	$idResto = Input::get("idResto");
	$resto = Resto::find($idResto);
	if($resto == NULL) return Resto::CODE_ERROR_GET_RESTO;
	return json_encode($resto->getData(Resto::GetData_TIPEManageMenu));	
});



Route::get("/autocompleteKMR/{a}", function($name){
	$km = new MsKategoriMenu;
	return json_encode($km->getDatasByName($name));
});
Route::post("/autocompleteKMR", function(){
	$name = Input::get("namaNya");
	$km = new MsKategoriMenu;
	return json_encode($km->getDatasByName($name));
});


Route::get("/insertKMR/{a}/{b}/{c}", function($idResto, $namaBaru, $descBaru){
	$kmr = new KategoriMenuResto;
	return $kmr->tambahData($idResto, $namaBaru, $descBaru);
});
Route::post("/insertKMR", function(){
	$kmr = new KategoriMenuResto;
	$idResto = Input::get("idResto");
	$namaBaru = Input::get("namaBaru");
	$descBaru = Input::get("descBaru");
	return $kmr->tambahData($idResto, $namaBaru, $descBaru);
});


Route::get("/updateKMR/{a}/{b}/{c}", function($idKMR, $namaBaru, $descBaru){
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return $kmr->gantiDataNamaAndDesc($namaBaru, $descBaru);
});
Route::post("/updateKMR", function(){
	$idKMR = Input::get("idKMR");
	$namaBaru = Input::get("namaBaru");
	$descBaru = Input::get("descBaru");

	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return $kmr->gantiDataNamaAndDesc($namaBaru, $descBaru);
});
Route::get("/updateKMR_withoutNama/{a}/{b}", function($idKMR, $descBaru){
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return $kmr->gantiDataDesc($descBaru);
});
Route::post("/updateKMR_withoutNama", function(){
	$idKMR = Input::get("idKMR");
	$descBaru = Input::get("descBaru");

	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return $kmr->gantiDataDesc($descBaru);
});


Route::get("/deleteKMR/{a}", function($idKMR){
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return $kmr->deleteData();
});
Route::post("/deleteKMR", function(){
	$idKMR = Input::get("idKMR");
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return $kmr->deleteData();
});


Route::get("/gantiurutanKMR", function(){
	$json = '[{"i":4,"u":1},{"i":1,"u":2}]';
	$kmr = new KategoriMenuResto;
	return $kmr->gantiUrutanFromJSON($json);
});
Route::post("/gantiurutanKMR", function(){
	$json = Input::get("jsonNya");
	$kmr = new KategoriMenuResto;
	return $kmr->gantiUrutanFromJSON($json);
});
/*--------------------------------------------KMR---------------------------------------------*/


/*-------------------------------------------MENU---------------------------------------------*/
Route::get("/getDatasManageMenuByIdKMR/{a}", function($idKMR){
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return json_encode($kmr->getData(KategoriMenuResto::GetData_TIPEManageMenu));
});
Route::post("/getDatasManageMenuByIdKMR", function(){
	$idKMR = Input::get("idKMR");
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return json_encode($kmr->getData(KategoriMenuResto::GetData_TIPEManageMenu));
});


Route::get("/insertMenu/{a}/{b}/{c}/{d}/{e}", function($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
	$menu = new Menu;
	return $menu->tambahData($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend);
});
Route::post("/insertMenu", function(){
	$idKMR = Input::get("idKMR");
	$namaBaru = Input::get("namaBaru");
	$hargaBaru = Input::get("hargaBaru");
	$descBaru = Input::get("descBaru");
	$isRecomend = Input::get("isRecomend");
	$menu = new Menu;
	return $menu->tambahData($idKMR, $namaBaru, $hargaBaru, $descBaru, $isRecomend);
});


Route::get("/updateMenu/{a}/{b}/{c}/{d}/{e}", function($idMenu, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
	$menu = Menu::find($idMenu);
	if($menu == NULL) return MENU::CODE_ERROR_GET_MENU;
	return $menu->gantiData($namaBaru, $hargaBaru, $descBaru, $isRecomend);
});
Route::post("/updateMenu", function(){
	$idMenu = Input::get("idMenu");
	$namaBaru = Input::get("namaBaru");
	$hargaBaru = Input::get("hargaBaru");
	$descBaru = Input::get("descBaru");
	$isRecomend = Input::get("isRecomend");
	$menu = Menu::find($idMenu);
	if($menu == NULL) return MENU::CODE_ERROR_GET_MENU;
	return $menu->gantiData($namaBaru, $hargaBaru, $descBaru, $isRecomend);
});


Route::get("/deleteMenu/{a}", function($idMenu){
	$menu = Menu::find($idMenu);
	if($menu == NULL) return MENU::CODE_ERROR_GET_MENU;
	return $menu->deleteData();
});
Route::post("/deleteMenu", function(){
	$idMenu = Input::get("idMenu");
	$menu = Menu::find($idMenu);
	if($menu == NULL) return MENU::CODE_ERROR_GET_MENU;
	return $menu->deleteData();
});


Route::get("/gantiurutanMenu", function(){
	$json = '[{"i":5,"u":2},{"i":7,"u":1}]';
	$menu = new Menu;
	return $menu->gantiUrutanFromJSON($json);
});
Route::post("/gantiurutanMenu", function(){
	$json = Input::get("jsonNya");
	$menu = new Menu;
	return $menu->gantiUrutanFromJSON($json);
});


Route::get("/duplikatMenu/{a}/{b}/{c}/{d}/{e}/{f}", function($idMenuLama, $idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend){
	$menu = Menu::find($idMenuLama);
	if($menu == NULL) return MENU::CODE_ERROR_GET_MENU;
	return $menu->duplikatData($idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend);
});
Route::post("/duplikatMenu", function(){
	$idMenuLama = Input::get("idMenuLama");
	$idKMRBaru = Input::get("idKMRBaru");
	$namaBaru = Input::get("namaBaru");
	$hargaBaru = Input::get("hargaBaru");
	$descBaru = Input::get("descBaru");
	$isRecomend = Input::get("isRecomend");
	$menu = Menu::find($idMenuLama);
	if($menu == NULL) return MENU::CODE_ERROR_GET_MENU;
	return $menu->duplikatData($idKMRBaru, $namaBaru, $hargaBaru, $descBaru, $isRecomend);
});
/*-------------------------------------------MENU---------------------------------------------*/


/*-------------------------------------------KTM---------------------------------------------*/
Route::get("/autocompleteKT/{a}", function($name){
	$kt = new MsKategoriTambahan;
	return json_encode($kt->getDatasByName($name));
});
Route::post("/autocompleteKT", function(){
	$name = Input::get("namaNya");
	$kt = new MsKategoriTambahan;
	return json_encode($kt->getDatasByName($name));
});


Route::get("/insertKategoriTambahanMenu/{a}/{b}/{c}/{d}/{e}", function($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis){
	$ktm = new KategoriTambahanMenu;
	return $ktm->tambahData($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis);
});
Route::post("/insertKategoriTambahanMenu", function(){
	$idMenu = Input::get("idMenu");
	$namaBaru = Input::get("namaBaru");
	$minPilih = Input::get("minPilih");
	$maxPilih = Input::get("maxPilih");
	$maxGratis = Input::get("maxGratis");
	$ktm = new KategoriTambahanMenu;
	return $ktm->tambahData($idMenu, $namaBaru, $minPilih, $maxPilih, $maxGratis);
});


Route::get("/updateKategoriTambahanMenu/{a}/{b}/{c}/{d}/{e}", function($idKTM, $namaBaru, $minPilih, $maxPilih, $maxGratis){
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->gantiDataNamaDanMinMaxMax($namaBaru, $minPilih, $maxPilih, $maxGratis);
});
Route::post("/updateKategoriTambahanMenu", function(){
	$idKTM = Input::get("idKTM");
	$namaBaru = Input::get("namaBaru");
	$minPilih = Input::get("minPilih");
	$maxPilih = Input::get("maxPilih");
	$maxGratis = Input::get("maxGratis");
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->gantiDataNamaDanMinMaxMax($namaBaru, $minPilih, $maxPilih, $maxGratis);
});
Route::get("/updateKategoriTambahanMenu/{a}/{b}/{c}/{d}", function($idKTM, $minPilih, $maxPilih, $maxGratis){
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->gantiDataMinMaxMax($minPilih, $maxPilih, $maxGratis);
});
Route::post("/updateKategoriTambahanMenu_withoutNama", function(){
	$idKTM = Input::get("idKTM");
	$minPilih = Input::get("minPilih");
	$maxPilih = Input::get("maxPilih");
	$maxGratis = Input::get("maxGratis");
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->gantiDataMinMaxMax($minPilih, $maxPilih, $maxGratis);
});


Route::get("/deleteKategoriTambahanMenu/{a}", function($idKTM){
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->deleteData();
});
Route::post("/deleteKategoriTambahanMenu", function(){
	$idKTM = Input::get("idKTM");
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->deleteData();
});


Route::get("/duplikatKategoriTambahanMenu/{a}/{b}/{c}/{d}/{e}/{f}", function($idKTM, $idMenuBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis){
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->duplikatData($idMenuBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis);
});
Route::post("/duplikatKategoriTambahanMenu", function(){
	$idKTM = Input::get("idKTM");
	$idMenuBaru = Input::get("idMenuBaru");
	$namaBaru = Input::get("namaBaru");
	$minPilih = Input::get("minPilih");
	$maxPilih = Input::get("maxPilih");
	$maxGratis = Input::get("maxGratis");
	$ktm = KategoriTambahanMenu::find($idKTM);
	if($ktm == NULL) return KategoriTambahanMenu::CODE_ERROR_GET_KTM;
	return $ktm->duplikatData($idMenuBaru, $namaBaru, $minPilih, $maxPilih, $maxGratis);
});
/*-------------------------------------------KTM---------------------------------------------*/


/*------------------------------------------IKTM---------------------------------------------*/
Route::get("/autocompleteIKT/{a}", function($name){
	$ikt = new MsItemKategoriTambahan;
	return json_encode($ikt->getDatasByName($name));
});
Route::post("/autocompleteIKT", function(){
	$name = Input::get("namaNya");
	$ikt = new MsItemKategoriTambahan;
	return json_encode($ikt->getDatasByName($name));
});


Route::get("/insertItemKategoriTambahanMenu/{a}/{b}/{c}", function($idKTM, $namaBaru, $hargaIKT){
	$iktm = new ItemKategoriTambahanMenu;
	return $iktm->tambahData($idKTM, $namaBaru, $hargaIKT);
});
Route::post("/insertItemKategoriTambahanMenu", function(){
	$idKTM = Input::get("idKTM");
	$namaBaru = Input::get("namaBaru");
	$hargaIKT = Input::get("hargaIKT");
	$iktm = new ItemKategoriTambahanMenu;
	return $iktm->tambahData($idKTM, $namaBaru, $hargaIKT);
});


Route::get("/updateItemKategoriTambahanMenu/{a}/{b}/{c}", function($idIKTM, $namaBaru, $hargaIKT){
	$iktm = ItemKategoriTambahanMenu::find($idIKTM);
	if($iktm == NULL) return ItemKategoriTambahanMenu::CODE_ERROR_GET_IKTM;
	return $iktm->gantiDataNamaDanHargaIKT($namaBaru, $hargaIKT);
});
Route::post("/updateItemKategoriTambahanMenu", function(){
	$idIKTM = Input::get("idIKTM");
	$namaBaru = Input::get("namaBaru");
	$hargaIKT = Input::get("hargaIKT");
	$iktm = ItemKategoriTambahanMenu::find($idIKTM);
	if($iktm == NULL) return ItemKategoriTambahanMenu::CODE_ERROR_GET_IKTM;
	return $iktm->gantiDataNamaDanHargaIKT($namaBaru, $hargaIKT);
});
Route::get("/updateItemKategoriTambahanMenu_withoutNama/{a}/{b}", function($idIKTM, $hargaIKT){
	$iktm = ItemKategoriTambahanMenu::find($idIKTM);
	if($iktm == NULL) return ItemKategoriTambahanMenu::CODE_ERROR_GET_IKTM;
	return $iktm->gantiDataHargaIKT($hargaIKT);
});
Route::post("/updateItemKategoriTambahanMenu_withoutNama", function(){
	$idIKTM = Input::get("idIKTM");
	$hargaIKT = Input::get("hargaIKT");
	$iktm = ItemKategoriTambahanMenu::find($idIKTM);
	if($iktm == NULL) return ItemKategoriTambahanMenu::CODE_ERROR_GET_IKTM;
	return $iktm->gantiDataHargaIKT($hargaIKT);
});


Route::get("/deleteItemKategoriTambahanMenu/{a}", function($idIKTM){
	$iktm = ItemKategoriTambahanMenu::find($idIKTM);
	if($iktm == NULL) return ItemKategoriTambahanMenu::CODE_ERROR_GET_IKTM;
	return $iktm->deleteData();
});
Route::post("/deleteItemKategoriTambahanMenu", function(){
	$idIKTM = Input::get("idIKTM");
	$iktm = ItemKategoriTambahanMenu::find($idIKTM);
	if($iktm == NULL) return ItemKategoriTambahanMenu::CODE_ERROR_GET_IKTM;
	return $iktm->deleteData();
});
/*-------------------------------------------KTM---------------------------------------------*/


/*-------------------------------------------KM---------------------------------------------*/
Route::get("/autocompleteKomposisi/{a}", function($name){
	$ikt = new MsKomposisi;
	return json_encode($ikt->getDatasByName($name));
});
Route::post("/autocompleteKomposisi", function(){
	$name = Input::get("namaNya");
	$ikt = new MsKomposisi;
	return json_encode($ikt->getDatasByName($name));
});


Route::get("/getDatasKomposisiMenuByIdKMR/{a}", function($idKMR){
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return json_encode($kmr->getData(KategoriMenuResto::GetData_TIPEKomposisi));
});
Route::post("/getDatasKomposisiMenuByIdKMR", function(){
	$idKMR = Input::get("idKMR");
	$kmr = KategoriMenuResto::find($idKMR);
	if($kmr == NULL) return KategoriMenuResto::CODE_ERROR_GET_KMR;
	return json_encode($kmr->getData(KategoriMenuResto::GetData_TIPEKomposisi));
});	


Route::get("/InsertDeleteKomposisiMenu", function(){
	$json = '{"im":7,"ins":["Babi"],"del":[]}';
	$km = new KomposisiMenu;
	return $km->manageKomposisiMenuFromJSON($json);
});
Route::post("/InsertDeleteKomposisiMenu", function(){
	$json = Input::get("json");
	$km = new KomposisiMenu;
	return $km->manageKomposisiMenuFromJSON($json);
});
/*-------------------------------------------KM---------------------------------------------*/



Route::get("/m/post/header/hot/readnew", function(){
	/*
		return = data header (id & lastUpdated)
		requirement = max 10 post where whenIsHot is not null && whenIsHot order by desc
	*/

	return '[[1,"2015-12-04 15:06:02"],[5,"2015-12-04 16:15:15"],[23,"2015-12-04 16:22:50"]]';

	$arr = array();
	$tf = new FzyTimeFormat;

	$id = 1;
	$lastUpdated = $tf->FzyDateVersion(1);
	$objHeader = array(
		$id, $lastUpdated
	);
	array_push($arr, $objHeader);

	$id = 2;
	$lastUpdated = $tf->FzyDateVersion(1);
	$objHeader = array(
		$id, $lastUpdated
	);
	array_push($arr, $objHeader);

	$id = 3;
	$lastUpdated = $tf->FzyDateVersion(1);
	$objHeader = array(
		$id, $lastUpdated
	);
	array_push($arr, $objHeader);

	return json_encode($arr);

});

Route::get("/m/post/header/hot/readmore", function(){

});













/*
Kalendar
*/

Route::get("/kal", function(){
	return View::make("kalendar.main");
});