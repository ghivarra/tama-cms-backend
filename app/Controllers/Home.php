<?php namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'code'    => 200,
            'status'  => 'OK',
            'message' => 'API GSS LIT dalam kondisi aktif'
        ]);
    }
}
