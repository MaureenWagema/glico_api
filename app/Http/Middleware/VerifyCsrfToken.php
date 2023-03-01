<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'auth/tokenRequest',
        'auth/getTest',
        'auth/AgentRegistration',
        'auth/AgentLogin',
        'auth/ClientRegistration',
        'auth/ClientLogin',
        'auth/POSLogin',
        'auth/POSRegistration',

        'reports/getAgentProducts',

        'params/getParams',

        'sync/synProposal',
        'sync/syncImage',
        
        'calc/ESB',
        'calc/IdealFuneralPlan',
        'calc/PremiumFuneralPlan',
        'calc/GEEP',
        'calc/lifeSavingsPlan',
        'calc/DepAnidaso',
        'calc/LifeAnidaso',

        'policy/getProposal',
        'policy/getPolicyDependants',
        'policy/getPolicyBeneficiaries',
        'policy/getPolicyDetails',
        'policy/getRequestedEndorsements',
        'policy/saveEndorsement',


        'collections/getClientnPolicies',
        'collections/sendOTP',
        'collections/receiveOTP',
        'collections/Remit',
        'collections/updateRemit',
        'collections/updateHubtel',
        'client/getClientPolicies',
        'client/getClientPremiums',
        'client/getClientInvestment',
        'client/getClientDetails',

        'claims/insertClaimEntries',
        'claims/getClientClaims',
        'claims/getClaimAttachments',

        'agents/getAgentsPaymentMethods',
        'agents/getAgentsRegions',
        'agents/getAgentsBranches',
        'agents/getAgentsUnits',
        'agents/getAgentsTeams',
        'agents/getAgentsChannel',
        'agents/getAgentsEducationLevel',
        'agents/getAgentsFileChecklist',
        'agents/AgentsRegistration',
        'agents/getAgentsComplianceLicense',

        'agents/getRegions',
        'agents/getBanks',
        'agents/getBankBranches',
        'agents/getRecruitedBy',
        'agents/getIdTypes',
        'agents/getGender',
        'agents/getMaritalStatus',

        'agents/getExprienceSector',
        'agents/getRelationships',

        'quotation/saveQuote',
        'sms/SMS',

        'orders',
    ];
}
