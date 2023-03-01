<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class loginController extends Controller
{

    public function getTest(Request $request)
    {
        try{
            /*$FirstName = $request->input('FirstName');
			$results = $this->smartlife_db->table('tblContact as p')
            ->select('*')->get();*/
           // ->where(array('p.FirstName' => $FirstName));
           //$sql_query = "SELECT id, FirstName, LastName, Email FROM tblContact";
           //$results = $this->smartlife_db->select($this->smartlife_db->raw($sql_query));

            /*$user_id = 1;//\Auth::user()->id;
            $table_data = $request->all();
            $table_name = $table_data['table_name'];
            unset($table_data['table_name']);
            $res = DbHelper::insertRecord($table_name, $table_data, $user_id);*/

            $sql = "select * from gender_info";
            $res = DbHelper::getTableRawData($sql);
        }catch (\Exception $exception) {
			$res = array(
                'success' => false,
                'message' => $exception->getMessage()
			);
		}catch (\Throwable $throwable) {
			$res = array(
                'success' => false,
                'message' => $throwable->getMessage()
			);
		}
        return response()->json($res);
    }

    //change password (Agent & Client) - Send client a link.....

    function generateRandomFileName($length=6,$level=2){
        list($usec,$sec)=explode(' ',microtime());
        srand((float)$sec+((float)$usec*100000));
        $validchars[1]="0123456789abcdefghijklmnopqrstuvwxyz";
        $validchars[2]="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        //$validchars[3]="0123456789_!@#$%&*()-=+/abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";
        $random_filename="";
        $counter=0;
        while($counter<$length){
            $actChar=substr($validchars[$level],rand(0,strlen($validchars[$level])-1),1);
            if(!strstr($random_filename,$actChar)){
                $random_filename.=$actChar;
                $counter++;
            }
        }
        return $random_filename;
    }

    //Register Agent 
    public function AgentRegistration(Request $request)
    {
        try{
            $res=array();
            //TODO
            //1. Search in Agents info if exists
            $agent_no = $request->input('agent_no');

            //check if agent_no exists
            $user_id = DbHelper::getColumnValue('portal_users', 'agent_no',$agent_no,'id');
            if(isset($user_id) && (int)$user_id > 0){
                $res = array(
                    'success' => false,
                    'msg' => "Agent is already Registered",
                );
                return $res;
            }


            $sql = "SELECT p.agent_no,p.mobile,p.Ismanager,p.BusinessChannel,p.IsActive,p.Emailaddress FROM agents_info p WHERE p.IsActive=1 AND agent_no='$agent_no'";
            $Agent = DbHelper::getTableRawData($sql);
           
            
            if(sizeof($Agent) > 0){
                //2. If true, Insert details and send sms & email with the default password
                //agent_no, password,mobile_no,email,created_on
                $password = $this->generateRandomFileName();
                $table_data = array(
                    'agent_no' => $Agent[0]->agent_no,
                    'password' => md5($password),
                    'mobile_no' => $Agent[0]->mobile,
                    'email' => $Agent[0]->Emailaddress,
                    'created_on' => Carbon::now()
                );
                $record_id = DB::connection('sqlsrv')->table('portal_users')->insertGetId($table_data);

            }else{
                //terminate (agent no doesn't exist)
                $res = array(
                    'success' => false,
                    'msg' => "Provide a valid Agent no or the Agent is deactivated",
                );
                return $res;
            }
            //3. 
            //health questionnaire
            $res = array(
                'success' => true,
                'user_id' => $record_id,
                'password' => $password
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

    //login Agent (know whether: micro, life, bancassurance, manager)
    public function AgentLogin(Request $request)
    {
        try{
            $res=array();
            //TODO
            //1. check if Agent is active..
            $agent_no = $request->input('agent_no');
            $password = md5($request->input('password'));

            //check if agent_no exists
            $agents_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
            if(isset($agents_id) && (int)$agents_id > 0){
                //check if Agent is active..
                $isActive = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'IsActive');
                if(!$isActive){
                    //check if Agent is active..
                    $res = array(
                        'success' => false,
                        'msg' => "Agent is in active",
                    );
                    return $res;
                }
            }else{
                $res = array(
                    'success' => false,
                    'msg' => "Agent does not exist",
                );
                return $res;
            }

            //2. Login with agent_no and password
            $sql = "SELECT * FROM portal_users p WHERE p.agent_no='$agent_no' AND p.password='$password'";
            $Agent = DbHelper::getTableRawData($sql);
           
            
            if(sizeof($Agent) > 0){
                //get the business Channel of Agent
                $BusinessChannel = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'BusinessChannel');
                $name = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'name');
            }else{
                $res = array(
                    'success' => false,
                    'msg' => "Wrong Password",
                );
                return $res;
            }

            //3. return, agentsChannel 

            $res = array(
                'success' => true,
                'agent_no' => $agent_no,
                'BusinessChannel' => $BusinessChannel,
                'name' => $name,
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

    //client Registration
    public function ClientRegistration(Request $request)
    {
        try{
            $res=array();
            //TODO
            //1. Check if Client exists(policy_no & mobile_no)
            $policy_no = $request->input('policy_no');
            $mobile_no = $request->input('mobile_no');

            //check if client exists
            if(isset($policy_no)){
                //get client_number
                $client_no = DbHelper::getColumnValue('polinfo', 'policy_no',$policy_no,'client_number');
                if(!isset($client_no)){
                    $clientId = DbHelper::getColumnValue('MicroPolicyInfo', 'PolicyNumber',$policy_no,'Client');
                    $client_no = DbHelper::getColumnValue('MicroClientInfo', 'Id',$clientId,'ClientNumber');
                }
                $user_id = DbHelper::getColumnValue('portal_users', 'client_no',$client_no,'id');
            }else if(isset($mobile_no)){
                $client_no = DbHelper::getColumnValue('clientinfo', 'mobile',$mobile_no,'client_number');
                if(!isset($client_no)){
                    //could be micro: The genius himself
                    $client_no = DbHelper::getColumnValue('MicroClientInfo', 'Mobile',$mobile_no,'ClientNumber');
                }
                $user_id = DbHelper::getColumnValue('portal_users', 'client_no',$client_no,'id');
            }else{
                $res = array(
                    'success' => false,
                    'msg' => "Fill Policy Number or Mobile Number",
                );
                return $res;
            }
            
            if(isset($user_id) && (int)$user_id > 0){
                $res = array(
                    'success' => false,
                    'msg' => "Client is already Registered",
                );
                return $res;
            }

            if(isset($policy_no)){
                $sql = "SELECT p.policy_no,d.client_number,d.mobile,d.email FROM polinfo p INNER JOIN clientinfo d ON d.client_number=p.client_number WHERE p.policy_no='$policy_no' ";
                $Client = DbHelper::getTableRawData($sql);
                if(sizeof($Client) == 0){
                    //it could be micro made by the genius himself
                    $sql = "SELECT p.PolicyNumber AS policy_no,d.ClientNumber AS client_number,d.Mobile AS mobile,d.Email AS email FROM MicroPolicyInfo p INNER JOIN MicroClientInfo d ON d.Id=p.Client WHERE p.PolicyNumber='$policy_no' ";
                    $Client = DbHelper::getTableRawData($sql);
                }
            }else if(isset($mobile_no)){
                $sql = "SELECT p.policy_no,d.client_number,d.mobile,d.email FROM polinfo p INNER JOIN clientinfo d ON d.client_number=p.client_number WHERE d.mobile='$mobile_no' ";
                $Client = DbHelper::getTableRawData($sql);
                if(sizeof($Client) == 0){
                    $sql = "SELECT p.PolicyNumber AS policy_no,d.ClientNumber AS client_number,d.Mobile AS mobile,d.Email AS email FROM MicroPolicyInfo p INNER JOIN MicroClientInfo d ON d.Id=p.Client WHERE p.PolicyNumber='$mobile_no' ";
                    $Client = DbHelper::getTableRawData($sql);
                }
            }else{
                $res = array(
                    'success' => false,
                    'msg' => "Please pass Policy No or Mobile No",
                );
                return $res;
            }

            
            if(sizeof($Client) > 0){
                //2. If true, Insert details and send sms & email with the default password
                //agent_no, password,mobile_no,email,created_on
                $password = $this->generateRandomFileName();
                $client_no = $Client[0]->client_number;
                $table_data = array(
                    'client_no' => $Client[0]->client_number,
                    'password' => md5($password),
                    'mobile_no' => $Client[0]->mobile,
                    'email' => $Client[0]->email,
                    'created_on' => Carbon::now()
                );
                $record_id = DB::connection('sqlsrv')->table('portal_users')->insertGetId($table_data);

            }else{
                //terminate (agent no doesn't exist)
                $res = array(
                    'success' => false,
                    'msg' => "Provide a valid Policy No or registered Mobile No",
                );
                return $res;
            }
            //3. 
            //health questionnaire
            $res = array(
                'success' => true,
                'user_id' => $record_id,
                'client_no' => $client_no,
                'password' => $password
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

    //client Login
    public function ClientLogin(Request $request)
    {
        try{
            $res=array();
            //TODO
            $mobile_no = $request->input('policy_no_mobile_no');
            $policy_no = $request->input('policy_no_mobile_no');
            $password = md5($request->input('password'));
            $client_no=null;

            //get the client_no
            if(isset($policy_no)){
                //get client_number
                $client_no = DbHelper::getColumnValue('polinfo', 'policy_no',$policy_no,'client_number');
                if(!isset($client_no)){
                    $clientId = DbHelper::getColumnValue('MicroPolicyInfo', 'PolicyNumber',$policy_no,'Client');
                    $client_no = DbHelper::getColumnValue('MicroClientInfo', 'Id',$clientId,'ClientNumber');
                }
                $user_id = DbHelper::getColumnValue('portal_users', 'client_no',$client_no,'id');
                if(!isset($client_no)){
                    $client_no = DbHelper::getColumnValue('clientinfo', 'mobile',$mobile_no,'client_number');
                    if(!isset($client_no)){
                        //could be micro: The genius himself
                        $client_no = DbHelper::getColumnValue('MicroClientInfo', 'Mobile',$mobile_no,'ClientNumber');
                    }
                    $user_id = DbHelper::getColumnValue('portal_users', 'client_no',$client_no,'id');
                }
            }else{
                $res = array(
                    'success' => false,
                    'msg' => "Fill Policy Number or Mobile Number",
                );
                return $res;
            }

            if(!isset($client_no)){
                //could be micro: The genius himself
                $res = array(
                    'success' => false,
                    'msg' => "InValid Policy No or Mobile No is not registered in the system",
                );
                return $res;
            }

            //2. Login with agent_no and password
            $sql = "SELECT * FROM portal_users p WHERE p.client_no='$client_no' AND p.password='$password'";
            $Client = DbHelper::getTableRawData($sql);
           
            
            if(sizeof($Client) > 0){
                //get the name of Client
                $client_name = DbHelper::getColumnValue('clientinfo', 'client_number',$client_no,'name');
                if(!isset($client_name)){
                    //then its micro from the genius himself
                    $client_name = DbHelper::getColumnValue('MicroClientInfo', 'ClientNumber',$client_no,'Name');
                }
            }else{
                $res = array(
                    'success' => false,
                    'msg' => "Wrong Password",
                );
                return $res;
            }

            //3. return, agentsChannel 

            $res = array(
                'success' => true,
                'client_no' => $client_no,
                'client_name' => $client_name
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

    //POS login
    public function POSLogin(Request $request)
    {
        try{
            $res=array();
            //TODO
            $username = $request->input('username');
            $password = md5($request->input('password'));

            //2. Login with agent_no and password
            $sql = "SELECT * FROM portal_users p WHERE p.username='$username' AND p.password='$password'";
            $POS = DbHelper::getTableRawData($sql);
           
            if(sizeof($POS) == 0){
                //get the name of Client
                $res = array(
                    'success' => false,
                    'msg' => "Wrong Password",
                );
                return $res;
            }

            $res = array(
                'success' => true,
                'user_id' => $POS[0]->id
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

    //POS Registration
    public function POSRegistration(Request $request)
    {
        try{
            $res=array();
            //TODO
            //1. Search in Agents info if exists
            $username = $request->input('username');
            $mobile_no = $request->input('mobile_no');
            $email = $request->input('email');
            $password = $request->input('password');
            $table_data = array(
                'username' => $username,
                'password' => md5($password),
                'mobile_no' => $mobile_no,
                'email' => $email,
                'created_on' => Carbon::now()
            );
            //check if username exists
            $user_id = DbHelper::getColumnValue('portal_users', 'username',$username,'id');
            if(isset($user_id) && (int)$user_id > 0){
                //update
                DB::connection('sqlsrv')->table('portal_users')
                    ->where(array(
                        "id" => $user_id
                    ))
                    ->update($table_data);
            }else{
                //insert
                $user_id = DB::connection('sqlsrv')->table('portal_users')->insertGetId($table_data);
            }

            $res = array(
                'success' => true,
                'user_id' => $user_id
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
