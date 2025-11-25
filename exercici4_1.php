<!DOCTYPE html>
<html lang="ca">

<head>
	<meta charset="UTF-8">
	<title>Filtrar països per continent (BD world.sql)</title>
	<style>
		body {
			font-family: 'Segoe UI', Arial, sans-serif;
			margin: 20px;
			background: linear-gradient(120deg, #fff0f6 0%, #f8bbd0 100%);
		}

		h1 {
			background: #ec407a;
			color: #fff;
			padding: 20px 32px;
			border-radius: 10px;
			box-shadow: 0 3px 13px rgba(233, 30, 99, 0.16);
			margin-bottom: 34px;
			letter-spacing: 1.2px;
			font-family: 'Segoe UI', Arial, sans-serif;
			text-shadow: 1px 2px 12px rgba(233, 30, 99, 0.09);
		}

		table {
			width: 100%;
			border-collapse: collapse;
			box-shadow: 0 2px 15px rgba(233, 30, 99, 0.11);
			margin-bottom: 35px;
			border-radius: 10px;
			overflow: hidden;
			background: #fff;
		}
		table th, table td {
			border: 1px solid #f8bbd0;
			padding: 12px 14px;
			text-align: left;
			font-size: 16px;
		}
		table th {
			background: #f06292;
			color: #fff;
			font-weight: bold;
		}
		table tr:nth-child(even) {
			background: #fce4ec;
		}
		table tr:hover {
			background: #f8bbd0 !important;
			transition: background 0.2s;
		}

		form {
			margin: 22px 0 28px 0;
			background: #fff0f6;
			border-radius: 12px;
			box-shadow: 0 3px 13px rgba(233, 30, 99, 0.06);
			padding: 18px 22px;
			width: max-content;
			border: 2px solid #f8bbd0;
		}

		select {
			padding: 9px 13px;
			width: 240px;
			margin-right: 12px;
			border-radius: 8px;
			border: 1.5px solid #f06292;
			background: #fce4ec;
			font-size: 17px;
			color: #c2185b;
			transition: border 0.18s;
		}
		select:focus {
			border: 2px solid #ec407a;
			outline: none;
			background: #fff5fa;
		}

		button {
			padding: 9px 22px;
			cursor: pointer;
			border-radius: 8px;
			border: none;
			background: #e75480;
			color: #fff;
			font-weight: bold;
			letter-spacing: 0.6px;
			box-shadow: 0 1px 6px rgba(236, 64, 122, 0.11);
			transition: background 0.15s;
			font-size: 16px;
			margin-right: 4px;
		}
		button[type="submit"]:last-child {
			background: #ad1457;
		}
		button:hover {
			background: #d81b60 !important;
			color: #ffe3ed !important;
		}
		button[type="submit"]:last-child:hover {
			background: #880e4f !important;
		}

		.filtros {
			display: flex;
			gap: 13px;
			align-items: center;
		}

		.error {
			color: #c62828;
			font-weight: bold;
			margin: 10px 0 18px 0;
			padding: 10px 18px;
			background: #ffdde4;
			border: 1.5px solid #f06292;
			border-radius: 8px;
		}

		p strong {
			color: #c2185b;
		}

		@media (max-width: 600px) {
			.filtros {
				flex-direction: column;
				align-items: stretch;
				gap: 8px;
			}
			form {
				width: 100%;
			}
			table th, table td {
				padding: 8px 6px;
				font-size: 14px;
			}
			select {
				width: 100%;
			}
			h1 {
				font-size: 19px;
				padding: 10px 12px;
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
			<label for="continent" style="font-weight:600;color:#ad1457;">Selecciona un continent:</label>
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