<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - 404</title>
    <style>
        /* Modern, clean, and centered look */
        body {
            display: flex; /* Gamit ang Flexbox para i-center */
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Buong taas ng screen */
            margin: 0;
            background-color: #f8f9fa; /* Light gray background */
            color: #343a40; /* Dark text for contrast */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            padding: 20px;
        }

        /* Styling para sa 404 number */
        .error-code {
            font-size: 150px; /* Mas malaki */
            font-weight: 800; /* Mas makapal */
            color: #e74c3c; /* Striking red color */
            margin-bottom: 0px;
            letter-spacing: 5px; /* Para mas cool tingnan */
        }

        /* Styling para sa main message */
        .error-message {
            font-size: 32px;
            font-weight: 600;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        /* Styling para sa secondary text */
        .secondary-text {
            font-size: 18px;
            color: #6c757d; /* Lighter gray for less emphasis */
            margin-bottom: 40px;
        }

        /* Styling para sa button/link */
        .home-link {
            text-decoration: none;
            background-color: #3498db; /* Blue button color */
            color: white;
            padding: 12px 25px;
            border-radius: 5px; /* Soft edges */
            font-size: 18px;
            font-weight: 600;
            transition: background-color 0.3s ease; /* Para may animation pag-hover */
        }

        .home-link:hover {
            background-color: #2980b9; /* Mas dark na blue pag-hover */
        }
    </style>
</head>
<body>
    <div class="error-code">404</div>
    <div class="error-message">Medyo naligaw tayo!</div>
    <p class="secondary-text">Hindi namin mahanap ang pahina na hinahanap mo. Baka nagkamali lang sa pag-type?</p>
    <a href="/catering/" class="home-link">Bumalik sa Homepage</a>
</body>
</html>