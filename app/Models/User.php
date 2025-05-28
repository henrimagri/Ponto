<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
        'role',
        'dob',
        'cep',
        'address',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'manager_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
        ];
    }

    /**
     * Get the manager that owns the user.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users that are managed by this user.
     */
    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager
     */
    public function isManager()
    {
        return $this->role === 'gestor';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee()
    {
        return $this->role === 'funcionario';
    }
    
    /**
     * Obter os registros de ponto do usuário.
     */
    public function registrosPonto()
    {
        return $this->hasMany(RegistroPonto::class)->orderBy('data', 'desc');
    }
    
    /**
     * Obter o registro de ponto do dia atual.
     */
    public function registroPontoHoje()
    {
        return $this->registrosPonto()
                    ->where('data', now()->toDateString())
                    ->first();
    }
}
