<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class reportsController extends Controller
{
    //Agents products within a timeline....
    public function getAgentProducts(Request $request)
    {
        try{
            $res=array();

            $agent_no = $request->input('agent_no');
            //$agent_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
            
            $sql = "SELECT * FROM MicroProposalInfo d WHERE d.Agent='$agent_id'";
            $Products = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Products' => $Products,
            );
        } catch (\Exception $exception) {
			$res = array(
                'success' => false,
                'message' => $exception->getMessage()
			);
            return response()->json($res);
		}catch (\Throwable $throwable) {
			$res = array(
                'success' => false,
                'message' => $throwable->getMessage()
			);
            return response()->json($res);
		}
        return response()->json($res);
    }

}
