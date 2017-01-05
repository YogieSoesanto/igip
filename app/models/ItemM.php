<?php
class ItemM extends BaseModel{

	protected $table = 'item';
	private $itemCategory;

	protected $colNames = array(
		"id",
		"name",
		"slot",
		"iditemcategory"
	);

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

	public function Get_DataItem_ForSearchVending($idItem){
		$item = ItemM::whereRaw("id = ?", array($idItem))->first();

		$arrayItem = $item->toArray();
		$arrayItem['namaKategori'] = $item->getItemCategoryName();
	
		return ($arrayItem);
	}

	private function getItemCategoryName(){
		if($this->itemCategory == null)$this->itemCategory = ItemCategoryM::whereRaw("id = ?", array($this->iditemcategory))->first();
		return $this->itemCategory->getName();
	}

}
