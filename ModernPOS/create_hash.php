<?php
// File: create_hash.php

// The password you want to use
$passwordToHash = '123';

// Generate the hash using this server's PHP version
$hash = password_hash($passwordToHash, PASSWORD_DEFAULT);

echo "<h1>Password Hash Generator</h1>";
echo "<p>This page uses your server's current PHP version to create a password hash.</p>";
echo "<hr>";
echo "<strong>Password to hash:</strong> " . htmlspecialchars($passwordToHash) . "<br><br>";
echo "<strong>Generated Hash (Copy this entire value):</strong><br>";
echo "<textarea rows='3' cols='70' readonly style='font-size:1.2em;'>" . htmlspecialchars($hash) . "</textarea>";
?>