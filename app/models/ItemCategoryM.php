<?php
class ItemCategoryM extends BaseModel{

	protected $table = 'itemcategory';

	protected $colNames = array(
		"id",
		"name_given",
	);

	
	public function getName(){
		return $this->name_given;
	}

}
