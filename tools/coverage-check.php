<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/coverage-check.php <clover.xml> [classes>=100] [methods>=100] [lines>=100]\n");
    exit(2);
}

$cloverPath = $argv[1];
$requiredClasses = isset($argv[2]) ? (float) $argv[2] : 100.0;
$requiredMethods = isset($argv[3]) ? (float) $argv[3] : 100.0;
$requiredLines = isset($argv[4]) ? (float) $argv[4] : 100.0;

if (!is_file($cloverPath)) {
    fwrite(STDERR, "Coverage file not found: {$cloverPath}\n");
    exit(2);
}

$xml = simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, "Failed to parse Clover XML: {$cloverPath}\n");
    exit(2);
}

$metricsNodes = $xml->xpath('/coverage/project/metrics');
if ($metricsNodes === false || count($metricsNodes) === 0) {
    fwrite(STDERR, "Clover XML missing /coverage/project/metrics\n");
    exit(2);
}

$metrics = $metricsNodes[0];

$classes = (int) ($metrics['classes'] ?? 0);
$methods = (int) ($metrics['methods'] ?? 0);
$coveredMethods = (int) ($metrics['coveredmethods'] ?? 0);
$statements = (int) ($metrics['statements'] ?? 0);
$coveredStatements = (int) ($metrics['coveredstatements'] ?? 0);
$elements = (int) ($metrics['elements'] ?? 0);
$coveredElements = (int) ($metrics['coveredelements'] ?? 0);

if ($classes <= 0 || $methods <= 0 || $statements <= 0 || $elements <= 0) {
    fwrite(STDERR, "Invalid metrics in Clover XML\n");
    exit(2);
}

$classCoverage = ($coveredElements / $elements) * 100.0;
$methodCoverage = ($coveredMethods / $methods) * 100.0;
$lineCoverage = ($coveredStatements / $statements) * 100.0;

$ok = true;
if ($classCoverage + 1e-9 < $requiredClasses) {
    $ok = false;
}
if ($methodCoverage + 1e-9 < $requiredMethods) {
    $ok = false;
}
if ($lineCoverage + 1e-9 < $requiredLines) {
    $ok = false;
}

printf(
    "Coverage: classes=%.2f%% methods=%.2f%% lines=%.2f%% (required: %.2f/%.2f/%.2f)\n",
    $classCoverage,
    $methodCoverage,
    $lineCoverage,
    $requiredClasses,
    $requiredMethods,
    $requiredLines
);

exit($ok ? 0 : 1);
