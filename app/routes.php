<?php

Route::get('/Get_ItemNameForAutoComplete',
	array('uses' => 'SearchVending@Get_ItemNameForAutoComplete'));


Route::get('/Get_MasterCardItemVersion',
	array('uses' => 'SearchVending@Get_MasterCardItemVersion'));


Route::get('/Get_MasterCardItem',
	array('uses' => 'SearchVending@Get_MasterCardItem'));


Route::get('/Get_DetailSearch_DataForClientDB',
	array('uses' => 'SearchVending@Get_SearchVendingItem'));
















Route::get('/getIdVending',
	array('uses' => 'Vending@getIdVendingActiveOrNull'));


Route::get('/startVending',
	array('uses' => 'Vending@startVending'));


Route::get('/ilham',
	array('uses' => 'Vending@startVending'));





/* controllers */
Route::post('/ajaxGetItemsByName',
	array('before'=>'csrf', 'uses' => 'SearchVending@getItemsByName'));




/* views */

//Route::get("/", function(){	return ""; });
Route::get('/',
	array('uses' => 'SearchVending@asal'));

Route::get('/tc1',
	array('uses' => 'ViewsMaker@testcase1'));

Route::get('/tc1/{a}', function($namaitem)
{
    $svHelper = new SearchVendingHelper;
	return $svHelper->getItemsByName($namaitem);
});


Route::get("/fadly", function(){
	return View::make("anak", array(
		"inidarimanahayo" => "nah ini isinya",
	));
});