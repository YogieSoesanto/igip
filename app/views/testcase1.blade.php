@extends('template_testcase')

@section("docReady")
	$("input").focus();

	$('#namaitem').keyup(function(e) {
	    clearTimeout($.data(this, 'timer'));
	    if (e.keyCode == 13)
	      search(true);
	    else
	      $(this).data('timer', setTimeout(search, 500));
	});

	function search(force) {
	    var existingString = $("#namaitem").val();
	    var changeHTMLText = "";
	    if(existingString.length >= 3){
	    	var urlAJAX = "{{URL::to('/ajaxGetItemsByName')}}";
		   	$.post(urlAJAX, { _token: "{{csrf_token()}}", namaitem: existingString }).done(function(result) {
				changeHTMLText = manageResultSearch(result);
				$("#asal").html(changeHTMLText);
			});
	    }else{
	    	changeHTMLText = "Masukan minimal 3 huruf";
			$("#asal").html(changeHTMLText);
	    }
	}

	function manageResultSearch(result){
		var text = "";
		
		var datas = JSON.parse(result);
		var byk = datas.length;
		if(byk == 0)text = "Data tidak ditemukan";
		else{
			for(var i = 0 ; i < byk ; i++){
				var obj = datas[i];

				var iconNya = "icons/" + obj.id + ".png";
				var imagePath = "{{asset("asset/items/")}}/"+iconNya;
				text += "<tr>";
					text += "<td>"+obj.name+"</td>";
					text += "<td>"+obj.idItemCategory+"</td>";
					text += "<td><img src='"+imagePath+"'/></td>";
				text += "</tr>";
			}
		}
		return text;
	}


@stop

@section('mainBody')
	
	<input id='namaitem' />
	<input type='button' id='but' />
	<p id='asal'></p>

@stop