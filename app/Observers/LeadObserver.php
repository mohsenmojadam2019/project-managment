<?php

namespace App\Observers;

use App\Events\LeadEvent;
use App\Models\Lead;
use App\Models\UniversalSearch;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeadImported;
use App\Models\LeadSetting;
use App\Models\LeadAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadObserver
{

    public function saving(Lead $lead)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $userID = (!is_null(user())) ? user()->id : null;
            $lead->last_updated_by = $userID;
        }

    } 

    public function creating(Lead $leadContact)
    {
        $leadContact->hash = md5(microtime());

    
        if (!isRunningInConsoleOrSeeding()) {
            if (company()) {
                $leadContact->company_id = company()->id;
            }

            if (request()->has('added_by')) {
                $leadContact->added_by = request('added_by');
            } else {
                $userID = (!is_null(user())) ? user()->id : null;
                $leadContact->added_by = $userID;
            }          
            $Settings = LeadSetting::select('status')->first();
            if ($Settings && $Settings->status == 1) {
                
                $leadAgents = LeadAgent::whereHas('user', function ($q) {
                    $q->where('status', 'active');
                })->with('user')->get();

                $leadAgentArray = $leadAgents->pluck('user_id')->toArray();
                                
                $leadCounts = DB::table('leads')
                    ->whereIn('lead_owner', $leadAgentArray)
                    ->whereNotNull('lead_owner')
                    ->select('lead_owner', DB::raw('count(*) as lead_count'))
                    ->groupBy('lead_owner')
                    ->get()
                    ->pluck('lead_count', 'lead_owner')
                    ->toArray();
                                
                if (is_null(request()->lead_owner)) {
                    if (!empty($leadAgentArray)) {
                        $agentsWithoutLeads = array_diff($leadAgentArray, array_keys($leadCounts));
                       
                        if (!empty($agentsWithoutLeads)) {
                            $leadContact->lead_owner = current($agentsWithoutLeads);
                        } else {
                            $minLeadCount = min($leadCounts);
                            $lead_owner = array_search($minLeadCount, $leadCounts);
                            $leadContact->lead_owner = $lead_owner;
                        }
                    }
                } else {
                    $leadContact->lead_owner = request()->lead_owner;
                }
            }
        }
    }

    public function created(Lead $leadContact)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (!session()->has('is_imported')) {

                event(new LeadEvent($leadContact, 'NewLeadCreated'));
            }else{

                // info('leads_count:' . session('leads_count'));
                // info('total_leads:' . session('total_leads'));

                if (session('leads_count') == (session('total_leads'))) {

                    info('check');
                    $admins = User::allAdmins(company()->id);
                    Notification::send($admins, new LeadImported());
                }

            }
        }
    }

    public function deleting(Lead $leadContact)
    {
        $notifyData = ['App\Notifications\LeadAgentAssigned', 'App\Notifications\NewDealCreated', 'App\Notifications\NewLeadCreated', 'App\Notifications\LeadImported'];
        \App\Models\Notification::deleteNotification($notifyData, $leadContact->id);
    }

    public function deleted(Lead $leadContact)
    {
        UniversalSearch::where('searchable_id', $leadContact->id)->where('module_type', 'lead')->delete();
    }

}
