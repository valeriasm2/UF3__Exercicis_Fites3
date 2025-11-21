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
			width: 250px;
		}

		button {
			padding: 5px 15px;
			cursor: pointer;
		}
	</style>
</head>

<body>
	<h1> Filtrar por ciudad</h1>

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

	<!-- Formulari de filtre -->
	<form method="POST" action="">
		<input type="text" name="ciudad" placeholder="Introduce el nombre de la ciudad" value="<?php echo isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : ''; ?>">
		<button type="submit">Filtrar</button>
	</form>

	<?php
	# Comprovar si s'ha enviat el formulari
	if (isset($_POST['ciudad']) && !empty($_POST['ciudad'])) {
		$ciudad = mysqli_real_escape_string($conn, $_POST['ciudad']);
		$consulta = "SELECT * FROM city WHERE Name LIKE '%$ciudad%';";
	} else {
		# Si no hi ha filtre, mostrar totes les ciutats
		$consulta = "SELECT * FROM city;";
	}

	# Enviem la query al SGBD
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
		<p>S'han trobat <strong><?php echo $num_resultats; ?></strong> resultat(s)</p>
		<table>
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Código de país</th>
					<th>Distrito</th>
					<th>Población</th>
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
		<p>No s'han trobat resultats per la cerca "<?php echo htmlspecialchars($_POST['ciudad'] ?? ''); ?>"</p>
	<?php endif; ?>

	<?php
	mysqli_close($conn);
	?>
</body>

</html>