<?php
class VersioningM extends BaseModel{

	protected $table = 'versioning';

	protected $colNames = array(
		"id",
		"name",
		"version",
	);

	
	public function Get_MasterCardItemVersion(){
		$res = self::whereRaw( $this->colNames[0] . " = 1")->get();
		return $res[0]->version;
	}

}
