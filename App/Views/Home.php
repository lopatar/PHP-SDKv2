<?php
declare(strict_types=1);
?>
<!DOCTYPE HTML>
<html lang="en">
	<head>
		<title>Home view</title>
		<meta charset="UTF-8">
		<meta content="width=device-width, initial-scale=1" name="viewport">
	</head>

	<body>
		<p>Welcome: <?= $this->getProperty('user')->username ?></p>
	</body>
</html>

