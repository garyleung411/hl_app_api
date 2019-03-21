<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/common/bootstrap/css/bootstrap.css">
	<script type="text/javascript" src="<?= base_url() ?>common/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?= base_url() ?>/common/bootstrap/js/bootstrap.js"></script>
	<style type="text/css">
		body{
			background:rgb(222,232,234);
		}
		h3{
			margin: 0px;
			display: inline;
		}
		a:hover{
			text-decoration: none;
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
			padding: 20px;
			margin-bottom: 50px;
		}
		span{
			font-weight: bold;
		}
		table{
			background:rgb(255,255,255);
			margin-top:10px;
			text-align: center;
		}
		.type{
			width: 100%;
			height: 35px;
			float: left;
		}
		.type a{
			display: block;
			padding: 5px 10px;
			float: left;
		}
		.search{
			float: right;
		}
		.page{
			text-align: center;
		}
		.page input{
			height: 20px;
			padding: 0px;
			margin: 0 5px;
		}
		.page button{
			height: 20px;
			padding:0px;
			width: 40px;
		}
	</style>
</head>
<body>
	
	<div class="container">
	    <div class="row">
	        
	        <div class="col-xs-12">
	        	<div class="form-inline" style="text-align: right;">
	        		
					<button class="btn btn-info"  data-toggle="modal" data-target="#myModal" onclick="window.location.href='<?php echo base_url().'Ads/form' ?>'">新增廣告</button>
					
					<button class="btn btn-success" id='save' >保存</button>
								
								
			    </div>
	        </div>
	        <div class="col-md-12">
	         	<table  class="table table-bordered">
					<thead>
						<tr style="background: rgb(0,0,0);color:rgb(255,255,255);">
							<!-- <td width="80">權值</td> -->
							<td width="140">出版時間</td>
							<td width="250">推薦圖片</td>
							<td width="140">標題</td>
							<td width="100">類別</td>
							<!-- <td style="width: 100px;">主頁推薦</td>
							<td style="width: 100px;">類別推薦</td> -->
							<td style="width: 100px;">出版</td>
							<td style="width: 100px;">操作</td>
						</tr>
					</thead>
					<tbody>
					
					</tbody>
				</table>
				
	        </div>
	        
	    </div>
	</div>
	
	
</body>
</html>