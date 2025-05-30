<?php

namespace App\Http\Controllers;

use App\Models\RegistroPonto;
use Illuminate\Http\Request;

class PontoController extends Controller
{
    /**
     * Registra um ponto para o usuário atual
     * 
     * Este método registra as 4 marcações diárias (entrada, saída para almoço, retorno do almoço, saída)
     * Todas as marcações são salvas no mesmo registro (linha) da tabela, em campos diferentes
     */
    public function registrarPonto(Request $request)
    {
        $user = auth()->user();
        $agora = now();
        $hoje = $agora->toDateString();
        
        // Verificar se já existe um registro para hoje
        // Garantir que só exista um registro por dia usando formato correto para MySQL
        $registroDia = $user->registrosPonto()
                            ->where('data', $hoje)
                            ->first();
                            
        if (!$registroDia) {
            // Criar novo registro para hoje com a primeira marcação (Entrada)
            $registroDia = $user->registrosPonto()->create([
                'data' => $hoje,
                'marcacao1' => $agora  // Primeira marcação: Entrada
                // As outras marcações (marcacao2, marcacao3, marcacao4) ficarão null por enquanto
            ]);
            
            $numeroMarcacao = 1;
            $tipoMarcacao = 'Entrada';
        } else {
            // Verificar qual marcação será feita (qual é o próximo campo a preencher)
            $proximaMarcacao = $registroDia->proximaMarcacao();
            
            if ($proximaMarcacao == 0) {
                // Todas as 4 marcações já foram realizadas
                return response()->json([
                    'status' => 'error',
                    'message' => 'Todas as marcações do dia já foram realizadas.',
                    'registroDia' => $registroDia
                ]);
            }
            
            // Registrar a próxima marcação no mesmo registro (atualizando o campo correspondente)
            $campo = "marcacao{$proximaMarcacao}"; // marcacao2, marcacao3 ou marcacao4
            $registroDia->$campo = $agora;
            $registroDia->save();
            
            $numeroMarcacao = $proximaMarcacao;
            
            $tiposMarcacao = [
                1 => 'Entrada',
                2 => 'Saída para almoço',
                3 => 'Retorno do almoço',
                4 => 'Saída'
            ];
            
            $tipoMarcacao = $tiposMarcacao[$proximaMarcacao];
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Ponto registrado com sucesso!',
            'data' => [
                'data' => $agora->format('d/m/Y'),
                'hora' => $agora->format('H:i:s'),
                'tipo_marcacao' => $tipoMarcacao,
                'numero_marcacao' => $numeroMarcacao,
                'todas_marcacoes_realizadas' => $registroDia->todasMarcacoesRealizadas()
            ],
            'registroDia' => $registroDia
        ]);
    }
    
    /**
     * Obtém o estado atual do ponto para o usuário logado
     * 
     * Retorna informações sobre as marcações já realizadas e a próxima marcação a ser feita
     */
    public function statusPontoHoje()
    {
        $user = auth()->user();
        $hoje = now()->toDateString();
        
        // Verificar se já existe um registro para hoje
        // Usando formato de data apropriado para MySQL
        $registroDia = $user->registrosPonto()
                            ->where('data', $hoje)
                            ->first();
                            
        if (!$registroDia) {
            // Se não houver registros hoje, a próxima marcação será a primeira (Entrada)
            return response()->json([
                'status' => 'pending',
                'message' => 'Nenhuma marcação realizada hoje.',
                'proximaMarcacao' => 1,
                'tipoMarcacao' => 'Entrada',
                'tipoProximaMarcacao' => 'Entrada',
                'marcacoes' => []
            ]);
        }
        
        // Define os tipos de marcação para cada campo no banco
        $tiposMarcacao = [
            'marcacao1' => 'Entrada',
            'marcacao2' => 'Saída para almoço',
            'marcacao3' => 'Retorno do almoço',
            'marcacao4' => 'Saída'
        ];
        
        // Coleta as marcações já realizadas (campos preenchidos)
        $marcacoes = [];
        foreach ($tiposMarcacao as $campo => $tipo) {
            if (!is_null($registroDia->$campo)) {
                $marcacoes[] = [
                    'tipo' => $tipo,
                    'hora' => $registroDia->$campo->format('H:i:s'),
                    'campo' => $campo
                ];
            }
        }
        
        // Determina qual é a próxima marcação
        $proximaMarcacao = $registroDia->proximaMarcacao();
        $tipoProximaMarcacao = $proximaMarcacao > 0 ? $tiposMarcacao["marcacao{$proximaMarcacao}"] : null;
        
        return response()->json([
            'status' => 'success',
            'data' => $hoje,
            'proximaMarcacao' => $proximaMarcacao,
            'tipoProximaMarcacao' => $tipoProximaMarcacao,
            'todasMarcacoesRealizadas' => $registroDia->todasMarcacoesRealizadas(),
            'marcacoes' => $marcacoes,
            'registroDia' => $registroDia
        ]);
    }
    
    /**
     * Exibe o relatório de ponto de um funcionário (SQL puro, compatível com ONLY_FULL_GROUP_BY)
     *
     * Esta versão utiliza SQL puro para buscar os registros de ponto do funcionário,
     * aplicando filtros de data e agrupando corretamente por data.
     */
    public function relatorioFuncionario($id, Request $request)
    {
        // Verificar se o usuário atual é admin ou gestor
        $currentUser = auth()->user();
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar este recurso.');
        }

        // Buscar o usuário pelo ID
        $user = \App\Models\User::findOrFail($id);

        // Se for gestor, só pode ver seus subordinados
        if ($currentUser->isManager() && $user->manager_id !== $currentUser->id && $user->id !== $currentUser->id) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar este recurso.');
        }

        // Filtros de data
        $dataInicio = $request->filled('data_inicio') ? $request->data_inicio : null;
        $dataFim = $request->filled('data_fim') ? $request->data_fim : null;

        // Subquery para pegar o menor ID de cada data
        $subSql = "SELECT MIN(id) as id FROM registro_ponto WHERE user_id = ?";
        $params = [$user->id];
        if ($dataInicio) {
            $subSql .= " AND data >= ?";
            $params[] = $dataInicio;
        }
        if ($dataFim) {
            $subSql .= " AND data <= ?";
            $params[] = $dataFim;
        }
        $subSql .= " GROUP BY data ORDER BY data DESC LIMIT ".($dataInicio ? 100 : 30);
        $ids = collect(\DB::select($subSql, $params))->pluck('id')->all();

        if (empty($ids)) {
            $registros = collect();
        } else {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT id, data, marcacao1, marcacao2, marcacao3, marcacao4 FROM registro_ponto WHERE id IN ($placeholders) ORDER BY data DESC";
            $registros = collect(\DB::select($sql, $ids));
        }

        // Converter campos de data/hora para objetos Carbon
        $registros = $registros->map(function($registro) {
            $registro->data = \Carbon\Carbon::parse($registro->data);
            foreach ([1,2,3,4] as $i) {
                $campo = "marcacao".$i;
                $registro->$campo = $registro->$campo ? \Carbon\Carbon::parse($registro->$campo) : null;
            }
            // Adiciona métodos auxiliares como propriedades reais para uso nas views
            $registro->todasMarcacoesRealizadas = ($registro->marcacao1 && $registro->marcacao2 && $registro->marcacao3 && $registro->marcacao4);
            $registro->calcularHorasTrabalhadas = function() use ($registro) {
                if ($registro->marcacao1 && $registro->marcacao2 && $registro->marcacao3 && $registro->marcacao4) {
                    $manha = $registro->marcacao2->diffInMinutes($registro->marcacao1);
                    $tarde = $registro->marcacao4->diffInMinutes($registro->marcacao3);
                    $total = $manha + $tarde;
                    return [
                        'horas' => floor($total / 60),
                        'minutos' => $total % 60,
                        'total_minutos' => $total
                    ];
                }
                return ['horas' => 0, 'minutos' => 0, 'total_minutos' => 0];
            };
            return $registro;
        });

        return view('ponto.relatorio_funcionario', compact('registros', 'user', 'dataInicio', 'dataFim'));
    }
    
    /**
     * Exibe o relatório de pontos do usuário logado (SQL puro, compatível com ONLY_FULL_GROUP_BY)
     *
     * Esta versão utiliza SQL puro para buscar os registros de ponto do próprio usuário,
     * aplicando filtros de data e agrupando corretamente por data.
     */
    public function relatorio(Request $request)
    {
        $user = auth()->user();
        $dataInicio = $request->filled('data_inicio') ? $request->data_inicio : null;
        $dataFim = $request->filled('data_fim') ? $request->data_fim : null;

        // Subquery para pegar o menor ID de cada data
        $subSql = "SELECT MIN(id) as id FROM registro_ponto WHERE user_id = ?";
        $params = [$user->id];
        if ($dataInicio) {
            $subSql .= " AND data >= ?";
            $params[] = $dataInicio;
        }
        if ($dataFim) {
            $subSql .= " AND data <= ?";
            $params[] = $dataFim;
        }
        $subSql .= " GROUP BY data ORDER BY data DESC LIMIT ".($dataInicio ? 100 : 30);
        $ids = collect(\DB::select($subSql, $params))->pluck('id')->all();

        if (empty($ids)) {
            $registros = collect();
        } else {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT id, data, marcacao1, marcacao2, marcacao3, marcacao4 FROM registro_ponto WHERE id IN ($placeholders) ORDER BY data DESC";
            $registros = collect(\DB::select($sql, $ids));
        }

        // Converter campos de data/hora para objetos Carbon
        $registros = $registros->map(function($registro) {
            $registro->data = \Carbon\Carbon::parse($registro->data);
            foreach ([1,2,3,4] as $i) {
                $campo = "marcacao".$i;
                $registro->$campo = $registro->$campo ? \Carbon\Carbon::parse($registro->$campo) : null;
            }
            // Adiciona métodos auxiliares como propriedades reais para uso nas views
            $registro->todasMarcacoesRealizadas = ($registro->marcacao1 && $registro->marcacao2 && $registro->marcacao3 && $registro->marcacao4);
            $registro->calcularHorasTrabalhadas = function() use ($registro) {
                if ($registro->marcacao1 && $registro->marcacao2 && $registro->marcacao3 && $registro->marcacao4) {
                    $manha = $registro->marcacao2->diffInMinutes($registro->marcacao1);
                    $tarde = $registro->marcacao4->diffInMinutes($registro->marcacao3);
                    $total = $manha + $tarde;
                    return [
                        'horas' => floor($total / 60),
                        'minutos' => $total % 60,
                        'total_minutos' => $total
                    ];
                }
                return ['horas' => 0, 'minutos' => 0, 'total_minutos' => 0];
            };
            return $registro;
        });

        return view('ponto.relatorio', compact('registros', 'user', 'dataInicio', 'dataFim'));
    }
}
