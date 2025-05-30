<?php

namespace App\Http\Controllers\Admin;

class RedirectController
{
    public function index(): void
    {
        header('Location: ' . base_url() . '/admin/dashboard');
        exit;
    }
}
