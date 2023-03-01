<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class syncController extends Controller
{
    //TODO .... generate policy no
    public function generate_policyno($plan_code){
        $policy_no = null;
        //get the policy_serial
        $qry = DB::table('planinfo as p')
                ->select('p.policy_serial','p.PlanOldName')
                ->where(array('p.plan_code' => $plan_code));
        $results = $qry->first();
        //generate policy no
        $policy_no = $results->PlanOldName.'-'.date("Y").'-'.str_pad($results->policy_serial, 5, 0, STR_PAD_LEFT);
        return $policy_no;
    }
    //
    public function synProposal(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                $record_id = $request->input('ID');
                //Handle the dates here
                $last_consult = $request->input('last_consult');
                if(isset($last_consult) && ($last_consult == "null" || $last_consult =="NaN-NaN-NaN")){
                    $last_consult = null;
                }
                $deduction_date = $request->input('deduction_date');
                if(isset($deduction_date) && $deduction_date == "null"){
                    $deduction_date = null;
                }
                $start_drinking = $request->input('start_drinking');
                if(isset($start_drinking) && ($start_drinking == "null"  || $start_drinking =="NaN-NaN-NaN")){
                    $start_drinking = null;
                }
                $start_smoking = $request->input('start_smoking');
                if(isset($start_smoking) && ($start_smoking == "null" || $start_smoking =="NaN-NaN-NaN")){
                    $start_smoking = null;
                }

                $DateFrom = $request->input('DateFrom');
                if(isset($DateFrom) && ($DateFrom == "null"  || $DateFrom =="NaN-NaN-NaN")){
                    $DateFrom = null;
                }
                $DateTo = $request->input('DateTo');
                if(isset($DateTo) && ($DateTo == "null" || $DateTo =="NaN-NaN-NaN")){
                    $DateTo = null;
                }

                $plan_code = $request->input('plan_code');
                $is_topup = $request->input('is_top_up');
                if(!isset($is_topup) || $is_topup == false){
                    $proposal_no = $this->generate_policyno($plan_code);
                } 
                $add_edwa_nkosuo = $request->input('add_edwa_nkosuo');
                $edwa_nkoso_premium = $request->input('edwa_nkoso_premium');

                $table_data = array(
                    'mobile_id' => $request->input('mobile_id'),
                    'surname' => $request->input('surname'),
                    'other_name' => $request->input('other_name'),
                    'employer' => $request->input('employer_code'),
                    //'paysource_br' => $request->input('FirstName'),
                    //'paysource_br_code' => $request->input('paysource_br_code'),
                    //'sort_code' => $request->input('sort_code'),
                    'email' => $request->input('email'),
                    //'tel_no' => $request->input('tel_no'),
                    'mobile' => $request->input('mobile'),
                    'marital_status' => $request->input('marital_status_code'),
                    'gender' => $request->input('gender_code'),
                    //'plan_code' => $plan_code,
                    'good_health' => (bool)$request->input('good_health'),
                    'health_condition' => $request->input('health_condition'),
                    'business_name' => $request->input('business_name'),
                    //'address_type' => $request->input('FirstName'),
                    'country' => $request->input('country_code'),
                    'city' => $request->input('city'),
                    'region' => $request->input('region'),
                    'occupation' => $request->input('occupation_code'),
                    'hobbies_pastimes' => $request->input('hobbies_pastimes'),
                    'client_class_code' => $request->input('client_class_code'),
                    //'second_class_code' => $request->input('FirstName'),
                    'Dob' => $request->input('dob'),
                    'anb' => $request->input('anb'),
                    'home_town' => $request->input('home_town'),
                    'business_location' => $request->input('business_location'),
                    'sig' => "id three.jpg",//'$request->input('FirstName'),
                    'id_file' => "1444765676_sig_m2gGQ1.png",
                    //'pregnant' => $request->input('FirstName'),
                    'name_doctor' => $request->input('name_doctor'),
                    'smoke_pol' => (bool)$request->input('smoke_pol'),
                    'cigarettes_day' => $request->input('cigarettes_day'),
                    'date_start_smoking' => $start_smoking,
                    'alcohol_pol' => (bool)$request->input('alcohol_pol'),
                    'average_alcohol' => $request->input('average_alcohol'),
                    'pop_height' => $request->input('pop_height'),
                    'pop_weight' => $request->input('pop_weight'),

                    'pay_code' => $request->input('pay_method_code'),
                    'bank_code' => $request->input('bank_code'),
                    'bank_account_no' => $request->input('bank_account_no'),
                    'bank_branch' => $request->input('bank_branch'),
                    //'bank_account_name' => $request->input('bank_account_name'),
                    'life_assuarance' => (bool)$request->input('life_assuarance'),
                    'previousClaimCheck' => (bool)$request->input('previousClaimCheck'),
                    'existing_pol_no' => $request->input('existing_pol_no'),
                    'claim_pol_no' => $request->input('claim_pol_no'),
                    'protection' => $request->input('protection'),
                    'investment' => $request->input('investment'),
                    'bo_inc' => (bool)$request->input('bo_inc'),
                    'percentage_increase' => $request->input('percentage_increase'),
                    'anidaso_pol' => (bool)$request->input('anidaso_pol'),
                    'anidaso_premium_amount' => $request->input('anidaso_premium_amount'),

                    //'topup_policyno' => $request->input('topup_policyno'),
                    //'is_top_up' => (bool)$request->input('is_top_up'),
                    'term' => $request->input('term'),
                    'employee_no' => $request->input('employer_no'),
                    'paymode_code' => $request->input('paymode_code'),
                    'deduction_date' => Carbon::now(),
                    'Prem_rate' => $request->input('Prem_rate'),

                    'inv_premium' => $request->input('inv_premium'),
                    'basic_premium' => $request->input('basic_premium'),
                    'modal_premium' => $request->input('modal_premium'),
                    'rider_premium' => $request->input('rider_premium'),
                    'annual_premium' => $request->input('annual_premium'),
                    'total_premium' => $request->input('total_premium'),
                    'Sum_Assured' => $request->input('sum_assured'),
                    'pol_fee' => $request->input('pol_fee'),
                    'cepa' => $request->input('cepa'),
                    'tot_protection' => $request->input('tot_protection'),
                    'transfer_charge' => $request->input('transfer_charge'),

                    'second_l_name' => $request->input('second_l_name'),
                    'second_l_address' => $request->input('second_l_address'),
                    'second_gender_code' => $request->input('second_gender_code'),
                    'second_dob' => $request->input('second_dob'),
                    'second_age' => $request->input('second_age'),
                    //'agent_code' => $request->input('agent_code'),
                    //'second_agent_code' => $request->input('second_agent_code'),
                    //'employer_transfer_rate' => $request->input('employer_transfer_rate'),
                    'rpt_name' => $request->input('rpt_name'),
                    //'ach_file' => $request->input('ach_file'),
                    //'mandate_form' => $request->input('mandate_form'),
                    //'id_file' => $request->input('id_file'),
                    'proposal_date' => Carbon::now(),
                    //'propsalNoGenerated' => $request->input('propsalNoGenerated'),
                    'rpt_delivery_mode' => (bool)$request->input('doc_delivery_mode'),
                    'tin_no' => $request->input('tin_no'),
                    'edwa_nkoso_policy' => $request->input('edwa_nkoso_policy'),
                    'postal_address' => $request->input('postal_address'),
                    'residential_address' => $request->input('residential_address'),//IsPep
                    'Doyouhavesecondaryincome' => (bool)$request->input('Doyouhavesecondaryincome'),
                    'secondary_income' => $request->input('secondary_income'),
                    'IsPep' => $request->input('IsPep'),
                    'politicaly_affiliated_person' => $request->input('politicaly_affiliated_person'),
                    //'proposal_no' => $request->input('proposal_no'),
                    //'policy_no' => $request->input('policy_no'),

                    //'DiscAmt' => $request->input('DiscAmt'),
                    //'No_Of_Children' => $request->input('No_Of_Children'),
                    'Date_Last_Consult' => $last_consult,
                    'Date_Start_Drinking' => $start_drinking,
                    'Life_Premium' => $request->input('life_Premium'),

                    //'created_by' => "MPROPOSAL",
                    'Date_Saved' => Carbon::now(),
                    'date_synced' => Carbon::now(),
                    'proposal_no' => $proposal_no,
                    //'policy_no' => $proposal_no,
                    'plan_code' => $request->input('plan_code'),//DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code'),
                    'EntryCategory' => 1,
                    'InsuranceType' => $request->input('InsuranceType'),
                    'agent_code' => DbHelper::getColumnValue('agents_info', 'agent_no',$request->input('agent_code'),'id'),

                    //telco,momo_no
                    'momo_no' => $request->input('momo_no'),
                    'id_type' => $request->input('id_type'),
                    'IdNumber' => $request->input('IdNumber'),
                    'title' => $request->input('title'),
                    'MobileSecondary' => $request->input('MobileSecondary'),

                    'GuarantorBank' => $request->input('GuarantorBank'),
                    'currency' => $request->input('currency'),
                    'DateFrom' => $DateFrom,
                    'DateTo' => $DateTo,
                    'DurationDays' => $request->input('DurationDays'),
                    'CostOfProperty' => $request->input('CostOfProperty'),

                    'ClaimDefaultPay_method' => $request->input('ClaimDefaultPay_method'),
                    'ClaimDefaultTelcoCompany' => $request->input('ClaimDefaultTelcoCompany'),
                    'ClaimDefaultMobileWallet' => $request->input('ClaimDefaultMobileWallet'),
                    'ClaimDefaultEFTBank_code' => $request->input('ClaimDefaultEFTBank_code'),
                    //'ClaimDefaultEFTBankBranchCode' => $request->input('ClaimDefaultEFTBankBranchCode'),
                    'ClaimDefaultEFTBank_account' => $request->input('ClaimDefaultEFTBank_account'),
                    'ClaimDefaultEftBankaccountName' => $request->input('ClaimDefaultEftBankaccountName')
                );

                if($record_id > 0){
                    //update
                    DB::connection('sqlsrv')->table('mob_prop_info')
                    ->where(array(
                        "ID" => $record_id
                    ))
                    ->update($table_data);

                }else{
                    //insert
                    $record_id = DB::connection('sqlsrv')->table('mob_prop_info')->insertGetId($table_data);
                    //fsadfl
                    //get the policy_serial
                    $qry = DB::table('planinfo as p')
                            ->select('p.policy_serial')
                            ->where(array('p.plan_code' => $plan_code));
                    $results = $qry->first();
                    //generate policy no
                    $policy_serial = $results->policy_serial;
                    //update policy serial here
                    DB::connection('sqlsrv')->table('planinfo')
                        ->where(array(
                            "plan_code" => $plan_code
                        ))->update(array('policy_serial'=>$policy_serial+1));

                    //just check if add edwa nkosuo is 1 and if edwa nkosuo premim has value
                    if((isset($add_edwa_nkosuo) && (float)$add_edwa_nkosuo > 0) && 
                    (isset($edwa_nkoso_premium) && (float)$edwa_nkoso_premium > 0)){
                        //insert again
                        $edwa_proposal_no = $this->generate_policyno("18");
                        $table_data['proposal_no'] = $edwa_proposal_no;
                        $table_data['policy_no'] = $edwa_proposal_no;//PlanID,plan_code,paymode_code
                        $table_data['PlanID'] = "13";
                        $table_data['plan_code'] = "18";
                        $table_data['paymode_code'] = 98;

                        $table_data['inv_premium'] = null;
                        $table_data['basic_premium'] = null;
                        $table_data['modal_premium'] = $edwa_nkoso_premium;
                        $table_data['rider_premium'] = null;
                        $table_data['total_premium'] = null;
                        $table_data['Sum_Assured'] = null;

                        DB::connection('sqlsrv')->table('mob_prop_info')->insertGetId($table_data);
                        $qry = DB::table('planinfo as p')
                            ->select('p.policy_serial')
                            ->where(array('p.PlanOldName' => "18"));
                        $results = $qry->first();
                        //generate policy no
                        $policy_serial = $results->policy_serial;
                        //update policy serial here
                        DB::connection('sqlsrv')->table('planinfo')
                            ->where(array(
                                "PlanOldName" => "18"
                            ))->update(array('policy_serial'=>$policy_serial+1));
                        }
                }
                

                //insert into the respective tables
                //rider
                $rider_array = array();
                $rider_arr = json_decode($request->input('riders'));
                if(isset($rider_arr)){
                    DB::connection('sqlsrv')->table('mob_rider_info')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($rider_arr);$i++){
                        $rider_array[$i]['prop_id'] = $record_id;
                        $rider_array[$i]['rider'] = $rider_arr[$i]->r_rider;
                        $rider_array[$i]['sa'] = $rider_arr[$i]->r_sa;
                        $rider_array[$i]['premium'] = $rider_arr[$i]->r_premium;
                        //delete then insert
                        $rider_id = DB::connection('sqlsrv')->table('mob_rider_info')->insertGetId($rider_array[$i]);
                    }
                }
                
                
                //dependants
                $dependants_array = array();
                $dependants_arr = json_decode($request->input('dependants'));
                if(isset($dependants_arr)){
                    DB::connection('sqlsrv')->table('mob_funeralmembers')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($dependants_arr);$i++){
                        $dependants_array[$i]['prop_id'] = $record_id;
                        $dependants_array[$i]['names'] = $dependants_arr[$i]->dp_fullname;
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
                        $dependants_array_id = DB::connection('sqlsrv')->table('mob_funeralmembers')->insertGetId($dependants_array[$i]);
                    }
                }
                

                //beneficiaries
                $beneficiaries_array = array();
                
                $beneficiaries_embb = $request->input('beneficiaries_embb');
                $beneficiaries_arr = json_decode($request->input('beneficiaries'));
                if(isset($beneficiaries_embb)){
                    DB::connection('sqlsrv')->table('mob_beneficiary_info')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($beneficiaries_embb);$i++){
                        $beneficiaries_array[$i]['prop_id'] = $record_id;
                        $beneficiaries_array[$i]['Names'] = $beneficiaries_embb[$i]['b_name'];
                        $beneficiaries_array[$i]['relationship'] = $beneficiaries_embb[$i]['b_relationship'];
                        $beneficiaries_array[$i]['birth_date'] = $beneficiaries_embb[$i]['b_dob'];
                        if($beneficiaries_array[$i]['birth_date'] == "null"){
                            $beneficiaries_array[$i]['birth_date'] = null;
                        }
                        $beneficiaries_array[$i]['perc_alloc'] = $beneficiaries_embb[$i]['b_percentage_allocated'];
                        $beneficiaries_array[$i]['telephone'] = $beneficiaries_embb[$i]['b_mobile_no'];
                            
                        $beneficiaries_id = DB::connection('sqlsrv')->table('mob_beneficiary_info')->insertGetId($beneficiaries_array[$i]);
                    }
                }
                
                if(isset($beneficiaries_arr)){
                    DB::connection('sqlsrv')->table('mob_beneficiary_info')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($beneficiaries_arr);$i++){
                        $beneficiaries_array[$i]['prop_id'] = $record_id;
                        $beneficiaries_array[$i]['Names'] = $beneficiaries_arr[$i]->b_name;
                        $beneficiaries_array[$i]['relationship'] = $beneficiaries_arr[$i]->b_relationship;
                        $beneficiaries_array[$i]['birth_date'] = $beneficiaries_arr[$i]->b_dob;
                        if($beneficiaries_array[$i]['birth_date'] == "null"){
                            $beneficiaries_array[$i]['birth_date'] = null;
                        }
                        $beneficiaries_array[$i]['perc_alloc'] = $beneficiaries_arr[$i]->b_percentage_allocated;
                        $beneficiaries_array[$i]['telephone'] = $beneficiaries_arr[$i]->b_mobile_no;
                        
                        $beneficiaries_id = DB::connection('sqlsrv')->table('mob_beneficiary_info')->insertGetId($beneficiaries_array[$i]);
                    }
                }
                

                //family health
                $family_health_array = array();
                $family_health_arr = json_decode($request->input('family_health'));
                if(isset($family_health_arr)){
                    DB::connection('sqlsrv')->table('mob_family_healthinfo')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($family_health_arr);$i++){
                        $family_health_array[$i]['prop_id'] = $record_id;
                        $family_health_array[$i]['family'] = $family_health_arr[$i]->fh_family;
                        $family_health_array[$i]['state'] = $family_health_arr[$i]->fh_state;
                        $family_health_array[$i]['age'] = $family_health_arr[$i]->fh_age;
                        $family_health_array[$i]['state_health'] = $family_health_arr[$i]->fh_state_health;
                        $family_health_id = DB::connection('sqlsrv')->table('mob_family_healthinfo')->insertGetId($family_health_array[$i]);
                    }
                }
                

                //health history
                $health_history_array = array();
                $health_history_arr = json_decode($request->input('family_history'));
                if(isset($health_history_arr)){
                    //DB::connection('sqlsrv')->table('mob_intermediary_family_disease')->where('prop_id', '=', $record_id)->delete();
                    $sql_query="DELETE FROM mob_intermediary_family_disease  WHERE prop_id='$record_id'";
                    DB::connection('sqlsrv')->raw($sql_query);
                    for($i=0;$i<sizeof($health_history_arr);$i++){
                        $health_history_array[$i]['prop_id'] = $record_id;
                        if(!empty($health_history_arr[$i]->hi_disease_id)){
                            $health_history_array[$i]['disease_id'] = $health_history_arr[$i]->hi_disease_id;
                            $health_history_array[$i]['disease_injury'] = $health_history_arr[$i]->hi_disease_injury;
                            $health_history_array[$i]['disease_date'] = $health_history_arr[$i]->hi_disease_date;
                            if($health_history_array[$i]['disease_date'] == "null"){
                                $health_history_array[$i]['disease_date'] = null;
                            }
                            $health_history_array[$i]['disease_duration'] = $health_history_arr[$i]->hi_disease_duration;
                            $health_history_array[$i]['disease_result'] = $health_history_arr[$i]->hi_disease_result;
                            $health_history_array[$i]['disease_doc'] = $health_history_arr[$i]->hi_disease_doc;
                            $health_history_id = DB::connection('sqlsrv')->table('mob_intermediary_family_disease')->insertGetId($health_history_array[$i]);
                        }
                    }
                }

                //Questionnaire
                /*$family_health_array = array();
                $qn_arr = json_decode($request->input('qn_intermediary'));
                if(isset($qn_arr)){
                    DB::connection('sqlsrv')->table('mob_intermediary_health_info')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($qn_arr);$i++){
                        $family_health_array[$i]['prop_id'] = $record_id;
                        $family_health_array[$i]['health_id'] = $qn_arr[$i]->qn_health_id;
                        $family_health_id = DB::connection('sqlsrv')->table('mob_intermediary_health_info')->insertGetId($qn_arr[$i]);
                    }
                }*/

                $health_checklist_array = array();
                $qn_intermediary_arr = json_decode($request->input('qn'));
                if(isset($qn_intermediary_arr)){
                    DB::connection('sqlsrv')->table('mob_health_intermediary')->where('prop_id', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($qn_intermediary_arr);$i++){
                        $health_checklist_array[$i]['prop_id'] = $record_id;
                        //$health_checklist_array[$i]['fam_inter_id'] = $qn_intermediary_arr[$i]->hi_fam_inter_id;
                        if(!empty($qn_intermediary_arr[$i]->hi_disease_id)){
                            //check if checklist index isset and is greater than 0
                            if(isset($qn_intermediary_arr[$i]->checklist_index) && (int)$qn_intermediary_arr[$i]->checklist_index > 0){
                                $health_checklist_array[$i]['disease_id'] = $qn_intermediary_arr[$i]->checklist_index;
                            }else{
                                $health_checklist_array[$i]['disease_id'] = $qn_intermediary_arr[$i]->hi_disease_id;
                            }
                            $health_checklist_array[$i]['disease_injury'] = $qn_intermediary_arr[$i]->hi_disease_injury;
                            $health_checklist_array[$i]['disease_date'] = $qn_intermediary_arr[$i]->hi_disease_date;
                            if($health_checklist_array[$i]['disease_date'] == "null"){
                                $health_checklist_array[$i]['disease_date'] = null;
                            }
                            $health_checklist_array[$i]['disease_duration'] = $qn_intermediary_arr[$i]->hi_disease_duration;
                            $health_checklist_array[$i]['disease_result'] = $qn_intermediary_arr[$i]->hi_disease_result;
                            $health_checklist_array[$i]['disease_doc'] = $qn_intermediary_arr[$i]->hi_disease_doc;
                            $health_checklist_id = DB::connection('sqlsrv')->table('mob_health_intermediary')->insertGetId($health_checklist_array[$i]);
                        }
                    }
                }
                
//
                $this->syncImage($request,$record_id,$proposal_no);

                //health questionnaire
                $res = array(
                    'success' => true,
                    'record_id' => $record_id,
                    'policy_no' => $proposal_no,//$rider_id,
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
    //Image Sync
    public function syncImage(Request $request,$record_id,$proposal_no)
    {
        try{
            //for base64
            //$proposal_no = '29-2023-00056'; 
            //$record_id = 18;
            
            $photo = $request->file('photo');
            $id_front = $request->file('id_front');
            $id_back = $request->file('id_back');
            $medical_rpt = $request->file('medical_rpt');
            $signature = $request->input('signature');
            $category_id = 1;
            $policy_no = $proposal_no;//;
            $proposal_id = $record_id;
            $fileName = "signature.png";

            if(isset($photo)) $this->savePhysicalFile($photo,$category_id,$policy_no,$proposal_id,true);
            if(isset($id_front)) $this->savePhysicalFile($id_front,$category_id,$policy_no,$proposal_id);
            if(isset($id_back)) $this->savePhysicalFile($id_back,$category_id,$policy_no,$proposal_id);
            if(isset($medical_rpt)) $this->savePhysicalFile($medical_rpt,$category_id,$policy_no,$proposal_id);
            if(isset($signature)) $this->saveStringFile($signature,$category_id,$policy_no,$proposal_id,$fileName);

            

            $res = array(
                'success' => true
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

    public function savePhysicalFile($file,$category_id,$policy_no,$proposal_id,$is_photo=false){
        $fileName = $file->getClientOriginalName();
        //Display File Extension
        $file->getClientOriginalExtension();
        //Display File Real Path
        $file->getRealPath();
        //Display File Size
        $file_size = $file->getSize();
        //Display File Mime Type
        $file->getMimeType();
        //Move Uploaded File
        //FileCategoriesStore
        $destinationPath = 'C:\Users\kgach\Documents\SmartLife\PolicyDocuments';
        //$destinationPath = DbHelper::getColumnValue('FileCategoriesStore', 'ID',1,'FileStoreLocationPath'),
        $file->move($destinationPath,$file->getClientOriginalName());
        $uuid = Uuid::uuid4();
        $uuid = $uuid->toString();

        //insert into mob_proposalFileAttachment
        $table_data = array(
            'created_on' => Carbon::now(),
            'MobileProposal' => $proposal_id,
            'DocumentType' => $category_id,
            'File' => $uuid,
            'Description' => $fileName,
        );
        $record_id = DB::connection('sqlsrv')->table('mob_proposalFileAttachment')->insertGetId($table_data);
        //insert into Mob_ProposalStoreObject
        $table_data = array(
            'Oid' => $uuid,
            'mobpolno' => $policy_no,
            'FileName' => $fileName,
            'MobProposal' => $proposal_id,
            'Size' => $file_size,
        );
        $record_id = DB::connection('sqlsrv')->table('Mob_ProposalStoreObject')->insertGetId($table_data);
        if($is_photo){
            //insert photo 
            //$image_path = $destinationPath."\\".$fileName;
            //echo $image_binary = file_get_contents($image_path);
            //exit();
            //$image_base64 = base64_encode($image_binary);
//echo $image_varbinary = bin2hex($image_binary);

            //$base64 = 'YWJjMTIzIT8kKiYoKSctPUB+';
            //$varbinary = $this->base64ToVarbinary($base64);

            //$image_binary = mb_convert_encoding($image_binary, 'UTF-8', 'auto');
            //exit();
            //$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            //$image_varbinary = bin2hex($image_binary);
            /*$table_data = array(
                'PassportPhoto' => $image_base64,
            );
            DB::connection('sqlsrv')->table('mob_prop_info')
                ->where(array(
                    "id" => 18
                ))->update($table_data);*/
            /*$binary_char = "89504e470d0a1a0a0000000d494844520000015e000000540803000000fd19affa00000300504c54450000006f6f6f0158af0059b0055bb1095eb2165fb30f63b31364b61a66b6196ab71e6db9286eb92973bc3176bd377bc03d81c34485c54e80c34e8bc7558bc85a94cd5894d06c9acf77a5d5ea0000ea0101e80404ea0505ea0606ec0000ed0505eb0909eb0a0aeb0c0ceb0e0eee0a0aee0c0ceb1111eb1414ec1313ee1414ec1c1cf00808f21a1aed2525ed2828ee2d2dee2e2eee3232ee3535ef3c3cf22929f23333ef4040f04141f04545f64747f04a4af25151f15454f65656f15959f25c5cf25f5ff75f5ff26161f36363f26565f56767f06669f36868f36b6bf36c6cf36f6ff86d6df37171f47474f47676f47777f47979f47b7bf47d7df87d7d8787879292929f9f9f84aad788b2da88b3de8bb4dd8fb2da96b6dc91b8df9abbde9fbcdf95bbe09bc0e4a2c3e5a6c9ebabc2e6aec1e3aec2e4adc3e5adc3e6afc5e3adc4e6adc4e7accaecb1c5e7b4c6e7b1c7e8b1c9eab4c8e9b8cbe9bacceab5d1ecbbd3eab8d0edbbd5efbed1eab4d1f1b6d9f8bcdaf6bde3fbf58383f68687f68e8ef39396f69191f69696f79999f79b9bf79d9dfc9797fa9999f7a0a0f7a3a3faa3a3f8a9a9f8aaaaf8ababf8adadf8afaff4b3b5f9b1b1f9b3b3f9b7b7fdb0b0f9babaccccccdbdbdbc2d2ecc5d4eecad6efcbd9efc5dbf1c0dffacbdcf1d0dcf1c5e0f9c9e4fbcbecfed5e2f3d1ebfddbe4f4dce8f5dbedfcd7f3ffdff6fed9fafff7c1c3f4cbcffac0c0fac5c5fdc6c6fbcacafbccccfecacafad1d2fbd4d4ffd3d3ffd4d4fcd5d5fcd7d7fadedffdd9d9fcddddededede1e9f6e5e9f4e4ecf6e0ecf8e7eef8e9eff7e6f5ffe6f9ffe9f0f8ebf6feecf3faeff5faedf6fdfae5e7fce0e0ffe1e1fde2e2ffe3e3fee7e7f9edeffee9e9fdececfeededfdeeeefceff0f1f5fbf3f6fbf4f5f9f2f9fef2fcfff5f8fcfef1f1fef4f4fdf6f7f9f9f9f9fbfdfbfdfefef8f8fff9f9fefafbfcfbfcfefcfdfcfdfefffefefdfeff000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000e5e3c0e70000000174524e530040e6d86600000001624b47440088051d48000000097048597300000ec300000ec301c76fa8640000000774494d4507df0a1a062d37c6b228520000070a4944415478daed9cff5f137518c01fbf956986a6a5e38b22aba9531883d02986362d84266c0849dfb44c83965f3115532140130324314da5441da056a4288541a45684865628ba14b1c1e6bcfd17edee73b2ddbe7170488c9ef76fecb6dde7f37e3dbb7b9ee7f33900100441100441100441100441bc97685f7f1bbe0b5148cf325f648f0a85f42c311cbd7128a46759807a512fea45502fea45bd08ea45bda81741bd0f9d3bd53556aa6aa14dcfd062b9df70c94ac3250b503555d52ea9aabae3426f65fe1e1b79e7e86f6fac6df448ed35fa4d0505bb79505058ddfb7afe494adc2fe0e3edeae0102bc18ba0743d439961b1cf482b3eaf019c9205bb4196e7acd73c2f486c636286199a17ca64a11e095103d42a82f8210eb31ff98641346ea6c53d48fe726494c7a3ec87939212137fecbedeb31246ced46f6e6d4ed15a5977be7c08f3ed83df017851e4961c67bd5414e78504807d01a2ce980d902fe24b50efeb4d4c1af0cae7ddb64ba9c9c067c289f769bb29252de3d8931be03ba9db8906ee70a1770ee7856d00057e9d1a9b0390e7cf57efe4ded7bb7f40e280ee076f233138a1c8b4f55d5aef9a3afde324789701c4fb0ad4bbdb9f97de803eac1740c0a501b2c8b823e0ab15f4b56179897e29f9f227f47045e67ea281dbbd4f6fb2034b3c1e4dee81fb62ab9c19f6f8ede6ad2be8e05d55074f10bdaf027cec61da0139dea71778bdb727d9412e8ed21bbfaea28337e550c37b0399930dbf004d3344a8571026d6e022cb5112bc65f02409de71d61bba3fea15c63e92954df9fdf25ada6eeac1f3c54399733daa83d6284f130dc846bd9d61569251cfb39432c1bba618c692e07dca006726a15e619c2659596045cb7adaae767db9ee31e654433e002a56847a85a121838e3495332585b6181693e01ddd023512cf7ab3506f275c95b12585e5c31452525cb29514199e27eaef42af7936b728a6f8e88decb77ab3485116613ab98609de83862524787deaa1298413ab5119191a69a717875dd12a1bd17b1da357ac502ae7725146653bea9566e66477909329f556bd4de1a4a4f808b632c1ab2dd3fb30271a4897149c7a38b811a85699c8d7862bbd00947d37031cf586d5189d683739ea955fb71f64b3dc5bf51691213f77e397b5b4ded4f4fa7412bc23cac118c651b780ee5c66c52774a0569f76a1d7d87ad746abc951affcaaeb8170f5cab87a655eaad7184982570347c98dadcc309ae81d0bf05990fdac2495bc562ba89c28bbdf7d54a193de465e7aaff50bbd156452536a6f6da67b65a9697a1d69f40e3d02eddc9b5414c54baf3992f38286fa3feb9d4b82f72528658277b9ce3296b41b9eb6aae794147e45d614393656654fdce9ae27667d5b6fcf36ccce1183cf9ebdbd910ede94b49b3f3f424a8a4d00d11c4fe1cdce15b2df76efd3eb40b2e76eb030bdec2ac52c38ceb4d1579598d99262640b7c1fecb8ee53e1586404e4a25e4fab14c4e0f802c316466f5a590b2929062d036a1b4753c85530ce776aa7a35e4f64928947dcbdc8d6c32d4bc98dcda701ae847234a94d503909f576697303dbe8dd69f992e995add399479192e24d804f389624df824925f2f3f7f37abd0ef7ae0d0f2f73c80fa447eb3bedafcb5a520feb570f26ab143ab8abe0f65cdac1589893bb2b6bb2b7ebedb5c4ec0e5b5224c051b2b9a1d832865da56883224e3d2cdef3e033d25ed2ebfd555b859819edb4b37f6f668237adfe3059a518f60598b8c5c18ceb46a3d164a549b0de306e514c3138e90d6d32d95a12a6a650efd36b5691d1be00c7981b5b6a89e5c12a451b9ce1e660d2590ac2f440a17aa5dbb233ed48506b349af83c47bd62c52c3b1462efd35b4d0271ead7f748a377f5c53a76956225c0cbbc3aafddd1eb6e13547febf7b279ed4cf30966f93db5d890ccee5bb90535d2ded5dbff562bae9156f9d44fdbb6b01b9f6eb2ab146f032cf245bd02f78e90b421c27452cbee1d5946b2b2110df0a75c847a8565651dab14479892425b073e1d75cc4e3fd42b8cdd6436d39a7f5b4b82f7623aa987879743f30c11ea1596954d27438d371f231b9f743066e0838d4f7b8250af300a49fefacc0fb7d7d1496fca817a1ddbe83d0c46a508f50a0b5eb6ca7c1e4ad9462fd84a8a5312d42bb01e2689d7840ac3a614b2f1e93cc9ca866c04a34ae4757a9d763d6fe8a1c5a08eddd55d229c5da5b8475629b487eebf4eaebca31ae08af4bfd12be4d90a376ddcaeeb75fb6c4057b84e82777c2eb5454b1abdf5ec76f4b780cae06d57e4bfc3f97fe9383c19443fba22e2a337df97ef5983fabade04f6598aa69f56327a0f18de609fa5b8007fc8027923b6ea8d91d8a306b37222e7b9360a2a3bff39cc0668548879112491f771bd6685441a363d5c9e01c70fd0a497c1e26123ac0c1f67819a7973f932475961fde9abe2e23b58580454ae32c6c6fcbd14b46a42641e090d8eb30e6a6fec82181e44abf2fa7af422088220088220088220088220088220dec3bf03e1cd9e2aeb37e60000000049454e44ae426082";
            $sql = "UPDATE mob_prop_info SET ClientPassportPhoto=CONVERT(VARBINARY, '$binary_char') WHERE id=18";
            DB::connection('sqlsrv')->raw($sql);*/
            //DbHelper::getTableRawData($sql);
            //exit();
        }
    }

    function base64ToVarbinary($base64) {
        $binary = base64_decode($base64);
        return bin2hex($binary);
    }

    public function saveStringFile($file,$category_id,$policy_no,$proposal_id,$fileName){
        //$destinationPath = DbHelper::getColumnValue('FileCategoriesStore', 'ID',1,'FileStoreLocationPath'),
        file_put_contents('C:\Users\kgach\Documents\SmartLife\PolicyDocuments\signature.png', base64_decode($file));
        //insert into mob_proposalFileAttachment
        $uuid = Uuid::uuid4();
        $uuid = $uuid->toString();
        $table_data = array(
            'created_on' => Carbon::now(),
            'MobileProposal' => $proposal_id,
            'DocumentType' => $category_id,
            'File' => $uuid,
            'Description' => $fileName,
        );
        $record_id = DB::connection('sqlsrv')->table('mob_proposalFileAttachment')->insertGetId($table_data);
        //insert into Mob_ProposalStoreObject
        $table_data = array(
            'Oid' => $uuid,
            'mobpolno' => $policy_no,
            'FileName' => $fileName,
            'MobProposal' => $proposal_id,
            'Size' => 1480,
        );
        $record_id = DB::connection('sqlsrv')->table('Mob_ProposalStoreObject')->insertGetId($table_data);
    }


}
