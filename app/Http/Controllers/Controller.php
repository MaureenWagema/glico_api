<?php

namespace App\Http\Controllers;
//namespace App\Helpers\DbHelper;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $smartlife_db;

    public function __construct()
    {
        $this->smartlife_db = DB::connection('sqlsrv');//life
    }
}
