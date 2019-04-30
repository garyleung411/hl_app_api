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
		});
		</script>
		
		<style>
			img.img-responsive{
				width:100%;
				height:auto;
			}
			#carouselExampleIndicators img{ 
				width: 100%; 
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
									<img class="d-block w-100" src="<?= $hl_app_img_url.$img['src'] ?>" alt="">
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