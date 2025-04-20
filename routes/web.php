<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ExtraHoursForm;

Route::get('/', ExtraHoursForm::class)->name('public.activity.create');

/*
Route::get('/', function () {
    return view('welcome');
});
*/

// Aquí añadiremos las rutas de autenticación y administración más adelante
