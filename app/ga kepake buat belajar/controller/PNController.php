<?php

class PNController extends BaseController {

/*
|--------------------------------------------------------------------------
| Default Home Controller
|--------------------------------------------------------------------------
r
| You may wish to use controllers instead of, or in addition to, Closure
| based routes. That's great! Here is an example controller method to
| get you started. To route to this controller, just add the route:
|
|	Route::get('/', 'HomeController@showWelcome');
|
*/
	public function indexPN(){
		return View::make('PN.index');
	}

	public function listPage(){

		$userid =  Auth::id();

		$page = DB::table('page')->where('userid',$userid)->orderBy('id','desc')->get();

		if(empty($page)){
			$return = array(
        	'status' => 201,
        	'msg' => 'No Page'
        	);
		}
		else
		{
			$temp=array();

			foreach ($page as $key => $value) {
				$temp[$key] = array(
	        	'name' => $page[$key]->name ,
	        	'id' => $page[$key]->id,
	        	'updated_at' => $page[$key]->updated_at );
			}

			$return = array(
        	'status' => 200,
        	'msg' => 'Success',
        	'data' => $temp
        	);
		}

		echo json_encode($return);
	}

	public function publishPage($id)
	{
		//get page id and content_json and name
		$page_id = $id;
		$page = Page::find($page_id);

 		echo json_encode($return);
	}

	public function loadPage(){
		//get page id
		$page_id =  Input::get('page_id','');
		$userid = Auth::id();
		$page = DB::table('page')->where('id', $page_id)->where('userid',$userid)->first();
		
		//$this->clearUnusedImages($page_id, $page->content_json);

		if(empty($page)){
			$return = array(
        	'status' => 201,
        	'msg' => 'No File'
        	);
		}
		else
		{
			$return = array(
        	'status' => 200,
        	'msg' => 'Success',
        	'data' => $page
        	);
		}

		echo json_encode($return);
	}
	
	public function clearUnusedImages($id, $items){
		$items = json_decode($items);

		$bykitems = sizeof($items);
		$images = array();
		for($i = 0 ; $i < $bykitems; $i++)
		{
			if($items[$i]->type == 'image')
			{
				$temp = explode('/', $items[$i]->value);
				array_push($images,$temp[3]);
			}
		}
		$bykimages = sizeof($images);
		if($bykimages == 0){
			$allImages = scandir(public_path() . '/pages/' . $id);
			$bykallimages = sizeof($allImages);
			for($i = 2; $i < $bykallimages; $i++)
			{
				unlink(public_path() . '/pages/' . $id . '/' . $allImages[$i]);
			}
		}else{
			$allImages = scandir(public_path() . '/pages/' . $id);
			$bykallimages = sizeof($allImages);
			for($i = 2; $i < $bykallimages; $i++)
			{
				if(!in_array($allImages[$i], $images))
				{
					unlink(public_path() . '/pages/' . $id . '/' . $allImages[$i]);
				}
			}
		}
	}


	public function deleteImage(){

		$get_path = Input::get('link','');

		if(file_exists($get_path))
		{	
			unlink($get_path);
			$return = array(
        	'status' => 200,
        	'msg' => 'Success'
        	);
		}
		else
		{
			$return = array(
        	'status' => 201,
        	'msg' => 'No File'
        	);
		}
		
		echo json_encode($return);
	}

	public function deletePage(){
		//get page id
		$page_id= 1;
		$page = Page::find($page_id);

		if(!empty($page))
		{
			$page->delete();
			$return = array(
        	'status' => 200,
        	'msg' => 'Success'
        	);

		}
		else
		{
			$return = array(
        	'status' => 201,
        	'msg' => 'Failed to delete'
        	);
		}

 		echo json_encode($return);

	}

	public function savePage(){
		//get page id and content_json and name
		$page_id = Input::get('id',1);
		$content_json =  Input::get('content_json','');
		$page_setup =  Input::get('page_setup','');
		$page_name =  Input::get('page_name','');
		$page = Page::find($page_id);
		
		$this->clearUnusedImages($page_id, $content_json );

		if(!empty($page))
		{
			$page->content_json = $content_json;
			$page->page_setup = $page_setup;
			$page->name = $page_name;
			$page->save();
			$return = array(
        	'status' => 200,
        	'msg' => 'Success'
        	);
		}
		else
		{
			$return = array(
        	'status' => 201,
        	'msg' => 'Failed to save'
        	);
		}
 		

 		echo json_encode($return);

	}

	public function newPage(){
		//get page name for insert to db

		$pagename = Input::get('pagename','');
		$userid = Auth::id();
		$width =  Input::get('width','');
		$height =  Input::get('height','');
		if(!empty($pagename)&& !empty($width) && !empty($height)){
			$temp = array('width'=>$width.'px' , 'height'=>$height.'px', 'color'=>'#FFFFFF', 'counter_text'=>0, 'counter_image'=>0);
			$page = new Page;
			$page->userid = $userid;
			$page->name = $pagename;
			$page->page_setup = json_encode($temp);
			$page->save();


			$cek = DB::table('page')->orderBy('id','desc')->first();
			$path = public_path().'/pages/'.$cek->id;

			
		
			$return = array();
			
			if(file_exists($path)){	
				 $return = array(
	        	'status' => 201,
	        	'msg' => 'Already Exists'
	        	);
			}
			else
			{
				//new folder , insert db
			
				File::makeDirectory($path, $mode = 0777, true, true);

				 $return = array(
	        	'status' => 200,
	        	'msg' => 'Success',
	        	'data'=> array(
	        			'id' => $cek->id
	        		)
	        	);
			}
		}
		else{
			 $return = array(
        	'status' => 201,
        	'msg' => 'No Page Name'
        	);
		}
		echo json_encode($return);
	}

	public function doUpload(){
		//get id page
		$id =  Input::get('id','');
		$name =  Input::get('name','');
		$destinationPath = public_path().'/pages/' . $id;

		$return = array();

		$input = Input::all();
		$maxsize = 1024*1024;
		$file = Input::file('photo');



		if(!empty($id)){
			if(!empty($file)){
				if ($file->isValid())
					{
			    		$size = $file->getSize();
						/*
							$mime = $file->getMimeType();

			    			if($mime == 'image/jpeg' || $mime =='image/png'){
			    		*/

	    				$fileName = Input::file('photo')->getClientOriginalName();
						$titikfile = strrpos($fileName, ".");
						$nilainyaposisi = strlen($fileName) - $titikfile - 1;
						$ext = substr($fileName, -$nilainyaposisi, $nilainyaposisi);

					    if($ext == "jpg" || $ext == "bmp" || $ext == "png" || $ext == "jpeg"){
					    	
							if($size < $maxsize){
						    	//$destinationPath =  public_path().'/pages/3'; #dummy
						    	$temp = explode('.', $file->getClientOriginalName());

						        $filename        = $name . "." . $temp[sizeof($temp)-1];
						        $uploadSuccess   = $file->move($destinationPath, $filename);
						       
						        $return = array(
						        	'status' => 200,
						        	'msg' => 'Success',
						        	'data' => array(
						        		'filename' => $filename
						        		)
						        	);
						    }
						    else
						    {
						    	  $return = array(
						        	'status' => 201,
						        	'msg' => 'Oversize'
						        	);
						    }
					    }
					    else
					    {
					    	  $return = array(
					        	'status' => 201,
					        	'msg' => 'Wrong Type'
					        	);
					    }
					    
					}
					else
					{
						  $return = array(
				        	'status' => 201,
				        	'msg' => 'Invalid file'
				        	);
					}

			}
			else
			{
					  $return = array(
			        	'status' => 201,
			        	'msg' => 'Empty Image'
			        	);
			}
	
		}else{  
			$return = array(
	        	'status' => 201,
	        	'msg' => 'Empty id'
	        	);
		}
		return json_encode($return);
	}
	
	function publish($page_id){
		//get page id
		$data["page_id"] = $page_id;

		return View::make('PN.publish', $data);
	}
}