<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function generateExercises(Request $request): View
    {
        $request->validate([
            'check_sum' => 'required_without_all:check_subtraction,check_multiplication,check_division',
            'check_subtraction' => 'required_without_all:check_sum,check_multiplication,check_division',
            'check_multiplication' => 'required_without_all:check_sum,check_subtraction,check_division',
            'check_division' => 'required_without_all:check_sum,check_subtraction,check_multiplication',
            'number_one' => 'required|integer|min:0|max:999',
            'number_two' => 'required|integer|min:0|max:999',
            'number_exercises' => 'required|integer|min:5|max:50',
        ]);

        // Filtra apenas operações selecionadas
        $operations = collect([
            'sum' => $request->check_sum,
            'subtraction' => $request->check_subtraction,
            'multiplication' => $request->check_multiplication,
            'division' => $request->check_division,
        ])->filter()->keys()->toArray();

        // Se nenhuma operação for selecionada, retorna um erro
        if (empty($operations)) {
            return response()->json(['error' => 'At least one operation must be selected'], 400);
        }

        // Garante que min < max
        $min = min($request->number_one, $request->number_two);
        $max = max($request->number_one, $request->number_two);

        $numberExercises = $request->number_exercises;
        $exercises = [];

        for ($index = 1; $index <= $numberExercises; $index++) {
            $exercises[] = $this->generateExercise(
                $index,
                $operations,
                $min,
                $max
            );
        }

        $request->session()->put('exercises', $exercises);

        return view('operations', ['exercises' => $exercises]);
    }

    public function printExercises()
    {
        if (!session()->has('exercises')) {
            return redirect()->route('home');
        }

        $exercises = session('exercises');

        echo '<pre>';
        echo '<h1>Exercícios de Matemática (' . env('APP_NAME') . ')</h1>';
        echo '<hr>';

        foreach ($exercises as $exercise) {
            echo '<h2><small>' . str_pad($exercise['exercise_number'], 2, "0", STR_PAD_LEFT) . ' > </small> ' . $exercise['exercise'] . '</h2>';
        }

        echo '<hr>';
        echo '<small>Soluções:</small><br>';

        foreach ($exercises as $exercise) {
            echo '<small>' . str_pad($exercise['exercise_number'], 2, "0", STR_PAD_LEFT) . ' > ' . $exercise['exercise'] . $exercise['solution'] . '</small><br>';
        }
    }

    public function exportExercises()
    {
        if (!session()->has('exercises')) {
            return redirect()->route('home');
        }

        $exercises = session('exercises');

        $filename = 'exercises_' . env('APP_NAME') . '_' . date('YmdHis') . '.txt';

        $content = 'Exercícios de Matemática (' . env('APP_NAME') . ')' . "\n\n";
        foreach ($exercises as $exercise) {
            $content .= "{$exercise['exercise_number']} > {$exercise['exercise']}\n";
        }

        $content .= "\n";
        $content .= "\nSoluções\n" . str_repeat('-', 20) . "\n";
        foreach ($exercises as $exercise) {
            $content .= "{$exercise['exercise_number']} > {$exercise['exercise']}{$exercise['solution']}\n";
        }

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function generateExercise($index, $operations, $min, $max): array
    {
        $operation = $operations[array_rand($operations)];
        $number1 = rand($min, $max);
        $number2 = rand($min, $max);

        // Ajusta para divisão e subtração
        if ($operation === 'division') {
            $number2 = $number2 === 0 ? 1 : $number2; // Garante que nunca seja zero
            $number1 = rand($min, $max);
        } elseif ($operation === 'subtraction' && $number1 < $number2) {
            [$number1, $number2] = [$number2, $number1]; // Inverte para evitar números negativos
        }

        switch ($operation) {
            case 'sum':
                $exercise = "$number1 + $number2 = ";
                $solution = $number1 + $number2;
                break;
            case 'subtraction':
                $exercise = "$number1 - $number2 = ";
                $solution = $number1 - $number2;
                break;
            case 'multiplication':
                $exercise = "$number1 × $number2 = ";
                $solution = $number1 * $number2;
                break;
            case 'division':
                $exercise = "$number1 ÷ $number2 = ";
                $solution = $number1 / $number2;

                // Trunca para 2 casas decimais sem arredondar
                $solution = floor($solution * 100) / 100;
                break;
        }

        return [
            'operation' => $operation,
            'exercise_number' => $index,
            'exercise' => $exercise,
            'solution' => $solution,
        ];
    }
}
