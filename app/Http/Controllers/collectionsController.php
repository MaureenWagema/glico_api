<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;

class collectionsController extends Controller
{
    //TODO - 1. Sync clients and polices of the agent
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
    public function SMS(Request $request)
    {
        try{
            $msg = $request->input('msg');
            $mobile_no = $request->input('mobile_no');
            $url_path = "http://193.105.74.59/api/sendsms/plain?user=Glico2018&password=glicosmpp&sender=GLICO&SMSText=".$msg."&GSM=".$mobile_no;
			
            $client = new \GuzzleHttp\Client;
			$smsRequest =  $client->get($url_path);


            //health questionnaire
            $res = array(
                'success' => true,
                'otp' => $otp
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
    //2. send otp
    public function sendOTP(Request $request)
    {
        try{
            //TODO
            //push the amount only client_no 
            $res=array();

            $agent_no = $request->input('agent_no');
            $policy_no = $request->input('policy_no');
            $client_no = $request->input('client_no');
            $amount = $request->input('amount');
            //$market_code = $request->input('market_code');
            //$agent_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
            $agent_name = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'name');
            $mobile_no = "233204194298"; //"233244790337";//DbHelper::getColumnValue('MicroClientInfo', 'ClientNumber',$client_no,'Mobile');

            //$url_path = "http://193.105.74.59/api/sendsms/plain?user=Glifelife01&password=Glico@2021!&sender=Glicolife&SMSText=".$message."&GSM=".$mobileno;
            $otp = mt_rand(1000,9999);
            $msg = "We GLICO acknowledge ".$amount." GHC collected for policy: ".$policy_no." Kindly provide security code: ".$otp. " to Agent: ".$agent_name;
            $url_path = "http://193.105.74.59/api/sendsms/plain?user=Glico2018&password=glicosmpp&sender=GLICO&SMSText=".$msg."&GSM=".$mobile_no;
			
            $client = new \GuzzleHttp\Client;
			$smsRequest =  $client->get($url_path);


            //health questionnaire
            $res = array(
                'success' => true,
                'otp' => $otp
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
    //3. save the amount in the table
    public function receiveOTP(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {
                $agent_no = $request->input('agent_no');
                $policy_no = $request->input('policy_no');
                $client_no = $request->input('client_no');
                $amount = $request->input('amount');
                $market_code = $request->input('market_code');
                //$payment_date = $request->input('payment_date');
                $payment_date = $request->input('payment_date');
                $payment_type = $request->input('payment_type');

                $otp = $request->input('otp');
                $agent_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
                $agent_name = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'name');

                $table_data = array(
                    'Agent_no' => $agent_id,
                    'Policy_no' => $policy_no,
                    'Client_no' => $client_no,
                    'Amount' => $amount,
                    'Payment_date' => date($payment_date),
                    'Payment_Type' => $payment_type,
                    'Market_code' => $market_code,
                    'OTP' => $otp,
                    'IsMicro' => 1,
                    'created_on' => Carbon::now()
                );

                //insert into 
                $record_id = DB::connection('sqlsrv')->table('collection_payments')->insertGetId($table_data);

                //health questionnaire
                $res = array(
                    'success' => true,
                    'agent_no' => $agent_no,
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


    //4. handle remittance -baas
    public function Remit(Request $request)
    {
        try{
            $res=array();

            $agent_no = $request->input('agent_no');
            $total_amount = 0;
            $transactions = array();
            $agent_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
            // put in a transaction the whole process of syncing data...
            $sql = "SELECT p.Payment_date,p.Amount,p.Policy_no FROM collection_payments p WHERE p.Payment_type=1 AND p.Batch IS NULL AND p.Agent_no='$agent_id' ORDER BY p.Payment_date ASC";
            $transactions = DbHelper::getTableRawData($sql);

            $sql = "SELECT SUM(p.Amount) AS total_amount FROM collection_payments p WHERE p.Payment_type=1 AND p.Batch IS NULL AND p.Agent_no='$agent_id'";
            $result_amount = DbHelper::getTableRawData($sql);
            if(sizeof($result_amount )>0){
                $total_amount = $result_amount[0]->total_amount;
            }

            $batch = null;

            $sql = "SELECT p.CollectionSerial FROM agents_info p WHERE p.id=15";
            $result_serial = DbHelper::getTableRawData($sql);
            if(sizeof($result_serial )>0){
                if($result_serial[0]->CollectionSerial == null){
                    $batch = $agent_no.'-1';
                }else{
                    $batch = $agent_no.'-'.$result_serial[0]->CollectionSerial;
                }
            }

            //
            $res = array(
                'success' => true,
                'transactions' => $transactions,
                'total_amount' => $total_amount,
                'batch' => $batch
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

    public function updateRemit(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {

                $agent_no = $request->input('agent_no');
                $total_amount = 0;
                $agent_id = DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'id');
                // put in a transaction the whole process of syncing data...
                $sql = "SELECT p.Payment_date,p.Amount,p.Policy_no FROM collection_payments p WHERE p.Payment_type=1 AND p.Batch IS NULL AND p.Agent_no='$agent_id' ORDER BY p.Payment_date ASC";
                $transactions = DbHelper::getTableRawData($sql);

                $sql = "SELECT SUM(p.Amount) AS total_amount FROM collection_payments p WHERE p.Payment_type=1 AND p.Batch IS NULL AND p.Agent_no='$agent_id'";
                $result_amount = DbHelper::getTableRawData($sql);
                if(sizeof($result_amount )>0){
                    $total_amount = $result_amount[0]->total_amount;
                }

                $batch = null;
                $serial = 0;

                $sql = "SELECT p.CollectionSerial FROM agents_info p WHERE p.id=15";
                $result_serial = DbHelper::getTableRawData($sql);
                if(sizeof($result_serial )>0){
                    if($result_serial[0]->CollectionSerial == null){
                        $batch = $agent_no.'-1';
                        $serial=1;
                    }else{
                        $batch = $agent_no.'-'.$result_serial[0]->CollectionSerial;
                        $serial = $result_serial[0]->CollectionSerial;
                    }
                }

                //update collections_payments and create the Batch record update the collectionSerial in agents info
                //insert the batch
                //created_on,BatchNumber,PaymentDate,Agent,ExpectedAmount
                $table_data = array(
                    'created_on' => Carbon::now(),
                    'BatchNumber' => $batch,
                    'PaymentDate' => Carbon::now(),
                    'Agent' => $agent_id,
                    'ExpectedAmount' => $total_amount
                );
                $batch_id = DB::connection('sqlsrv')->table('BatchAllocation')->insertGetId($table_data);

                //update Agents info serial
                DB::connection('sqlsrv')->table('agents_info')
                ->where(array(
                    "id" => $agent_id
                ))
                ->update(array("CollectionSerial" => $serial+1));

                //update the batch_id in that agents records
                DB::connection('sqlsrv')->table('collection_payments')
                ->where(array(
                    "agent_no" => $agent_id,
                    "Batch" => null
                ))
                ->update(array("Batch" => $batch_id));

                
                $res = array(
                    'success' => true,
                    'batch' => $batch
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

    public function updateHubtel(Request $request)
    {
        try{
            $res=array();
            // put in a transaction the whole process of syncing data...
            DB::connection('sqlsrv')->transaction(function () use (&$res,$request) {
                $data = json_encode($request->input('Data'));
                $data = json_decode($data);
                //
                //$agent_name = "";//DbHelper::getColumnValue('agents_info', 'agent_no',$agent_no,'name');
               
                $table_data = array(
                    'Payment_Type' => 6,
                    'Policy_no' => $data->ClientReference,
                    'ResponseCode' => $request->input('ResponseCode'),
                    'Description' => $data->Description,
                    'TransactionId' => $data->TransactionId,
                    'ClientReference' => $data->ClientReference,
                    'Charges' => $data->Charges,
                    'AmountAfterCharges' => $data->AmountAfterCharges,
                    'AmountCharged' => $data->AmountCharged,
                    'Amount' => $data->AmountAfterCharges,
                    'Payment_date' => Carbon::now(),
                    'created_on' => Carbon::now()
                );

                //insert into 
                $record_id = DB::connection('sqlsrv')->table('collection_payments')->insertGetId($table_data);

                //health questionnaire
                $res = array(
                    'success' => true,
                    'id' => $record_id,
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
}
