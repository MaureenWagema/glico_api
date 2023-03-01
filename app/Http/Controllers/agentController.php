<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;

class agentController extends Controller
{
    //post agent registration
    public function AgentsRegistration(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                //save here
                $table_data = array(
                    'BusinessChannel' => $request->input('BusinessChannel'),
                    'BancassuranceBankLink' => $request->input('BancassuranceBankLink'),
                    'AllDocumentReceived' => $request->input('AllDocumentReceived'),
                    'StopPFDeduction' => $request->input('StopPFDeduction'),
                    //'StatusCode' => $request->input('StatusCode'),
                    'IsAttachedToAbranch' => $request->input('IsAttachedToAbranch'),
                    'IsAttachedToUnit' => $request->input('IsAttachedToUnit'),
                    'IsAttachedToTeam' => $request->input('IsAttachedToTeam'),
                    'IsAttachedToRegion' => $request->input('IsAttachedToRegion'),
                    'ManagerialFlag' => $request->input('ManagerialFlag'),
                    'UseThisOverrideRate' => $request->input('UseThisOverrideRate'),
                    'BranchName' => $request->input('BranchName'),
                    'UnitName' => $request->input('UnitName'),
                    'TeamName' => $request->input('TeamName'),
                    'name' => $request->input('name'),
                    'mobile' => $request->input('mobile'),
                    'mobile2' => $request->input('mobile2'),
                    'RestrictCommission' => $request->input('RestrictCommission'),
                    'CommissionCutoffDate' => $request->input('CommissionCutoffDate'),
                    'appointed_on' => $request->input('appointed_on'),
                    'stopped_date' => $request->input('stopped_date'),
                    'Compliance_licence' => $request->input('Compliance_licence'),
                    'licenceNumber' => $request->input('licenceNumber'),
                    'LicenceStartDate' => $request->input('LicenceStartDate'),
                    'LicenceExpiryDate' => $request->input('LicenceExpiryDate'),
                    'Emailaddress' => $request->input('Emailaddress'),
                    'physicaladdress' => $request->input('physicaladdress'),

                    'GpsCode' => $request->input('GpsCode'),
                    'postalcode' => $request->input('postalcode'),
                    'postaladdress' => $request->input('postaladdress'),
                    'Country' => '001',
                    'RegionName' => $request->input('RegionName'),
                    'bank_code' => $request->input('bank_code'),
                    'bank_branch' => $request->input('bank_branch'),
                    'bank_ac' => $request->input('bank_ac'),
                    'Bank_ac_Name' => $request->input('Bank_ac_Name'),
                    'EducationLevel' => $request->input('EducationLevel'),
                    'RecruitedBy' => $request->input('RecruitedBy'),
                    'payment_method' => $request->input('payment_method'),
                    'id_type' => $request->input('id_type'),

                    'IdentityNumber' => $request->input('IdentityNumber'),
                    'KRANumber' => $request->input('KRANumber'),
                    'gender' => $request->input('gender'),
                    'marital_status' => $request->input('marital_status'),
                    'birthdate' => $request->input('birthdate'),
                    'EntryAge' => $request->input('EntryAge'),
                    'EmploymentType' => $request->input('EmploymentType'),
                    'SellingExperience' => $request->input('SellingExperience'),
                    'ExperienceSector' => $request->input('ExperienceSector'),
                    'PromotionMinimumPeriod' => $request->input('PromotionMinimumPeriod'),
                );
                $record_id = DB::connection('sqlsrv')->table('eAgentsEntries')->insertGetId($table_data);
                //TODO save all beneficiaries as well...
                //post agent beneficiary

                //Names, marital_status, email,relationship,id_type,idNumber,birthdate,EntryAge,mobile
                //beneficiaries
                $beneficiaries_array = array();
                $beneficiaries_arr = $request->input('beneficiaries');
                
                if(isset($beneficiaries_arr)){
                    DB::connection('sqlsrv')->table('AgentBeneficiaryInfo')->where('AgentIdKey', '=', $record_id)->delete();
                    for($i=0;$i<sizeof($beneficiaries_arr);$i++){
                        $beneficiaries_array[$i]['AgentIdKey'] = $record_id;
                        $beneficiaries_array[$i]['Names'] = $beneficiaries_arr[$i]['Names'];
                        $beneficiaries_array[$i]['marital_status'] = $beneficiaries_arr[$i]['marital_status'];
                        $beneficiaries_array[$i]['email'] = $beneficiaries_arr[$i]['email'];
                        $beneficiaries_array[$i]['birthdate'] = $beneficiaries_arr[$i]['birthdate'];
                        if($beneficiaries_array[$i]['birthdate'] == "null"){
                            $beneficiaries_array[$i]['birthdate'] = null;
                        }
                        $beneficiaries_array[$i]['relationship'] = $beneficiaries_arr[$i]['relationship'];
                        $beneficiaries_array[$i]['id_type'] = $beneficiaries_arr[$i]['id_type'];
                        $beneficiaries_array[$i]['idNumber'] = $beneficiaries_arr[$i]['idNumber'];
                        $beneficiaries_array[$i]['EntryAge'] = $beneficiaries_arr[$i]['EntryAge'];
                        $beneficiaries_array[$i]['mobile'] = $beneficiaries_arr[$i]['mobile'];
                        
                        $beneficiaries_id = DB::connection('sqlsrv')->table('AgentBeneficiaryInfo')->insertGetId($beneficiaries_array[$i]);
                    }
                }

                
                $res = array(
                    'success' => true,
                    'record_id' => $record_id,
                    'message' => 'Agent Registered Successfully!!'
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

    //get Region
    public function getRelationships(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT code,description FROM relationship_mainteinance p";
            $Relationships = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Relationships' => $Relationships
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

    //get Region
    public function getRegions(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM Towns p";
            $Regions = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Regions' => $Regions
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

    //get bank codes
    public function getBanks(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT bank_code,description FROM bankcodesinfo p";
            $Banks = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Banks' => $Banks
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

    //get bank branches
    public function getBankBranches(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT id,bankBranchCode,bankBranchName,bank_code FROM bankmasterinfo p";
            $BankBranches = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'BankBranches' => $BankBranches
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

    //get recruited by
    public function getRecruitedBy(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT id,agent_no,name FROM agents_info p";
            $RecruitedBy = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'RecruitedBy' => $RecruitedBy
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

    //get id types
    public function getIdTypes(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM identity_types p";
            $IdTypes = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'IdTypes' => $IdTypes
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

    //get gender
    public function getGender(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM gender_info p";
            $Gender = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'Gender' => $Gender
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

    //get marital status
    public function getMaritalStatus(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM MaritalStatusInfo p";
            $MaritalStatusInfo = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'MaritalStatusInfo' => $MaritalStatusInfo
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


    //get experience sector
    public function getExprienceSector(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsExperienceSector p";
            $AgentsExperienceSector = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsExperienceSector' => $AgentsExperienceSector
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

    //get compliance
    public function getAgentsComplianceLicense(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsCompliance_Licence p";
            $AgentsComplianceLicense = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsComplianceLicense' => $AgentsComplianceLicense
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

    //get payment method
    public function getAgentsPaymentMethods(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsPaymethodInfo p";
            $AgentsPaymethods = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsPaymethods' => $AgentsPaymethods
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
    //get Regions
    public function getAgentsRegions(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsRegionInfo p";
            $AgentsRegionInfo = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsRegionInfo' => $AgentsRegionInfo
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
    //get Branches
    public function getAgentsBranches(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsBranchInfo p";
            $AgentsBranchInfo = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsBranchInfo' => $AgentsBranchInfo
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
    //get unit
    public function getAgentsUnits(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsunitsInfo p";
            $AgentsunitsInfo = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsunitsInfo' => $AgentsunitsInfo
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
    //get teams
    public function getAgentsTeams(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsTeamsInfo p";
            $AgentsTeamsInfo = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsTeamsInfo' => $AgentsTeamsInfo
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
    //get channel
    public function getAgentsChannel(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM agentsChannel p";
            $agentsChannel = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'agentsChannel' => $agentsChannel
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

    //get Education level
    public function getAgentsEducationLevel(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM EducationLevel p";
            $agentsEducationLevel = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'agentsEducationLevel' => $agentsEducationLevel
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

    //get AgentsFileChecklist
    public function getAgentsFileChecklist(Request $request)
    {
        try{
            //$agent_no = $request->input('agent_no');
            $sql = "SELECT * FROM AgentsFileChecklist p";
            $AgentsFileChecklist = DbHelper::getTableRawData($sql);

            //health questionnaire
            $res = array(
                'success' => true,
                'AgentsFileChecklist' => $AgentsFileChecklist
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
