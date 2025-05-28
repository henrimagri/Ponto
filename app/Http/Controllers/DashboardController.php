<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RegistroPonto;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $users = User::with('manager')->paginate(10);
            $totalUsers = User::count();
            $totalAdmins = User::where('role', 'admin')->count();
            $totalManagers = User::where('role', 'gestor')->count();
            $totalEmployees = User::where('role', 'funcionario')->count();
            
            return view('dashboard.admin', compact('users', 'totalUsers', 'totalAdmins', 'totalManagers', 'totalEmployees'));
        } elseif ($user->isManager()) {
            $subordinates = User::where('manager_id', $user->id)->paginate(10);
            $totalSubordinates = User::where('manager_id', $user->id)->count();
            
            return view('dashboard.manager', compact('subordinates', 'totalSubordinates'));
        } else {
            // Buscar o registro de ponto do dia atual
            $registroPonto = $user->registroPontoHoje();
            
            return view('dashboard.employee', compact('user', 'registroPonto'));
        }
    }
}
