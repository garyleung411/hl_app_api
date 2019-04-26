<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no"> 
		<title>頭條日報</title>
		<style>
			img.img-responsive{
				width:100%;
				height:auto;
			}
		</style>
	</head>
	<body style="">
		<div id="container">
			<div class="layer_page">
				<section>
					<div id="detailVideo" class=""></div>
					<div id="photoWrapper" class=""></div>
					<div id="title" class=""><?= $ads['detail_title'] ?></div>
					<div id="publish_date" class=""><?= date('Y-m-d',strtotime($ads['publish_datetime'])) ?></div>
					<div id="content" class=""><?= $ads['detail_content'] ?></div>	
				</section>
				<div class="seperate_div"></div>
			</div>
			
		</div>
	</body>
</html>