<?php		
include('global.inc.php');
?>
<!doctype html>
<html lang="fr">
	<head>
<?php		
include('head-content.inc.php');
?>
		<link href="<?php echo $uriPrefix ?>/assets/css/home.css" rel="stylesheet">
	</head>

  <body>
<?php		
include('navbar.inc.php');
?>

		<main role="main">

			<div style="height: 440px; max-height: 500px;">
				
				<div id="carouselExampleControls" class="carousel slide" data-ride="carousel"  data-interval="2000" style="margin-bottom: 0;">
					<div class="carousel-inner" style="opacity: 0.6;">
						<div class="carousel-item ">
							<img class="d-block w-100" style="min-height: 100%; width: auto;" src="<?php echo $uriPrefix ?>/img/home/1.jpg">
						</div>
						<div class="carousel-item active">
							<img class="d-block w-100" style="min-height: 100%; width: auto;" src="<?php echo $uriPrefix ?>/img/home/2.jpg">
						</div>
						<div class="carousel-item">
							<img class="d-block w-100" style="min-height: 100%; width: auto;" src="<?php echo $uriPrefix ?>/img/home/3.jpg">
						</div>
					</div>		
				</div>

				<!-- Main jumbotron for a primary marketing message or call to action -->
				<div class="jumbotron" style="position: relative;top: -415px; background-color: transparent; color: #000; margin-bottom: 0;">
					<div class="container">
						<h1 class="display-3">Int&eacute;ress&eacute; par...</h1>
						<p><strong>TAEKWON KIDO, SINKIDO, TAEKWONDO, HAPKIDO, KHIDO</strong></p>
						<p>Alors peut-&ecirc;tre trouverez-vous un club proche de chez vous !</p>
						<p><a class="btn btn-primary btn-lg" href="<?php echo $uriPrefix ?>/clubs" role="button">Chercher un club &raquo;</a></p>
					</div>
				</div>
				
			</div>

			<div class="container">
				<div class="row">
					<div class="col-md-4">
						<h2>Le C&eacute;nacle</h2>
						<p>Le Cénacle Rémi Mollet est une école d'arts martiaux français et asiatiques sous la direction technique de Maître Rémi Mollet. Doté d'une expérience de plus de 45 ans,
Maître Rémi Mollet, assisté de ses ceintures noires, enseigne le Taekwonkido, le Taekwondo, le Hapkido, le Gumdo et d'autres arts martiaux dans une trentaine de clubs
dispatchés dans toute la France.</p>
							<p><a class="btn btn-secondary" href="<?php echo $uriPrefix ?>/pres/cenacle" role="button">En savoir plus &raquo;</a></p>
					</div>
					<div class="col-md-4">
						<h2>Taekwonkido</h2>
						<p>Le Taekwonkido<sup>®</sup> 
est une méthode éducative, fondée par Me Rémi
Mollet et le Dr. Jean-Laurent Taddeï, synthétisant les techniques de Karaté,
Taekwondo, de Hapkido, de Gumdo (sabre), d’arts martiaux coréens et surtout de
Khido, méthode supérieure instaurée par Me Michel Morlon, créée en 1984.</p>
						<p><a class="btn btn-secondary" href="<?php echo $uriPrefix ?>/pres/taekwonkido" role="button">En savoir plus &raquo;</a></p>
					</div>
					<div class="col-md-4">
						<h2>Taekwondo</h2>
						<p>Le Taekwondo, discipline olympique, est un art martial sans armes 
et l’esprit dans lequel il est pratiqué entraîne obligatoirement 
courtoisie, loyauté, persévérance, maîtrise de soi, combativité 
sans agressivité.</p>
						<p><a class="btn btn-secondary" href="<?php echo $uriPrefix ?>/pres/taekwondo" role="button">En savoir plus &raquo;</a></p>
					</div>
					<div class="col-md-4">
						<h2>Hapkido</h2>
						<p>Le Hapkido a été fondé par Me. CHOI Yong Sul  qui partit pour le Japon en 
1912 où il fut adopté par la famille de Sokaku Takeda avec qui il 
s’entraîna durant 30 ans.</p>
						<p><a class="btn btn-secondary" href="<?php echo $uriPrefix ?>/pres/hapkido" role="button">En savoir plus &raquo;</a></p>
					</div>
					<div class="col-md-4">
						<h2>Sinkido Self-Défense Système</h2>
						<p>Le Sinkido Self-Défense Système est une méthode qui s'adresse exclusivement aux adultes et aux seniors.
C'est un programme d'autodéfense accessible, axé sur l'efficacité, il est directement applicable en combat réel.</p>
						<p><a class="btn btn-secondary" href="<?php echo $uriPrefix ?>/pres/sinkidosds" role="button">En savoir plus &raquo;</a></p>
					</div>
				</div>
				<hr />
			</div>

		</main>

<?php		
include('foot-content.inc.php');
?>
	</body>
</html>
