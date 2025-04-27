<?php 
session_start();

if (!isset($_SESSION['user']) && isset($_COOKIE['user'])) {
    $cookieData = json_decode($_COOKIE['user'], true);
     // Basisvalidatie: controleer of de cookie een geldige sessie bevat
     if (isset($cookieData['user']) && is_string($cookieData['user'])) {
        $_SESSION['user'] = htmlspecialchars($cookieData['user']);
    }
}
if (!isset($_SESSION['user'])) {
    header("Location: inloggen.php?redirect=" . urlencode(basename($_SERVER['PHP_SELF'])));
    exit();
}
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'voetbal_vereniging';
// Verbinding met de database maken
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}
// Functie om input schoon te maken
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
// Functie: Verdeel textarea-tekst in regels
function getLines($text) {
    $lines = explode("\n", trim($text));
    $result = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $result[] = $line;
        }
    }
    return $result;
}

// Functie: Invoegen van telefoonnummers
function insertTelefoonnummers($conn, $lidnummer, $telefoonnummers) {
    $stmt = $conn->prepare("INSERT INTO telefoonnummers (telefoonnummer, lidnummer) VALUES (?, ?)");
    foreach ($telefoonnummers as $tel) {
        if (preg_match('/^[0-9]{10}$/', $tel)) { // Basis telefoonvalidatie
        $stmt->bind_param("si", $tel, $lidnummer);
        $stmt->execute();
    }
}
    $stmt->close();
}
// Functie: Invoegen van emailadressen
function insertEmails($conn, $lidnummer, $emails) {
    $stmt = $conn->prepare("INSERT INTO email (emailadres, lidnummer) VALUES (?, ?)");
    foreach ($emails as $email) {
        $validatedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($validatedEmail !== false) { 
        $stmt->bind_param("si", $email, $lidnummer);
        $stmt->execute();
    }
}
    $stmt->close();
}
// Functie: Zorg dat de postcode in de postcode-tabel bestaat
function ensurePostcodeExists($conn, $postcode) {
    if (!preg_match('/^[1-9][0-9]{3}\s?[A-Z]{2}$/i', $postcode)) {
        die("Ongeldige postcode ingevoerd.");
    }
    $stmt = $conn->prepare("INSERT IGNORE INTO postcode (postcode) VALUES (?)");
    $stmt->bind_param("s", $postcode);
    $stmt->execute();
    $stmt->close();
}

$action = $_GET['action'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_action = $_POST['action'] ?? '';
    
    if ($form_action == 'add') {
       // Gegevens uit het formulier ophalen en opschonen
       $naam = sanitizeInput($_POST['naam']);
       $voornaam = sanitizeInput($_POST['voornaam']);
       $postcode = sanitizeInput($_POST['postcode']);
       $huisnummer = sanitizeInput($_POST['huisnummer']);

     // Zorg dat de postcode bestaat in de postcode-tabel
        ensurePostcodeExists($conn, $postcode);

        // Nieuwe member record invoegen,
        $stmt = $conn->prepare("INSERT INTO lid (naam, voornaam, postcode, huisnummer) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $naam, $voornaam, $postcode, $huisnummer);
        
        if ($stmt->execute()) {
            $lidnummer = $stmt->insert_id;
            $stmt->close();
            
             // Verwerk telefoonnummers indien ingevuld
            if (!empty($_POST['telefoonnummers'])) {
                $telnummers = getLines($_POST['telefoonnummers']);

                insertTelefoonnummers($conn, $lidnummer, $telnummers);
            }
            // Verwerk emailadressen indien ingevuld
            if (!empty($_POST['emails'])) {
                $emails = getLines($_POST['emails']);
                $validEmails = [];
                foreach ($emails as $email) {
                    $validatedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
                    if ($validatedEmail !== false) {
                        $validEmails[] = $validatedEmail;
                    }
                }
                insertEmails($conn, $lidnummer, $validEmails);
            }
            $message = "Lid succesvol toegevoegd.";
        } else {
            $message = "Error bij toevoegen: " . $stmt->error;
            $stmt->close();
        }
        
    } elseif ($form_action == 'update') {
       // Gegevens uit het formulier ophalen en opschonen
        $lidnummer = intval($_POST['lidnummer']);
        $naam = trim($_POST['naam']);
        $voornaam = trim($_POST['voornaam']);
        $postcode = trim($_POST['postcode']);
        $huisnummer = trim($_POST['huisnummer']);
        
       // Zorg dat de postcode bestaat in de postcode-tabel
        ensurePostcodeExists($conn, $postcode);
        // Update record in de lid-tabel
        $stmt = $conn->prepare("UPDATE lid SET naam = ?, voornaam = ?, postcode=? , huisnummer=? WHERE lidnummer = ?");
        $stmt->bind_param("ssssi", $naam, $voornaam, $postcode, $huisnummer, $lidnummer);
        
        if ($stmt->execute()) {
            $stmt->close();
 
             // Oude telefoonnummers en emailadressen verwijderen
            $conn->query("DELETE FROM telefoonnummers WHERE lidnummer = $lidnummer");
            $conn->query("DELETE FROM email WHERE lidnummer = $lidnummer");
            
             // Nieuwe telefoonnummers verwerken indien ingevuld
            if (!empty($_POST['telefoonnummers'])) {
                $telnummers = getLines($_POST['telefoonnummers']);
                insertTelefoonnummers($conn, $lidnummer, $telnummers);
            }
            // Nieuwe emailadressen verwerken indien ingevuld
            if (!empty($_POST['emails'])) {
                $emails = getLines($_POST['emails']);
                $validEmails = [];
    foreach ($emails as $email) {
        $validatedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($validatedEmail !== false) {
            $validEmails[] = $validatedEmail;
        }
    }
                insertEmails($conn, $lidnummer, $validEmails);
            }
            $message = "Lid succesvol bijgewerkt.";
        } else {
            $message = "Error bij updaten: " . $stmt->error;
            $stmt->close();
        }
    }
}

 // Verwijderen van een lid (via GET)
if ($action == 'delete' && isset($_GET['id'])) {
    $lidnummer = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM lid  WHERE lidnummer = ?");
    $stmt->bind_param("i", $lidnummer);
    if ($stmt->execute()) {
        $message = "Lid succesvol verwijderd.";
    } else {
        $message = "Error bij verwijderen: " ;
    }
    $stmt->close();
}
// Ophalen van lidgegevens voor bewerken
if ($action == 'edit' && isset($_GET['id'])) {
    $lidnummer = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM lid   WHERE lidnummer = ?");
    $stmt->bind_param("i", $lidnummer);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
        $stmt->close();
        
         // Telefoonnummers en emailadressen ophalen
        $phones = [];
        $emails = [];
        
        $stmtTel = $conn->prepare("SELECT telefoonnummer FROM telefoonnummers WHERE lidnummer = ?");
        $stmtTel->bind_param("i", $lidnummer);
        $stmtTel->execute();
        $resultTel = $stmtTel->get_result();
        while ($row = $resultTel->fetch_assoc()) {
            $phones[] = $row['telefoonnummer'];
        }
        $stmtTel->close();
        
        $stmtEmail = $conn->prepare("SELECT emailadres FROM email WHERE lidnummer = ?");
        $stmtEmail->bind_param("i", $lidnummer);
        $stmtEmail->execute();
        $resultEmail = $stmtEmail->get_result();
        while ($row = $resultEmail->fetch_assoc()) {
            $emails[] = $row['emailadres'];
        }
        $stmtEmail->close();
    } else {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Ledenbeheer</title>
    <style>
    form {
         margin-bottom: 16px;  
         padding: 12px; 
         border: 2px solid #7b42f5;
         border-radius: 8px;
         background-color: #f9f9f9;
         box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
    }
    .input-group { 
         margin-bottom: 10px; 
    }
    .input-group input, .input-group select { 
         width: 50%; 
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
         transition: 0.3s;
    }
    button:hover {
         background-color: #652dd0;
    }
    table {
         width: 100%;
         border-collapse: collapse;
         margin-top:10px;
         background-color: #fff;
    }
    th,td {
         border: 1px solid #aaa;
         padding: 8px;
         text-align: left;
    }
    th {
         background-color: #7b42f5;
         color: white;
    }
    @media (max-width: 600px){ 
         table{ font-size: 14px;}
         button{ width: 100%;}
    }
    </style>
</head>
<body class="p-3 mb-2 bg-light text-dark">
<h1>Ledenbeheer</h1>
<p><a href="teams.php">Teamsbeheer</a> | <a href="loguit.php">Loguit</a></p>
<?php 
if (!empty($message)) {
    echo "<p>$message</p>";
} 
?>

<?php if ($action == 'edit' && isset($member)) { ?>
    <h2>Bewerk Lid</h2>
    <form method="post" action="leden.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="lidnummer" value="<?php echo $member['lidnummer']; ?>">
        <div class="input-group">
            <label>Naam:</label>
            <input type="text" name="naam" value="<?php echo htmlspecialchars($member['naam']); ?>" required>
        </div>
        <div class="input-group">
            <label>Voornaam:</label>
            <input type="text" name="voornaam" value="<?php echo htmlspecialchars($member['voornaam']); ?>" required>
        </div>
        <div class="input-group">
            <label>Postcode:</label>
            <input type="text" name="postcode" value="<?php echo htmlspecialchars($member['postcode']); ?>" required>
        </div>
        
        <div class="input-group">
            <label>Huisnummer:</label>
            <input type="text" name="huisnummer" value="<?php echo htmlspecialchars($member['huisnummer']); ?>" required>
        </div>
        <div class="input-group">
            <label>Telefoonnummers (één per regel):</label><br>
            <textarea name="telefoonnummers" rows="4" cols="30"><?php echo implode("\n", $phones ?? []); ?></textarea>
        </div>
        <div class="input-group">
            <label>Emailadressen (één per regel):</label><br>
            <textarea name="emails" rows="4" cols="30"><?php echo implode("\n", $emails ?? []); ?></textarea>
        </div>
        <button type="submit">Update Lid</button>
    </form>
<?php } else { ?>
    <h2>Voeg Nieuw Lid Toe</h2>
    <form method="post" action="leden.php">
        <input type="hidden" name="action" value="add">
        <div class="input-group">
            <label>Naam: </label>
            <input type="text" name="naam" required>
        </div>
        <div class="input-group">
            <label>Voornaam: </label>
            <input type="text" name="voornaam" required>
        </div>
        <div class="input-group">
            <label>Postcode: </label>
            <input type="text" name="postcode" required>
        </div>
        <div class="input-group">
            <label>Huisnummer: </label>
            <input type="text" name="huisnummer" required>
        </div>
        <div class="input-group">
            <label>Telefoonnummers (één per regel):</label><br>
            <textarea name="telefoonnummers" rows="4" cols="30" placeholder="Bijv. 0612345678"></textarea>
        </div>
        <div class="input-group">
            <label>Emailadressen (één per regel):</label><br>
            <textarea name="emails" rows="4" cols="30" placeholder="bijv. naam@example.com"></textarea>
        </div>
        <button type="submit">Voeg Lid Toe</button>
    </form>
<?php } ?>

<?php
// Alle leden ophalen
$result = $conn->query("SELECT * FROM lid");
if ($result->num_rows > 0) {
    echo "<h2>Overzicht Leden</h2>";
    echo "<table>";
    echo "<tr><th>Lidnummer</th><th>Naam</th><th>Voornaam</th><th>Postcode</th><th>Huisnummer</th><th>Acties</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['lidnummer'] . "</td>";
        echo "<td>" . htmlspecialchars($row['naam']) . "</td>";
        echo "<td>" . htmlspecialchars($row['voornaam']) . "</td>";
        echo "<td>" . htmlspecialchars($row['postcode']) . "</td>";
        echo "<td>" . htmlspecialchars($row['huisnummer']) . "</td>";
        echo "<td><a href='leden.php?action=edit&id=" . $row['lidnummer'] . "'>Bewerk</a> | <a href='leden.php?action=delete&id=" . $row['lidnummer'] . "' onclick=\"return confirm('Weet je zeker dat je dit lid wilt verwijderen?');\">Verwijder</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Geen leden gevonden.</p>";
}

$conn->close();
?>
</body>
</html>
 