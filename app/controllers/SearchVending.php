<?php

class SearchVending extends BaseController {

	public function Get_ItemNameForAutoComplete(){
		$itemName = Input::get('itemname');
		$item = new ItemM;
		return $item->Get_ItemNameForAutoComplete($itemName);
	}

	public function Get_MasterCardItemVersion(){
		$versioning = new VersioningM;
		return $versioning->Get_MasterCardItemVersion();
	}

	public function Get_MasterCardItem(){
		$item = new ItemM;
		return $item->Get_MasterCardItem();
	}

	public function Get_SearchVendingItem(){
		$idItem = Input::get('iditem');
		$idItem = 505;
		$jsonString = $this->getJsonString_SearchVendingItem($idItem);
		return $jsonString;
	}

	private function getJsonString_SearchVendingItem($idItem){
		$item = new ItemM;
		$jsonString_Item = $item->Get_DataItem_ForSearchVending($idItem);

		$vending = new VendingM;
		$jsonString_Vending = $vending->Get_DataVending_ForSearchVending($idItem);

		$itemVendingCard = new ItemVendingCardM;
		$jsonString_ItemVendingCard = $itemVendingCard->Get_DataItemVendingCard_ForSearchVending($idItem);

		$itemVending = new ItemVendingM;
		$jsonString_ItemVending = $itemVending->Get_DataItemVending_ForSearchVending($idItem);

		$json = array(
			"datatableitem" => $jsonString_Item,
			"datatablevending" => $jsonString_Vending,
			"datatableitemvendingcard" => $jsonString_ItemVendingCard,
			"datatableitemvending" => $jsonString_ItemVending,
		);
		return json_encode($json);
	}
}
