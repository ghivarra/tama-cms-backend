<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use App\Libraries\Akses;
use App\Models\AdminModul;
use Config\Services;

class AksesFilter implements FilterInterface
{
    private function getModul($key)
    {
        $uri   = new Uri(current_url());
        $modul = new AdminModul();

        // set
        $link = $uri->getSegments();
        $slug = [];
        $inst = '';

        foreach ($link as $n => $val):

            if ($n == $key)
            {
                $inst = $val;
                array_push($slug, $inst);

            } elseif ($n > $key) {

                $inst .= "/{$val}";
                array_push($slug, $inst);
            }

        endforeach;

        if (empty($slug))
        {
            return [];
        }

        // return
        return $modul->select('mod_id')->whereIn('mod_nama', $slug)->first();
    }

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // get modul name
        $response = Services::response();
        $session  = Services::session();

        // get modul
        $modul = $this->getModul(1);

        // empty
        if (isset($modul['mod_id']))
        {
            $akses = new Akses();

            if (!$akses->cekAksesModul($modul['mod_id']))
            {
                return $response->setStatusCode(403)->setJSON([
                    'code'    => 403,
                    'status'  => 'error',
                    'title'   => 'Akses Diblokir',
                    'message' => 'Anda tidak memiliki izin untuk mengakses data ini'
                ]);
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
