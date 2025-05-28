<?php
// Corrige registros duplicados de ponto

// Carrega o ambiente Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RegistroPonto;
use Illuminate\Support\Facades\DB;

echo "Iniciando correção de registros duplicados...\n";

try {
    // Encontrar todas as datas onde existem múltiplos registros para o mesmo usuário
    $duplicatas = DB::table('registro_ponto')
        ->select('user_id', 'data', DB::raw('COUNT(*) as total'))
        ->groupBy('user_id', 'data')
        ->having('total', '>', 1)
        ->get();
        
    echo "Encontradas {$duplicatas->count()} datas com registros duplicados\n";
    
    foreach ($duplicatas as $duplicata) {
        echo "Processando registros do usuário ID: {$duplicata->user_id}, data: {$duplicata->data}\n";
        
        // Buscar todos os registros duplicados ordenados por ID
        $registros = RegistroPonto::where('user_id', $duplicata->user_id)
            ->where('data', $duplicata->data)
            ->orderBy('id')
            ->get();
            
        // Mantemos o registro mais antigo (menor ID) e mesclamos as outras marcações
        $registroPrincipal = $registros->first();
        echo "  - Mantendo registro ID: {$registroPrincipal->id}\n";
        
        // Processar os demais registros (duplicados)
        foreach ($registros as $index => $registro) {
            if ($index === 0) continue; // Pular o primeiro (que é o principal)
            
            echo "  - Mesclando registro ID: {$registro->id}\n";
            
            // Mesclar marcações, dando prioridade para valores não nulos
            for ($i = 1; $i <= 4; $i++) {
                $campo = "marcacao{$i}";
                if (is_null($registroPrincipal->$campo) && !is_null($registro->$campo)) {
                    echo "    - Copiando marcacao{$i}: {$registro->$campo}\n";
                    $registroPrincipal->$campo = $registro->$campo;
                }
            }
            
            // Excluir o registro duplicado
            $registro->delete();
        }
        
        // Salvar o registro principal com as marcações mescladas
        $registroPrincipal->save();
        echo "  - Registro atualizado com sucesso!\n";
    }
    
    echo "Correção concluída com sucesso!\n";
} finally {
    // Reabilitar verificação de chaves estrangeiras
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
}
