<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;

class policyController extends Controller
{
    //get all data here..
    //complete form data..
    //beneficiaries, dependants, health_info, health_history, 
    public function getProposal(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                //get the record data
                $record_id = $request->input('record_id');

                //TODO get data from mob_prop_info...
                if(isset($record_id) && $record_id > 0){
                    $qry = DB::connection('sqlsrv')->table('mob_prop_info')
                    ->select('*')
                    ->where(array(
                        'ID' => $record_id
                    ));
                }else{
                    $qry = DB::connection('sqlsrv')->table('mob_prop_info')->select('*');
                }
                $row_arr = $qry->get();
                $organised_arr = array();

                foreach($row_arr as $results){
                    //print_r($results);
                    
                    $organised_arr[]=array(
                        'ID' => $results->ID,//
                        'isApproved' => $results->isApproved,
                        'mobile_id' => $results->mobile_id,
                        'surname' => $results->surname,
                        'other_name' => $results->other_name,
                        'name' => $results->surname." ".$results->other_name,
                        'employer_code' => $results->employer,
                        //'policy_no' => $results->policy_no,
                        //'paysource_br' => $request->input('FirstName,
                        //'paysource_br_code' => $results->paysource_br,
                        //'sort_code' => $results->sort_code,
                        'email' => $results->email,
                        //'tel_no' => $results->tel_no,
                        'mobile' => $results->mobile,
                        'marital_status_code' => $results->marital_status,
                        'gender_code' => $results->gender,
                        'plan_code' => $results->plan_code,
                        'good_health' => $results->good_health,
                        'health_condition' => $results->health_condition,
                        'business_name' => $results->business_name,
                        //'address_type' => $request->input('FirstName,
                        'country_code' => $results->country,
                        'city' => $results->city,
                        'region' => $results->region,
                        'occupation_code' => $results->occupation,
                        'hobbies_pastimes' => $results->hobbies_pastimes,
                        'client_class_code' => $results->client_class_code,
                        //'second_class_code' => $request->input('FirstName,
                        'dob' => $results->Dob,
                        'anb' => $results->anb,
                        'home_town' => $results->home_town,
                        'business_location' => $results->business_location,
                        //'sig' => $request->input('FirstName,
                        //'pregnant' => $request->input('FirstName,
                        'name_doctor' => $results->name_doctor,
                        'smoke_pol' => $results->smoke_pol,
                        'cigarettes_day' => $results->cigarettes_day,
                        'start_smoking' => $results->date_start_smoking,
                        'alcohol_pol' => $results->alcohol_pol,
                        'average_alcohol' => $results->average_alcohol,
                        'pop_height' => $results->pop_height,
                        'pop_weight' => $results->pop_weight,
    
                        'pay_method_code' => $results->pay_code,
                        'bank_code' => $results->bank_code,
                        'bank_branch' => $results->bank_branch,
                        'bank_account_no' => $results->bank_account_no,
                        //'bank_account_name' => $request->input('bank_account_name,
                        'life_assuarance' => $results->life_assuarance,
                        'existing_policy' => $results->existing_policy,
                        'existing_pol_no' => $results->existing_pol_no,
                        'claim_pol_no' => $results->claim_pol_no,
                        'protection' => $results->protection,
                        'investment' => $results->investment,
                        'bo_inc' => $results->bo_inc,
                        'percentage_increase' => $results->percentage_increase,
                        'anidaso_pol' => $results->anidaso_pol,
                        'anidaso_premium_amount' => $results->anidaso_premium_amount,
    
                        'topup_policyno' => $results->topup_policyno,
                        'is_top_up' => $results->is_top_up,
                        'term' => $results->term,
                        'employer_no' => $request->input('employee_no'),
                        'paymode_code' => $results->paymode_code,
                        'deduction_date' => $results->deduction_date,
                        //'Prem_rate' => $results->Prem_rate,
    
                        'inv_premium' => $results->inv_premium,
                        'basic_premium' => $results->basic_premium,
                        'modal_premium' => $results->modal_premium,
                        'rider_premium' => $results->rider_premium,
                        'annual_premium' => $results->annual_premium,
                        'total_premium' => $results->total_premium,
                        'pol_fee' => $results->pol_fee,
                        'cepa' => $results->cepa,
                        'tot_protection' => $results->tot_protection,
                        'transfer_charge' => $results->transfer_charge,
    
                        //'second_l_name' => $results->second_l_name,
                        //'second_l_address' => $results->second_l_address,
                        //'second_gender_code' => $results->second_gender_code,
                        //'agent_code' => $request->input('agent_code,
                        //'second_agent_code' => $request->input('second_agent_code,
                        //'employer_transfer_rate' => $request->input('employer_transfer_rate,
                        //'rpt_name' => $results->rpt_name,
                        //'ach_file' => $request->input('ach_file,
                        //'mandate_form' => $request->input('mandate_form,
                        //'id_file' => $request->input('id_file,
                        'proposal_date' => $results->proposal_date,
                        //'propsalNoGenerated' => $request->input('propsalNoGenerated,
                        'doc_delivery_mode' => $results->rpt_delivery_mode,
                        'tin_no' => $results->tin_no,
                        'edwa_nkoso_policy' => $results->edwa_nkoso_policy,
                        'postal_address' => $results->postal_address,
                        'residential_address' => $results->residential_address,
                        'Doyouhavesecondaryincome' => (bool)$results->Doyouhavesecondaryincome,
                        'secondary_income' => $results->secondary_income,
                        'IsPep' => (bool)$results->IsPep,
                        'politicaly_affiliated_person' => $results->politicaly_affiliated_person,
                        'policy_no' => $results->proposal_no,
                        //'policy_no' => $results->policy_no,
    
                        //'DiscAmt' => $request->input('DiscAmt,
                        //'No_Of_Children' => $request->input('No_Of_Children,
                        'last_consult' => $results->Date_Last_Consult,
                        'start_drinking' => $results->Date_Start_Drinking,
                        'life_premium' => $results->Life_Premium,
    
                        //'created_by' => "MPROPOSAL",
                        'Date_Saved' => $results->Date_Saved,
                        //'date_synced' => $results->date_synced
                        ////bank,bank_acc_no,telco,momo_no
                        //'bank' => $results->bank,
                        //'bank_acc_no' => $results->bank_acc_no,
                        //'telco' => $results->telco,
                        'momo_no' => $results->momo_no,
                        'id_type' => $results->id_type,
                        'IdNumber' => $results->IdNumber,
                        'title' => $results->title,
                        'MobileSecondary' => $results->MobileSecondary,
                        'InsuranceType' => $results->InsuranceType,

                        'GuarantorBank' => $results->GuarantorBank,
                        'currency' => $results->currency,
                        'DateFrom' => $results->DateFrom,
                        'DateTo' => $results->DateTo,
                        'DurationDays' => $results->DurationDays,
                        'CostOfProperty' => $results->CostOfProperty
                    );
                }

                $qry = DB::connection('sqlsrv')->table('mob_rider_info')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));
                $row_arr = $qry->get();
                $rider_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $rider_arr[$i]['r_rider'] = $row_arr[$i]->rider;
                    $rider_arr[$i]['r_sa'] = $row_arr[$i]->sa;
                    $rider_arr[$i]['r_premium'] = $row_arr[$i]->premium;
                }

                $qry = DB::connection('sqlsrv')->table('mob_funeralmembers')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));
                $row_arr = $qry->get();
                $dependants_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $dependants_arr[$i]['dp_fullname'] = $row_arr[$i]->names;
                    $dependants_arr[$i]['dp_dob'] = $row_arr[$i]->date_of_birth;
                    $dependants_arr[$i]['dp_anb'] = $row_arr[$i]->age;
                    $dependants_arr[$i]['dp_sa'] = $row_arr[$i]->sa;
                    $dependants_arr[$i]['dp_premium'] = $row_arr[$i]->premium;
                    $dependants_arr[$i]['dp_relationship'] = $row_arr[$i]->Relationship;
                    $dependants_arr[$i]['dp_class_code'] = $row_arr[$i]->class_code;
                    //$dependants_arr[$i]['dp_bapackage'] = $row_arr[$i]->bapackage;
                    $dependants_arr[$i]['dp_hci_sum'] = $row_arr[$i]->Hci_sum;
                }

                $qry = DB::connection('sqlsrv')->table('mob_beneficiary_info')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));
                $row_arr = $qry->get();
                $beneficiaries_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $beneficiaries_arr[$i]['b_name'] = $row_arr[$i]->Names;
                    $beneficiaries_arr[$i]['b_relationship'] = $row_arr[$i]->relationship;
                    $beneficiaries_arr[$i]['b_dob'] = $row_arr[$i]->birth_date;
                    $beneficiaries_arr[$i]['b_percentage_allocated'] = $row_arr[$i]->perc_alloc;
                    $beneficiaries_arr[$i]['b_mobile_no'] = $row_arr[$i]->telephone;
                }

                $qry = DB::connection('sqlsrv')->table('mob_family_healthinfo')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));
                $row_arr = $qry->get();
                $family_health_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $family_health_arr[$i]['fh_family'] = $row_arr[$i]->family;
                    $family_health_arr[$i]['fh_state'] = $row_arr[$i]->state;
                    $family_health_arr[$i]['fh_age'] = $row_arr[$i]->age;
                    $family_health_arr[$i]['fh_state_health'] = $row_arr[$i]->state_health;
                }

                //SELECT *,p.disease_id AS qn_id FROM mob_family_disease p LEFT JOIN mob_intermediary_family_disease d ON p.disease_id=d.disease_id 
                /*$qry = DB::connection('sqlsrv')->table('mob_intermediary_family_disease')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));*/
                $sql_query = "SELECT *,p.disease_id AS qn_id FROM mob_family_disease p LEFT JOIN mob_intermediary_family_disease d ON p.disease_id=d.disease_id WHERE d.prop_id IS NULL OR d.prop_id='$record_id'";
                $row_arr = DB::connection('sqlsrv')->select(DB::connection('sqlsrv')->raw($sql_query));
                //$row_arr = $qry->get();
                $qn_health_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $qn_health_arr[$i]['prop_id'] = $record_id;
                    $qn_health_arr[$i]['qn_id'] = $row_arr[$i]->qn_id;
                    $qn_health_arr[$i]['qn_description'] = $row_arr[$i]->name;
                    if($row_arr[$i]->prop_id != null){
                        $qn_health_arr[$i]['ans'] = 1;
                    }else{
                        $qn_health_arr[$i]['ans'] = 0;
                    }
                }

                /*$qry = DB::connection('sqlsrv')->table('mob_intermediary_family_disease')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));*/
                $sql_query = "SELECT *,p.disease_id AS qn_id FROM mob_family_disease p LEFT JOIN mob_intermediary_family_disease d ON p.disease_id=d.disease_id WHERE d.prop_id IS NULL OR d.prop_id='$record_id'";
                $row_arr = DB::connection('sqlsrv')->select(DB::connection('sqlsrv')->raw($sql_query));
                //$row_arr = $qry->get();
                $health_history_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $health_history_arr[$i]['hi_disease_id'] = $row_arr[$i]->disease_id;
                    $health_history_arr[$i]['hi_disease_injury'] = $row_arr[$i]->disease_injury;
                    $health_history_arr[$i]['hi_disease_date'] = $row_arr[$i]->disease_date;
                    $health_history_arr[$i]['hi_disease_duration'] = $row_arr[$i]->disease_duration;
                    $health_history_arr[$i]['hi_disease_result'] = $row_arr[$i]->disease_result;
                    $health_history_arr[$i]['hi_disease_doc'] = $row_arr[$i]->disease_doc;
                    //$health_history_arr[$i]['prop_id'] = $record_id;
                    $health_history_arr[$i]['qn_id'] = $row_arr[$i]->qn_id;
                    $health_history_arr[$i]['qn_description'] = $row_arr[$i]->name;
                    if($row_arr[$i]->prop_id != null){
                        $health_history_arr[$i]['ans'] = 1;
                    }else{
                        $health_history_arr[$i]['ans'] = 0;
                    }
                }

                //get checklist
                /*$qry = DB::connection('sqlsrv')->table('mob_intermediary_health_info')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));*/
                $sql_query = "SELECT *,p.id AS qn_id FROM mob_health_info p LEFT JOIN mob_health_intermediary d ON p.id=d.disease_id WHERE d.prop_id IS NULL OR d.prop_id='$record_id'";
                $row_arr = DB::connection('sqlsrv')->select(DB::connection('sqlsrv')->raw($sql_query));
                //$row_arr = $qry->get();
                //qn_disease_id,qn_disease_injury,qn_disease_date,qn_disease_duration,qn_disease_result,qn_disease_doc
                $qn_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $qn_arr[$i]['prop_id'] = $record_id;
                    $qn_arr[$i]['qn_id'] = $row_arr[$i]->qn_id;
                    $qn_arr[$i]['qn_description'] = $row_arr[$i]->description;
                    if($row_arr[$i]->prop_id != null){
                        $qn_arr[$i]['ans'] = 1;
                    }else{
                        $qn_arr[$i]['ans'] = 0;
                    }
                }

                //get the checklist intermediary
                /*$qry = DB::connection('sqlsrv')->table('mob_health_intermediary')->select('*')
                    ->where(array(
                        'prop_id' => $record_id
                    ));
                $row_arr = $qry->get();*/
                $sql_query = "SELECT *,p.id AS qn_id FROM mob_health_info p LEFT JOIN mob_health_intermediary d ON p.id=d.disease_id WHERE d.prop_id IS NULL OR d.prop_id='$record_id'";
                $row_arr = DB::connection('sqlsrv')->select(DB::connection('sqlsrv')->raw($sql_query));
                $qn_intermediary_arr = array();
                for($i=0;$i<sizeof($row_arr);$i++){
                    $qn_intermediary_arr[$i]['hi_disease_id'] = $row_arr[$i]->disease_id;
                    $qn_intermediary_arr[$i]['hi_disease_injury'] = $row_arr[$i]->disease_injury;
                    $qn_intermediary_arr[$i]['hi_disease_date'] = $row_arr[$i]->disease_date;
                    $qn_intermediary_arr[$i]['hi_disease_duration'] = $row_arr[$i]->disease_duration;
                    $qn_intermediary_arr[$i]['hi_disease_result'] = $row_arr[$i]->disease_result;
                    $qn_intermediary_arr[$i]['hi_disease_doc'] = $row_arr[$i]->disease_doc;
                    //$qn_intermediary_arr[$i]['prop_id'] = $record_id;
                    $qn_intermediary_arr[$i]['qn_id'] = $row_arr[$i]->qn_id;
                    $qn_intermediary_arr[$i]['qn_description'] = $row_arr[$i]->description;
                    if($row_arr[$i]->prop_id != null){
                        $qn_intermediary_arr[$i]['ans'] = 1;
                    }else{
                        $qn_intermediary_arr[$i]['ans'] = 0;
                    }
                }
                //health questionnaire
                $res = array(
                    'success' => true,
                    'record_id' => $record_id,
                    'policy_arr' => $organised_arr,//$rider_id,
                    'beneficiaries' => $beneficiaries_arr,
                    'dependants' => $dependants_arr,
                    'riders' => $rider_arr,
                    'family_health' => $family_health_arr ,
                    'family_history' => $qn_health_arr,
                    'fm_health_intermediary' => $health_history_arr,
                    'qn' => $qn_arr,
                    'qn_intermediary' => $qn_intermediary_arr,
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

    //get policy dependants
    public function getPolicyDependants(Request $request)
    {
        try{
            $policyId = $request->input('policyId');
            $sql = "SELECT * FROM funeralmembers p where p.Policy_no=$policyId";
            $FuneralMembers = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'FuneralMembers' => $FuneralMembers
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

    //get policy beneficiaries
    public function getPolicyBeneficiaries(Request $request)
    {
        try{
            $policyId = $request->input('policyId');
            $sql = "SELECT * FROM beneficiary_info p where p.policy_no=$policyId";
            $Beneficiaries = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Beneficiaries' => $Beneficiaries
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
    //get policy details
    public function getPolicyDetails(Request $request)
    {
        try{
            $policyId = $request->input('policyId');
            $sql = "SELECT * FROM polinfo p where p.id=$policyId";
            $PolicyDetails = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'PolicyDetails' => $PolicyDetails
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

    //get requested endorsements
    public function getRequestedEndorsements(Request $request)
    {
        try{
            //$policyId = $request->input('policyId');
            $sql = "SELECT *,e.description AS statuscode FROM eEndorsmentEntries p  INNER JOIN ClaimStatusInfo e ON e.id=p.status";
            $Endorsements = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Endorsements' => $Endorsements
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

    //save endorsement
    //post agent registration
    public function saveEndorsement(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                //save here
                $table_data = array(
                    'date_synced' => Carbon::now(),
                    'created_on' => Carbon::now(),
                    'Endorsementtype' => $request->input('Endorsementtype'),
                    'RequestDate' => Carbon::now(),
                    'PolicyNumber' => $request->input('PolicyNumber'),
                    'Narration' => $request->input('Narration'),
                    'status' => 1
                );
                $record_id = DB::connection('sqlsrv')->table('eEndorsmentEntries')->insertGetId($table_data);
                
                $res = array(
                    'success' => true,
                    'record_id' => $record_id,
                    'message' => 'Endorsement Successfully!!'
                );
                
            }, 5);
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
