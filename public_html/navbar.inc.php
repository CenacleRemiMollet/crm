<?php
$currentUri = $_SERVER['REQUEST_URI'];
?>

<?php
function menuItem($uri, $title) {
	global $uriPrefix;
	global $currentUri;
	echo '<li class="nav-item">';
	echo '<a class="nav-link'.($currentUri == $uriPrefix.$uri.'/' ? ' active' : '').'" href="'.$uriPrefix.$uri.'">'.$title.'</a>';
	echo '</li>';
}
?>

<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
	<a class="navbar-brand" href="<?php echo $uriPrefix ?>/"><img src="<?php echo $uriPrefix ?>/img/logo-sinkido.png"/></a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarsExampleDefault">
		<ul class="navbar-nav mr-auto">
			<?php menuItem('/pres/cenacle', 'Cénacle'); ?>
			<?php menuItem('/clubs', 'Liste des clubs'); ?>
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;">Disciplines</a>
				<div class="dropdown-menu" aria-labelledby="dropdown01">
					<a class="dropdown-item" href="<?php echo $uriPrefix ?>/pres/taekwonkido">Taekwonkido</a>
					<a class="dropdown-item" href="<?php echo $uriPrefix ?>/pres/taekwondo">Taekwondo</a>
					<a class="dropdown-item" href="<?php echo $uriPrefix ?>/pres/hapkido">Hapkido</a>
					<a class="dropdown-item" href="<?php echo $uriPrefix ?>/pres/sinkidosds">Sinkido Self-Défense Système</a>
				</div>
			</li>
			<?php menuItem('/pres/master-rm', 'Maître'); ?>
			<?php menuItem('/pres/videos', 'Vidéos'); ?>
		</ul>
	</div>
</nav>
