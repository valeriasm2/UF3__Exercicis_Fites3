<html>

<head>
	<title>Exemple de lectura de dades a MySQL</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			margin: 20px;
		}

		table,
		td,
		th {
			border: 1px solid black;
			border-spacing: 0px;
			padding: 8px;
		}

		form {
			margin: 20px 0;
		}

		input[type="text"] {
			padding: 5px;
			width: 180px;
			margin-right: 10px;
		}

		button {
			padding: 5px 15px;
			cursor: pointer;
		}

		.filtros {
			display: flex;
			gap: 10px;
			align-items: center;
		}

		.error {
			color: red;
			margin: 10px 0;
		}
	</style>
</head>

<body>
	<h1>Filtrar per nombre d'habitants</h1>

	<?php
	# (1.1) Connectem a MySQL (host,usuari,contrassenya)
	$conn = mysqli_connect('localhost', 'admin', 'admin1234');

	# (1.2) Triem la base de dades amb la que treballarem
	mysqli_select_db($conn, 'world');

	# Verificar connexió
	if (!$conn) {
		die("Error de connexió: " . mysqli_connect_error());
	}
	?>

	<!-- Formulari amb mínim i màxim d'habitants -->
	<form method="POST" action="">
		<div class="filtros">
			<label>Mínim habitants:</label>
			<input type="text" name="min_habitants" placeholder="Mínim" pattern="[0-9]*" value="<?php echo isset($_POST['min_habitants']) ? htmlspecialchars($_POST['min_habitants']) : ''; ?>">
			
			<label>Màxim habitants:</label>
			<input type="text" name="max_habitants" placeholder="Màxim" pattern="[0-9]*" value="<?php echo isset($_POST['max_habitants']) ? htmlspecialchars($_POST['max_habitants']) : ''; ?>">
			
			<button type="submit">Filtrar</button>
			<button type="submit" name="reset">Mostrar tots</button>
		</div>
	</form>

	<?php
	# Inicialitzar condicions i errors
	$condicions = array();
	$min_habitants = null;
	$max_habitants = null;
	$errors = array();

	# Comprovar si s'ha fet reset
	if (!isset($_POST['reset'])) {
		# Comprovar mínim d'habitants
		if (isset($_POST['min_habitants']) && $_POST['min_habitants'] !== '') {
			$min_input = trim($_POST['min_habitants']);
			
			// Validar que sigui un número vàlid enter positiu
			if (ctype_digit($min_input)) {
				$min_habitants = mysqli_real_escape_string($conn, $min_input);
				$condicions[] = "Population >= $min_habitants";
			} else {
				$errors[] = "El mínim d'habitants ha de ser un número enter positiu vàlid";
			}
		}

		# Comprovar màxim d'habitants
		if (isset($_POST['max_habitants']) && $_POST['max_habitants'] !== '') {
			$max_input = trim($_POST['max_habitants']);
			
			// Validar que sigui un número vàlid enter positiu
			if (ctype_digit($max_input)) {
				$max_habitants = mysqli_real_escape_string($conn, $max_input);
				$condicions[] = "Population <= $max_habitants";
			} else {
				$errors[] = "El màxim d'habitants ha de ser un número enter positiu vàlid";
			}
		}

		# Validar que el mínim no sigui major que el màxim
		if ($min_habitants !== null && $max_habitants !== null && intval($min_habitants) > intval($max_habitants)) {
			$errors[] = "El mínim d'habitants no pot ser major que el màxim";
		}
	}

	# Mostrar errors si n'hi ha
	if (count($errors) > 0) {
		echo '<div class="error">';
		echo '<strong>Errors de validació:</strong><ul>';
		foreach ($errors as $error) {
			echo '<li>' . htmlspecialchars($error) . '</li>';
		}
		echo '</ul></div>';
	}

	# Només executar la consulta si no hi ha errors
	if (count($errors) === 0) {
		# Construir la consulta
		$consulta = "SELECT * FROM city";
		
		if (count($condicions) > 0) {
			$consulta .= " WHERE " . implode(" AND ", $condicions);
		}
		
		$consulta .= " ORDER BY Population DESC;";

		# Enviar la query al SGBD
		$resultat = mysqli_query($conn, $consulta);

		# Verificar si hi ha error
		if (!$resultat) {
			$message  = 'Consulta invàlida: ' . mysqli_error($conn) . "\n";
			$message .= 'Consulta realitzada: ' . $consulta;
			die($message);
		}

		# Comprovar si hi ha resultats
		$num_resultats = mysqli_num_rows($resultat);
	?>

	<!-- Taula de resultats -->
	<?php if ($num_resultats > 0): ?>
		<p>S'han trobat <strong><?php echo number_format($num_resultats, 0, ',', '.'); ?></strong> resultat(s)
		<?php 
		if ($min_habitants !== null || $max_habitants !== null) {
			echo " amb població";
			if ($min_habitants !== null) echo " >= " . number_format($min_habitants, 0, ',', '.');
			if ($min_habitants !== null && $max_habitants !== null) echo " i";
			if ($max_habitants !== null) echo " <= " . number_format($max_habitants, 0, ',', '.');
		}
		?>
		</p>
		<table>
			<thead>
				<tr>
					<th>Nom</th>
					<th>Codi país</th>
					<th>Districte</th>
					<th>Població</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($registre = mysqli_fetch_assoc($resultat)): ?>
					<tr>
						<td><?php echo htmlspecialchars($registre['Name']); ?></td>
						<td><?php echo htmlspecialchars($registre['CountryCode']); ?></td>
						<td><?php echo htmlspecialchars($registre['District']); ?></td>
						<td><?php echo number_format($registre['Population'], 0, ',', '.'); ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>No s'han trobat resultats amb els filtres aplicats</p>
	<?php endif; ?>

	<?php
	} // Tanca el if (count($errors) === 0)
	?>

	<?php
	mysqli_close($conn);
	?>
</body>

</html>