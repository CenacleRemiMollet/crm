<?php		

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Accept");

include('../global.inc.php');
?>
<!doctype html>
<html lang="fr">
	<head>
<?php		
include('../head-content.inc.php');
?>
		<link href="clubs.css" rel="stylesheet">
		
		<script id="club-template" type="text/x-handlebars-template">
			<div class="col-md-2" style="width: 50%" id="club-{{id}}">
				{{#if url }}<a href="{{url}}">{{/if}}
					<div class="card mb-4 box-shadow" style="height: 270px">
						<div class="clubitemcont">
							<img class="card-img-top w-100" src="{{image_url}}" />
						</div>
						<div class="card-body" style="padding: 0.3rem">
							<p class="card-text"><strong>{{city}}</strong>{{#if department}} ({{department}}){{/if}}</p>
						</div>
					</div>
				{{#if url }}</a>{{/if}}
			</div>
		</script>
	</head>

	<body>
<?php		
include('../navbar.inc.php');
?>
    <main role="main">

			<!--<iframe width="525" height="450" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
src="https://maps.google.fr/maps/ms?msa=0&amp;msid=204481577009356126574.0004c3992bfea45b567b6&amp;ie=UTF8&amp;ll=47.620528,2.292366&amp;spn=8.998942,7.443924&amp;t=h&amp;output=embed"></iframe>-->
			
			<div class="album py-5 bg-light" style="padding-top: 6rem!important;">
		    <div class="club-search">
		    	<input class="form-control mr-sm-2" type="text" placeholder="Chercher" id="searchclubinput" aria-label="Chercher" style="display:none;">
				</div>
				<div class="container">
					<div class="row" id="club-row">
<?php
include('../assets/spinner.svg');
?>
					</div>
				</div>
			</div>

		</main>

<?php		
include('../foot-content.inc.php');
?>
		<script src="<?php echo $uriPrefix ?>/assets/vendor/handlebars/handlebars.min-v4.1.0.js"></script>
		<script src="clubs.js"></script>
		<!--<script src="<?php echo $uriPrefix ?>/assets/js/handlebars-cheat.js"></script>-->
  </body>
</html>
