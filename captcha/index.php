


<?php
use Random\RandomException;
session_start();

if (isset($_COOKIE["captcha_verified"]) && $_COOKIE["captcha_verified"] == "true") {
    $redirect_to = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : '/pages/index.php';

    if (parse_url($redirect_to, PHP_URL_HOST) && parse_url($redirect_to, PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
        $redirect_to = '/pages/index.php';
    }

    header("Refresh: 3; url=$redirect_to");
    echo getLoadingScreen();
    exit;
}

if (!isset($_SESSION["captcha_code"])) {
    try {
        $_SESSION["captcha_code"] = bin2hex(random_bytes(3));
    } catch (Exception $e) {
        $message = '<p class="text-center text-danger">Error generating CAPTCHA. Please try again later.</p>';
    }
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["captcha_code"]) && !empty(trim($_POST["captcha_code"]))) {
        if (trim($_POST["captcha_code"]) === $_SESSION["captcha_code"]) {
            setcookie("captcha_verified", "true", time() + 7200, "/");
            unset($_SESSION["captcha_code"]);

            $redirect_to = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : '/pages/index.php';

            if (parse_url($redirect_to, PHP_URL_HOST) && parse_url($redirect_to, PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
                $redirect_to = '/pages/index.php';
            }

            header("Refresh: 3; url=$redirect_to");
            echo getLoadingScreen();
            exit;
        } else {
            try {
                $_SESSION["captcha_code"] = bin2hex(random_bytes(3));
            } catch (RandomException $e) {}

            $message = '<p class="text-center text-danger">Invalid CAPTCHA. Please try again.</p>';
        }
    } elseif (isset($_POST["captcha_code"])) {
        $message = '<p class="text-center text-danger">CAPTCHA code cannot be empty. Please try again.</p>';
    }
}

function getLoadingScreen(): string
{
    return '
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Redirecting...</title>
    <style>
      body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    font-family: "Ubuntu Titling", Arial, sans-serif; 
    background-color: #a9a9a9;
}

.loading-container {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.loading-text {
    font-size: 24px;
    font-weight: bold;
    margin-right: 10px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 2s linear infinite;
}


@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="loading-text">Redirecting... Please wait.</div>
        <div class="spinner"></div>
    </div>
</body>
</html>
';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CAPTCHA - TechScript Services</title>
    <meta name="description" content="captcha,captcha-generator">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styles.css">
    <style>

        body {
            background-color: #a9a9a9;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .captcha-container {
            background-color: rgba(51, 51, 51, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            padding: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .captcha-container img.logo {
            width: 100%;
            max-width: 360px;
            height: auto;
            margin: 0 auto 20px;
            display: block;
        }

        .captcha-container img.captcha {
            width: 50%;
            border-radius: 5px;
            height: 50px;
            margin: 0 auto;
        }

        .form-horizontal {
            margin-bottom: 0;
        }

        .captcha-input {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            border-radius: 5px;
            width: 100%;
            height: 50px;
            margin-right: 10px;
            max-width: 150px;
            padding: 10px;
            font-size: 1.2rem;
            border: 1px solid #ccc;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 0 20px;
        }

        input[type="submit"] {
            display: inline-block;
            width: 100%;
            background-color: transparent;
            color: white;
            border: none;
            padding: 10px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover {
            color: #a3a3a3;
        }
        .message {
            font-size: 1.1rem;
            margin-top: 15px;
        }


    </style>
    <link rel="icon" href="../images/favicon.png" type="image/x-icon">
</head>
<body>
<div class="captcha-container">
    <img src="/captcha/capLogo.png" alt="Header Image" class="logo" />
    <form class="form-horizontal" method="POST" action="">
        <div class="captcha-input">
            <label>
                <input type="text" name="captcha_code" class="form-control" autocomplete="off" placeholder="CAPTCHA:" />
            </label>
            <img src="../captcha/captcha_gen.php" alt="CAPTCHA Image" class="captcha" />
        </div>
        <div class="button-container">
            <input type="submit" name='submit' value="Submit" class="btn btn-danger" />
        </div>
    </form>
    <p class="message"><?php echo $message; ?></p>
</div>

<div class="footer-box">
    <?php include 'pageElements/footer.php'; ?>
</div>
</body>
</html>
