<?php
echo "Current directory: " . __DIR__ . "<br>";
echo "Include path: " . get_include_path() . "<br>";

$test_path = __DIR__ . '/views/tasks/index.php';
echo "Testing path: " . $test_path . "<br>";
echo "File exists: " . (file_exists($test_path) ? 'Yes' : 'No') . "<br>";
?>