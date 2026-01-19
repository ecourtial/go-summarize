<?php

use Illuminate\Http\Request;
use App\Services\AuthStore;
use App\Services\BackendClient;
use Illuminate\Support\Facades\Route;

Route::get('/', function (AuthStore $store) {
    if ($store->isLoggedIn()) {
        return redirect()->route('review');
    }

    return redirect()->route('login');
});

Route::get('/login', function (AuthStore $store) {
    if ($store->isLoggedIn()) {
        return redirect()->route('review');
    }

    return view('login');
})->name('login');

Route::post('/login', function (Request $request, AuthStore $store) {
    $data = $request->validate([
        'server_url' => ['required', 'url'],
        'email'      => ['required', 'email'],
        'password'   => ['required', 'string'],
    ]);

    $serverUrl = rtrim($data['server_url'], '/');

    try {
        $client = new BackendClient($serverUrl, null);
        $token = $client->login($data['email'], $data['password']);

        $store->set($serverUrl, $token);

        return redirect()->route('review');
    } catch (\Throwable $e) {
        return back()
            ->withInput($request->except('password'))
            ->with('error', 'Login failed: ' . $e->getMessage());
    }
})->name('login.post');

Route::get('/review', function (AuthStore $store) {
    if (!$store->isLoggedIn()) {
        return redirect()->route('login');
    }

    $client = new BackendClient($store->getServerUrl(), $store->getToken());

    try {
        $item = $client->getOldestPendingItem();
        $errorMessage = null;
    } catch (\Throwable $e) {
        $item = null;
        $errorMessage = $e->getMessage();
    }

    return view('review', [
        'item' => $item,
        'error' => $errorMessage,
    ]);
})->name('review');

Route::post('/review/discard', function (Request $request, AuthStore $store) {
    if (!$store->isLoggedIn()) return redirect()->route('login');

    $data = $request->validate(['id' => ['required', 'string']]);

    $client = new BackendClient($store->getServerUrl(), $store->getToken());

    try {
        $client->updatePageStatus($data['id'], 'DISCARDED');
    } catch (\Throwable $e) {
        return redirect()->route('review')->with('error', $e->getMessage());
    }

    return redirect()->route('review');
})->name('review.discard');

Route::post('/review/to-summarize', function (Request $request, AuthStore $store) {
    if (!$store->isLoggedIn()) return redirect()->route('login');

    $data = $request->validate(['id' => ['required', 'string']]);

    $client = new BackendClient($store->getServerUrl(), $store->getToken());

    try {
        $client->updatePageStatus($data['id'], 'TO_SUMMARIZE');
    } catch (\Throwable $e) {
        return redirect()->route('review')->with('error', $e->getMessage());
    }

    return redirect()->route('review');
})->name('review.to-summarize');

Route::post('/review/manual', function (Request $request, AuthStore $store) {
    if (!$store->isLoggedIn()) return redirect()->route('login');

    $data = $request->validate(['id' => ['required', 'string']]);

    $client = new BackendClient($store->getServerUrl(), $store->getToken());

    try {
        $client->updatePageStatus($data['id'], 'TO_READ');
    } catch (\Throwable $e) {
        return redirect()->route('review')->with('error', $e->getMessage());
    }

    return redirect()->route('review');
})->name('review.manual');

Route::post('/logout', function (Request $request, AuthStore $store) {
    $store->clear();
    return redirect()->route('login');
})->name('logout');
