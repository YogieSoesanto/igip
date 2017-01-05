<?php
class ItemVendingM extends BaseModel{

	protected $table = 'itemvending';

	protected $colNames = array(
		"id",
		"idvending",
		"iditem",
		"plus",
		"element",
		"slothave",
		"leftslotunused",
		"cardused",
	);

	/*public function Get_IdVendingRelatedToItemId($idItem){

	}*/

	public function Get_DataItemVending_ForSearchVending($idItem){
		$res = self::whereRaw( $this->colNames[2] . " = ?", array($idItem))->get();
		return ($res);
	}

	
	public function Get_ItemNameForAutoComplete($name){
		$coloumns = $this->colNames[0] . ", " . $this->colNames[1];
		$condition = $this->colNames[1] . " like '%" . $name . "%'";
		$return = DB::select("select ".$coloumns." from " .$this->table. " where " . $condition);
		return json_encode($return);
	}

	public function Get_MasterCardItem(){
		$coloumns = $this->colNames[0] . ", " . $this->colNames[1];
		$condition = $this->colNames[3] . " = 10";
		$return = DB::select("select ".$coloumns." from " .$this->table. " where " . $condition);
		return json_encode($return);
	}

}
