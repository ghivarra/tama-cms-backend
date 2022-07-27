<?php namespace App\Controllers;

/**
 * API Website
 *
 * "I treat my works as my own child, be careful with my childrens"
 *
 * Created with love and proud by Ghivarra Senandika Rushdie
 *
 * @package API TAMA CMS
 *
 * @var https://facebook.com/bcvgr
 * @var https://twitter.com/ghivarra
 * @var https://github.com/ghivarra
 *
**/

use App\Models\Pengaturan;
use Config\Services;

class WebsiteController extends BaseController
{
    public function get()
    {
        // perpanjang atau buat sesi
        $sesi = Services::session();

        // get data
        $pengaturan = new Pengaturan();

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Penarikan Data Berhasil',
            'message' => 'Pengambilan Pengaturan Website berhasil',
            'data'    => $pengaturan->select('pgn_nama, pgn_slug, pgn_tagline, pgn_deskripsi, pgn_versi_logo, pgn_versi_icon, pgn_versi_web')->first()
        ]);
    }

    //=====================================================================================================
}