<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Configuration\Exceptions;

return function (Exceptions $exceptions) {
    $exceptions->renderable(function (NotFoundHttpException $e, $request) {
        if ($request->expectsJson()) {
            $previous = $e->getPrevious();
            if ($previous instanceof ModelNotFoundException) {
                $model = $previous->getModel();
                $message = match ($model) {
                    'App\\Models\\Project' => 'Проект не найден',
                    //'App\\Models\\Room' => 'Комната не найдена',
                    default => 'Ресурс не найден',
                };
                return response()->json(['message' => $message], 404);
            }

            return response()->json(['message' => 'Ресурс не найден'], 404);
        }
    });
};
