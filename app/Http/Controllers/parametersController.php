<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DbHelper;

class parametersController extends Controller
{
    //get Insurance Cover Types
    public function getInsuranceCoverTypes(Request $request)
    {
        try{
            $Plan_code = $request->input('Plan_code');
            $sql = "SELECT p.InsuranceType,p.PremiumAmount,d.description FROM ProductBenefitsConfig p INNER JOIN InsuranceCoverTypes d ON d.id=p.InsuranceType WHERE p.Plan_code='$Plan_code' GROUP BY p.InsuranceType,p.PremiumAmount,d.description";
            $InsuranceCoverTypes = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'InsuranceCoverTypes' => $InsuranceCoverTypes
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

    public function getCommonParams(Request $request)
    {
        try{
            
            if(isset($_GET['is_micro']) && $_GET['is_micro'] == "1"){
                $sql = "SELECT p.*,p.OrdinaryLife AS ordinary_life,p.plan_code as plan_id,p.maxMatAge AS maturity_age,p.MinAgeParents AS min_age_parents,
                p.MaxAgeParents AS max_age_parents,p.isBancAssurance AS isbancassurance,0 AS istr,p.MinSum AS min_sum,p.CategoryCode+1 as CategoryCode,
                p.PlanOldName as plan_code from planinfo p 
                LEFT JOIN plan_prop_category d 
                    ON (p.CategoryCode=d.prop_code) WHERE isForMportal = 1";
            }else{
                if(isset($_GET['n']) && $_GET['n'] == "1"){
                    /*$sql = "SELECT p.*,p.OrdinaryLife AS ordinary_life,p.plan_code as plan_id,p.maxMatAge AS maturity_age,p.MinAgeParents AS min_age_parents,
                    p.MaxAgeParents AS max_age_parents,p.isBancAssurance AS isbancassurance,0 AS istr,p.MinSum AS min_sum,p.CategoryCode+1 as CategoryCode,
                    p.PlanOldName as plan_code from planinfo p 
                    LEFT JOIN plan_prop_category d 
                    ON (p.CategoryCode=d.prop_code) WHERE p.is_active=1 AND ( (p.investment_plan=1 AND p.microassurance=1) 
                     OR p.OrdinaryLife=1) AND p.isBancAssurance = 0 AND isForMportal = 1";*/
                     $sql = "SELECT p.*,p.OrdinaryLife AS ordinary_life,p.plan_code as plan_id,p.maxMatAge AS maturity_age,p.MinAgeParents AS min_age_parents,
                    p.MaxAgeParents AS max_age_parents,p.isBancAssurance AS isbancassurance,0 AS istr,p.MinSum AS min_sum,p.CategoryCode+1 as CategoryCode,
                    p.PlanOldName as plan_code from planinfo p 
                    LEFT JOIN plan_prop_category d 
                    ON (p.CategoryCode=d.prop_code) WHERE  isForMportal = 1";
                }else{
                    $sql = "SELECT p.*,p.OrdinaryLife AS ordinary_life,p.plan_code as plan_id,p.maxMatAge AS maturity_age,p.MinAgeParents AS min_age_parents,
                    p.MaxAgeParents AS max_age_parents,p.isBancAssurance AS isbancassurance,0 AS istr,p.MinSum AS min_sum,p.CategoryCode+1 as CategoryCode,
                    p.PlanOldName as plan_code from planinfo p 
                    LEFT JOIN plan_prop_category d 
                    ON (p.CategoryCode=d.prop_code) WHERE isForMportal = 1";
                }
            }
            $plan_info_rows = DbHelper::getTableRawData($sql);

            $sql = "select * from rider_info";
            $rider_info_rows = DbHelper::getTableRawData($sql);

            $sql = "select p.id AS plan_rider_id,p.plan_code,p.rider_code,p.use_flat_rate, p.rate_basis, p.rate, p.rate2, p.apply_comm, p.gl_premium_account,
            d.description AS plan_description, e.description AS rider_description, e.short_description as short_desc from plan_rider_config p 
            LEFT JOIN planinfo d ON (p.plan_code=d.plan_code) LEFT JOIN rider_info e ON (p.rider_code=e.rider_code)";
            $plan_rider_info_rows = DbHelper::getTableRawData($sql);

            //$sql = "select p.code,p.description,d.code as category from relationship_mainteinance p left JOIN funeralcateginfo d ON p.Categ=d.id";
            $sql = "select * from relationship_mainteinance";
            $relationship_info_rows = DbHelper::getTableRawData($sql);

            $sql = "SELECT * from maritalstatusinfo";
            $maritalinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select * from gender_info";
            $gender_info_rows = DbHelper::getTableRawData($sql);

            if(isset($_GET['is_micro']) && $_GET['is_micro'] == "1"){
                $sql="select emp_code,Name,transfer_rate from pay_source_mainteinance";
            }else{
                if(isset($_GET['n']) && $_GET['n'] == "1"){
                    $sql="select emp_code,Name,transfer_rate from pay_source_mainteinance";
                }else{
                    $sql="select emp_code,Name,transfer_rate from pay_source_mainteinance";
                }
            }
            $employer_info_rows = DbHelper::getTableRawData($sql);

            //return gender, marital status, occupation and paymethod
            if(isset($_GET['is_micro']) && $_GET['is_micro'] == "1"){
                $sql="select agent_no,name from agents_info where IsforMicro=1";
            }else{
                $sql="select agent_no,name from agents_info WHERE IsforMicro=1";
            }
            $life_agents_rows = DbHelper::getTableRawData($sql);

            //$sql = "select account_no,branchName, branchCode, sort_code from paysourcebranches";
            $paysourcebr_rows = array();// DbHelper::getTableRawData($sql);

            $sql = "select class_code,Description,rate from paclass";
            $paclass_info_rows = DbHelper::getTableRawData($sql);

            $sql = "select occupation_code,occupation_name from occupationinfo";
            $Occupationinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select Code,Name from countryinfo";
            $countryinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select *,id as qn_id,description as qn_description FROM mob_health_info";
            $healthinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select *,disease_id as qn_id,name as qn_description FROM mob_family_disease";
            $familydisease_rows = DbHelper::getTableRawData($sql);

            ///////
            //$sql = "select payment_mode AS paymethod,decription AS paymethodDescription FROM payment_type";
            $sql = "select * FROM payment_type";
            $paymentmeth_rows = DbHelper::getTableRawData($sql);

            $sql = "select bank_code,description FROM bankcodesinfo";
            $bankinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select * FROM paymentmodeinfo";
            $paymentmode_rows = DbHelper::getTableRawData($sql);

            //$sql = "select p.OldPlanCode as plan_code,p.OldPlanCode as plan,p.oldPayMode as payment_mode,p.description,p.premyr,p.loadingfactor,p.coverperiod,p.singleprem FROM paymentmodeinfo p";
            $sql = "select * FROM paymentmodeinfo";
            $paymentmodeinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select * FROM premdistinfo";
            $premdistribinfo_rows = DbHelper::getTableRawData($sql);

            //////
            $sql = "select * FROM defaultsinfo";
            $defaultsinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select p.* FROM premium_rate_setup p";
            $premrateinfo_rows = DbHelper::getTableRawData($sql);

            $sql = "select * FROM rider_premuim_rates";
            $riderpremuimrate_rows = DbHelper::getTableRawData($sql);

            $sql = "select * FROM funeralratesinfo";
            $Funeralratesinfo_rows = DbHelper::getTableRawData($sql);

            //$sql = "select * FROM bapackages";
            $bapackages_rows = array();//DbHelper::getTableRawData($sql);

            //$sql = "SELECT p.id, p.plan_code,p.Description,p.code,p.Min_age AS min_age,p.Max_age AS max_age,p.Min_sa AS min_Sa,p.Max_sa AS max_Sa,p.created_by,p.created_on,p.altered_by,p.dola FROM funeralcateginfo p";
            $sql = "select * from funeralcateginfo";
            $funeralcat_rows = DbHelper::getTableRawData($sql);

            //$sql = "select * FROM parentspremratesinfo";
            $parentspremrates_rows = array();//DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM MicroClientInfo p INNER JOIN MicroProposalInfo d ON p.Id=d.Client WHERE d.Agent='15'";
            $Clients = DbHelper::getTableRawData($sql);

            $sql = 'SELECT d.*,p.Name,p.Mobile,e.description AS plan_name FROM MicroProposalInfo d INNER JOIN MicroClientInfo p ON p.Id=d.Client INNER JOIN planinfo e ON d."Plan"=e.plan_code WHERE d.Agent=15';
            $Policies = DbHelper::getTableRawData($sql);

            //ClientPolicies, ClaimType, PartialWithdrawalPurposes, ClaimCause

            $sql = "SELECT * FROM polinfo d WHERE d.client_number='C00300142'";
            $ClientPolicies = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM claims_types d WHERE d.ShowInClientPortal=1";
            $ClaimType = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM PartialWidthrawalReasons d";
            $PartialWithdrawalPurposes = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM claimcausesinfo d";
            $ClaimCause = DbHelper::getTableRawData($sql);
            //EndorsementTypes
            $sql = "SELECT * FROM LifeEndorsementTypeInfo d where MakeVisible=1";
            $EndorsementTypes = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM Towns d";
            $Region = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM bankcodesinfo d";
            $Banks = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM bankmasterinfo d";
            $BanksBranches = DbHelper::getTableRawData($sql);
            
            $sql = "SELECT * FROM identity_types d";
            $IDTypes = DbHelper::getTableRawData($sql);
            //
            $sql = "SELECT * FROM pay_source_mainteinance p WHERE p.TelcoCompany=1";
            $Telcos = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM PremiumIncrementPercentage p";
            $PremiumIncrementPercentage = DbHelper::getTableRawData($sql);

            $sql = "SELECT * FROM titleInfo p";
            $titleInfo = DbHelper::getTableRawData($sql);

            return response()->json(array("Planinfo" => $plan_info_rows,"Riderinfo" => $rider_info_rows,"PlanRiderinfo" => $plan_rider_info_rows
            ,"Relationshipinfo" => $relationship_info_rows,"Maritalinfo" => $maritalinfo_rows,"Genderinfo" => $gender_info_rows
            ,"Employerinfo" => $employer_info_rows,"Paclassinfo" => $paclass_info_rows,"Occupationinfo" => $Occupationinfo_rows
            ,"Countryinfo" => $countryinfo_rows,"Healthinfo" => $healthinfo_rows,"Bankinfo" => $bankinfo_rows,"Paymentinfo" => $paymentmeth_rows
            ,"Paymentmodeinfo" => $paymentmodeinfo_rows,"Defaultsinfo" => $defaultsinfo_rows,"Premrateinfo" => $premrateinfo_rows,"Paymentmode" => $paymentmode_rows
            ,"Riderpremuimrate" => $riderpremuimrate_rows,"Funeralratesinfo" => $Funeralratesinfo_rows,"Paysourcebr" => $paysourcebr_rows
            ,"LifeAgents" => $life_agents_rows,"Premdistribinfo" => $premdistribinfo_rows,"BaPackages"=>$bapackages_rows
            ,"FuneralCat" => $funeralcat_rows,"ParentsPrem" => $parentspremrates_rows,"FamDisease" => $familydisease_rows
            ,"Clients" => $Clients,"Policies" => $Policies,"ClientPolicies" => $ClientPolicies,"ClaimType" => $ClaimType,
            "PartialWithdrawalPurposes" => $PartialWithdrawalPurposes,"ClaimCause" => $ClaimCause,"EndorsementTypes"=>$EndorsementTypes,
            "Region" => $Region,"Banks" => $Banks,"BanksBranches" => $BanksBranches,"IDTypes" => $IDTypes,"Telcos" => $Telcos,
            "PremiumIncrementPercentage" => $PremiumIncrementPercentage,"titleInfo" => $titleInfo
        ));

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
        //return response()->json($res);
    }
}
