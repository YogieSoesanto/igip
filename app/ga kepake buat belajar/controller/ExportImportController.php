<?php
class ExportImportController extends BaseController {
	
	public function tarik($nama){
		//tarik data dari table $nama dari DB yang di specify di app/database.php, 
		//lalu simpan data dari table $nama ke storage/exportimport
		$columns = Schema::getColumnListing($nama);
		$bykKol = sizeof($columns);
		if($bykKol==0)
			return "GAGAL";
		$res = DB::table($nama)->get();
		$byk = sizeof($res);
		$resbaru = array();
		for($i=0;$i<$byk;$i++){
			$temp = array();
			for($j=0;$j<$bykKol;$j++){
				array_push($temp, $res[$i]->$columns[$j]);
			}
			array_push($resbaru, $temp);
		}
		
		file_put_contents(storage_path()."/exportimport/data.txt", json_encode($resbaru));
		file_put_contents(storage_path()."/exportimport/namakol.txt", json_encode($columns));
		file_put_contents(storage_path()."/exportimport/namadb.txt", Config::get('database.default'));
		
		return "SUKSES";
	}
	public function simpan($nama, $hapusDulu){
		//tarik data dari storage/exportimport, 
		//lalu simpan data ke table $nama ke DB yang di specify di app/database.php

		if(file_get_contents(storage_path()."/exportimport/namadb.txt") == Config::get('database.default')){
			return "DB SAMA";
		}
		$stringdata = file_get_contents(storage_path()."/exportimport/data.txt");
		$stringNamaKolNya2 = file_get_contents(storage_path()."/exportimport/namakol.txt");

		$columns = json_decode($stringNamaKolNya2);
		$res = json_decode($stringdata);
		$byk = sizeof($res);
		$bykKolom = sizeof($columns);

		$arrayjson = array();

		$kolomMulaiDari = 1;
		if($hapusDulu == "1"){
			DB::table($nama)->delete();
			$kolomMulaiDari = 0;
		}

		$namaKolNya = "";
		for($j=$kolomMulaiDari;$j<$bykKolom;$j++){
			$namaKolNya .= $columns[$j];
			if($j < $bykKolom-1)
				$namaKolNya .= ",";
			
		}

		for($i=0;$i<$byk;$i++){
			$dataBaris1 = $res[$i];
			$isi = "";
			for($j = $kolomMulaiDari ; $j < $bykKolom ; $j++){

				if($dataBaris1[$j] == "null" || $dataBaris1[$j] == ""){
					$isi .= "NULL";
				}
				else{
					$dataCell = $dataBaris1[$j];
					$isi .= "'" . $dataCell ."'";
				}

				if($j < $bykKolom - 1){
					$isi .= ",";
				}
			}
			$query = "INSERT INTO " .$nama ." (" . $namaKolNya . ") VALUES (".$isi.")";
			array_push($arrayjson, $query);
			
			try{
				$testres = DB::Select("SELECT * from " . $nama . " WHERE " . $columns[0] . " = " . $dataBaris1[0]);
				if(sizeof($testres) > 0){
					DB::insert("ASD");
				}
				if($nama != "users"){
					DB::insert($query);
				}else{
					$user = new User;
					for($j = $kolomMulaiDari ; $j < $bykKolom ; $j++){
						$user->$columns[$j] = $dataCell;
					}
					$user->save();
				}
			}catch(Exception $e){
				//return $e;
				$query = "UPDATE " . $nama . " SET ";
				for($j = 1 ; $j < $bykKolom ; $j++){
					if($dataBaris1[$j] == "null"){
						$query .= $columns[$j] . " = NULL";
					}
					else{
						$dataCell = $dataBaris1[$j];
						$query .= $columns[$j] . " = '" . $dataCell . "'";
					}
					if($j < $bykKolom - 1){
						$query .= ",";
					}
				}
				$query .= " WHERE " . $columns[0] . " = " . $dataBaris1[0];
			}
		}
		//return json_encode($arrayjson);
		return "SUKSES";
	}
}
