<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>common/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="<?= base_url() ?>common/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?= base_url() ?>common/bootstrap/js/bootstrap.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>common/ui/js/jquery-ui-1.10.4.custom.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>common/ui/js/jquery-ui-1.10.4.custom.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>common/ui/css/base/jquery-ui-1.10.4.custom.min.css">
	<!--Bootstrap datetimepicker-->
		<script type="text/javascript" src="<?= base_url() ?>common/bootstrap_datetimepick/moment-develop/moment.js"></script>
		<script type="text/javascript" src="<?= base_url() ?>common/bootstrap_datetimepick/bootstrap/js/transition.js"></script>
		<script type="text/javascript" src="<?= base_url() ?>common/bootstrap_datetimepick/bootstrap/js/collapse.js"></script>
		<script type="text/javascript" src="<?= base_url() ?>common/bootstrap_datetimepick/bootstrap/dist/js/bootstrap.min.js"></script>
		
		<script type="text/javascript" src="<?= base_url() ?>common/bootstrap_datetimepick/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js"></script>
		<link rel="stylesheet" href="<?= base_url() ?>common/bootstrap_datetimepick/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?= base_url() ?>common/bootstrap_datetimepick/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.css">
	<!--tinymce-->	
	<script type="text/javascript" src="<?= base_url() ?>common/tinymce/tinymce.min.js"></script>
	<!---->
	<style type="text/css">
		body{
			background: rgb(222,232,234);
			min-height:600px;
		}
		h3{
			margin: 0px;
			display: inline;
		}
		.top{
			padding: 10px 10px;
			background: rgb(255,255,255);
			margin-bottom: 30px;
		}
		.loginout{
			padding:5px;
			text-align: center;
			display: block;
			background:rgb(0,0,0);
			color:rgb(255,255,255);
			float: right;
			border-radius: 20px;
			width: 30px;
			height: 30px;
		}
		.loginout:active,.loginout:visited,.loginout:link{
			color:rgb(255,255,255);
		}
		.loginout:hover{
			border:1px solid rgb(0,0,0);
			background: rgb(255,255,255);
			color: rgb(0,0,0);

		}
		.username{
			float: right;
			margin-right: 10px;
			padding: 5px;
		}
		.white{
			background:rgb(255,255,255);
			padding: 20px;
			margin-top: 50px;
		}
		textarea{
			resize:none
		}
		.lock{
			color:rgb(255,0,0);
		}
		.img-head{
			background:rgb(237,237,237);
			height: 50px;
			padding:5px 10px;
			margin-top: 20px;
			border:1px solid rgb(176,176,176);
			border-bottom: 0px;
		}
		.img-head .img-logo{
			display: block;
			margin: 5px;
			float: left;
			width: 30px;
			height: 30px;
			font-size: 25px;
		}
		.img-body{
			min-height: 200px;
			border-left:1px solid rgb(176,176,176);
			border-right:1px solid rgb(176,176,176);
		}
		.image-body{
			width: 100%;
			display:inline-block;
		}
		.image-item{
			margin:10px;
			float: left;
			width:90px;
			height: 90px;
			border: 1px solid #999;
		}
		.image-del{
			position: relative;
			bottom:100%;
			left: 65px;
		}
		.image-del a{
		    text-decoration: none;
		    text-align: center;
		    display: block;
		    background: red;
		    width: 20px;
		    height: 20px;
		    color: #fff;
		    border-radius: 20px;
		    margin-top: 4px;
		}
		.image-item img{
			width: 100%;
			height: 100%;

		}
		.img-foot{
			background:rgb(237,237,237);
			height: 30px;
			padding:3px 6px;
			border:1px solid rgb(176,176,176);		
		}
		.img-foot span{
			color:rgb(255,255,255);
			background:rgb(0,0,0);
			border-radius: 5px;
			margin-right: 3px;
		}
		.form-group{
			width: 100%;
			display: table;
			margin-bottom:10px
		}
		input[type="checkbox"]{
			margin-right: 5px;
		}
		label{
		    padding-right: 10px;
		}
	</style>
	<script>
		$(document).ready(function() {
			
			$(window).bind("pageshow", function(event){
				//reset hidden 
				$('input[type=radio]:checked').click();
				
				
				if($('#imageurl').val()!=""){
					$('input[name=ads_image]').val($('#imageurl').val());
					$('#lookimage').attr({'src':'<?=$ads_img_url ?>'+$('#imageurl').val()}).css({'display':'inline'});
				}
			});
			
			//datetime picker
			$("#publish_datetime").datetimepicker({		
				showClose: true,
				showTodayButton: true,
				keepOpen: false,
				//daysOfWeekDisabled: [1,2,3,4,5,6],
				format: "YYYY-MM-DD"	
			});
			$("#end_datetime").datetimepicker({		
				showClose: true,
				showTodayButton: true,
				keepOpen: false,
				//daysOfWeekDisabled: [1,2,3,4,5,6],
				format: "YYYY-MM-DD"	
			});
			
			//form detail
			ads_type = 0;
			$('input[name=ads_type]').click(function(){
				if($(this).is(':checked')){
					ads_type = $(this).val();
				}
				
				//hide or show
				if(ads_type!=0){
					$('#form-detail ').removeClass('hide');
				}
				else{
					$('#form-detail ').addClass('hide');
				}
				
				$('#form-detail .type-all').addClass('hide');
				$('#form-detail .type-'+ads_type).removeClass('hide');
				
			});
			
			//form landing
			landing_type = 0;
			$('input[name=landing_type]').click(function(){
				if($(this).is(':checked')){
					landing_type = $(this).val();
				}
				
				//hide or show
				if(landing_type!=0){
					$('#form-landing ').removeClass('hide');
				}
				else{
					$('#form-landing ').addClass('hide');
				}
				
				$('#form-landing .type-all').addClass('hide');
				$('#form-landing .type-'+landing_type).removeClass('hide');
				
			});
			
			//form detail upload image
			$('#image').click(function(){
				$('#newfile').attr({accept:"image/*"}).unbind('change').bind('change',function(){
					if(!$("#newfile")[0].files[0]['type'].match(/.jpg|.jpeg|.png/i)){//|.gif|.bmp
						$("#newfile").val('');
						alert('圖片只能為png,jpg,jpeg格式!');
						return;
					};

					if($("#newfile")[0].files[0]['size']>2*1024*1024)					{
						$("#newfile").val('');
						alert('圖片超過'+2+'MB!');
						return;
					};
					
					var formData = new FormData();
					formData.append("image",$("#newfile")[0].files[0]);
					$.ajax({
						type:"post",
						async:true,
						Accept:'text/html;charset=UTF-8',
						data:formData,
						contentType:"multipart/form-data",
						url: "<?=base_url().'Ads/uploadImage/'?>"+ads_type,
						processData: false, // 告诉jQuery不要去处理发送的数据
						contentType: false, // 告诉jQuery不要去设置Content-Type请求头,
						dataType:'json',
						success:function(data){    
							
							if(data.code==200){
								$('#imageurl').val(data.file);
								$('input[name=ads_image]').val(data.file);
								$('#lookimage').attr({'src':'<?=$ads_img_url ?>'+data.file}).css({'display':'inline'});
								$('#delimage').css({'display':'inline-block'});
							}
							else{
								alert(data.msg);
							}
							$("#newfile").val('');
						},
						error:function(){
							alert("上傳失敗");
						}
					});
				})
				$('#newfile').click();
			})
			
			//form submit
			$('#save').click(function(){
				
				// $.post($("#ads_form").attr('action'), $('#ads_form').serialize()).done(function( data ) {
					// json = $.parseJSON(data);
					// if(json.code!=200){
						// alert(json.msg);
					// }
				// });
				
				$("#ads_form").submit();
			});
			
			//form landing
			tinyMCE.init({
				
				selector: "#detail_content", 
				
				// language: "zh_TW", // 語系(CDN沒有中文，需要下載原始source才有)
				height : "300",
				theme: "modern", 
				plugins : "advlist autolink link image lists preview",
				 
				menubar: "view",
				
				images_upload_url: '<?=base_url().'Ads/uploadImage/-1'?>',
				images_upload_handler: function (blobInfo, success, failure) {
					var xhr, formData;
      
					xhr = new XMLHttpRequest();
					xhr.withCredentials = false;
					xhr.open('POST', '<?=base_url().'Ads/uploadImage/-1'?>');
				  
					xhr.onload = function() {
						var json;
					
						if (xhr.status != 200) {
							failure('HTTP Error: ' + xhr.status);
							return;
						}
					
						json = JSON.parse(xhr.responseText);
						console.log( json );
						if (!json || typeof json.file != 'string') {
							failure('Invalid JSON: ' + xhr.responseText);
							return;
						}
						
						success('<?=$ads_img_url ?>'+json.file);
					};
				  
					formData = new FormData();
					formData.append('image', blobInfo.blob(), blobInfo.filename());
				  
					xhr.send(formData);
				},

			});
		});
	
		

		
	</script>
</head>
<body>

	
	
	<div class="container">
		
			<form id="ads_form" action="<?= base_url().'Ads/'.$action ?>" enctype="multipart/form-data" method="post" accept-charset="utf-8">
				<div class="col-md-12 white">
					<div class="form-group">
						<h3><label for="inputPassword3" class="col-sm-2 control-label">廣告類型<span class="lock">*</span></label></h3>
						<div class="col-sm-10">							
							<label><input name="ads_type" type="radio" value="1">Banner - 300x100</label>
							<label><input name="ads_type" type="radio" value="2">Banner - 300x300</label>
							<label><input name="ads_type" type="radio" value="3">標題(30字)</label>
							<label><input name="ads_type" type="radio" value="4">標題(20字)+內容(50字)</label>
							<label><input name="ads_type" type="radio" value="5">縮圖+標題(20字)</label>
			
							
						</div>
					</div>
				</div>
				
				<div class="col-md-12 white">
					<div class="row"><h3><label class="col-sm-2 control-label">廣告列表資料</label></h3></div>
					
					<div id="form-detail" class="row hide">
						<div class="form-group ">
							<label for="ads_code" class="col-sm-2 control-label">識別碼(count數用)<span class="lock">*</span></label>
							<div class="col-sm-10">
								<input id="ads_code" type="text" name="ads_code" class="form-control" value="" required >
							</div>
						</div>
						<div class="form-group type-all type-3 type-4 type-5 ">
							 <label for="title" class="col-sm-2 control-label">標題<span class="lock">*</span></label>
							<div class="col-sm-10">
								<input id="title" type="text" name="title" class="form-control" value="">
							</div>
						</div>
						<div class="form-group type-all type-4">
							 <label for="content" class="col-sm-2 control-label">内容<span class="lock">*</span></label>
							<div class="col-sm-10">
								<textarea id="content" class="form-control" name="content" rows="5"></textarea>
							</div>
						</div>
						<div class="form-group type-all type-1 type-2 type-5">
							<label for="ads_image" class="col-sm-2 control-label">圖片<span class="lock">*</span></label>
							<div class="col-sm-10">
								<div class="col-sm-5">
									<div class="row">
										<div class="form-group">
										    <div id="image" class="input-group">
										      	<input type="text" id="imageurl" class="form-control" disabled="" style="cursor: pointer;">
										     	<input type="hidden" id="ads_image" name="ads_image" value="">
										        <div class="input-group-addon" ><span class="glyphicon glyphicon-file"></span>選擇圖片</div>
										    </div>
										    <img src="" id='lookimage' width="300"  style="display:none;margin: 10px;">
										</div>
									</div>
									<div id="img_reminder" class="col-sm-5">
							        
									</div>
						        </div>
						     
							</div>
						</div>
						
						
						<div class="form-group ">
							 <label for="publish_datetime" class="col-sm-2 control-label">出版日期<span class="lock">*</span></label>
							<div class="col-sm-10">
								<div class="input-group">
						            <input type="text" class="form-control" id="publish_datetime" name="publish_datetime" required >
						            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
						        </div>
						       
							</div>
						</div>
						<div class="form-group ">
							 <label for="end_datetime" class="col-sm-2 control-label">完結日期<span class="lock">*</span></label>
							<div class="col-sm-10">
								<div class="input-group">
						            <input type="text" class="form-control" id="end_datetime" name="end_datetime" required >
						            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
						        </div>
						        
							</div>
						</div>
						<div class="form-group">
								<label  class="col-sm-2 control-label">鏈接類型<span class="lock">*</span></label>
								<div class="col-sm-10">			
									<label><input name="landing_type" type="radio" value="1">App外顯示</label>
									<label><input name="landing_type" type="radio" value="2" required>App內顯示</label>
								</div>
							</div>
					</div>
				</div>
				
				<div class="col-md-12 white">
					<div class="row"><h3><label class="col-sm-2 control-label">廣告鏈接資料</label></h3></div>
						<div id="form-landing" class="row hide">
							<div class="form-group type-all type-1 ">
								 <label for="landing_url" class="col-sm-2 control-label">外部URL<span class='lock'>*</span></label>
								<div class="col-sm-10">
									<input type="text" id="landing_url" name="landing_url" class="form-control" value="" />
								</div>
							</div>
							<div class="form-group type-all type-2 ">
								<label for="detail_title" class="col-sm-2 control-label">文章標題<span class="lock">*</span></label>
								<div class="col-sm-10">
									<input id="detail_title" type="text" name="detail_title" class="form-control" value="">
								</div>
							</div>
							<div class="form-group type-all type-2 ">
								 <label for="detail_content" class="col-sm-2 control-label">文章内容<span class="lock">*</span></label>
								<div class="col-sm-10">
									<textarea id="detail_content" class="form-control" name="detail_content" rows=10 ></textarea>
								</div>
							</div>
						</div>					
				</div>
				
				<div class="row">
					<div style="text-align: center;margin-bottom: 20px;">
							<button class="btn btn-danger" type="button"  id="save">保存</button>
							<button class="btn btn-primary" onclick="window.location.href ='<?= base_url() ?>Ads/list';return false;">返回</button>
					</div>
				</div>
			
			</form>


  

			<input type="file" id="newfile" accept="image/*" style="display:none;">
	   
	</div>
	
</body>
</html>