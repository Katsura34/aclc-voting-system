<?php
/**
 * MIME Type Guesser Diagnostic Script
 * Access via: http://localhost/debug_mime.php
 * DELETE THIS FILE after debugging!
 */

echo "<h1>MIME Type Guesser Diagnostic</h1>";

// 1. PHP Version and SAPI
echo "<h2>1. PHP Environment</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>PHP SAPI:</strong> " . php_sapi_name() . "</p>";
echo "<p><strong>PHP Binary:</strong> " . PHP_BINARY . "</p>";

// 2. Check fileinfo extension
echo "<h2>2. Fileinfo Extension</h2>";
echo "<p><strong>extension_loaded('fileinfo'):</strong> " . (extension_loaded('fileinfo') ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "</p>";
echo "<p><strong>function_exists('finfo_open'):</strong> " . (function_exists('finfo_open') ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "</p>";
echo "<p><strong>class_exists('finfo'):</strong> " . (class_exists('finfo') ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "</p>";

// 3. Test finfo directly
echo "<h2>3. Direct finfo Test</h2>";
if (function_exists('finfo_open')) {
    try {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        echo "<p><strong>finfo instantiation:</strong> <span style='color:green'>SUCCESS</span></p>";
        
        // Test with a known file
        $testFile = __DIR__ . '/index.php';
        if (file_exists($testFile)) {
            $mimeType = $finfo->file($testFile);
            echo "<p><strong>Test file MIME type (index.php):</strong> " . htmlspecialchars($mimeType) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p><strong>finfo error:</strong> <span style='color:red'>" . htmlspecialchars($e->getMessage()) . "</span></p>";
    }
} else {
    echo "<p style='color:red'><strong>finfo_open function not available!</strong></p>";
}

// 4. Check Symfony MimeTypes
echo "<h2>4. Symfony MimeTypes Test</h2>";
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Mime\FileBinaryMimeTypeGuesser;

$fileinfoGuesser = new FileinfoMimeTypeGuesser();
$fileBinaryGuesser = new FileBinaryMimeTypeGuesser();

echo "<p><strong>FileinfoMimeTypeGuesser->isGuesserSupported():</strong> " . 
    ($fileinfoGuesser->isGuesserSupported() ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "</p>";
echo "<p><strong>FileBinaryMimeTypeGuesser->isGuesserSupported():</strong> " . 
    ($fileBinaryGuesser->isGuesserSupported() ? '<span style="color:green">YES</span>' : '<span style="color:red">NO (expected on Windows)</span>') . "</p>";

$mimeTypes = MimeTypes::getDefault();
echo "<p><strong>MimeTypes->isGuesserSupported():</strong> " . 
    ($mimeTypes->isGuesserSupported() ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "</p>";

// 5. Test actual MIME guessing
echo "<h2>5. Full MIME Guessing Test</h2>";
try {
    $testFile = __DIR__ . '/index.php';
    $mimeType = $mimeTypes->guessMimeType($testFile);
    echo "<p><strong>Result:</strong> <span style='color:green'>SUCCESS - $mimeType</span></p>";
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> <span style='color:red'>" . htmlspecialchars($e->getMessage()) . "</span></p>";
}

// 6. Loaded extensions
echo "<h2>6. All Loaded Extensions</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
$fileinfoFound = in_array('fileinfo', $extensions);
echo "<p><strong>Total extensions:</strong> " . count($extensions) . "</p>";
echo "<p><strong>'fileinfo' in list:</strong> " . ($fileinfoFound ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "</p>";
echo "<pre>" . implode(", ", $extensions) . "</pre>";

// 7. php.ini location
echo "<h2>7. Configuration</h2>";
echo "<p><strong>Loaded php.ini:</strong> " . php_ini_loaded_file() . "</p>";
$additionalInis = php_ini_scanned_files();
if ($additionalInis) {
    echo "<p><strong>Additional ini files:</strong> " . $additionalInis . "</p>";
}

echo "<hr><p><em>Remember to delete this file after debugging!</em></p>";
