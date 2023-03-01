<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\parametersController;
use App\Http\Controllers\syncController;
use App\Http\Controllers\premCalController;
use App\Http\Controllers\policyController;
use App\Http\Controllers\collectionsController;
use App\Http\Controllers\clientController;
use App\Http\Controllers\emailController;
use App\Http\Controllers\agentController;
use App\Http\Controllers\quotationController;
use App\Http\Controllers\claimController;
use App\Http\Controllers\reportsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('auth/tokenRequest', [AuthController::class, 'clientCredentialsAccessToken']);
Route::post('auth/getTest', [loginController::class, 'getTest']);//AgentRegistration

Route::post('auth/AgentRegistration', [loginController::class, 'AgentRegistration']);
Route::post('auth/AgentLogin', [loginController::class, 'AgentLogin']);
Route::post('auth/ClientRegistration', [loginController::class, 'ClientRegistration']);//
Route::post('auth/ClientLogin', [loginController::class, 'ClientLogin']);
Route::post('auth/POSLogin', [loginController::class, 'POSLogin']);
Route::post('auth/POSRegistration', [loginController::class, 'POSRegistration']);

Route::post('reports/getAgentProducts', [reportsController::class, 'getAgentProducts']);

Route::get('params/getParams', [parametersController::class, 'getCommonParams']);
Route::get('params/getInsuranceCoverTypes', [parametersController::class, 'getInsuranceCoverTypes']);

Route::post('sync/synProposal', [syncController::class, 'synProposal']);
Route::post('sync/syncImage', [syncController::class, 'syncImage']);

//policy 
Route::get('policy/getProposal', [policyController::class, 'getProposal']);
Route::get('policy/getPolicyDependants', [policyController::class, 'getPolicyDependants']);
Route::get('policy/getPolicyBeneficiaries', [policyController::class, 'getPolicyBeneficiaries']);
Route::get('policy/getPolicyDetails', [policyController::class, 'getPolicyDetails']);
Route::get('policy/getRequestedEndorsements', [policyController::class, 'getRequestedEndorsements']);
Route::post('policy/saveEndorsement', [policyController::class, 'saveEndorsement']);

Route::post('calc/ESB', [premCalController::class, 'esbcalculation']);
Route::post('calc/IdealFuneralPlan', [premCalController::class, 'IdealFuneralPlan']);
Route::post('calc/PremiumFuneralPlan', [premCalController::class, 'PremiumFuneralPlan']);
Route::post('calc/GEEP', [premCalController::class, 'GEEP']);
Route::post('calc/lifeSavingsPlan', [premCalController::class, 'lifeSavingsPlan']);
Route::post('calc/DepAnidaso', [premCalController::class, 'DepAnidaso']);//
Route::post('calc/LifeAnidaso', [premCalController::class, 'LifeAnidaso']);

//
Route::post('collections/getClientnPolicies', [collectionsController::class, 'getClientnPolicies']);
Route::post('collections/sendOTP', [collectionsController::class, 'sendOTP']);
Route::post('collections/receiveOTP', [collectionsController::class, 'receiveOTP']);
Route::post('collections/Remit', [collectionsController::class, 'Remit']);
Route::post('collections/updateRemit', [collectionsController::class, 'updateRemit']);
Route::post('collections/updateHubtel', [collectionsController::class, 'updateHubtel']);

Route::get('sms/SMS', [collectionsController::class, 'SMS']);

Route::post('quotation/saveQuote', [quotationController::class, 'saveQuote']);

//client
Route::get('client/getClientPolicies', [clientController::class, 'getClientPolicies']);
Route::get('client/getClientPremiums', [clientController::class, 'getClientPremiums']);
Route::get('client/getClientInvestment', [clientController::class, 'getClientInvestment']); 
Route::get('client/getClientDetails', [clientController::class, 'getClientDetails']);

//agents  
Route::get('agents/getAgentsPaymentMethods', [agentController::class, 'getAgentsPaymentMethods']);
Route::get('agents/getAgentsRegions', [agentController::class, 'getAgentsRegions']);
Route::get('agents/getAgentsBranches', [agentController::class, 'getAgentsBranches']);
Route::get('agents/getAgentsUnits', [agentController::class, 'getAgentsUnits']);
Route::get('agents/getAgentsTeams', [agentController::class, 'getAgentsTeams']);//
Route::get('agents/getAgentsChannel', [agentController::class, 'getAgentsChannel']);
Route::get('agents/getAgentsEducationLevel', [agentController::class, 'getAgentsEducationLevel']);
Route::get('agents/getAgentsFileChecklist', [agentController::class, 'getAgentsFileChecklist']);
Route::get('agents/getAgentsComplianceLicense', [agentController::class, 'getAgentsComplianceLicense']);
Route::post('agents/AgentsRegistration', [agentController::class, 'AgentsRegistration']);

Route::get('agents/getRegions', [agentController::class, 'getRegions']);
Route::get('agents/getBanks', [agentController::class, 'getBanks']);//
Route::get('agents/getBankBranches', [agentController::class, 'getBankBranches']);
Route::get('agents/getRecruitedBy', [agentController::class, 'getRecruitedBy']);
Route::get('agents/getIdTypes', [agentController::class, 'getIdTypes']);
Route::get('agents/getGender', [agentController::class, 'getGender']);
Route::post('agents/getMaritalStatus', [agentController::class, 'getMaritalStatus']);
Route::get('agents/getExprienceSector', [agentController::class, 'getExprienceSector']);
Route::get('agents/getRelationships', [agentController::class, 'getRelationships']);

//claims: 
Route::post('claims/insertClaimEntries', [claimController::class, 'insertClaimEntries']);
Route::get('claims/getClientClaims', [claimController::class, 'getClientClaims']);
Route::get('claims/getClaimAttachments', [claimController::class, 'getClaimAttachments']);

Route::group(['middleware' => ['client']], function () {
    
	

});

Route::get('/orders', function (Request $request) {
    echo "here";
    return "mister";
});
