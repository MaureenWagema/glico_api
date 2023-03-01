<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\DbHelper;
use Carbon\Carbon;

class premCalController extends Controller
{
    
    //TODO - Do calculations for life & micro products
    //1. ESB
    public function esbcalculation(Request $request)
    {
        try{

            $total_premium = $request->input('total_premium');
            $PlanCode = 2;//$request->input('PlanCode');
            $anb = $request->input('anb');
            $term = $request->input('term');
            $gender = $request->input('gender'); 
            $class_code = $request->input('class_code'); 
            $rider_array = array();
            
            $pol_fee = DbHelper::getColumnValue('planinfo', 'PlanOldName',$PlanCode,'policy_fee');
            
            $inv_prem = 0.7 * $total_premium;
            $transfer_charge = 0.05 * $total_premium;
            $rider_prem = 0;
            $tmp_risk = 0.3 * $total_premium;

            //prem 
            $sql = "SELECT * FROM premdistinfo WHERE PlanCode='$PlanCode'  AND ( '$total_premium' BETWEEN MinPrem AND MaxPrem)";
            $prem_rows = DbHelper::getTableRawData($sql);
            if(sizeof($prem_rows) > 0){
                //calculate transfer charge
                $transfer_charge = ($prem_rows[0]->TransferRate / 100)*$total_premium;
                $rider_prem = $tmp_risk - $pol_fee - $transfer_charge;
            }
            $term_rider_prem = 0.5 * $rider_prem;
            $hci_rider_prem = 0.33 * $rider_prem;
            $ai_rider_prem = 0.17 * $rider_prem;

            $sum_assured = 0;

            $term_rider_sa = 0;
            $hci_rider_sa = 0;
            $ai_rider_sa = 0;

            //term rider
            $sql = "SELECT * FROM rider_premuim_rates WHERE (rider_code='03' AND PlanCode='$PlanCode' AND age<='$anb' AND age2>='$anb') AND ('$term' between term_from and term_to)";
            $r_rows = DbHelper::getTableRawData($sql);
           
            if(sizeof($r_rows) > 0){
                $rider_rate = $r_rows[0]->normal_rate;
                $rider_rate_basis = DbHelper::getColumnValue('rider_info', 'rider_code','03','rate_basis');
                $term_rider_sa = ($term_rider_prem / $rider_rate) * $rider_rate_basis;
                $rider_array[] = array(
                    'r_rider' => "07", 
                    'r_sa' => number_format((float)$term_rider_sa, 2, '.', ''), 
                    'r_premium' => number_format((float)$term_rider_prem, 2, '.', ''),
                );

                //hci
                $sql = "SELECT * FROM rider_premuim_rates WHERE rider_code='02' AND age='$anb' AND PlanCode='$PlanCode' ";
                $r_rows = DbHelper::getTableRawData($sql);
            
                if(sizeof($r_rows) > 0){
                    if ($gender == "M") {
                        $hci_rider_rate = $r_rows[0]->normal_rate;
                    }
                    if ($gender == "F") {
                        $hci_rider_rate = $r_rows[0]->female_rate;
                    }
                    $hci_rider_rate_basis = DbHelper::getColumnValue('rider_info', 'rider_code','02','rate_basis');
                    $hci_rider_sa = ($hci_rider_prem / $hci_rider_rate) * $hci_rider_rate_basis;
                    $rider_array[] = array(
                        'r_rider' => "05", 
                        'r_sa' => number_format((float)$hci_rider_sa, 2, '.', ''), 
                        'r_premium' => number_format((float)$hci_rider_prem, 2, '.', ''),
                    );
                }

                //AI
                $ai_rider_rate = DbHelper::getColumnValue('paclass', 'class_code',$class_code,'rate');
                $ai_rider_rate_basis = DbHelper::getColumnValue('rider_info', 'rider_code','01','rate_basis');
                $ai_rider_sa = ($ai_rider_prem / $ai_rider_rate) * $ai_rider_rate_basis;
                $rider_array[] = array(
                    'r_rider' => "06", 
                    'r_sa' => number_format((float)$ai_rider_sa, 2, '.', ''), 
                    'r_premium' => number_format((float)$ai_rider_prem, 2, '.', ''),
                );

                //Also add up the sum assured to the sa value
                $sum_assured = $term_rider_sa + $hci_rider_sa + $ai_rider_sa;
                if ($term_rider_sa == 0 || $term_rider_sa == '0') {
                    //alert(inv_prem);
                    $inv_prem = $inv_prem + $term_rider_prem;
                    $rider_prem = $rider_prem - $term_rider_prem;
                } 
                if ($hci_rider_sa == 0 || $term_rider_sa == '0') {
                    //alert(inv_prem);
                    $inv_prem = $inv_prem + $hci_rider_prem;
                    $rider_prem = $rider_prem - $hci_rider_prem;
                }
            }
            


            $res = array(
                'success' => true,
                'sum_assured' => number_format((float)$sum_assured, 2, '.', ''),
                'policy_fee' => number_format((float)$pol_fee, 2, '.', ''),
                'inv_prem' => number_format((float)$inv_prem, 2, '.', ''),
                'rider_prem' => number_format((float)$rider_prem, 2, '.', ''),
                'transfer_charge' => number_format((float)$transfer_charge, 2, '.', ''),
                'riders' => $rider_array,
                'message' => 'ESB Premiums calculated Successfully!!'
            );
            return response()->json($res);
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

    //2. GEEP
    public function GEEP(Request $request){
        try{
            //
            $plan_code = $request->input('plan_code');
            $monthly_premium = $request->input('monthly_premium');
            $total_premium = 0;
            $anb = $request->input('anb');
            $term = $request->input('term');
            $paymode = $request->input('paymode');
            $plan_premium_table = '001';
            $plan_rate_basis = 1000;
            $rider_prem = 0;
            $pol_fee = 1;

            if($paymode == "M") { 
                $payment_factor = 1;
                $pol_fee *= 1;
            }
            if($paymode == "Q") { 
                $payment_factor = 3;
                $pol_fee *= 3;
            }
            if($paymode == "H") { 
                $payment_factor = 6;
                $pol_fee *= 6;
            }
            if($paymode == "Y") { 
                $payment_factor = 12; 
                $pol_fee *= 12;
            }
            $total_premium = $monthly_premium * $payment_factor;

            
            $divided_premium = $total_premium / $payment_factor;
            $res = array();
            $plan_id = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code'); 
            //$sql = "SELECT * FROM premdistinfo WHERE PlanCode='$PlanCode'  AND ( '$total_premium' BETWEEN MinPrem AND MaxPrem)";
            $sql_query = "SELECT * FROM premdistinfo WHERE PlanCode='$plan_id'  AND ('$divided_premium' BETWEEN MinPrem AND MaxPrem)";
			$results_prem = DB::select( DB::raw($sql_query));
            //$results_prem = $qry->first();
            if($results_prem){
                $investment_per = $results_prem[0]->InvestmentRate;
                $rider_per = $results_prem[0]->ProtectionRate;
                $protection_rate = $results_prem[0]->ProtectionRate;
                $cepa_rate = $results_prem[0]->CepaRate;
                $TransferRate = $results_prem[0]->TransferRate;
                //number_format((float)$foo, 2, '.', '');
                $protection_prem = number_format((float)($rider_per / 100) * $total_premium, 2, '.', '');
                $inv_prem = number_format((float)($investment_per / 100) * $total_premium, 2, '.', '');
                $inv_prem  -= $pol_fee;
                $cepa_prem = number_format((float)($cepa_rate / 100) * $total_premium, 2, '.', '');
                $transfer_prem = number_format((float)($TransferRate / 100) * $total_premium, 2, '.', '');

                $prem_rate_qry="SELECT * FROM premium_rate_setup WHERE plan_code='$plan_id' AND age='$anb' AND table_code='1' AND term='$term' ";
                $result_prem_rate = DB::select( DB::raw($prem_rate_qry));
                if($result_prem_rate) {
                    $rider_prem = number_format((float)($protection_rate / 100)*$divided_premium, 2, '.', '');
                    $sum_assured = number_format((float)($rider_prem * $plan_rate_basis)/$result_prem_rate[0]->rate, 2, '.', '');
                }

                //get the loading factor
                $qry = DB::table('paymentmodeinfo as p')
                ->select('*')
                ->where(array('p.OldPlanCode' => $plan_code,'p.OldPayMode' => $paymode));
                $results_paymode = $qry->first();
                $loadingfactor = $results_paymode->loadingfactor;

                $total_premium *= number_format((float)$loadingfactor, 2, '.', '');

            }
			
            //if success it will return premium per month plus suspense account.
            $res = array(
                'success' => true,
                'policy_fee' => $pol_fee,
                'Protection_premium' => $protection_prem,
                'Inv_prem' => $inv_prem,
                'Cepa_prem' => $cepa_prem,
                'Transfer_charge' => $transfer_prem,
                'Total_Premium' => $total_premium,
                'Sum_Assured' => $sum_assured,
                'loadingfactor' => $loadingfactor,
                'message' => 'Premium Calculated successfully'
            );	
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
    //3. Life Savings
    public function lifeSavingsPlan(Request $request){
        try{
            //
            $plan_code = $request->input('plan_code');
            $sum_assured = $request->input('sum_assured');
            $anb = $request->input('anb');
            $term = $request->input('term');
            $paymode = $request->input('paymode');
            $plan_premium_table = 2;//'002';
            $plan_rate_basis = 1000;
            $rider_prem = 0;
            $pol_fee = 1;
            $payment_factor = 1;

            if($paymode == "M") { 
                $payment_factor = 1;
                $pol_fee *= 1;
            }
            if($paymode == "Q") { 
                $payment_factor = 3;
                $pol_fee *= 3;
            }
            if($paymode == "H") { 
                $payment_factor = 6;
                $pol_fee *= 6;
            }
            if($paymode == "Y") { 
                $payment_factor = 12; 
                $pol_fee *= 12;
            }
            

            $res = array();
            $plan_id = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code'); 
            $qry = DB::table('premium_rate_setup as p')
                ->select('*')
                ->where(array('p.plan_code' => $plan_id,'p.age' => $anb,
                'p.term' => $term,'p.table_code' => $plan_premium_table));
            $results_prem = $qry->first();
            if($results_prem){
                $qry = DB::table('paymentmodeinfo as p')
                ->select('*')
                ->where(array('p.OldPlanCode' => $plan_code,'p.OldPayMode' => $paymode));
                $results_paymode = $qry->first();

                //Do the calculations here
                $loadingfactor = $results_paymode->loadingfactor;
                $w_rate2 = $results_prem->rate;
                $w_cover_period = $results_paymode->coverperiod;

                $w_tmp = ($w_rate2 / $plan_rate_basis) * $sum_assured;
                $basic_premium = $w_tmp * $w_cover_period;
                $modal_prem = ($basic_premium + $rider_prem + $pol_fee) * $loadingfactor;
            }
			
            //if success it will return premium per month plus suspense account.
            $res = array(
                'success' => true,
                'policy_fee' => $pol_fee,
                'basic_premium' => number_format((float)$basic_premium, 2, '.', ''),
                'modal_prem' => number_format((float)$modal_prem, 2, '.', ''),
                'message' => 'Premium Calculated successfully'
            );	
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
    //4. Funeral-ideal
    public function IdealFuneralPlan(Request $request){
        try{
            //
            $plan_code = $request->input('plan_code');
            $sum_assured = $request->input('sum_assured');
            $anb = $request->input('anb');
            $term = $request->input('term');
            $paymode = $request->input('paymode');
            $relationship_code = $request->input('relationship_code');
            //$this->getRelatioshipCategory($request->input('relationship_code'));
            if($relationship_code == null){
				$res = array(
					'success' => true,
					'message' => 'Not allowed for selected relationship'
				);
				return response()->json($res);
			}
            $plan_premium_table = '002';
            $plan_rate_basis = 1000;
            $rider_prem = 0;
            $pol_fee = 1;
            $payment_factor = 1;

            if($paymode == "M") { 
                $payment_factor = 1;
                $pol_fee *= 1;
            }
            if($paymode == "Q") { 
                $payment_factor = 3;
                $pol_fee *= 3;
            }
            if($paymode == "H") { 
                $payment_factor = 6;
                $pol_fee *= 6;
            }
            if($paymode == "Y") { 
                $payment_factor = 12; 
                $pol_fee *= 12;
            }

            
            $res = array();
            $qry = DB::table('paymentmodeinfo as p')
                ->select('*')
                ->where(array('p.OldPlanCode' => $plan_code,'p.OldPayMode' => $paymode));
            $results_paymode = $qry->first();

            //$plan_id = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code'); 
            
            if($results_paymode){
                //
                $qry = DB::table('funeralratesinfo as p')
                ->select('*')
                ->where(array('p.plan_code' => $plan_code,'p.code' => $relationship_code,'p.tableCode' => '002','p.Min_age' => $anb));
                $results_prem = $qry->first();

                

                //Do the calculations here
                $loadingfactor = $results_paymode->loadingfactor;
                $funeral_cover_period = $results_paymode->coverperiod;

                $funeral_rate = $results_prem->Rate;
                $funeral_rate_basis = 1000;

                $premiumVAR = (($funeral_rate / $funeral_rate_basis) * ($sum_assured * $funeral_cover_period));
                //$premiumVAR = ($premiumVAR + $pol_fee) * $loadingfactor;
            }
			
            //if success it will return premium per month plus suspense account.
            //ba_package, hci_sum_assured, days_covered, sum_assured, premium
            $res = array(
                'success' => true,
                'policy_fee' => $pol_fee,
                'sum_assured' => number_format((float)$sum_assured, 2, '.', ''),
                'premium' => number_format((float)$premiumVAR, 2, '.', ''),
                'loadingfactor' => $loadingfactor,
                'message' => 'Premium Calculated successfully'
            );
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
    //5. Funeral-premium
    public function PremiumFuneralPlan(Request $request){
        try{
            $plan_code = $request->input('plan_code');
            $sum_assured = $request->input('sum_assured');
            $anb = $request->input('anb');
            $term = $request->input('term');
            $paymode = $request->input('paymode');
            $relationship_code = $request->input('relationship_code');
            //$this->getRelatioshipCategory($request->input('relationship_code'));
            if($relationship_code == null){
				$res = array(
					'success' => true,
					'message' => 'Not allowed for selected relationship'
				);
				return response()->json($res);
			}
            $plan_premium_table = '001';
            $plan_rate_basis = 1000;
            $rider_prem = 0;
            $pol_fee = 1;
            $payment_factor = 1;

            if($paymode == "M") { 
                $payment_factor = 1;
                $pol_fee *= 1;
            }
            if($paymode == "Q") { 
                $payment_factor = 3;
                $pol_fee *= 3;
            }
            if($paymode == "H") { 
                $payment_factor = 6;
                $pol_fee *= 6;
            }
            if($paymode == "Y") { 
                $payment_factor = 12; 
                $pol_fee *= 12;
            }
            $res = array();
            $qry = DB::table('paymentmodeinfo as p')
                ->select('*')
                ->where(array('p.OldPlanCode' => '29','p.OldPayMode' => $paymode));
            $results_paymode = $qry->first();
            if($results_paymode){
                //,'p.tableCode' => '002'
                $qry = DB::table('funeralratesinfo as p')
                ->select('*')
                ->where(array('p.plan_code' => 37,'p.CategoryCode' => $relationship_code,'p.Min_age' => $anb));
                $results_prem = $qry->first();

                //Do the calculations here
                $loadingfactor = $results_paymode->loadingfactor;
                $funeral_cover_period = $results_paymode->coverperiod;

                $funeral_rate = $results_prem->Rate;
                $funeral_rate_basis = 1000;

                $premiumVAR = (($funeral_rate / $funeral_rate_basis) * ($sum_assured * $funeral_cover_period));
                //$premiumVAR = ($premiumVAR + $pol_fee) * $loadingfactor;
            }
			
            //if success it will return premium per month plus suspense account.
            //ba_package, hci_sum_assured, days_covered, sum_assured, premium
            $res = array(
                'success' => true,
                'policy_fee' => $pol_fee,
                'sum_assured' => number_format((float)$sum_assured, 2, '.', ''),
                'premium' => number_format((float)$premiumVAR, 2, '.', ''),
                'loadingfactor' => $loadingfactor,
                'message' => 'Premium Calculated successfully'
            );
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

    //6. LifeAnidaso
    public function LifeAnidaso(Request $request)
    {
        try{
            //
            $plan_code = $request->input('plan_code');
            $sum_assured = $request->input('sum_assured');
            $anb = $request->input('anb');
            $plan_premium_table = 1;
            $plan_rate_basis = DbHelper::getColumnValue('planinfo','PlanOldName',$plan_code,'rate_basis');
            $life_premium = 0;
            $pol_fee  = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'policy_fee');
            
            
            $res = array();
            $plan_id = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code'); 
            $qry = DB::table('premium_rate_setup as p')
                ->select('*')
                ->where(array('p.plan_code' => $plan_id,'p.age' => $anb,
                'p.table_code' => $plan_premium_table));
            $results_prem = $qry->first();
            if($results_prem){
                //let life_prem = (parseFloat(viewModel.sum_assured()) * parseFloat(results.rows.item(0).rate)) / 1000;
                $life_premium = ($sum_assured * $results_prem->rate) / $plan_rate_basis;
            }
			
            //if success it will return premium per month plus suspense account.
            $res = array(
                'success' => true,
                'policy_fee' => $pol_fee,
                'life_premium' => number_format((float)$life_premium, 2, '.', ''),
                'message' => 'Premium Calculated successfully'
            );	
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
    //7. Anidaso
    public function DepAnidaso(Request $request)
    {
        try{
            $plan_code = $request->input('plan_code');
            $sum_assured = $request->input('sum_assured');
            $anb = $request->input('anb');
            $relationship_code = $request->input('relationship_code');
            $category = $relationship_code;
            $plan_premium_table = 1;
            $plan_rate_basis = 1000;//DbHelper::getColumnValue('planinfo','PlanOldName',$plan_code,'rate_basis');
            $life_premium = 0;
            $dp_premium = 0;
            //$pol_fee  = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'policy_fee');
            
            
            $res = array();
            $plan_id = DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code'); 

            $qry = DB::table('plan_rider_config as p')
                ->select('*')
                ->where(array('p.plan_code' => $plan_id,'p.rider_code' => '03'));
            $results_prem = $qry->first();
            if($results_prem){
                $plan_rider_rate = $results_prem->rate;
                $plan_rider_rate_two = $results_prem->rate2;
            }

            DbHelper::getColumnValue('planinfo', 'PlanOldName',$plan_code,'plan_code');  
            $msg = 'Premium Calculated successfully';
            if ($relationship_code == "B") {
                //spouse
                $dp_premium = ($sum_assured * $plan_rider_rate) / $plan_rate_basis;
            } else if ($relationship_code == "C") {
                //child
                $dp_premium = ($sum_assured * $plan_rider_rate_two) / $plan_rate_basis;
            } else if ($relationship_code == "D") {
                //parent
                $qry = DB::table('parentspremratesinfo as p')
                ->select('*')
                ->where(array('p.plan_code' => $plan_code,'p.sumAssured' => $sum_assured));
                $results_prem = $qry->first();
                $results_prem;
                if($results_prem){
                    $dp_premium = $results_prem->premiumRate;
                }
            } else {
                $msg = "Not Applicable";
            }
			
            //if success it will return premium per month plus suspense account.
            $res = array(
                'success' => true,
                'dp_premium' => number_format((float)$dp_premium, 2, '.', ''),
                'message' => $msg
            );
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
