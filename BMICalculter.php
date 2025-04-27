<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            font-size: 16px;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>BMI Calculator</h1>
        <form method="post" action="">
            <label for="gewicht">Gewicht (kg):</label>
            <input type="number" id="gewicht" name="gewicht" step="0.1" required>
            
            <label for="lengte">Lengte (cm):</label>
            <input type="number" id="lengte" name="lengte" step="0.1" required>
            
            <input type="submit" value="Bereken BMI">
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $gewicht = floatval($_POST['gewicht']);
            $lengte = floatval($_POST['lengte']) / 100; // Omzetten naar meters

            if ($gewicht > 0 && $lengte > 0) {
                $bmi = $gewicht / ($lengte * $lengte);
                $bmi = round($bmi, 1);

                // Bepaal BMI-categorie
                if ($bmi < 18.5) {
                    $categorie = "Ondergewicht";
                } elseif ($bmi >= 18.5 && $bmi < 25) {
                    $categorie = "Normaal gewicht";
                } elseif ($bmi >= 25 && $bmi < 30) {
                    $categorie = "Overgewicht";
                } else {
                    $categorie = "Obesitas";
                }

                echo "<div class='result'>";
                echo "Je BMI is: $bmi<br>";
                echo "Categorie: $categorie";
                echo "</div>";
            } else {
                echo "<div class='error'>Voer geldige waarden in.</div>";
            }
        }
        ?>
    </div>
</body>
</html>