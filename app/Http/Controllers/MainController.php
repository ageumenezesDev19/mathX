<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    public function home()
    {
        echo 'Show the initial page';
    }

    public function generateExercise($Request)
    {
        echo 'Generate Exercises';
    }

    public function printExercises()
    {
        echo 'Print the exercises in the browser';
    }

    public function exportExercises()
    {
        echo 'Export exercises to a text file';
    }
}
