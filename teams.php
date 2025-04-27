<?php

session_start();

if (!isset($_SESSION['user']) && isset($_COOKIE['user'])) {
    $cookieData = json_decode($_COOKIE['user'], true);
    if (json_last_error() === JSON_ERROR_NONE && isset($cookieData['user'])) {
        // Voeg eventueel een validatie van de inhoud toe (bijv. vervaldatum, integriteit)
        $_SESSION['user'] = htmlspecialchars($cookieData['user'], ENT_QUOTES, 'UTF-8');
    }
}
if (!isset($_SESSION['user'])) {
    header("Location: inloggen.php?redirect=" . urlencode(basename($_SERVER['PHP_SELF'])));
    exit();
}

// Databaseconfiguratie
$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'voetbal_vereniging';

// Maak verbinding met de database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Verbinding mislukt: ". $conn->connect_error );
}

$message = '';

// Verwerken van POST-acties
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';
    
    // Team toevoegen
    if ($form_action === 'add_team') {
        $teamnaam    = trim($_POST['teamnaam']);
        $omschrijving = trim($_POST['omschrijving']);
        if ($teamnaam == '') {
            $message = "Teamnaam is vereist.";
        } else {
            $stmt = $conn->prepare("INSERT INTO Teams (teamnaam, omschrijving) VALUES (?, ?)");
            $stmt->bind_param("ss", $teamnaam, $omschrijving);
            if ($stmt->execute()) {
                $message = "Team succesvol toegevoegd.";
            } else {
                $message = "Fout bij toevoegen van team: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    // Team updaten (alleen de omschrijving)
    elseif ($form_action === 'update_team') {
        $teamnaam    = trim($_POST['teamnaam']);
        $omschrijving = trim($_POST['omschrijving']);
        $stmt = $conn->prepare("UPDATE Teams SET omschrijving = ? WHERE teamnaam = ?");
        $stmt->bind_param("ss", $omschrijving, $teamnaam);
        if ($stmt->execute()) {
            $message = "Team succesvol bijgewerkt.";
        } else {
            $message = "Fout bij bijwerken van team: " . $stmt->error;
        }
        $stmt->close();
    }
    // Lid toevoegen aan een team
    elseif ($form_action === 'add_member') {
        $teamnaam = trim($_POST['teamnaam']);
        $lidnummer = intval($_POST['lidnummer']);
        if ($teamnaam == '' || $lidnummer <= 0) {
            $message = "Selecteer een geldig team en voer een geldig lidnummer in.";
        } else {
            // Controleer of het lid al is toegevoegd aan dit team
            $stmt = $conn->prepare("SELECT tl_ID FROM TeamLid WHERE teamnaam = ? AND lidnummer = ?");
            $stmt->bind_param("si", $teamnaam, $lidnummer);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $message = "Lid is al toegevoegd aan dit team.";
            } else {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO TeamLid (teamnaam, lidnummer) VALUES (?, ?)");
                $stmt->bind_param("si", $teamnaam, $lidnummer);
                if ($stmt->execute()) {
                    $message = "Lid succesvol toegevoegd aan het team.";
                } else {
                    $message = "Fout bij toevoegen van lid aan team: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}
// Verwerken van GET-acties
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    // Team verwijderen
    if ($action === 'delete_team') {
        $teamnaam = $_GET['teamnaam'] ?? '';
        if ($teamnaam != '') {
            // Eerst de teamleden verwijderen
            $stmt = $conn->prepare("DELETE FROM TeamLid WHERE teamnaam = ?");
            $stmt->bind_param("s", $teamnaam);
            $stmt->execute();
            $stmt->close();
            // Vervolgens het team verwijderen
            $stmt = $conn->prepare("DELETE FROM Teams WHERE teamnaam = ?");
            $stmt->bind_param("s", $teamnaam);
            if ($stmt->execute()) {
                $message = "Team succesvol verwijderd.";
            } else {
                $message = "Fout bij verwijderen van team: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    // Lid verwijderen uit een team
    elseif ($action === 'remove_member') {
        $teamnaam  = $_GET['teamnaam'] ?? '';
        $lidnummer = intval($_GET['lidnummer'] ?? 0);
        if ($teamnaam != '' && $lidnummer > 0) {
            $stmt = $conn->prepare("DELETE FROM TeamLid WHERE teamnaam = ? AND lidnummer = ?");
            $stmt->bind_param("si", $teamnaam, $lidnummer);
            if ($stmt->execute()) {
                $message = "Lid succesvol verwijderd uit het team.";
            } else {
                $message = "Fout bij verwijderen van lid uit team: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Teamsbeheer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        form {
            margin-bottom: 20px;
            padding: 15px;
            border: 2px solid #7b42f5;
            border-radius: 8px;
            background-color: #fff;
        }
        .input-group {
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea, select, input[type="number"] {
            width: 60%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #7b42f5;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #652dd0;
        }
        table {
            width: 70%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #7b42f5;
            color: white;
        }
        .message {
            color: green;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        a {
            color: #7b42f5;
            text-decoration: underline;
        }
        .actions {
      display: flex;
      gap: 5px;
    }
    </style>
</head>
<body>
    <h1>Teamsbeheer</h1>
    <p><a href="leden.php">Ledenbeheer</a> | <a href="loguit.php">Loguit</a></p>
    
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Formulier voor het toevoegen van een nieuw team -->
    <h2>Voeg een nieuw team toe</h2>
    <form method="post" action="teams.php">
        <input type="hidden" name="action" value="add_team">
        <div class="input-group">
            <label for="teamnaam">Teamnaam:</label>
            <input type="text" name="teamnaam" id="teamnaam" required>
        </div>
        <div class="input-group">
            <label for="omschrijving">Omschrijving:</label>
            <textarea name="omschrijving" id="omschrijving" rows="3"></textarea>
        </div>
        <button type="submit">Team Toevoegen</button>
    </form>

    <!-- Overzicht van bestaande teams -->
    <h2>Overzicht Teams</h2>
    <?php
    $result = $conn->query("SELECT * FROM Teams");
    if ($result->num_rows > 0):
    ?>
    <table>
        <tr>
            <th>Teamnaam</th>
            <th>Omschrijving</th>
            <th>Acties</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['teamnaam']); ?></td>
            <td><?php echo htmlspecialchars($row['omschrijving']); ?></td>
            <td>
                <!-- Link om team te verwijderen -->
                <a href="teams.php?action=delete_team&teamnaam=<?php echo urlencode($row['teamnaam']); ?>" onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?');">Verwijderen</a>
                <!-- Formulier om teamomschrijving bij te werken -->
                <form style="display:inline;" method="post" action="teams.php">
                    <input type="hidden" name="action" value="update_team">
                    <input type="hidden" name="teamnaam" value="<?php echo htmlspecialchars($row['teamnaam']); ?>">
                    <input type="text" name="omschrijving" value="<?php echo htmlspecialchars($row['omschrijving']); ?>" required>
                    <button type="submit">Update</button>
                </form>
                <!-- Link naar het teamledenbeheer voor dit team -->
                <a href="#team-<?php echo urlencode($row['teamnaam']); ?>">Beheer leden</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>Geen teams gevonden.</p>
    <?php endif; ?>

    <!-- Teamledenbeheer: Voor elk team een sectie -->
    <?php
    // Voor elke team tonen we een sectie om leden toe te voegen en weer te geven
    $teamsResult = $conn->query("SELECT * FROM Teams");
    while ($team = $teamsResult->fetch_assoc()):
        $currentTeam = $team['teamnaam'];
    ?>
    <h3 id="team-<?php echo htmlspecialchars(urlencode($currentTeam)); ?>">Team: <?php echo htmlspecialchars($currentTeam); ?></h3>
    <!-- Formulier voor het toevoegen van een lid aan dit team -->
    <form method="post" action="teams.php">
        <input type="hidden" name="action" value="add_member">
        <input type="hidden" name="teamnaam" value="<?php echo htmlspecialchars($currentTeam); ?>">
        <div class="input-group">
            <label for="lidnummer-<?php echo htmlspecialchars($currentTeam); ?>">Lidnummer:</label>
            <input type="number" name="lidnummer" id="lidnummer-<?php echo htmlspecialchars($currentTeam); ?>" required>
        </div>
        <button type="submit">Lid Toevoegen</button>
    </form>
    <?php
    // Haal de leden op die bij dit team horen
    $stmt = $conn->prepare("SELECT tl.lidnummer, l.naam, l.voornaam FROM TeamLid tl JOIN lid l ON tl.lidnummer = l.lidnummer WHERE tl.teamnaam = ?");
    $stmt->bind_param("s", $currentTeam);
    $stmt->execute();
    $membersResult = $stmt->get_result();
    if ($membersResult->num_rows > 0):
    ?>
    <table>
        <tr>
            <th>Lidnummer</th>
            <th>Naam</th>
            <th>Voornaam</th>
            <th>Acties</th>
        </tr>
        <?php while ($member = $membersResult->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($member['lidnummer']); ?></td>
            <td><?php echo htmlspecialchars($member['naam']); ?></td>
            <td><?php echo htmlspecialchars($member['voornaam']); ?></td>
            <td>
                <a href="teams.php?action=remove_member&teamnaam=<?php echo urlencode($currentTeam); ?>&lidnummer=<?php echo $member['lidnummer']; ?>" onclick="return confirm('Weet je zeker dat je dit lid uit het team wilt verwijderen?');">Verwijderen</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>Geen leden in dit team.</p>
    <?php endif;
    $stmt->close();
    endwhile;
    $conn->close();
    ?>
</body>
</html>

// Maak verbinding met de database
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Verbinding mislukt: ". $conn->connect_error );
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// CRUD-operaties
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_team'])) {
        $stmt = $db->prepare("INSERT INTO Teams (teamnaam, omschrijving) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST['teamnaam'], $_POST['omschrijving']);
        $stmt->execute();
    } elseif (isset($_POST['update_team'])) {
        $stmt = $db->prepare("UPDATE Teams SET omschrijving=? WHERE teamnaam=?");
        $stmt->bind_param("ss", $_POST['omschrijving'], $_POST['teamnaam']);
        $stmt->execute();
    } elseif (isset($_POST['add_lid'])) {
        $stmt = $db->prepare("INSERT INTO TeamLid (teamnaam, lidnummer) VALUES (?, ?)");
        $stmt->bind_param("si", $_POST['teamnaam'], $_POST['lidnummer']);
        $stmt->execute();
    }
}

if (isset($_GET['delete_team'])) {
    $stmt = $db->prepare("DELETE FROM Teams WHERE teamnaam=?");
    $stmt->bind_param("s", $_GET['delete_team']);
    $stmt->execute();
}

if (isset($_GET['delete_lid'])) {
    $stmt = $db->prepare("DELETE FROM TeamLid WHERE tl_ID=?");
    $stmt->bind_param("i", $_GET['delete_lid']);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teams Beheer</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .team { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        .form-group { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Teams Beheer</h2>
    <a href="leden.php">Naar Leden</a> | <a href="logout.php">Uitloggen</a>

    <!-- Nieuw team toevoegen -->
    <h3>Nieuw Team</h3>
    <form method="POST">
        <div class="form-group">
            <input type="text" name="teamnaam" placeholder="Teamnaam" required>
            <textarea name="omschrijving" placeholder="Omschrijving" required></textarea>
            <button type="submit" name="add_team">Toevoegen</button>
        </div>
    </form>

    <!-- Teamslijst -->
    <h3>Teams</h3>
    <?php
    $result = $db->query("SELECT * FROM Teams");
    while ($team = $result->fetch_assoc()) {
        echo "<div class='team'>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='teamnaam' value='{$team['teamnaam']}'>";
        echo "<strong>{$team['teamnaam']}</strong>";
        echo "<textarea name='omschrijving'>{$team['omschrijving']}</textarea>";
        echo "<button type='submit' name='update_team'>Bijwerken</button>";
        echo " <a href='?delete_team={$team['teamnaam']}' onclick='return confirm(\"Weet je zeker?\")'>Verwijderen</a>";
        echo "</form>";

        // Leden toevoegen
        echo "<form method='POST' class='form-group'>";
        echo "<input type='hidden' name='teamnaam' value='{$team['teamnaam']}'>";
        echo "<select name='lidnummer'>";
        $leden = $db->query("SELECT lidnummer, naam, voornaam FROM Lid");
        while ($lid = $leden->fetch_assoc()) {
            echo "<option value='{$lid['lidnummer']}'>{$lid['voornaam']} {$lid['naam']}</option>";
        }
        echo "</select>";
        echo "<button type='submit' name='add_lid'>Lid Toevoegen</button>";
        echo "</form>";

        // Huidige teamleden
        $teamleden = $db->query("SELECT tl.tl_ID, l.naam, l.voornaam FROM TeamLid tl JOIN Lid l ON tl.lidnummer = l.lidnummer WHERE tl.teamnaam='{$team['teamnaam']}'");
        while ($lid = $teamleden->fetch_assoc()) {
            echo "<p>{$lid['voornaam']} {$lid['naam']} <a href='?delete_lid={$lid['tl_ID']}' onclick='return confirm(\"Weet je zeker?\")'>Verwijderen</a></p>";
        }
        echo "</div>";
    }
    ?>
</body>
</html>
