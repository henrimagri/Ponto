<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RegistroPonto;

class LimparRegistrosPontoDuplicados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ponto:limpar-duplicados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa registros de ponto duplicados, mantendo apenas um registro por dia por usuário';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando limpeza de registros duplicados...');

        // Busca todos os usuários
        $users = User::all();
        $totalDuplicados = 0;

        foreach ($users as $user) {
            $this->info("Processando registros do usuário {$user->name} (ID: {$user->id})");
            
            // Busca as datas que têm mais de um registro para este usuário
            $datasDuplicadas = RegistroPonto::where('user_id', $user->id)
                ->selectRaw('data, COUNT(*) as total')
                ->groupBy('data')
                ->having('total', '>', 1)
                ->get();
                
            if ($datasDuplicadas->count() == 0) {
                $this->info("  - Nenhum registro duplicado encontrado");
                continue;
            }
            
            $this->info("  - Encontrados registros duplicados em " . $datasDuplicadas->count() . " datas");
            
            foreach ($datasDuplicadas as $dataDuplicada) {
                $this->info("    - Processando data {$dataDuplicada->data}");
                
                // Busca todos os registros desta data
                $registros = RegistroPonto::where('user_id', $user->id)
                    ->whereDate('data', $dataDuplicada->data)
                    ->orderBy('id')
                    ->get();
                    
                // Mantém o primeiro registro e mescla as marcações dos demais
                $registroManterr = $registros->first();
                
                foreach ($registros as $index => $registro) {
                    // Pula o primeiro registro (que será mantido)
                    if ($index === 0) continue;
                    
                    // Mescla as marcações, mantendo as primeiras não nulas
                    for ($i = 1; $i <= 4; $i++) {
                        $campo = "marcacao{$i}";
                        
                        if (is_null($registroManterr->$campo) && !is_null($registro->$campo)) {
                            $registroManterr->$campo = $registro->$campo;
                        }
                    }
                    
                    // Exclui o registro duplicado
                    $registro->delete();
                    $totalDuplicados++;
                }
                
                // Salva o registro mesclado
                $registroManterr->save();
            }
        }
        
        $this->info("Limpeza concluída! Total de {$totalDuplicados} registros duplicados removidos.");
    }
}
