<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Statamic\View\View;

Route::get('/', function () {
    return (new Statamic\View\View)
        ->layout('layouts.master')
        ->template('welcome')
        ->with(['title' => 'Welcome!']);
});

Route::statamic('/periodicals', 'periodicals.index', ['layout' => 'layouts.master']);

Route::get('/contact', function () {
    return (new Statamic\View\View)
        ->layout('layouts.master')
        ->template('contact')
        ->with(['title' => 'Wanna chat!?']);
});