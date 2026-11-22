<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        return View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url): Response
    {
        return Response::redirect($url);
    }

    protected function back(): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? route('home');
        return Response::redirect($referer);
    }

    protected function validate(Request $request, array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $ruleString) {
            $value = $request->input($field);
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && (is_null($value) || trim((string)$value) === '')) {
                    $errors[$field] = "Câmpul '{$field}' este obligatoriu.";
                    break;
                }
                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Adresa de email este invalidă.";
                    break;
                }
                if (str_starts_with($rule, 'min:') && !empty($value)) {
                    $min = (int)substr($rule, 4);
                    if (strlen((string)$value) < $min) {
                        $errors[$field] = "Câmpul '{$field}' trebuie să aibă minim {$min} caractere.";
                        break;
                    }
                }
            }

            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->all());
        }

        return ['errors' => $errors, 'data' => $data];
    }
}
