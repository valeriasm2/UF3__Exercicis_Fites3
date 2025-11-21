<!DOCTYPE html>
<html lang="ca">

<head>
	<meta charset="UTF-8">
	<title>Filtrar països per continent (BD world.sql)</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			margin: 20px;
			background: linear-gradient(120deg, #f5f7fa 0%, #c3cfe2 100%);
		}

		h1 {
			background: #6a89cc;
			color: #fff;
			padding: 18px 25px;
			border-radius: 8px;
			box-shadow: 0 3px 8px rgba(106, 137, 204, 0.13);
			margin-bottom: 30px;
			letter-spacing: 1px;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			box-shadow: 0 2px 15px rgba(0,0,0,0.07);
			margin-bottom: 30px;
			border-radius: 6px;
			overflow: hidden;
		}
		table th, table td {
			border: 1px solid #b2bec3;
			padding: 10px 12px;
			text-align: left;
		}
		table th {
			background: #636e72;
			color: #fff;
		}
		table tr:nth-child(even) {
			background: #f1f2f6;
		}
		table tr:hover {
			background: #dfe4ea;
			transition: background 0.2s;
		}

		form {
			margin: 20px 0 25px 0;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 3px 8px rgba(74, 144, 226, 0.06);
			padding: 16px 18px;
			width: max-content;
		}

		select {
			padding: 7px 10px;
			width: 240px;
			margin-right: 10px;
			border-radius: 6px;
			border: 1px solid #b2bec3;
			background: #f7faff;
			font-size: 16px;
		}

		button {
			padding: 8px 18px;
			cursor: pointer;
			border-radius: 6px;
			border: none;
			background: #00b894;
			color: #fff;
			font-weight: bold;
			letter-spacing: 0.5px;
			box-shadow: 0 1px 4px rgba(0,184,148,0.08);
			transition: background 0.15s;
		}
		button[type="submit"]:last-child {
			background: #0984e3;
		}
		button:hover {
			background: #00cec9 !important;
			color: #fff;
		}
		button[type="submit"]:last-child:hover {
			background: #273c75 !important;
		}

		.filtros {
			display: flex;
			gap: 10px;
			align-items: center;
		}

		.error {
			color: #d63031;
			font-weight: bold;
			margin: 10px 0 18px 0;
			padding: 8px 14px;
			background: #ffe5e5;
			border: 1px solid #fab1a0;
			border-radius: 6px;
		}

		@media (max-width: 600px) {
			.filtros {
				flex-direction: column;
				align-items: stretch;
			}
			form {
				width: 100%;
			}
			table th, table td {
				padding: 7px 4px;
				font-size: 14px;
			}
			select {
				width: 100%;
			}
		}
	</style>
</head>

<body>
	<h1>Llistat de països amb filtre de continent</h1>

	<?php
	// (1) Connexió a la BD
	$conn = mysqli_connect('localhost', 'admin', 'admin1234', 'world');
	if (!$conn) {
		die("<div class='error'>Error de connexió: " . htmlspecialchars(mysqli_connect_error()) . "</div>");
	}

	// (2) Agafem la llista de continents únics
	$continents = array();
	$sql_continents = "SELECT DISTINCT Continent FROM country ORDER BY Continent ASC";
	$res = mysqli_query($conn, $sql_continents);
	if ($res) {
		while ($row = mysqli_fetch_assoc($res)) {
			$continents[] = $row['Continent'];
		}
		mysqli_free_result($res);
	} else {
		echo "<div class='error'>Error obtenint continentes: ".htmlspecialchars(mysqli_error($conn))."</div>";
	}

	// (3) Captura de selecció del continent
	$continent = '';
	if (isset($_POST['continent']) && $_POST['continent'] !== '') {
		$continent = $_POST['continent'];
	}
	// Si es prem "Mostrar tots", reinicia el filtre
	if (isset($_POST['reset'])) {
		$continent = '';
	}

	?>

	<!-- Formulari amb un selector desplegable per al continent -->
	<form method="POST" action="">
		<div class="filtros">
			<label for="continent" style="font-weight:600;">Selecciona un continent:</label>
			<select name="continent" id="continent">
				<option value="">-- Tots els continents --</option>
				<?php foreach ($continents as $cont): ?>
					<option value="<?php echo htmlspecialchars($cont); ?>" <?php if ($cont === $continent) echo 'selected'; ?>>
						<?php echo htmlspecialchars($cont); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="submit">Filtrar</button>
			<button type="submit" name="reset" value="1">Mostrar tots</button>
		</div>
	</form>

	<?php
	// (4) Preparar i executar la consulta
	$consulta = "SELECT Code, Name, Continent, Region, Population FROM country";
	if ($continent !== '') {
		$continent_clean = mysqli_real_escape_string($conn, $continent);
		$consulta .= " WHERE Continent = '$continent_clean'";
	}
	$consulta .= " ORDER BY Name ASC";

	$resultat = mysqli_query($conn, $consulta);
	if (!$resultat) {
		$message  = "<div class='error'>Consulta invàlida: " . htmlspecialchars(mysqli_error($conn)) . "<br>";
		$message .= "Consulta realitzada: " . htmlspecialchars($consulta) . "</div>";
		die($message);
	}
	$num_resultats = mysqli_num_rows($resultat);
	?>

	<!-- Taula de resultats -->
	<?php if ($num_resultats > 0): ?>
		<p>
			S'han trobat <strong><?php echo number_format($num_resultats, 0, ',', '.'); ?></strong> països
			<?php if ($continent !== '') echo " al continent <strong>" . htmlspecialchars($continent) . "</strong>"; ?>.
		</p>
		<table>
			<thead>
				<tr>
					<th>Nom país</th>
					<th>Codi</th>
					<th>Continent</th>
					<th>Regió</th>
					<th>Població</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($pais = mysqli_fetch_assoc($resultat)): ?>
					<tr>
						<td><?php echo htmlspecialchars($pais['Name']); ?></td>
						<td><?php echo htmlspecialchars($pais['Code']); ?></td>
						<td><?php echo htmlspecialchars($pais['Continent']); ?></td>
						<td><?php echo htmlspecialchars($pais['Region']); ?></td>
						<td><?php echo number_format($pais['Population'], 0, ',', '.'); ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>No s'han trobat països per al filtre aplicat.</p>
	<?php endif; ?>

	<?php mysqli_free_result($resultat); mysqli_close($conn); ?>
</body>
</html>