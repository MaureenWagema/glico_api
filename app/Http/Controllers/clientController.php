<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;

class clientController extends Controller
{
    //TODO...
    //1. Get client details
    public function getClientDetails(Request $request)
    {
        try{
            $client_no = $request->input('client_no');
            $mobile_no = $request->input('mobile_no');
            $is_micro = $request->input('is_micro');

            if(isset($mobile_no)){
                $sql = "SELECT p.Surname AS surname, p.OtherNames AS other_name,p.Address AS residential_address,p.Mobile AS mobile,
                p.Email AS email,p.BirthDate AS dob,p.Sex AS gender_code,p.Occupation AS occupation_code,p.Country AS country_code,
                p.Region AS region, p.MaritalStatus AS marital_status_code  FROM MicroClientInfo p 
                WHERE p.Mobile ='$mobile_no'";
            }else{
                $sql = "SELECT p.Surname AS surname, p.OtherNames AS other_name,p.Address AS residential_address,p.Mobile AS mobile,
                p.Email AS email,p.BirthDate AS dob,p.Sex AS gender_code,p.Occupation AS occupation_code,p.Country AS country_code,
                p.Region AS region, p.MaritalStatus AS marital_status_code  FROM MicroClientInfo p 
                WHERE p.ClientNumber ='$client_no'";
            }

            
            
            //$client_id = DbHelper::getColumnValue('clientinfo', 'client_number',$client_no,'id');
            // put in a transaction the whole process of syncing data...
            
            $Client = DbHelper::getTableRawData($sql);
            if(sizeof($Client) < 1){
                if(isset($mobile_no)){
                    $sql = "SELECT p.surname,p.other_name,p.address AS postal_address,p.Address2 AS residential_address,p.mobile,
                    p.birthdate AS dob,p.sex AS gender_code,p.marital_status AS marital_status_code,p.email,p.Country AS country_code,
                    p.occupation_code,p.occup_class AS client_class_code,p.pin_no as tin_no,p.Height as pop_height ,p.Weight as pop_weight   
                    FROM clientinfo p WHERE p.mobile='$mobile_no'";
                }else{
                    $sql = "SELECT p.surname,p.other_name,p.address AS postal_address,p.Address2 AS residential_address,p.mobile,
                    p.birthdate AS dob,p.sex AS gender_code,p.marital_status AS marital_status_code,p.email,p.Country AS country_code,
                    p.occupation_code,p.occup_class AS client_class_code,p.pin_no as tin_no,p.Height as pop_height ,p.Weight as pop_weight   
                    FROM clientinfo p WHERE p.client_number='$client_no'";
                    
                }
				$Client = DbHelper::getTableRawData($sql);
            }

            $res = array(
                'success' => true,
                'client_no' => $client_no,
                'Client' => $Client
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

    //get statements
    public function getClientPolicies(Request $request)
    {
        try{
            $client_no = $request->input('client_no');
            $mobile_no = $request->input('mobile_no');

            //$client_id = DbHelper::getColumnValue('clientinfo', 'client_number',$client_no,'id');
            $policy_no = $request->input('policy_no');
            if(isset($policy_no)){
                //get the client_no
                $client_no = DbHelper::getColumnValue('polinfo', 'policy_no',$policy_no,'client_number');
            }
            // put in a transaction the whole process of syncing data...
            if(isset($mobile_no)){
                $sql = "select t3.coverperiod,T1.*, T2.description, T2.investment_plan, T4.surname, T4.other_name, T4.mobile, T4.email,
                (DATEDIFF(month,t1.effective_date,GETDATE()) * t3.coverperiod * t1.modal_prem) AS expected_prem from polinfo T1 
                left join planinfo T2 on T1.plan_code = T2.plan_code left join paymentmodeinfo t3 on t1.plan_code = t3.plan_code and t1.pay_mode=t3.id 
                left join clientinfo T4 on T1.client_number = T4.client_number
                WHERE T4.mobile ='$mobile_no' ";
            }else{
                $sql = "select t3.coverperiod,T1.*, T2.description, T2.investment_plan, T4.surname, T4.other_name, T4.mobile, T4.email,
                (DATEDIFF(month,t1.effective_date,GETDATE()) * t3.coverperiod * t1.modal_prem) AS expected_prem from polinfo T1 
                left join planinfo T2 on T1.plan_code = T2.plan_code left join paymentmodeinfo t3 on t1.plan_code = t3.plan_code and t1.pay_mode=t3.id 
                left join clientinfo T4 on T1.client_number = T4.client_number
                WHERE T1.client_number ='$client_no' ";
            }
            $ClientPolicies = DbHelper::getTableRawData($sql);

            $res = array(
                'success' => true,
                'client_no' => $client_no,
                'ClientPolicies' => $ClientPolicies
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
    
    //client premiums
    public function getClientPremiums(Request $request)
    {
        try{
            $policy_no = $request->input('policy_no');
            // put in a transaction the whole process of syncing data... AND payment_status='P' AND received<>0
            $sql = "SELECT * FROM prmtransinfo WHERE policy_no ='".$policy_no."' ";
            $ClientPremiums = DbHelper::getTableRawData($sql);

            $res = array(
                'success' => true,
                'policy_no' => $policy_no,
                'ClientPremiums' => $ClientPremiums
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

    //client investments
    public function getClientInvestment(Request $request)
    {
        try{
            $policy_no = $request->input('policy_no');
            // put in a transaction the whole process of syncing data...
            $sql = "select t1.fund_year, t1.total_prem,t1.prem_allocated,t1.interest,t1.cacv,t1.amt_withdrawn from sipfundinfo t1 
            where t1.policy_no like '$policy_no'";
            $ClientInvestment = DbHelper::getTableRawData($sql);

            $res = array(
                'success' => true,
                'policy_no' => $policy_no,
                'ClientInvestment' => $ClientInvestment
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

    //client policies
    public function getClientnPolicies(Request $request)
    {
        try{
            $res=array();

            $agent_no = $request->input('agent_no');
            $agent_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
            // put in a transaction the whole process of syncing data...
            $sql = "SELECT * FROM MicroClientInfo p INNER JOIN MicroProposalInfo d ON p.Id=d.Client WHERE d.Agent='$agent_id'";
            $Clients = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM MicroProposalInfo d WHERE d.Agent='$agent_id'";
            $Policies = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'agent_no' => $agent_no,
                'Clients' => $Clients,
                'Policies' => $Policies
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
