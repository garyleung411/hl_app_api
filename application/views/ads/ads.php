<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no"> 
		<title>頭條日報</title>
		<link rel="stylesheet" href="<?= base_url() ?>common/bootstrap/css/bootstrap.min.css" >
		<script src="<?= base_url() ?>common/jquery/jquery.min.js"></script>
		<script src="<?= base_url() ?>common/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?= base_url() ?>common/jquerymobile/jquery.mobile.custom.min.js"></script>

		
		
		<script>
		/*** app func use ***/
		function callAppFunc(str){
			<?php 
				preg_match("/Android|webOS/", $_SERVER['HTTP_USER_AGENT'], $matches);
				$os = current($matches);
				if($os=="Android"){
			?>
				var functionArr = str.split("=");
				var valueStr = "";
				if(typeof functionArr[1] != 'undefined'){
					var valueArr = functionArr[1].split("@@");
					for(var k in valueArr){
						valueStr+="'"+valueArr[k]+"',";
					}
					if(valueStr != ""){
						valueStr = valueStr.substring(0, (valueStr.length-1));
					}
				}
				
				if(typeof window.jsinterface != "undefined"){
					var checkfunc = eval("window.jsinterface."+functionArr[0]);
					
					if(typeof checkfunc != "undefined"){
						eval("window.jsinterface."+functionArr[0]+"("+((valueStr!="")?valueStr:"")+");");
					}
				}
				
			<?php } ?>
			
			
			
		}
		
		$(document).ready(function(){
			// $('.carousel').carousel({
			  // // interval: 2000
			// });
			$("#carouselExampleIndicators").swiperight(function() {
				$(this).carousel('prev');
			});
		   $("#carouselExampleIndicators").swipeleft(function() {
				$(this).carousel('next');
		   });
		   $(".fancybox").click(function(event){
				event.preventDefault();
				src = $(this).find('img').attr('src');
				var photoJson = [src];
				var appFuncStr = "showGallery="+encodeURIComponent(JSON.stringify(photoJson));

				callAppFunc(appFuncStr);
			});
		   
		});
		</script>
		
		<style>
			img.img-responsive{
				width:100%;
				height:auto;
			}
			#carouselExampleIndicators{
				height:300px;
			}
			#carouselExampleIndicators img{ 
				width: auto; 
				height:100%;
				margin: auto; 
			}
		</style>
		
	</head>
	<body style="">
		<div id="container">
			<div class="layer_page">
				<section>
					<div id="detailVideo" class=""></div>
					
					<?php 
						if(is_array($ads_imgs)&&count($ads_imgs)>0){
					?>
					<div id="carouselExampleIndicators" class="carousel slide" >
						  <ol class="carousel-indicators">
							<?php
								foreach($ads_imgs as $k => $img){	
							?>
								<li data-target="#carouselExampleIndicators" data-slide-to="<?= $k ?>" class="<?= $k==0?'active':'' ?>"></li>
								
							<?php
								}
							?>
							
						  </ol>
						  <div class="carousel-inner">
							<?php
								foreach($ads_imgs as $k => $img){	
							?>
								
								<div class="item <?= $k==0?'active':'' ?>">
									<a class="fancybox" rel="gallery" href="<?= $hl_app_img_url.$img['src'] ?>"  title="">
										<img class="d-block w-100" src="<?= $hl_app_img_url.$img['src'] ?>" alt="">
									</a>
								</div>
								
							<?php
								}
							?>
							
						  </div>
							<a href="#carousel-demo" class="carousel-control-prev" data-slide="prev">
								<span class="carousel-control-prev-icon"></span>
							</a>
							<a href="#carousel-demo" class="carousel-control-next" data-slide="next">
							  <span class="carousel-control-next-icon"></span>
							</a>
					  </div>
					
					<?php 
						}
					?>
					
					
					<div id="title" class=""><h1><b><?= $ads['detail_title'] ?></b></h1></div>
					<div id="publish_date" class=""><b><?= date('Y-m-d',strtotime($ads['publish_datetime'])) ?></b></div>
					
					<br>
					<div id="content" class=""><?= $ads['detail_content'] ?></div>	
				</section>
				<div class="seperate_div"></div>
			</div>
			
		</div>
	</body>
</html>