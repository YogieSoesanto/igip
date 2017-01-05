<?php
class ItemVendingCardM extends BaseModel{

	protected $table = 'itemvendingcard';

	protected $colNames = array(
		"id",
		"iditemvending",
		"idcard",
		"qty"
	);

	
	public function Get_DataItemVendingCard_ForSearchVending($idItem){
		$res = self::whereRaw("iditemvending in (select id from itemvending where iditem = ?)", array($idItem))->get();
		return ($res);
	}

}
