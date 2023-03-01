<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use App\Helpers\EmailHelper;
use Carbon\Carbon;

class quotationController extends Controller
{
    //
    //dob,anb,plan_code,term,sum_assured,pol_fee,inv_premium,
    //cepa,rider_premium,life_premium,basic_premium,modal_premium,
    //total_premium,quotation_date
    public function saveQuote(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                //get the record data
                $record_id = $request->input('record_id');
                $plan_code = $request->input('plan_code');
                $dob = $request->input('dob');
                if(isset($dob) && $dob == "null"){
                    $dob = null;
                }
                $table_data=array(
                    'dob' => $request->input('dob'),
                    'anb' => $request->input('anb'),
                    'plan_code' => $request->input('plan_code'),
                    'term' => $request->input('term'),
                    'sum_assured' => $request->input('sum_assured'),
                    'inv_premium' => $request->input('inv_premium'),
                    'basic_premium' => $request->input('basic_premium'),
                    'modal_premium' => $request->input('modal_premium'),
                    'rider_premium' => $request->input('rider_premium'),
                    'pol_fee' => $request->input('pol_fee'),
                    'cepa' => $request->input('cepa'),
                    'quotation_date' => Carbon::now(),
                    'client_no' => $request->input('client_no')
                );

                if($record_id > 0){
                    //update
                    DB::connection('sqlsrv')->table('Quotation')
                    ->where(array(
                        "id" => $record_id
                    ))->update($table_data);
                }else{
                    //insert
                    $record_id = DB::connection('sqlsrv')->table('Quotation')->insertGetId($table_data);                   
                }
                

                //insert into the respective tables
                //rider
                $rider_array = array();
                $rider_arr = json_decode($request->input('riders'));
                if(isset($rider_arr)){
                    DB::connection('sqlsrv')->table('quotation_rider_info')->where('quotation_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($rider_arr);$i++){
                        $rider_array[$i]['quotation_id'] = $record_id;
                        $rider_array[$i]['rider'] = $rider_arr[$i]->r_rider;
                        $rider_array[$i]['sa'] = $rider_arr[$i]->r_sa;
                        $rider_array[$i]['premium'] = $rider_arr[$i]->r_premium;
                        //delete then insert
                        $rider_id = DB::connection('sqlsrv')->table('quotation_rider_info')->insertGetId($rider_array[$i]);
                    }
                }
                
                
                //dependants
                $dependants_array = array();
                $dependants_arr = json_decode($request->input('dependants'));
                if(isset($dependants_arr)){
                    DB::connection('sqlsrv')->table('quotation_funeralmembers')->where('quotation_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($dependants_arr);$i++){
                        $dependants_array[$i]['quotation_id'] = $record_id;
                        //$dependants_array[$i]['names'] = $dependants_arr[$i]->dp_fullname;
                        $dependants_array[$i]['date_of_birth'] = $dependants_arr[$i]->dp_dob;
                        if($dependants_array[$i]['date_of_birth'] == "null"){
                            $dependants_array[$i]['date_of_birth'] = null;
                        }
                        $dependants_array[$i]['age'] = $dependants_arr[$i]->dp_anb;
                        $dependants_array[$i]['sa'] = $dependants_arr[$i]->dp_sa;
                        $dependants_array[$i]['premium'] = $dependants_arr[$i]->dp_premium;
                        $dependants_array[$i]['Relationship'] = $dependants_arr[$i]->dp_relationship;
                        //$dependants_array[$i]['class_code'] = $dependants_arr[$i]->dp_class_code;
                        //$dependants_array[$i]['bapackage'] = $dependants_arr[$i]->dp_bapackage;
                        //$dependants_array[$i]['Hci_sum'] = $dependants_arr[$i]->dp_hci_sum;
                        $dependants_array_id = DB::connection('sqlsrv')->table('quotation_funeralmembers')->insertGetId($dependants_array[$i]);
                    }
                }

                //GET NAME
                $name="AGYEKUM RICHARD";//DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code');
                //GET EMAIL
                $email="kevin.softclans1@mailinator.com";//DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code');

                $product = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'description');
                //send email
                //sendMailNotification($trader_name, $to,$subject,$email_content,$cc,$bcc,$attachement,$attachement_name,$template_id, $vars);
                $email_content = "
                <p>GLICO acknowledges your interest in ".$product." </p>
                <p>One of our Customer Sales Agents will get in touch with you shortly.</p>
                ";
                $msg = EmailHelper::sendMailNotification($name, $email,"GLICO QUOTATION",$email_content,'','','','','', '');
                //print_r($msg);
                //exit();
                
                //health questionnaire
                $res = array(
                    'success' => true,
                    'record_id' => $record_id,
                    'message' => 'Data Synced Successfully!!'
                );

            }, 5);
            
            
        }catch (\Exception $exception) {
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
