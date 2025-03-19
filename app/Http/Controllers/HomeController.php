<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Muestra la vista de inicio.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('welcome'); // Asegúrate de que exista un archivo en resources/views/home.blade.php
    }
}
