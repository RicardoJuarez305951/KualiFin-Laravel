<?php

use App\Services\ExcelReaderService;

it('matches names regardless of order', function () {
    $service = new ExcelReaderService();

    $norm = new ReflectionMethod($service, 'normalizeComparable');
    $norm->setAccessible(true);
    $tokens = new ReflectionMethod($service, 'containsAllTokens');
    $tokens->setAccessible(true);

    $haystack = $norm->invoke($service, 'Perez Juan');
    $needle = $norm->invoke($service, 'Juan Perez');

    expect($tokens->invoke($service, $haystack, $needle))->toBeTrue();
});

it('matches names with additional components', function () {
    $service = new ExcelReaderService();

    $norm = new ReflectionMethod($service, 'normalizeComparable');
    $norm->setAccessible(true);
    $tokens = new ReflectionMethod($service, 'containsAllTokens');
    $tokens->setAccessible(true);

    $haystack = $norm->invoke($service, 'Juan Carlos Perez');
    $needle = $norm->invoke($service, 'Juan Perez');

    expect($tokens->invoke($service, $haystack, $needle))->toBeTrue();
});
