<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected int $uid = 0;
    protected string $role = '';

    /**
     * @param CLIRequest|IncomingRequest $request
     *
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        $this->uid  = (int) session()->get('user_id');
        $this->role = (string) session()->get('role');
    }

    /** Data user yang sedang login. */
    protected function me(): ?array
    {
        return current_user();
    }

    /** Halaman 404 standar. */
    protected function notFound(string $msg = 'Halaman tidak ditemukan.')
    {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($msg);
    }

    /** Ubah "1,5" / "25.000" menjadi angka yang bisa divalidasi. */
    protected function number(?string $v): string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return '';
        }
        // 25.000 -> 25000 ; 1,5 -> 1.5
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $v)) {
            return str_replace('.', '', $v);
        }

        return str_replace(',', '.', $v);
    }
}
