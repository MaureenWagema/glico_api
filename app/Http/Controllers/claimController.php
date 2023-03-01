<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;

class claimController extends Controller
{
    //claims entries
    public function insertClaimEntries(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                $mobile_id = $request->input('mobile_id');
                //$policy_no = $request->input('policy_no');
                $PolicyId = $request->input('PolicyId');//DbHelper::getColumnValue('polinfo', 'policy_no',$policy_no,'id');
                $claim_type = $request->input('claim_type');
                $PartialWithdPurpose = $request->input('PartialWithdPurpose');
                $CurrentCashValue = $request->input('CurrentCashValue');
                $PreviousloanAmount = $request->input('PreviousloanAmount');
                $AmountAppliedFor = $request->input('AmountAppliedFor');
                //get client_name
                $client_no = DbHelper::getColumnValue('polinfo', 'id',$PolicyId,'client_number');
                //use clientId to fetch client_name
                $ClientName = DbHelper::getColumnValue('clientinfo', 'client_number',$client_no,'name');


                $table_data = array(
                    'mobile_id'=>'',
                    'HasBeenPicked'=>0,
                    'created_on'=>Carbon::now(),
                    'RequestDate'=>Carbon::now(),
                    'statuscode'=>13,
                    'claim_type'=>$claim_type,
                    'PolicyId'=>$PolicyId,
                    'PartialWithdPurpose'=>$PartialWithdPurpose,
                    'CurrentCashValue'=>$CurrentCashValue,
                    'PreviousloanAmount'=>$PreviousloanAmount,
                    'AmountAppliedFor'=>$AmountAppliedFor,
                    'ClientName' => $ClientName
                );

                //insert into 
                $record_id = DB::connection('sqlsrv')->table('eClaimsEntries')->insertGetId($table_data);

                //health questionnaire
                $res = array(
                    'success' => true,
                    'claim_id' => $record_id
                );
            },5);           
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

    //get claims details
    public function getClientClaims(Request $request)
    {
        try{
            $res=array();

            $agent_no = $request->input('client_no');

            $sql = "SELECT d.*,p.policy_no,e.description AS statuscode FROM eClaimsEntries d INNER JOIN polinfo p ON d.PolicyId=p.id INNER JOIN ClaimStatusInfo e ON e.id=d.statuscode";
            $Claims = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Claims' => $Claims
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

    //get claims details
    public function getClaimAttachments(Request $request)
    {
        try{
            $res=array();

            $claim_type = $request->input('claim_type');

            $sql = "SELECT p.id,d.description FROM claimtyperequirementinfo p INNER JOIN claim_requirement d  ON p.req_code=d.reg_code WHERE p.claim_type='$claim_type'";
            $Attachments = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Attachments' => $Attachments
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
