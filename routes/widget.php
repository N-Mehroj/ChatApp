<?php

use Illuminate\Support\Facades\Route;

Route::get('/widget-vue-demo', function () {
    return view('widget-vue-demo');
})->name('widget.vue.demo');

Route::get('/widget-js-demo', function () {
    return view('widget-demo');
})->name('widget.js.demo');
