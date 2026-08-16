<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/sao-ke-ngan-hang', function () {
    $sample = [
        'gateway' => 'TPBank',
        'transactionDate' => now()->format('Y-m-d H:i:s'),
        'accountNumber' => '25096007048',
        'subAccount' => null,
        'code' => null,
        'content' => 'PHUNG XUAN NGOC NAPAB12CD34ECOIN chuyen FT26195114003691',
        'transferType' => 'in',
        'description' => 'BankAPINotify PHUNG XUAN NGOC NAPAB12CD34ECOIN chuyen FT26195114003691',
        'transferAmount' => 5000000,
        'referenceCode' => '916ITC1261951850',
        'accumulated' => 9220032,
        'id' => random_int(10000000, 99999999),
    ];

    return view('sao-ke-webhook-test', [
        'webhookUrl' => url('/api/v1/webhooks/sao-ke-ngan-hang'),
        'requiresToken' => filled(config('services.sao_ke_webhook.token')),
        'sampleJson' => json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
});
