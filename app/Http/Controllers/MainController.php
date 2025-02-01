<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class MainController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function generateExercises(Request $request): JsonResponse
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
        $operations = array_filter([
            $request->check_sum ? 'sum' : null,
            $request->check_subtraction ? 'subtraction' : null,
            $request->check_multiplication ? 'multiplication' : null,
            $request->check_division ? 'division' : null,
        ]);

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
            $operation = $operations[array_rand($operations)];
            $number1 = rand($min, $max);

            // Garante que number2 nunca seja zero
            do {
                $number2 = rand($min, $max);
            } while ($operation === 'division' && $number2 == 0);

            $exercise = '';
            $solution = '';

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
                    $exercise = "$number1 * $number2 = ";
                    $solution = $number1 * $number2;
                    break;
                case 'division':
                    $exercise = "$number1 / $number2 = ";
                    $solution = round($number1 / $number2, 2);
                    break;
            }

            $exercises[] = [
                'exercise_number' => $index,
                'exercise' => $exercise,
                'solution' => "$exercise $solution",
            ];
        }

        return response()->json($exercises);
    }

    public function printExercises(): Response
    {
        return response('Print the exercises in the browser');
    }

    public function exportExercises(): Response
    {
        return response('Export exercises to a text file');
    }
}
