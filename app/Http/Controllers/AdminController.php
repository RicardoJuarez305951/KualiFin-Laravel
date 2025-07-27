<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    // GET /promotora
    public function index()
    {
        return view('admin.index');
    }

    public function nuevoEmpleado()
    {
        return view('admin.create-user');
    }
}
