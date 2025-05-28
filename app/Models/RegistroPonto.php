<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroPonto extends Model
{
    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'registro_ponto';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'data',
        'marcacao1',
        'marcacao2',
        'marcacao3',
        'marcacao4',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'date',
        'marcacao1' => 'datetime',
        'marcacao2' => 'datetime',
        'marcacao3' => 'datetime',
        'marcacao4' => 'datetime',
    ];

    /**
     * Obter o usuário que registrou o ponto.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Verifica se todas as marcações do dia já foram feitas.
     *
     * @return bool
     */
    public function todasMarcacoesRealizadas()
    {
        return !is_null($this->marcacao1) && 
               !is_null($this->marcacao2) && 
               !is_null($this->marcacao3) && 
               !is_null($this->marcacao4);
    }
    
    /**
     * Obtém o próximo número da marcação a ser realizada.
     *
     * @return int
     */
    public function proximaMarcacao()
    {
        if (is_null($this->marcacao1)) return 1;
        if (is_null($this->marcacao2)) return 2;
        if (is_null($this->marcacao3)) return 3;
        if (is_null($this->marcacao4)) return 4;
        return 0; // Todas as marcações já foram realizadas
    }
    
    /**
     * Calcular a quantidade de horas trabalhadas
     *
     * @return array
     */
    public function calcularHorasTrabalhadas()
    {
        if (!$this->todasMarcacoesRealizadas()) {
            return [
                'horas' => 0,
                'minutos' => 0,
                'total_minutos' => 0
            ];
        }
        
        $periodoManha = $this->marcacao1->diffInMinutes($this->marcacao2);
        $periodoTarde = $this->marcacao3->diffInMinutes($this->marcacao4);
        $totalMinutos = $periodoManha + $periodoTarde;
        
        return [
            'horas' => floor($totalMinutos / 60),
            'minutos' => $totalMinutos % 60,
            'total_minutos' => $totalMinutos
        ];
    }
}
