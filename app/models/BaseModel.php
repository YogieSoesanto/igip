<?php

class BaseModel extends Eloquent{

	public $timestamps = false;

	public function scopeIdDescending($query){
		return $query->orderBy("id","DESC");
	}
		
}
