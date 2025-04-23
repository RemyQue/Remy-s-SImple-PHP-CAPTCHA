<?php
session_start(); // Start the session

// Function to generate a random CAPTCHA from a predefined char string consisting of uppercase letters, lowercase letters, and numbers
function generateCaptchaCode($length = 5): string
{
	$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // char string 1
    // $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnoqrstuvwxyz123456789'; // char string 2
    $charactersLength = strlen($characters);
    $captcha_code = '';

    for ($i = 0; $i < $length; $i++) {
        $captcha_code .= $characters[rand(0, $charactersLength - 1)]; // randomly pick a char;
    }

    return $captcha_code;
}

// Function to generate random RGB color
function randomColor($isBackground = false): array
{
    // Ensure background and text colors are not too similar
    if ($isBackground) {
        return [rand(180, 255), rand(180, 255), rand(180, 255)]; // Lighter background colors
    } else {
        return [rand(0, 150), rand(0, 150), rand(0, 150)]; // Darker text colors
    }
}

// Function to calculate brightness of an RGB color
function calculateBrightness($rgb): float
{
    return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2]; // Standard formula for brightness
}

// Function to ensure text and background are not too similar
function ensureContrast($background_rgb): array
{
    $brightness = calculateBrightness($background_rgb);

    // If brightness is low (dark background), use a light text color
    if ($brightness < 128) {
        return [rand(200, 255), rand(200, 255), rand(200, 255)]; // Light text
    }
    // If brightness is high (light background), use a dark text color
    else {
        return [rand(0, 100), rand(0, 100), rand(0, 100)]; // Dark text
    }
}

// Generate CAPTCHA
$captcha_code = generateCaptchaCode();
$_SESSION["captcha_code"] = $captcha_code; // Store in session

/* Width and Height of CAPTCHA */
$target_layer = imagecreatetruecolor(160, 70); // Slightly narrower CAPTCHA width

/* Background color of CAPTCHA */
$background_color_rgb = randomColor(true); // Random light background color
$captcha_background = imagecolorallocate($target_layer, $background_color_rgb[0], $background_color_rgb[1], $background_color_rgb[2]);
imagefill($target_layer, 0, 0, $captcha_background);

/* CAPTCHA Text Color RGB */
$text_color_rgb = ensureContrast($background_color_rgb); // Ensure text color contrasts with the background
$captcha_text_color = imagecolorallocate($target_layer, $text_color_rgb[0], $text_color_rgb[1], $text_color_rgb[2]);

/* Text size and properties */
$font_size = 36; // Font size remains the same as original

/** Calculate the total width of the text */
$total_text_width = 0;
foreach (str_split($captcha_code) as $char) {
    $total_text_width += imagettfbbox($font_size, 0, __DIR__ . '/Oswald-VariableFont_wght.ttf', $char)[2]; // Width of each character
}

/** Adjust the starting position based on total text width */
$left_margin = 0; // Additional margin on the left for more space
$x = max($left_margin, (20 - $total_text_width) / 70); // Ensure that it doesn't go off-screen

$angle = rand(-20, 20); // Increased random angle for more rotation distortion

/** For Lines */
$line_color_rgb = randomColor(); // Random color for lines
$line_color = imagecolorallocate($target_layer, $line_color_rgb[0], $line_color_rgb[1], $line_color_rgb[2]);
for ($i = 0; $i < 6; $i++) {
    imageline($target_layer, rand(0, 160), rand(0, 70), rand(0, 160), rand(0, 80), $line_color);
}

/** For pixels (noise) */
$pixel_color_rgb = randomColor(); // Random pixel color
$pixel_color = imagecolorallocate($target_layer, $pixel_color_rgb[0], $pixel_color_rgb[1], $pixel_color_rgb[2]);
for ($i = 0; $i < 1500; $i++) {  // Increased the noise for more distortion
    imagesetpixel($target_layer, rand(0, 160), rand(0, 70), $pixel_color);
}

/* Draw the CAPTCHA text */
foreach (str_split($captcha_code) as $char) {
    $x_offset = rand(3, 5); // Small horizontal offset to squish text more
    $y_offset = rand(-10, 10); // Increased random vertical offset for more vertical distortion
    imagettftext($target_layer, $font_size, $angle, $x + $x_offset, 55 + $y_offset, $captcha_text_color, __DIR__ . '/Oswald-VariableFont_wght.ttf', $char);
    $x += imagettfbbox($font_size, 0, __DIR__ . '/Oswald-VariableFont_wght.ttf', $char)[2] + rand(3, 6); // Decreased spacing to squish text
}

// Output the image as PNG
header("Content-type: image/png");
imagepng($target_layer);
imagedestroy($target_layer); // Free memory
