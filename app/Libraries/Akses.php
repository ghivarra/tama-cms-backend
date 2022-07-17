<?php namespace App\Libraries;

/**
 * API Akses Library
 *
 * "I treat my works as my own child, be careful with my childrens"
 *
 * Created with love and proud by Ghivarra Senandika Rushdie
 *
 * @package API GSS LIT
 *
 * @var https://facebook.com/bcvgr
 * @var https://twitter.com/ghivarra
 * @var https://instagram.com/ghivarra
 *
**/

use App\Models\Admin;
use App\Models\AdminModul;
use Config\Services;

class Akses
{
    public function cekAksesModul($idModul)
    {
        $get['mod_id'] = $idModul;

        // cek sesi
        if (!isset($_SESSION['admin']['rol_list_modul']) OR empty($_SESSION['admin']['rol_list_modul']))
        {
            session_destroy();
            return FALSE;
        }

        // cek yg bisa membuat true
        if (is_array($_SESSION['admin']['rol_list_modul']))
        {
            if (in_array($get['mod_id'], $_SESSION['admin']['rol_list_modul']))
            {
                return TRUE;
            }

        } elseif ($_SESSION['admin']['rol_list_modul'] == 'Semua Modul') {

            return TRUE;
        }

        // klo semua gagal maka return false
        return FALSE;
    }

    //======================================================================================================

    public function cekAksesMenu($idMenu)
    {

    }

    //======================================================================================================
}