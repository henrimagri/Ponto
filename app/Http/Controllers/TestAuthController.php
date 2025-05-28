<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestAuthController extends Controller
{
    public function testLogin()
    {
        return view('test-simple');
    }
}
